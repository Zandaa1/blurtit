<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use GeminiAPI\Client;
use GeminiAPI\Resources\ModelName;
use GeminiAPI\Resources\Parts\TextPart;

const ENV_FILE_PATH = __DIR__ . '/.env';

function loadEnvFromFile(string $filePath): void
{
	if (!is_file($filePath) || !is_readable($filePath)) {
		return;
	}

	$lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	if ($lines === false) {
		return;
	}

	foreach ($lines as $line) {
		$trimmed = trim($line);
		if ($trimmed === '' || str_starts_with($trimmed, '#')) {
			continue;
		}

		if (!str_contains($trimmed, '=')) {
			continue;
		}

		[$name, $value] = array_map('trim', explode('=', $trimmed, 2));
		if ($name === '') {
			continue;
		}

		if ($value !== '') {
			if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
				$value = substr($value, 1, -1);
			}
			$value = trim($value);
		}

		putenv(sprintf('%s=%s', $name, $value));
		$_ENV[$name] = $value;
		$_SERVER[$name] = $value;
	}
}

function getGeminiApiKey(): string
{
	static $cached;
	if ($cached !== null) {
		return $cached;
	}

	$apiKey = getenv('GEMINI_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? '');
	if ($apiKey === '') {
		loadEnvFromFile(ENV_FILE_PATH);
		$apiKey = getenv('GEMINI_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? '');
	}

	$cached = $apiKey;
	return $cached;
}

function normalizeGeminiJsonString(string $raw): string
{
	$trimmed = trim($raw);

	if ($trimmed === '') {
		return $trimmed;
	}

	if (str_starts_with($trimmed, '```')) {
		$trimmed = preg_replace('/^```[a-zA-Z0-9_-]*\s*/', '', $trimmed) ?? $trimmed;
		$trimmed = preg_replace('/\s*```$/', '', $trimmed) ?? $trimmed;
		$trimmed = trim($trimmed);
	}

	if ($trimmed !== '' && !in_array($trimmed[0], ['{', '['], true)) {
		$start = strpos($trimmed, '{');
		$end = strrpos($trimmed, '}');
		if ($start !== false && $end !== false && $end >= $start) {
			$trimmed = substr($trimmed, $start, $end - $start + 1);
		}
	}

	return trim($trimmed);
}

/**
 * Generate structured Gemini feedback for a blurting session.
 *
 * @return array{raw:string,data:array<string,mixed>}
 *
 * @throws RuntimeException when the API key is missing or the response is invalid
 */
function generateGeminiFeedback(string $topic, string $blurt): array
{
	$apiKey = getGeminiApiKey();
	if (empty($apiKey)) {
		throw new RuntimeException('GEMINI_API_KEY environment variable is not set.');
	}

	$client = new Client($apiKey);
	$prompt = sprintf('Topic: %s | Blurt: %s', $topic, $blurt);
	$maxAttempts = 3;
	$attempt = 0;
	$baseDelayMicros = 500000; // 0.5 seconds

	while (true) {
		try {
			$response = $client->withV1BetaVersion()
				->generativeModel(ModelName::GEMINI_2_5_FLASH)
				->withSystemInstruction('You are Blurt It, an AI insight validator for a blurting-based learning app.
Always respond ONLY as a single valid JSON object in this exact structure:

{
  "shortTopicTitle": "A concise version of the topic title, no more than 4 words, suitable for small UI labels.",
  "accuracyRating": <integer from 0 to 100>,
  "overallFeedback": "A short, honest, but constructive message about the overall correctness of the blurt.",
  "mistakes": [
    {
      "incorrectPhrase": "Exact text copied from the user’s blurt that is factually wrong.",
      "explanation": "A clear and concise explanation of why that phrase is incorrect.",
      "correction": "A short correction that states the fact accurately."
    }
  ],
  "suggestions": "One to three short and practical suggestions to help the user study or recall correctly next time."
}

Instructions:
1. Focus ONLY on factual correctness about the given topic. Ignore grammar, spelling, and delivery.
2. Quote the user’s words exactly in each `incorrectPhrase`.
3. Do not repeat identical mistakes more than once.
4. If there are no factual errors, return an empty array for `mistakes`.
5. Make the `overallFeedback` brief — no more than two sentences.
6. Keep suggestions constructive and focused on improving understanding or study habits.
7. The `shortTopicTitle` must be a clean, human-readable label derived from the provided topic, max 4 words, no quotes, no emojis.
8. Ensure the response is strictly valid JSON with no additional text, no markdown, and no commentary outside the JSON.')
			->generateContent(
				new TextPart($prompt),
			);

			$raw = trim($response->text());
			if ($raw === '') {
				throw new RuntimeException('Gemini returned an empty response.');
			}

			$normalized = normalizeGeminiJsonString($raw);
			try {
				$data = json_decode($normalized, true, 512, JSON_THROW_ON_ERROR);
			} catch (JsonException $jsonException) {
				throw new RuntimeException('Gemini request failed: ' . $jsonException->getMessage(), (int) $jsonException->getCode(), $jsonException);
			}
			return [
				'raw' => $normalized,
				'data' => $data,
			];
		} catch (Throwable $e) {
			$attempt++;
			if ($attempt >= $maxAttempts || !shouldRetryGeminiRequest($e)) {
				throw new RuntimeException('Gemini request failed: ' . $e->getMessage(), (int) $e->getCode(), $e);
			}
			usleep((int) ($baseDelayMicros * $attempt));
		}
	}
}

function shouldRetryGeminiRequest(Throwable $e): bool
{
	$message = $e->getMessage() ?? '';
	if ($message === '') {
		return false;
	}

	$retrySignals = [
		'UNAVAILABLE',
		'503',
		'RESOURCE_EXHAUSTED',
		'429',
		'timeout',
		'timed out',
		'temporarily unavailable',
	];

	foreach ($retrySignals as $signal) {
		if (stripos($message, $signal) !== false) {
			return true;
		}
	}

	return false;
}

if (php_sapi_name() === 'cli' && isset($argv) && basename($argv[0]) === basename(__FILE__)) {
	if (($argv[1] ?? '') === '' || ($argv[2] ?? '') === '') {
		fwrite(STDERR, "Usage: php geminiapi.php <topic> <blurt>\n");
		exit(1);
	}

	try {
		$result = generateGeminiFeedback($argv[1], $argv[2]);
		echo $result['raw'] . PHP_EOL;
	} catch (Throwable $e) {
		fwrite(STDERR, 'Error: ' . $e->getMessage() . PHP_EOL);
		exit(1);
	}
}

