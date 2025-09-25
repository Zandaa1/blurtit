<?php 

include("config.php");
session_start();
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($link, $_POST['username']);
    $password = mysqli_real_escape_string($link, $_POST['password']);

    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($link, $sql);
    $count = mysqli_num_rows($result);

    if ($count == 1) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['login_user'] = $username;

        header("location: index.php");
        exit();
    } else {
        $error = "Invalid login. Try again!";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login | Blurt It!</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"
    />
    <link rel="stylesheet" href="css/output.css" />
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
        padding-bottom: env(safe-area-inset-bottom);
      }
    </style>
  </head>
  <body class="bg-gray-100">
    <div
      class="flex flex-col w-full h-dvh bg-white md:max-w-sm md:mx-auto md:shadow-2xl md:rounded-2xl md:my-8 overflow-hidden"
    >
      <div
        class="flex-shrink-0 flex items-center justify-center h-2/5 bg-gray-50"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-16 w-16 text-gray-300"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          stroke-width="1"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
          />
        </svg>
      </div>

      <div
        class="flex-1 flex flex-col justify-start px-8 pt-8 pb-10 bg-white"
      >
        <h1
          class="animate__animated animate__fadeInDown text-3xl font-bold text-gray-900 mb-6"
        >
          Welcome!
        </h1>

        <form action="#" method="POST" class="w-full">
          <div class="space-y-4">
            <div>
              <input
            type="text"
            name="username"
            id="username"
            placeholder="Username"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
              />
            </div>

            <div class="relative">
              <input
            type="password"
            name="password"
            id="password"
            placeholder="Password"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
              />
              <button
            type="button"
            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400"
            aria-label="Show password"
              >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a9.97 9.97 0 01-1.563 3.029m-2.14 2.14l-3.289-3.29m0 0a3 3 0 11-4.243-4.243"
              />
            </svg>
              </button>
            </div>
          </div>

          <button
            type="submit"
            class="mt-6 w-full py-3 bg-blue-500 text-white font-bold rounded-lg shadow-md shadow-blue-500/30 hover:bg-blue-600 active:bg-blue-700 active:scale-95 transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Login
          </button>

          <div class="text-center mt-8">
            <p class="text-sm text-gray-500">
              Not a member?
              <a
            href="register.php"
            class="font-semibold text-blue-600 hover:text-blue-500"
              >
            Register now
              </a>
            </p>
          </div>
        </form>
      </div>
    </div>
  </body>
</html>