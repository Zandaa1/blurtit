<?php
include('session.php');
include('config.php');

$user_id = $_SESSION['id'];
$user_nickname = $_SESSION['nickname'] ?? 'Learner';

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Blurt It! App UI</title>
    <link rel="manifest" crossorigin="use-credentials" href="manifest.json" />
    <!-- Animate.css -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"
    />
    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="css/output.css" />
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

      .safe-area-bottom {
        padding-bottom: calc(1rem + env(safe-area-inset-bottom));
      }

      .loading-spinner {
        width: 3rem;
        height: 3rem;
        border-radius: 9999px;
        border: 4px solid rgba(59, 130, 246, 0.2);
        border-top-color: #2563eb;
        animation: spin 0.8s linear infinite;
      }

      @keyframes spin {
        to {
          transform: rotate(360deg);
        }
      }
    </style>
  </head>
  <body class="bg-gray-100">
    <!-- App Container (mobile full height, desktop centered card) -->
    <div
      class="flex flex-col w-full h-dvh md:h-auto bg-white md:max-w-lg md:mx-auto md:shadow-2xl md:rounded-2xl md:my-8 relative"
    >
      <!-- Main Scrollable Content only-->
      <main
        class="flex-1 flex flex-col overflow-y-auto px-4 pt-10 pb-13 md:px-6 md:pt-12 md:pb-32"
      >
        <h1 class="animate__animated animate__fadeInDown text-2xl text-center font-extrabold text-gray-900 mb-8">
          Welcome back, <?php echo htmlspecialchars($user_nickname); ?>!
        </h1>

        <form
          id="blurtForm"
          action="result.php"
          method="POST"
          class="flex flex-col flex-1"
        >
          <div class="mb-6">
            <label
              for="topic"
              class="block text-base font-semibold text-gray-800 mb-2"
              >What is your topic?</label
            >
            <input
              type="text"
              id="topic"
              name="topic"
              placeholder="Enter your topic here"
              value=""
              class="w-full px-4 py-3 border-2 rounded-xl border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400 transition-shadow"
              required
            />
          </div>

          <div class="mb-4 flex flex-col flex-1">
            <label
              for="knowledge"
              class="block text-base font-semibold text-gray-800 mb-2"
              >What do you know about this?</label
            >
            <textarea
              id="knowledge"
              name="knowledge"
              placeholder="Tell me everything you know!"
              class="flex-1 min-h-[12rem] px-4 py-3 border-2 border-gray-300 rounded-xl resize-none focus:outline-none focus:ring-2 focus:ring-blue-400 transition-shadow"
              required
            ></textarea>
          </div>

          <button
            type="submit"
            class="mt-2 w-full py-3 bg-white border-2 border-blue-500 text-blue-500 font-bold rounded-xl hover:bg-blue-50 active:bg-blue-100 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Submit
          </button>
        </form>
      </main>

      <!-- Bottom Navigation (sticks to bottom on mobile, absolute overlay styling on desktop) -->
      <footer
        class="mt-auto relative border-t border-gray-200 safe-area-bottom h-28 md:absolute md:bottom-0 md:left-0 md:right-0 md:rounded-b-2xl bg-white"
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
            <a href="#"
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
      <div
        id="submit-loading"
        class="hidden absolute inset-0 bg-white/85 backdrop-blur-sm z-50 flex flex-col items-center justify-center px-6 text-center"
        role="status"
        aria-live="polite"
      >
        <div class="loading-spinner mb-4" aria-hidden="true"></div>
        <p class="text-base font-semibold text-gray-700">Checking your blurt…</p>
        <p class="text-sm text-gray-500 mt-1">Hang tight while we analyze your response.</p>
      </div>
    </div>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('blurtForm');
        const loadingOverlay = document.getElementById('submit-loading');
        if (!form) {
          return;
        }

        form.addEventListener(
          'submit',
          function () {
            if (loadingOverlay) {
              loadingOverlay.classList.remove('hidden');
            }

            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
              submitButton.disabled = true;
              submitButton.classList.add('opacity-70', 'cursor-not-allowed');
              submitButton.textContent = 'Submitting…';
            }

            const inputs = form.querySelectorAll('input, textarea');
            inputs.forEach(function (element) {
              element.setAttribute('readonly', 'readonly');
              element.classList.add('opacity-80');
            });
          },
          { once: true }
        );
      });
    </script>
  </body>
</html>
