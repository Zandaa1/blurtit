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
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/gh/philfung/add-to-homescreen@3.4/dist/add-to-homescreen.min.css"
    />
    <script src="https://cdn.jsdelivr.net/gh/philfung/add-to-homescreen@3.4/dist/add-to-homescreen.min.js"></script>
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
      /* Added transition for smooth progress bar animation */
      #progress-circle {
          transition: stroke-dashoffset 0.5s ease-out;
      }
    </style>
  </head>
  <body class="bg-gray-100 ">
    <div
      class="flex flex-col w-full h-dvh md:h-auto bg-white md:max-w-lg md:mx-auto md:shadow-2xl md:rounded-2xl md:my-8 relative"
    >
      <main
        class="flex-1 flex flex-col overflow-y-auto px-4 pt-10 pb-40 md:px-6 md:pt-12 md:pb-32"
      >
        <div class="text-center mb-6">
          <div class="animate__animated animate__fadeInDown text-3xl font-bold">Cancer</div>
          <div class="animate__animated animate__fadeInDown animate__delay-0.5s text-gray-500 text-xl font-medium mb-4">Results</div>
          <div class="flex justify-center mb-2 animate__animated animate__fadeInUp">
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
        <div class="flex items-start gap-2 bg-gray-100 rounded-xl p-4 mb-4">
          <span class="text-lg mt-1">
            <!-- Message icon (color: #2F3036) -->
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M2.5 3H21.5V17H6.5L2.5 20.5V3Z" stroke="#2F3036" stroke-width="2" stroke-linecap="square"/>
            </svg>
          </span>
          <div class="text-justify text-sm text-gray-700">
            Your understanding of cancer is fundamentally incorrect. Cancer is a serious disease, not a treatment. You need to correct your understanding of what cancer is.
          </div>
        </div>
        <hr>
        <div class="flex items-center gap-2 font-bold text-base mb-2 mt-4">
          <span class="text-red-500 text-xl" aria-hidden="true">
            <!-- Danger icon (color: #ED3241) -->
            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 26 26" fill="none">
              <path d="M7.79167 25.5L0.5 18.2083V7.79167L7.79167 0.5H18.2083L25.5 7.79167V18.2083L18.2083 25.5H7.79167ZM9.04167 18.9028L13 14.9444L16.9583 18.9028L18.9028 16.9583L14.9444 13L18.9028 9.04167L16.9583 7.09722L13 11.0556L9.04167 7.09722L7.09722 9.04167L11.0556 13L7.09722 16.9583L9.04167 18.9028Z" fill="#ED3241"/>
            </svg>
          </span>
          Mistakes Found
        </div>
        <!-- Mistakes List -->
        <div class="bg-red-100 rounded-lg p-3 flex gap-2 mb-2">
          <span class="text-red-500 text-lg mt-1" aria-hidden="true">
            <!-- Danger icon (color: #ED3241) small -->
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 26 26" fill="none">
              <path d="M7.79167 25.5L0.5 18.2083V7.79167L7.79167 0.5H18.2083L25.5 7.79167V18.2083L18.2083 25.5H7.79167ZM9.04167 18.9028L13 14.9444L16.9583 18.9028L18.9028 16.9583L14.9444 13L18.9028 9.04167L16.9583 7.09722L13 11.0556L9.04167 7.09722L7.09722 9.04167L11.0556 13L7.09722 16.9583L9.04167 18.9028Z" fill="#ED3241"/>
            </svg>
          </span>
          <div>
            <div class="font-semibold text-sm mb-1">“Cancer is a drug that helps people”.</div>
            <div class="text-xs text-gray-700">Cancer is a disease in which cells grow uncontrollably and can spread to other parts of the body. It is not a drug, and it certainly does not help people.</div>
          </div>
        </div>
                <div class="bg-red-100 rounded-lg p-3 flex gap-2 mb-2">
          <span class="text-red-500 text-lg mt-1" aria-hidden="true">
            <!-- Danger icon (color: #ED3241) small -->
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 26 26" fill="none">
              <path d="M7.79167 25.5L0.5 18.2083V7.79167L7.79167 0.5H18.2083L25.5 7.79167V18.2083L18.2083 25.5H7.79167ZM9.04167 18.9028L13 14.9444L16.9583 18.9028L18.9028 16.9583L14.9444 13L18.9028 9.04167L16.9583 7.09722L13 11.0556L9.04167 7.09722L7.09722 9.04167L11.0556 13L7.09722 16.9583L9.04167 18.9028Z" fill="#ED3241"/>
            </svg>
          </span>
          <div>
            <div class="font-semibold text-sm mb-1">“Cancer is a drug that helps people”.</div>
            <div class="text-xs text-gray-700">Cancer is a disease in which cells grow uncontrollably and can spread to other parts of the body. It is not a drug, and it certainly does not help people.</div>
          </div>
        </div>
        <div class="flex items-center gap-2 font-bold text-base mb-2 mt-5">
          <span class="text-green-500 text-xl" aria-hidden="true">
            <!-- Brain icon (color: #14AE5C) -->
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
              <path d="M15.5 13C14.5717 13 13.6815 13.3687 13.0251 14.0251C12.3687 14.6815 12 15.5717 12 16.5M12 16.5V17.5M12 16.5C12 15.5717 11.6313 14.6815 10.9749 14.0251C10.3185 13.3687 9.42826 13 8.5 13M12 17.5C12 18.4283 12.3687 19.3185 13.0251 19.9749C13.6815 20.6313 14.5717 21 15.5 21C16.4283 21 17.3185 20.6313 17.9749 19.9749C18.6313 19.3185 19 18.4283 19 17.5V15.7M12 17.5C12 18.4283 11.6313 19.3185 10.9749 19.9749C10.3185 20.6313 9.42826 21 8.5 21C7.57174 21 6.6815 20.6313 6.02513 19.9749C5.36875 19.3185 5 18.4283 5 17.5V15.7" stroke="#14AE5C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M17.5 16C18.4283 16 19.3185 15.6313 19.9749 14.9749C20.6313 14.3185 21 13.4283 21 12.5C21 11.5717 20.6313 10.6815 19.9749 10.0251C19.3185 9.36875 18.4283 9 17.5 9H17" stroke="#14AE5C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M19 9.3V6.5C19 5.57174 18.6313 4.6815 17.9749 4.02513C17.3185 3.36875 16.4283 3 15.5 3C14.5717 3 13.6815 3.36875 13.0251 4.02513C12.3687 4.6815 12 5.57174 12 6.5M6.5 16C5.57174 16 4.6815 15.6313 4.02513 14.9749C3.36875 14.3185 3 13.4283 3 12.5C3 11.5717 3.36875 10.6815 4.02513 10.0251C4.6815 9.36875 5.57174 9 6.5 9H7" stroke="#14AE5C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M5 9.3V6.5C5 5.57174 5.36875 4.6815 6.02513 4.02513C6.6815 3.36875 7.57174 3 8.5 3C9.42826 3 10.3185 3.36875 10.9749 4.02513C11.6313 4.6815 12 5.57174 12 6.5V16.5" stroke="#14AE5C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </span>
          Suggestions
        </div>
        <div class="bg-green-50 rounded-xl p-4 flex gap-2 mb-2">
          <span class="text-green-500 text-lg mt-1" aria-hidden="true">
            <!-- Pencil icon (color: #1F2024) -->
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
              <mask id="mask0_1227_6327" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="2" y="2" width="20" height="20">
                <path d="M8.14132 20.6086L18.1416 10.6084L13.3916 5.85845L3.3914 15.8587C3.25373 15.9965 3.15593 16.1691 3.10839 16.358L2 22L7.64093 20.8916C7.83033 20.8443 8.00358 20.7463 8.14132 20.6086ZM21.3699 7.38006C21.7733 6.97646 22 6.42914 22 5.85845C22 5.28776 21.7733 4.74044 21.3699 4.33684L19.6632 2.63014C19.2596 2.22666 18.7122 2 18.1416 2C17.5709 2 17.0235 2.22666 16.6199 2.63014L14.9132 4.33684L19.6632 9.08676L21.3699 7.38006Z" fill="#006FFD"/>
              </mask>
              <g mask="url(#mask0_1227_6327)">
                <rect width="24" height="24" fill="#1F2024"/>
              </g>
            </svg>
          </span>
          <div class="text-sm text-gray-700">Start by researching the basic definition of cancer and its effects on the human body. Focus on understanding the difference between diseases and treatments.</div>
        </div>
      </main>

      <footer
        class="mt-auto relative border-t border-gray-200 pb-[calc(0.25rem+env(safe-area-inset-bottom))] h-28 md:absolute md:bottom-0 md:left-0 md:right-0 md:rounded-b-2xl bg-white"
      >
        <nav
          class="relative flex justify-around items-center h-full pt-2 pb-6 text-gray-500"
        >
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
      const circumference = 377; // Circumference of the circle (2 * PI * 60)

      // Ensure the percentage is between 0 and 100
      const clampedPercentage = Math.max(0, Math.min(100, percentage));

      const offset = circumference - (clampedPercentage / 100) * circumference;

      if (circle) {
        circle.style.strokeDashoffset = offset;

        // Set stroke color based on thresholds
        if (clampedPercentage < 50) {
          circle.style.stroke = '#ED3241'; // RED
        } else if (clampedPercentage < 80) {
          circle.style.stroke = '#E86339'; // ORANGE
        } else {
          circle.style.stroke = '#298267'; // GREEN
        }
      }

      if (text) text.textContent = `${Math.round(clampedPercentage)}%`;
            
      // Optional: Change subtext based on score
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

    document.addEventListener('DOMContentLoaded', function () {
      // Smooth animation helper: animate from current value to target over duration (ms)
      function animateProgressTo(targetPercentage, duration = 1000) {
        const text = document.getElementById('progress-text');
        const circle = document.getElementById('progress-circle');
        const circumference = 377; // keep in sync with the circle r=60

        // Read current displayed percentage from text (fallback to 0)
        const current = parseFloat((text && text.textContent || '0').replace('%','')) || 0;
        const start = performance.now();

        function easeOutCubic(t) { return 1 - Math.pow(1 - t, 3); }

        function step(now) {
          const elapsed = now - start;
          const t = Math.min(1, elapsed / duration);
          const eased = easeOutCubic(t);
          const value = current + (targetPercentage - current) * eased;

          // Update visual
          const offset = circumference - (value / 100) * circumference;
          if (circle) circle.style.strokeDashoffset = offset;
          if (text) text.textContent = `${Math.round(value)}%`;

          // Set stroke color based on thresholds
          if (circle) {
            if (value < 50) circle.style.stroke = '#ED3241';
            else if (value < 80) circle.style.stroke = '#E86339';
            else circle.style.stroke = '#298267';
          }

          // Update subtext logic (same as updateProgressBar)
          const subtext = document.getElementById('progress-subtext');
          if (subtext) {
            if (value < 50) subtext.textContent = 'You could do better!';
            else if (value < 80) subtext.textContent = 'Good job!';
            else subtext.textContent = 'Excellent!';
          }

          if (t < 1) requestAnimationFrame(step);
        }

        requestAnimationFrame(step);
      }

      // Test Code Here: Animate to X% over 1.2 seconds
      animateProgressTo(9, 1200);
    });
    </script>
    
    <script>
      // Simple JavaScript to show a custom message on form submission for demo purposes.
      // Assuming you might have a form with id="blurtForm", otherwise this will not run.
      const blurtForm = document.getElementById("blurtForm");
      if (blurtForm) {
        blurtForm.addEventListener("submit", (event) => {
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
      }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
        window.AddToHomeScreenInstance = window.AddToHomeScreen({
        appName: 'Blurt It!',
        appNameDisplay: 'standalone', 
        appIconUrl: 'apple-touch-icon.png',
        assetUrl: 'https://cdn.jsdelivr.net/gh/philfung/add-to-homescreen@3.4/dist/assets/img/',
        maxModalDisplayCount: 0,
        displayOptions:{ showMobile: true, showDesktop: true },
        allowClose: true,
        showArrow: true,
        });

        ret = window.AddToHomeScreenInstance.show('en');
        });
    </script>
  </body>
</html>