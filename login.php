<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Blurt It! App UI</title>
    <!-- Add to Home Screen -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/gh/philfung/add-to-homescreen@3.4/dist/add-to-homescreen.min.css" />
    <script src="https://cdn.jsdelivr.net/gh/philfung/add-to-homescreen@3.4/dist/add-to-homescreen.min.js"></script>
    <link rel="manifest" crossorigin="use-credentials" href="manifest.json" />
</head>

<!-- Animate.css -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<!-- Tailwind CSS CDN -->
<link rel="stylesheet" href="css/output.css">
<!-- Google Fonts for Inter -->
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet" />
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
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <!-- App Container (responsive centered card) -->
    <div class="flex flex-col w-full max-w-md h-auto min-h-[70vh] bg-white shadow-2xl rounded-2xl relative mx-2 sm:mx-auto">
        <!-- Main Scrollable Content only-->
        <main class="flex-1 flex flex-col overflow-y-auto px-4 pt-10 pb-40 md:px-6 md:pt-12 md:pb-32">

        <form action="index.php" method="POST" id="blurtForm">

        <h1 class="animate__animated animate__fadeInDown text-2xl text-center font-bold text-gray-900 mb-8">
          Login
        </h1>

        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
        <input type="text" id="username" name="username"
            class="w-full mb-4 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input type="password" id="password" name="password"
            class="w-full mb-6 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
        <script>
            // Temporarily bypass validation and always redirect to index.php
            document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('blurtForm').addEventListener('submit', function (e) {
                e.preventDefault();
                window.location.href = 'index.php';
            });
            });
        </script>
        <button type="submit"
            class="w-full py-3 bg-blue-600 border-2 border-blue-500 text-white rounded-xl hover:bg-blue-300 active:bg-blue-100 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Login</button>
        <p class="mt-4 text-center text-sm text-gray-600">Don't have an account? <a href="#" class="text-blue-600 hover:underline">Register</a></p>

        </form>

        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.AddToHomeScreenInstance = window.AddToHomeScreen({
                appName: 'Blurt It!',
                appNameDisplay: 'standalone',
                appIconUrl: 'apple-touch-icon.png',
                assetUrl: 'https://cdn.jsdelivr.net/gh/philfung/add-to-homescreen@3.4/dist/assets/img/',
                maxModalDisplayCount: 0,
                displayOptions: { showMobile: true, showDesktop: true },
                allowClose: true,
                showArrow: true,
            });

            ret = window.AddToHomeScreenInstance.show('en');
        });
    </script>
</body>

</html>