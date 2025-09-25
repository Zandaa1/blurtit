<?php
   // Start the session
   session_start();

   if(!isset($_SESSION['login_user'])){
      header("location: login.php");
      die();
   }

   // Store session variables for easy access
   $login_session = $_SESSION['login_user'];
   $user_id = $_SESSION['id'];
   $user_nickname = $_SESSION['nickname'];

?>