<?php
include('session.php');
include('config.php');

$user_id = $_SESSION['id'];

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Blurt It! App UI</title>
    <!-- Add to Home Screen -->
    <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/gh/philfung/add-to-homescreen@3.4/dist/add-to-homescreen.min.css"
  />
  <script src="https://cdn.jsdelivr.net/gh/philfung/add-to-homescreen@3.4/dist/add-to-homescreen.min.js"></script>
    <link rel="manifest" crossorigin="use-credentials" href="manifest.json" />
</head>

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
      <!-- Main Scrollable Content only-->
      <main
        class="flex-1 flex flex-col overflow-y-auto px-4 pt-10 pb-40 md:px-6 md:pt-12 md:pb-32"
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
              class="w-full px-4 py-3 border-2 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 transition-shadow"
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
    </div>

  <script>
document.addEventListener('DOMContentLoaded', function () {
 window.AddToHomeScreenInstance = window.AddToHomeScreen({
  appName: 'Blurt It!',                                   // Name of the app.
                                                         // Required.
  appNameDisplay: 'standalone',                          // If set to 'standalone' (the default), the app name will be diplayed
                                                         // on it's own, beneath the "Install App" header. If set to 'inline', the
                                                         // app name will be displayed on a single line like "Install MyApp"
                                                         // Optional. Default 'standalone'
  appIconUrl: 'apple-touch-icon.png',                    // App icon link (square, at least 40 x 40 pixels).
                                                         // Required.
  assetUrl: 'https://cdn.jsdelivr.net/gh/philfung/add-to-homescreen@3.4/dist/assets/img/',  // Link to directory of library image assets.

  maxModalDisplayCount: 0,                              // If set, the modal will only show this many times.
                                                         // [Optional] Default: -1 (no limit).  (Debugging: Use this.clearModalDisplayCount() to reset the count)
  displayOptions:{ showMobile: true, showDesktop: true }, // show on mobile/desktop [Optional] Default: show everywhere
  allowClose: true, // allow the user to close the modal by tapping outside of it [Optional. Default: true]
  showArrow: true, // show the bouncing arrow on the modal [Optional. Default: true] (highly recommend leaving at true as drastically affects install rates)
});

 ret = window.AddToHomeScreenInstance.show('en');        // show "add-to-homescreen" instructions to user, or do nothing if already added to homescreen
                                                         // [optional] language.  If left blank, then language is auto-decided from (1) URL param locale='..' (e.g. /?locale=es) (2) Browser language settings
});
</script>


  </body>
</html>
