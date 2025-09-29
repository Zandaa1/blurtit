<?php
session_start();
include('config.php');

// Check if user is logged in
if(!isset($_SESSION['login_user'])){
    header("location: login.php");
    die();
}

$user_id = $_SESSION['id'];
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify the user wants to delete by checking a confirmation parameter
    if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes') {
        
        // Start transaction for data integrity
        mysqli_autocommit($link, FALSE);
        
        try {
            // Delete user's session history first (foreign key constraint)
            $delete_sessions = "DELETE FROM session_history WHERE userid = ?";
            $stmt1 = mysqli_prepare($link, $delete_sessions);
            mysqli_stmt_bind_param($stmt1, "i", $user_id);
            mysqli_stmt_execute($stmt1);
            
            // Delete the user account
            $delete_user = "DELETE FROM users WHERE id = ?";
            $stmt2 = mysqli_prepare($link, $delete_user);
            mysqli_stmt_bind_param($stmt2, "i", $user_id);
            mysqli_stmt_execute($stmt2);
            
            // Commit the transaction
            mysqli_commit($link);
            
            // Destroy the session
            session_destroy();
            
            // Redirect to login with success message
            header("location: login.php?deleted=1");
            exit();
            
        } catch (Exception $e) {
            // Rollback on error
            mysqli_rollback($link);
            $error = "Error deleting account. Please try again.";
        }
        
        // Re-enable autocommit
        mysqli_autocommit($link, TRUE);
    } else {
        $error = "Account deletion not confirmed.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account | Blurt It!</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="css/output.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
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
    <div class="flex flex-col w-full h-dvh bg-white md:max-w-md md:mx-auto md:shadow-2xl md:rounded-2xl md:my-8 overflow-hidden">
        <div class="flex-1 overflow-y-auto px-6 pt-10 pb-8">
            
            <div class="mb-8 animate__animated animate__fadeInDown text-center">
                <div class="text-6xl mb-4">⚠️</div>
                <h1 class="text-3xl font-bold text-red-600">Delete Account</h1>
                <p class="text-gray-600 mt-2">This action cannot be undone</p>
            </div>

            <?php if($error): ?>
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <div class="animate__animated animate__fadeInLeft animate__delay-1s bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
                <h2 class="text-lg font-semibold text-red-800 mb-3">🔴 What will be permanently deleted:</h2>
                <ul class="text-red-700 space-y-2">
                    <li>• Your profile and account information</li>
                    <li>• All your session history and blurting data</li>
                    <li>• Your nickname and username</li>
                    <li>• Everything associated with your account</li>
                </ul>
            </div>

            <div class="animate__animated animate__fadeInLeft animate__delay-2s bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <p class="text-yellow-800 text-sm">
                    <strong>⚡ Important:</strong> Once deleted, your account cannot be recovered. 
                    All data will be permanently removed from our servers.
                </p>
            </div>

            <form method="POST" class="space-y-4">
                <div class="animate__animated animate__fadeInLeft animate__delay-3s flex items-start">
                    <div class="flex items-center h-5">
                        <input id="confirm_delete" name="confirm_delete" type="checkbox" value="yes" required
                               class="focus:ring-red-500 h-4 w-4 text-red-600 border-gray-300 rounded">
                    </div>
                    <div class="animate__animated animate__fadeInLeft animate__delay-3s ml-3 text-sm">
                        <label for="confirm_delete" class="font-medium text-gray-700">
                            I understand that this action is permanent and cannot be undone. 
                            I want to permanently delete my account and all associated data.
                        </label>
                    </div>
                </div>

                <div class="animate__animated animate__fadeInUp flex space-x-3 pt-4">
                    <a href="profile.php" 
                       class="flex-1 py-3 px-4 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition-colors text-center">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="flex-1 py-3 px-4 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 active:bg-red-800 transition-colors">
                        Delete Forever
                    </button>
                </div>
            </form>

        </div>
    </div>

    <script>
        // Add extra confirmation on form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const checkbox = document.getElementById('confirm_delete');
            if (checkbox.checked) {
                const finalConfirm = confirm(
                    "🔴 FINAL WARNING\n\n" +
                    "You are about to permanently delete your account!\n" +
                    "This action CANNOT be undone.\n\n" +
                    "Click OK to proceed with deletion, or Cancel to stop."
                );
                if (!finalConfirm) {
                    e.preventDefault();
                }
            }
        });
    </script>
</body>
</html>
