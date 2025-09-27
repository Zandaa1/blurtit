<?php
declare(strict_types=1);

// test sessions here
$testSessionCount = 0;

include('session.php');
include('config.php');

$user_id = (int) $_SESSION['id'];

$sessions = [];
$error = null;

$stmt = mysqli_prepare(
    $link,
    'SELECT sessionid, session_topicName, session_score, time_created FROM session_history WHERE userid = ? ORDER BY time_created DESC, sessionid DESC'
);

if ($stmt === false) {
    $error = 'Unable to load your sessions right now.';
} else {
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_bind_result($stmt, $sessionId, $topicName, $sessionScore, $timeCreated);
        while (mysqli_stmt_fetch($stmt)) {
            $sessions[] = [
                'id' => (int) $sessionId,
                'topic' => trim((string) $topicName) !== '' ? trim((string) $topicName) : 'Untitled session',
                'score' => $sessionScore !== null ? (int) $sessionScore : null,
                'time' => $timeCreated,
            ];
        }
    } else {
        $error = 'Unable to load your sessions right now.';
    }
    mysqli_stmt_close($stmt);
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function formatTimestamp(?string $value): ?string
{
    if ($value === null || $value === '') {
        return null;
    }
    $timestamp = strtotime($value);
    return $timestamp !== false ? date('M j, Y g:i A', $timestamp) : null;
}

function scoreClass(?int $score): string
{
    if ($score === null) {
        return 'text-gray-500';
    }
    if ($score < 50) {
        return 'text-red-500';
    }
    if ($score < 80) {
        return 'text-orange-500';
    }
    return 'text-green-500';
}
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Blurt It! App UI</title>
    <!-- Animate.css -->
       <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"
  />
    <!-- Tailwind CSS CDN -->
    <link rel="stylesheet" href="css/output.css">
    <!-- Google Fonts for Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap"
      rel="stylesheet"
    />
    <style>
      /* Apply a font that closely matches the iOS system font */
      body {
        font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI",
          Roboto, Helvetica, Arial, sans-serif;
      }
      /* Dynamic viewport height helper (accounts for mobile browser UI) */
      .h-dvh {
        height: 100vh;
      }
      @supports (height: 100dvh) {
        .h-dvh {
          height: 100dvh;
        }
      }
      /* Safe area padding for devices with notches */
      body {
        padding-top: env(safe-area-inset-top);
      }
    </style>
  </head>
  <body class="bg-gray-100">
    <!-- App Container (mobile full height, desktop centered card) -->
    <div
      class="flex flex-col w-full h-dvh md:h-auto bg-white md:max-w-lg md:mx-auto md:shadow-2xl md:rounded-2xl md:my-8 relative"
    >
      <!-- Main Scrollable Content -->
      <main
        class="flex-1 flex flex-col overflow-y-auto px-4 pt-10 pb-40 md:px-6 md:pt-12 md:pb-32"
      >
        <h1 class="animate__animated animate__fadeInDown text-2xl text-center font-bold text-gray-900 mb-8">
          Previous Sessions
        </h1>

        <?php if ($error !== null): ?>
          <div class="p-4 bg-red-100 border border-red-200 text-red-700 rounded-xl text-sm mb-4">
            <?= e($error) ?>
          </div>
        <?php endif; ?>

        <?php if (empty($sessions)): ?>
          <div class="flex flex-col items-center justify-center h-full text-center">
            <h2 class="text-xl font-extrabold">Nothing here. For now.</h2>
            <p class="text-gray-600 mt-2">This is where your blurting sessions will appear.</p>
            <a href="index.php" class="mt-4 w-full py-3 bg-blue-600 border-2 border-blue-500 text-white rounded-xl hover:bg-blue-500 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
              Start a new session
            </a>
          </div>
        <?php else: ?>
          <div class="space-y-3">
            <?php foreach ($sessions as $session): ?>
              <?php
                $score = $session['score'];
                $scoreLabel = $score !== null ? $score . '%' : '—';
                $scoreClass = scoreClass($score);
                $recorded = formatTimestamp($session['time']) ?: 'Date unavailable';
              ?>
              <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                <a href="result.php?sessionid=<?= e((string) $session['id']) ?>" class="block">
                  <div class="flex items-center justify-between">
                    <div class="flex-1">
                      <h3 class="font-semibold text-gray-900 text-base mb-1"><?= e($session['topic']) ?></h3>
                      <p class="text-sm text-gray-500"><?= e($recorded) ?></p>
                    </div>
                    <div class="flex items-center space-x-3">
                      <div class="flex flex-col items-center">
                        <span class="text-2xl font-bold <?= e($scoreClass) ?>"><?= e($scoreLabel) ?></span>
                        <span class="text-xs text-gray-400">Score</span>
                      </div>
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                      </svg>
                    </div>
                  </div>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </main>

      <!-- Bottom Navigation (sticks to bottom on mobile, absolute overlay styling on desktop) -->
      <footer
        class="mt-auto relative border-t border-gray-200 pb-[calc(0.25rem+env(safe-area-inset-bottom))] h-28 md:absolute md:bottom-0 md:left-0 md:right-0 md:rounded-b-2xl bg-white"
      >
        <nav
          class="relative flex justify-around items-center h-full pt-2 pb-6 text-gray-500"
        >
          <!-- Sessions Tab (Active) -->
          <a
            href="sessions.php"
            aria-current="page"
            class="flex flex-col items-center justify-center space-y-1 text-blue-500 font-medium"
          >
            <img
              src="img/compass.svg"
              alt="Sessions"
              class="h-7 w-7"
            />
            <span class="text-xs font-semibold">Sessions</span>
          </a>

          <!-- Blurt It! Button (Placeholder for spacing) -->
          <div class="w-20"></div>

          <!-- Profile Tab -->
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

        <!-- Floating 'Blurt It!' Button -->
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

    <!-- Keep only original form submission demo script -->
    <script>
      // Simple JavaScript to show a custom message on form submission for demo purposes.
      document
        .getElementById("blurtForm")
        .addEventListener("submit", (event) => {
          event.preventDefault(); // Prevents the page from reloading

          // Remove existing message box if it exists
          const existingBox = document.querySelector(".custom-message-box");
          if (existingBox) {
            existingBox.remove();
          }

          // Create a custom message box
          const messageBox = document.createElement("div");
          messageBox.textContent = "Form submitted successfully!";
          messageBox.className = "custom-message-box";
          messageBox.style.position = "fixed";
          messageBox.style.top = "20px";
          messageBox.style.left = "50%";
          messageBox.style.transform = "translateX(-50%)";
          messageBox.style.padding = "12px 24px";
          messageBox.style.backgroundColor = "#2563eb"; // blue-600
          messageBox.style.color = "white";
          messageBox.style.borderRadius = "8px";
          messageBox.style.boxShadow = "0 4px 6px rgba(0, 0, 0, 0.1)";
          messageBox.style.zIndex = "1000";
          messageBox.style.opacity = "0";
          messageBox.style.transition =
            "opacity 0.3s ease-in-out, top 0.3s ease-in-out";

          document.body.appendChild(messageBox);

          // Animate in
          setTimeout(() => {
            messageBox.style.opacity = "1";
            messageBox.style.top = "40px";
          }, 10);

          // Animate out and remove after a delay
          setTimeout(() => {
            messageBox.style.opacity = "0";
            messageBox.style.top = "20px";
            setTimeout(() => {
              if (document.body.contains(messageBox)) {
                document.body.removeChild(messageBox);
              }
            }, 300);
          }, 2500);
        });
    </script>

  </body>
</html>
