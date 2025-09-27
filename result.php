<?php
declare(strict_types=1);

include 'session.php';
include 'config.php';
require_once __DIR__ . '/geminiapi.php';

$user_id = (int) $_SESSION['id'];

$errors = [];
$sessionRow = null;
$sessionData = null;
$sessionId = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $topic = trim($_POST['topic'] ?? '');
    $knowledge = trim($_POST['knowledge'] ?? '');

    if ($topic === '' || $knowledge === '') {
        $errors[] = 'Topic and blurt text are required.';
  } else {
    try {
      $geminiResult = generateGeminiFeedback($topic, $knowledge);
      $sessionData = $geminiResult['data'];
      $rawJson = $geminiResult['raw'];

      $sessionData['userBlurt'] = trim($knowledge);
      $sessionData['topic'] = trim($topic);

      try {
        $rawJson = json_encode($sessionData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
      } catch (JsonException $encodeException) {
        throw new RuntimeException('Failed to encode the blurting session: ' . $encodeException->getMessage(), (int) $encodeException->getCode(), $encodeException);
      }

      $sessionScore = null;
      if (isset($sessionData['accuracyRating']) && is_numeric($sessionData['accuracyRating'])) {
        $sessionScore = max(0, min(100, (int) $sessionData['accuracyRating']));
      }

      $shortTopic = trim((string) ($sessionData['shortTopicTitle'] ?? $topic));
      if ($shortTopic === '') {
        $shortTopic = $topic;
      }

      if ($sessionScore === null) {
        $stmt = mysqli_prepare(
          $link,
          'INSERT INTO session_history (userid, sessionJSON, time_created, session_score, session_topicName) VALUES (?, ?, NOW(), NULL, ?)' // phpcs:ignore
        );
        if ($stmt === false) {
          throw new RuntimeException('Database error during preparation: ' . mysqli_error($link));
        }
        mysqli_stmt_bind_param($stmt, 'iss', $user_id, $rawJson, $shortTopic);
      } else {
        $stmt = mysqli_prepare(
          $link,
          'INSERT INTO session_history (userid, sessionJSON, time_created, session_score, session_topicName) VALUES (?, ?, NOW(), ?, ?)' // phpcs:ignore
        );
        if ($stmt === false) {
          throw new RuntimeException('Database error during preparation: ' . mysqli_error($link));
        }
        mysqli_stmt_bind_param($stmt, 'isis', $user_id, $rawJson, $sessionScore, $shortTopic);
      }

      if (!mysqli_stmt_execute($stmt)) {
        $message = mysqli_stmt_error($stmt) ?: 'Unknown database error.';
        mysqli_stmt_close($stmt);
        throw new RuntimeException('Failed to save the blurting session: ' . $message);
      }

      $sessionId = mysqli_insert_id($link);
      mysqli_stmt_close($stmt);

      header('Location: result.php?sessionid=' . $sessionId);
      exit();
    } catch (Throwable $e) {
      $errors[] = getProcessingErrorMessage($e);
      error_log('Gemini processing failed: ' . $e->getMessage());
    }
  }
}

if (isset($_GET['sessionid'])) {
    $sessionId = (int) $_GET['sessionid'];
    if ($sessionId > 0) {
        $stmt = mysqli_prepare(
            $link,
            'SELECT sessionid, sessionJSON, time_created, session_score, session_topicName FROM session_history WHERE sessionid = ? AND userid = ?'
        );
        if ($stmt === false) {
            $errors[] = 'Unable to look up the requested session right now.';
        } else {
            mysqli_stmt_bind_param($stmt, 'ii', $sessionId, $user_id);
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                $sessionRow = mysqli_fetch_assoc($result) ?: null;
            } else {
                $errors[] = 'Unable to look up the requested session right now.';
            }
            mysqli_stmt_close($stmt);
        }

        if ($sessionRow) {
            try {
                $sessionData = json_decode($sessionRow['sessionJSON'], true, 512, JSON_THROW_ON_ERROR);
            } catch (Throwable $e) {
                $errors[] = 'We couldn\'t read the stored AI feedback.';
                error_log('Session JSON parse failed: ' . $e->getMessage());
                $sessionData = null;
            }
        } elseif (empty($errors)) {
            $errors[] = 'Session not found.';
        }
    } elseif (empty($errors)) {
        $errors[] = 'Invalid session selected.';
    }
} elseif ($_SERVER['REQUEST_METHOD'] !== 'POST' && empty($errors)) {
    $errors[] = 'No session selected yet.';
}

$score = null;
if ($sessionRow !== null && isset($sessionRow['session_score']) && $sessionRow['session_score'] !== null) {
    $score = max(0, min(100, (int) $sessionRow['session_score']));
} elseif ($sessionData !== null && isset($sessionData['accuracyRating']) && is_numeric($sessionData['accuracyRating'])) {
    $score = max(0, min(100, (int) $sessionData['accuracyRating']));
}

$shortTopicTitle = '';
if ($sessionRow !== null && isset($sessionRow['session_topicName'])) {
    $shortTopicTitle = (string) $sessionRow['session_topicName'];
} elseif ($sessionData !== null && isset($sessionData['shortTopicTitle'])) {
    $shortTopicTitle = (string) $sessionData['shortTopicTitle'];
}
$shortTopicTitle = trim($shortTopicTitle);

$overallFeedback = null;
if ($sessionData !== null && isset($sessionData['overallFeedback']) && is_string($sessionData['overallFeedback'])) {
    $overallFeedback = trim($sessionData['overallFeedback']);
}

$mistakes = [];
if ($sessionData !== null && isset($sessionData['mistakes']) && is_array($sessionData['mistakes'])) {
    foreach ($sessionData['mistakes'] as $mistake) {
        if (!is_array($mistake)) {
            continue;
        }
        $incorrect = isset($mistake['incorrectPhrase']) ? trim((string) $mistake['incorrectPhrase']) : '';
        $explanation = isset($mistake['explanation']) ? trim((string) $mistake['explanation']) : '';
        $correction = isset($mistake['correction']) ? trim((string) $mistake['correction']) : '';
        if ($incorrect === '' && $explanation === '' && $correction === '') {
            continue;
        }
        $mistakes[] = [
            'incorrectPhrase' => $incorrect,
            'explanation' => $explanation,
            'correction' => $correction,
        ];
    }
}

$suggestions = [];
if ($sessionData !== null && isset($sessionData['suggestions'])) {
    $rawSuggestions = $sessionData['suggestions'];
    if (is_array($rawSuggestions)) {
        foreach ($rawSuggestions as $item) {
            $trimmed = trim((string) $item);
            if ($trimmed !== '') {
                $suggestions[] = $trimmed;
            }
        }
    } elseif (is_string($rawSuggestions)) {
      $normalized = preg_replace('/\r\n?|\n/', "\n", trim($rawSuggestions));
      if ($normalized !== '') {
  if (preg_match_all('/(?:^|\n)\s*(?:\d+[\.)]|[-\*•])\s*(.+?)(?=(?:\n\s*(?:\d+[\.)]|[-\*•])\s*)|\z)/s', $normalized, $matches)) {
          foreach ($matches[1] as $item) {
            $trimmed = trim(preg_replace('/\s+/', ' ', $item));
            if ($trimmed !== '') {
              $suggestions[] = $trimmed;
            }
          }
        }

        if (empty($suggestions)) {
          $maybeList = preg_split('/\n+|(?<=\.)\s+(?=[A-Z])/', $normalized) ?: [];
          foreach ($maybeList as $item) {
            $trimmed = trim($item);
            if ($trimmed !== '') {
              $suggestions[] = $trimmed;
            }
          }
        }

        if (empty($suggestions)) {
          $suggestions[] = $normalized;
        }
      }
    }
}

$userBlurt = null;
$correctStatements = [];

if ($sessionData !== null) {
    if (isset($sessionData['userBlurt']) && is_string($sessionData['userBlurt'])) {
        $userBlurt = trim($sessionData['userBlurt']);
    }

    if (isset($sessionData['correctStatements']) && is_array($sessionData['correctStatements'])) {
        foreach ($sessionData['correctStatements'] as $item) {
            $trimmed = trim((string) $item);
            if ($trimmed !== '' && !in_array($trimmed, $correctStatements, true)) {
                $correctStatements[] = $trimmed;
            }
        }
    }
}

if ($userBlurt !== null && $userBlurt !== '' && empty($correctStatements)) {
    $normalizedBlurt = preg_replace('/\r\n?|\n/', "\n", $userBlurt);
    $lines = $normalizedBlurt === null ? [$userBlurt] : (preg_split('/\n+/', $normalizedBlurt) ?: [$userBlurt]);

    $statements = [];
    foreach ($lines as $line) {
        $line = trim((string) $line);
        if ($line === '') {
            continue;
        }

        $line = preg_replace('/^\s*(?:[-\*•]\s+|\d+[\.)]\s+)/', '', $line) ?? $line;
        $sentenceParts = preg_split('/(?<=[.!?])\s+/', $line) ?: [];

        if (count($sentenceParts) > 1) {
            foreach ($sentenceParts as $part) {
                $part = trim($part);
                if ($part !== '') {
                    $statements[] = $part;
                }
            }
        } else {
            $statements[] = $line;
        }
    }

    if (empty($statements)) {
        $statements = [$userBlurt];
    }

    $incorrectPhrases = [];
    foreach ($mistakes as $mistake) {
        if ($mistake['incorrectPhrase'] !== '') {
            $incorrectPhrases[] = $mistake['incorrectPhrase'];
        }
    }

    foreach ($statements as $statement) {
        $cleanStatement = preg_replace('/\s+/', ' ', trim($statement));
        if ($cleanStatement === '') {
            continue;
        }

        $containsMistake = false;
        foreach ($incorrectPhrases as $phrase) {
            $phrase = trim($phrase);
            if ($phrase === '') {
                continue;
            }

            if (function_exists('mb_stripos')) {
                if (mb_stripos($cleanStatement, $phrase) !== false) {
                    $containsMistake = true;
                    break;
                }
            } else {
                if (stripos($cleanStatement, $phrase) !== false) {
                    $containsMistake = true;
                    break;
                }
            }
        }

        if (!$containsMistake && !in_array($cleanStatement, $correctStatements, true)) {
            $correctStatements[] = $cleanStatement;
        }
    }

    if (empty($incorrectPhrases) && empty($correctStatements)) {
        $correctStatements = array_map(static fn ($value) => preg_replace('/\s+/', ' ', trim((string) $value)), $statements);
        $correctStatements = array_values(array_filter($correctStatements, static fn ($value) => $value !== ''));
    }
}

$timeCreated = null;
if ($sessionRow !== null && !empty($sessionRow['time_created'])) {
    $timestamp = strtotime($sessionRow['time_created']);
    if ($timestamp !== false) {
        $timeCreated = date('M j, Y g:i A', $timestamp);
    }
}

function getProcessingErrorMessage(Throwable $e): string
{
  $message = $e->getMessage() ?? '';

  if (stripos($message, 'GEMINI_API_KEY environment variable is not set') !== false) {
    return 'Gemini is not configured yet. Please add a valid GEMINI_API_KEY on the server and try again.';
  }

  if (stripos($message, 'Failed to save the blurting session') !== false || stripos($message, 'Database error') !== false) {
    return 'We had trouble saving your blurting session. Please try again in a moment.';
  }

  if (stripos($message, 'Gemini request failed') !== false) {
    $detail = '';
    if (preg_match('/Gemini request failed:\s*(.+)/i', $message, $matches)) {
      $detail = trim($matches[1]);
    }

    if ($detail !== '') {
      if (stripos($detail, 'UNAUTHENTICATED') !== false || stripos($detail, '401') !== false || stripos($detail, '403') !== false) {
        return 'Gemini rejected the request. Double-check that your GEMINI_API_KEY is correct and has access.';
      }

      if (stripos($detail, 'RESOURCE_EXHAUSTED') !== false || stripos($detail, '429') !== false) {
        return 'Gemini is throttling requests right now. Wait a few moments before trying again.';
      }

      if (stripos($detail, 'UNAVAILABLE') !== false || stripos($detail, 'timeout') !== false) {
        return 'Gemini is temporarily unavailable. Please retry shortly.';
      }

      $shortDetail = mb_strlen($detail) > 180 ? mb_substr($detail, 0, 180) . '…' : $detail;
      return 'Gemini could not complete the request: ' . $shortDetail;
    }

    return 'Gemini could not complete the request. Please retry shortly.';
  }

  return 'We couldn\'t process your blurt right now. Please try again soon.';
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Blurt It! Results</title>
    <link rel="manifest" crossorigin="use-credentials" href="manifest.json" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"
    />
    <link rel="stylesheet" href="css/output.css">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap"
      rel="stylesheet"
    />
    <style>
      body {
        font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI",
          Roboto, Helvetica, Arial, sans-serif;
      }
      .h-dvh {
        height: 100vh;
      }
      @supports (height: 100dvh) {
        .h-dvh {
          height: 100dvh;
        }
      }
      body {
        padding-top: env(safe-area-inset-top);
      }
      #progress-circle {
        transition: stroke-dashoffset 0.5s ease-out;
      }
    </style>
  </head>
  <body class="bg-gray-100 ">
    <div
      class="flex flex-col w-full h-dvh md:h-auto bg-white md:max-w-lg md:mx-auto md:shadow-2xl md:rounded-2xl md:my-8 relative"
      id="result-root"
      data-score="<?= $score !== null ? $score : 0 ?>"
    >
      <main
        class="flex-1 flex flex-col overflow-y-auto px-4 pt-10 pb-40 md:px-6 md:pt-12 md:pb-32"
      >
        <?php if (!empty($errors)): ?>
          <div class="flex flex-col items-center justify-center h-full text-center">
            <?php foreach ($errors as $error): ?>
              <div class="w-full max-w-md p-4 bg-red-100 border border-red-200 text-red-700 rounded-xl text-sm">
                <?= e($error) ?>
              </div>
            <?php endforeach; ?>
            <a href="index.php" class="mt-2 w-full py-3 bg-white border-2 border-blue-500 text-blue-500 font-bold rounded-xl hover:bg-blue-50 active:bg-blue-100 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
              Back to blurting
            </a>
          </div>
        <?php endif; ?>

        <?php if ($sessionRow !== null && $sessionData !== null): ?>
          <div class="text-center mb-6">
            <div class="animate__animated animate__fadeInDown text-3xl font-bold">
              <?= e($shortTopicTitle !== '' ? $shortTopicTitle : 'Session Results') ?>
            </div>
            <div class="animate__animated animate__fadeInDown animate__delay-0-5s text-gray-500 text-xl font-medium mb-2">
              Results
            </div>
            <?php if ($timeCreated !== null): ?>
              <div class="text-xs uppercase tracking-wide text-gray-400">
                Recorded <?= e($timeCreated) ?>
              </div>
            <?php endif; ?>
            <div class="flex justify-center mb-2 mt-4 animate__animated animate__fadeInUp">
              <div class="relative w-72 h-72">
                <svg class="w-72 h-72" viewBox="0 0 140 140" style="transform: rotate(-90deg);">
                  <circle cx="70" cy="70" r="60" stroke="#e5e7eb" stroke-width="12" fill="none" />
                  <circle
                    id="progress-circle"
                    cx="70" cy="70" r="60"
                    stroke="#ef4444"
                    stroke-width="12"
                    fill="none"
                    stroke-dasharray="377"
                    stroke-dashoffset="377"
                    stroke-linecap="round" />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                  <span id="progress-text" class="text-3xl font-bold">0%</span>
                  <span id="progress-subtext" class="text-xs text-gray-400 mt-1">You could do better!</span>
                </div>
              </div>
            </div>
          </div>

          <?php if ($overallFeedback !== null && $overallFeedback !== ''): ?>
            <div class="flex items-start gap-2 bg-gray-100 rounded-xl p-4 mb-4">
              <span class="text-lg mt-1" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                  <path d="M2.5 3H21.5V17H6.5L2.5 20.5V3Z" stroke="#2F3036" stroke-width="2" stroke-linecap="square"/>
                </svg>
              </span>
              <div class="text-justify text-sm text-gray-700">
                <?= e($overallFeedback) ?>
              </div>
            </div>
          <?php endif; ?>

          <hr>

          <div class="flex items-center gap-2 font-bold text-base mb-2 mt-4">
            <span class="text-red-500 text-xl" aria-hidden="true">
              <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 26 26" fill="none">
                <path d="M7.79167 25.5L0.5 18.2083V7.79167L7.79167 0.5H18.2083L25.5 7.79167V18.2083L18.2083 25.5H7.79167ZM9.04167 18.9028L13 14.9444L16.9583 18.9028L18.9028 16.9583L14.9444 13L18.9028 9.04167L16.9583 7.09722L13 11.0556L9.04167 7.09722L7.09722 9.04167L11.0556 13L7.09722 16.9583L9.04167 18.9028Z" fill="#ED3241"/>
              </svg>
            </span>
            Mistakes Found
          </div>

          <?php if (!empty($mistakes)): ?>
            <?php foreach ($mistakes as $mistake): ?>
              <div class="bg-red-100 rounded-lg p-3 flex gap-2 mb-2">
                <span class="text-red-500 text-lg mt-1" aria-hidden="true">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 26 26" fill="none">
                    <path d="M7.79167 25.5L0.5 18.2083V7.79167L7.79167 0.5H18.2083L25.5 7.79167V18.2083L18.2083 25.5H7.79167ZM9.04167 18.9028L13 14.9444L16.9583 18.9028L18.9028 16.9583L14.9444 13L18.9028 9.04167L16.9583 7.09722L13 11.0556L9.04167 7.09722L7.09722 9.04167L11.0556 13L7.09722 16.9583L9.04167 18.9028Z" fill="#ED3241"/>
                  </svg>
                </span>
                <div>
                  <?php if ($mistake['incorrectPhrase'] !== ''): ?>
                    <div class="font-semibold text-sm mb-1">“<?= e($mistake['incorrectPhrase']) ?>”</div>
                  <?php endif; ?>
                  <?php if ($mistake['explanation'] !== ''): ?>
                    <div class="text-xs text-gray-700 mb-1"><?= e($mistake['explanation']) ?></div>
                  <?php endif; ?>
                  <?php if ($mistake['correction'] !== ''): ?>
                    <div class="text-xs text-gray-600 italic">Correction: <?= e($mistake['correction']) ?></div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="bg-green-100 rounded-lg p-3 text-sm text-green-700 mb-2">
              No factual mistakes detected—great job!
            </div>
          <?php endif; ?>

          <?php if (!empty($correctStatements)): ?>
            <div class="flex items-center gap-2 font-bold text-base mb-2 mt-5">
              <span class="text-emerald-500 text-xl" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 26 26" fill="none">
                  <path d="M23.25 13C23.25 18.6601 18.6601 23.25 13 23.25C7.33992 23.25 2.75 18.6601 2.75 13C2.75 7.33992 7.33992 2.75 13 2.75C18.6601 2.75 23.25 7.33992 23.25 13Z" stroke="#10B981" stroke-width="2"/>
                  <path d="M18.0625 10.8125L12.0208 16.8542L8.9375 13.7708" stroke="#10B981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </span>
              Correct Answers
            </div>
            <div class="space-y-2">
              <?php foreach ($correctStatements as $statement): ?>
                <div class="bg-green-100 rounded-lg p-3 flex gap-2 mb-2">
                  <span class="text-emerald-500 text-lg mt-1" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                      <path d="M9.99996 18.3333C14.6023 18.3333 18.3333 14.6024 18.3333 10C18.3333 5.39763 14.6023 1.66667 9.99996 1.66667C5.39759 1.66667 1.66663 5.39763 1.66663 10C1.66663 14.6024 5.39759 18.3333 9.99996 18.3333Z" stroke="#10B981" stroke-width="1.8"/>
                      <path d="M13.9584 7.625L9.04171 12.5417L6.87504 10.375" stroke="#10B981" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </span>
                  <div class="text-sm text-gray-700">
                    <?= e($statement) ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <div class="flex items-center gap-2 font-bold text-base mb-2 mt-5">
            <span class="text-green-500 text-xl" aria-hidden="true">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M15.5 13C14.5717 13 13.6815 13.3687 13.0251 14.0251C12.3687 14.6815 12 15.5717 12 16.5M12 16.5V17.5M12 16.5C12 15.5717 11.6313 14.6815 10.9749 14.0251C10.3185 13.3687 9.42826 13 8.5 13M12 17.5C12 18.4283 12.3687 19.3185 13.0251 19.9749C13.6815 20.6313 14.5717 21 15.5 21C16.4283 21 17.3185 20.6313 17.9749 19.9749C18.6313 19.3185 19 18.4283 19 17.5V15.7M12 17.5C12 18.4283 11.6313 19.3185 10.9749 19.9749C10.3185 20.6313 9.42826 21 8.5 21C7.57174 21 6.6815 20.6313 6.02513 19.9749C5.36875 19.3185 5 18.4283 5 17.5V15.7" stroke="#14AE5C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M17.5 16C18.4283 16 19.3185 15.6313 19.9749 14.9749C20.6313 14.3185 21 13.4283 21 12.5C21 11.5717 20.6313 10.6815 19.9749 10.0251C19.3185 9.36875 18.4283 9 17.5 9H17" stroke="#14AE5C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M19 9.3V6.5C19 5.57174 18.6313 4.6815 17.9749 4.02513C17.3185 3.36875 16.4283 3 15.5 3C14.5717 3 13.6815 3.36875 13.0251 4.02513C12.3687 4.6815 12 5.57174 12 6.5M6.5 16C5.57174 16 4.6815 15.6313 4.02513 14.9749C3.36875 14.3185 3 13.4283 3 12.5C3 11.5717 3.36875 10.6815 4.02513 10.0251C4.6815 9.36875 5.57174 9 6.5 9H7" stroke="#14AE5C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M5 9.3V6.5C5 5.57174 5.36875 4.6815 6.02513 4.02513C6.6815 3.36875 7.57174 3 8.5 3C9.42826 3 10.3185 3.36875 10.9749 4.02513C11.6313 4.6815 12 5.57174 12 6.5V16.5" stroke="#14AE5C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
            Suggestions
          </div>
          <?php if (!empty($suggestions)): ?>
            <div class="space-y-2">
              <?php foreach ($suggestions as $suggestion): ?>
                <div class="bg-green-50 rounded-xl p-4 flex gap-2">
                  <span class="text-green-500 text-lg mt-1" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                      <path d="M8.14132 20.6086L18.1416 10.6084L13.3916 5.85845L3.3914 15.8587C3.25373 15.9965 3.15593 16.1691 3.10839 16.358L2 22L7.64093 20.8916C7.83033 20.8443 8.00358 20.7463 8.14132 20.6086ZM21.3699 7.38006C21.7733 6.97646 22 6.42914 22 5.85845C22 5.28776 21.7733 4.74044 21.3699 4.33684L19.6632 2.63014C19.2596 2.22666 18.7122 2 18.1416 2C17.5709 2 17.0235 2.22666 16.6199 2.63014L14.9132 4.33684L19.6632 9.08676L21.3699 7.38006Z" fill="#1F2024"/>
                    </svg>
                  </span>
                  <div class="text-sm text-gray-700">
                    <?= e($suggestion) ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="bg-blue-50 rounded-xl p-4 text-sm text-blue-600">
              Keep exploring this topic to strengthen your understanding.
            </div>
          <?php endif; ?>

          <?php if ($userBlurt !== null && $userBlurt !== ''): ?>
            <div class="flex items-center gap-2 font-bold text-base mb-2 mt-5">
              <span class="text-blue-500 text-xl" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 26 26" fill="none">
                  <path d="M21.6667 22.75H4.33333C3.04467 22.75 2 21.7053 2 20.4167V5.58333C2 4.29467 3.04467 3.25 4.33333 3.25H7.58333C8.09867 3.25 8.54167 3.59067 8.68467 4.084L9.31533 6.166C9.45833 6.65933 9.90133 7 10.4167 7H21.6667C22.9553 7 24 8.04467 24 9.33333V20.4167C24 21.7053 22.9553 22.75 21.6667 22.75Z" fill="#3B82F6"/>
                </svg>
              </span>
              Your Blurt
            </div>
            <div class="text-sm bg-gray-100 rounded-lg p-3 flex gap-2 mb-2">
              <?= e($userBlurt) ?>
            </div>
          <?php endif; ?>
        <?php elseif (empty($errors)): ?>
          <div class="text-center text-gray-500">
            Nothing to show yet. Start by creating a new blurt.
            <div class="mt-4">
              <a href="index.php" class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-blue-500 rounded-lg hover:bg-blue-600 transition">
                Create a blurt
              </a>
            </div>
          </div>
        <?php endif; ?>
      </main>

      <footer
        class="mt-auto relative border-t border-gray-200 pb-[calc(0.25rem+env(safe-area-inset-bottom))] h-28 md:absolute md:bottom-0 md:left-0 md:right-0 md:rounded-b-2xl bg-white"
      >
        <nav
          class="relative flex justify-around items-center h-full pt-2 pb-6 text-gray-500"
        >
          <a
            href="sessions.php"
            class="flex flex-col items-center justify-center space-y-1 text-blue-500 font-medium"
          >
            <img
              src="img/compass.svg"
              alt="Sessions"
              class="h-7 w-7"
            />
            <span class="text-xs font-semibold">Sessions</span>
          </a>

          <div class="w-20"></div>

          <a
            href="profile.php"
            class="flex flex-col items-center justify-center space-y-1 hover:text-blue-500 transition-colors"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-7 w-7"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
              />
            </svg>
            <span class="text-xs font-semibold">Profile</span>
          </a>
        </nav>

        <div
          class="absolute -top-10 md:-top-10 left-1/2 -translate-x-1/2 transform"
        >
          <div class="text-center">
            <a href="index.php"
              aria-label="Create new blurt"
              class="bg-blue-500 w-20 h-20 rounded-full flex items-center justify-center text-white shadow-lg shadow-blue-500/30 hover:bg-blue-600 transition-all active:scale-95"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-9 w-9"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="3"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M12 6v12m6-6H6"
                />
              </svg>
              <span class="sr-only">Blurt It!</span>
            </a>
            <span class="block text-xs font-bold text-blue-500 mt-2"
              >Blurt It!</span
            >
          </div>
        </div>
      </footer>
    </div>

    <script>
      function updateProgressBar(percentage) {
        const circle = document.getElementById('progress-circle');
        const text = document.getElementById('progress-text');
        const subtext = document.getElementById('progress-subtext');
        const circumference = 377;
        const clampedPercentage = Math.max(0, Math.min(100, percentage));
        const offset = circumference - (clampedPercentage / 100) * circumference;

        if (circle) {
          circle.style.strokeDashoffset = offset;
          if (clampedPercentage < 50) {
            circle.style.stroke = '#ED3241';
          } else if (clampedPercentage < 80) {
            circle.style.stroke = '#E86339';
          } else {
            circle.style.stroke = '#298267';
          }
        }

        if (text) {
          text.textContent = `${Math.round(clampedPercentage)}%`;
        }

        if (subtext) {
          if (clampedPercentage < 50) {
            subtext.textContent = 'You could do better!';
          } else if (clampedPercentage < 80) {
            subtext.textContent = 'Good job!';
          } else {
            subtext.textContent = 'Excellent!';
          }
        }
      }

      function animateProgressBar(targetPercentage, duration = 1500) {
        const clampedTarget = Math.max(0, Math.min(100, targetPercentage));
        const startValue = 0;
        const changeInValue = clampedTarget - startValue;
        const startTime = performance.now();

        function easeOutCubic(t) {
          return 1 - Math.pow(1 - t, 3);
        }

        function step(now) {
          const elapsed = now - startTime;
          const progress = Math.min(1, elapsed / duration);
          const currentValue = startValue + changeInValue * easeOutCubic(progress);
          updateProgressBar(currentValue);
          if (progress < 1) {
            requestAnimationFrame(step);
          }
        }

        updateProgressBar(startValue);
        requestAnimationFrame(step);
      }

      document.addEventListener('DOMContentLoaded', function () {
        const root = document.getElementById('result-root');
        if (!root) return;
        const scoreStr = root.getAttribute('data-score') || '0';
        const scoreValue = parseFloat(scoreStr);
        if (!Number.isNaN(scoreValue)) {
          animateProgressBar(scoreValue, 1600);
        }

      });
    </script>
  </body>
</html>