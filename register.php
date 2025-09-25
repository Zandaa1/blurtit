<?php 
require_once('config.php');
session_start();

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nickname = mysqli_real_escape_string($link, $_POST['nickname']);
    $username = mysqli_real_escape_string($link, $_POST['username']);
    $password = mysqli_real_escape_string($link, $_POST['password']);
    
    // Check if username already exists
    $check_sql = "SELECT * FROM users WHERE username = '$username'";
    $check_result = mysqli_query($link, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        $error = "Username already exists! Please choose another.";
    } else {
        // Insert new user
        $insert_sql = "INSERT INTO users (username, password, nickname) VALUES ('$username', '$password', '$nickname')";
        
        if (mysqli_query($link, $insert_sql)) {
            $success = "Account created successfully! You can now login.";
        } else {
            $error = "Error creating account. Please try again.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign Up | Blurt It!</title>
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
        padding-bottom: env(safe-area-inset-bottom);
      }
    </style>
  </head>
  <body class="bg-gray-100">
    <div
      class="flex flex-col w-full h-dvh bg-white md:max-w-sm md:mx-auto md:shadow-2xl md:rounded-2xl md:my-8 overflow-hidden"
    >
      <main class="flex-1 overflow-y-auto px-6 pt-10 pb-8">
        <div class="mb-8 animate__animated animate__fadeInDown">
          <h1 class="text-3xl font-bold text-gray-900">Sign up</h1>
          <p class="text-gray-500 mt-1">Create an account to get started</p>
        </div>

        <?php if($error): ?>
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
          <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <?php if($success): ?>
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
          <?php echo $success; ?>
        </div>
        <?php endif; ?>

        <form action="#" method="POST" class="space-y-5">
          <div>
            <label for="nickname" class="block text-sm font-medium text-gray-800 mb-1"
              >Nickname</label
            >
            <input
              type="text"
              name="nickname"
              id="nickname"
              placeholder=""
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
            />
          </div>

          <div>
            <label for="username" class="block text-sm font-medium text-gray-800 mb-1"
              >Username</label
            >
            <input
              type="text"
              name="username"
              id="username"
              placeholder=""
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
            />
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-800 mb-1"
              >Password</label
            >
            <div class="relative">
              <input
                type="password"
                name="password"
                id="password"
                placeholder="Create a password"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
              />
              <button
                type="button"
                class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400"
                aria-label="Toggle password visibility"
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

          <div class="flex items-start pt-2">
            <div class="flex items-center h-5">
              <input
                id="terms"
                name="terms"
                type="checkbox"
                required
                class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
              />
            </div>
            <div class="ml-3 text-sm">
              <label for="terms" class="text-gray-600">
                I've read and agree with the
                <a href="#" class="font-medium text-blue-600 hover:underline"
                  >Terms and Conditions</a
                >
                and the
                <a href="#" class="font-medium text-blue-600 hover:underline"
                  >Privacy Policy</a
                >.
              </label>
            </div>
          </div>

          <button
            type="submit"
            class="!mt-8 w-full py-3 bg-blue-500 text-white font-bold rounded-xl shadow-md shadow-blue-500/30 hover:bg-blue-600 active:bg-blue-700 active:scale-95 transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Create Account
          </button>
        </form>

        <div class="text-center mt-8">
          <p class="text-sm text-gray-500">
            Already have an account?
            <a href="login.php" class="font-semibold text-blue-600 hover:underline">
              Log in
            </a>
          </p>
        </div>
      </main>
    </div>
  </body>
</html>