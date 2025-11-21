<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>School Blog - Login</title>
  <link rel="stylesheet" href="/vanilla_blog/public/css/auth.css">
  <link rel="stylesheet" href="/vanilla_blog/public/css/alerts.css">
</head>

<body>
  <div class="auth-container">
    <h1>School Blog</h1>
    <h2>Login to your account</h2>
    <form id="loginForm" method="post" action="/vanilla_blog/authenticate">
      <input type="text" name="email" placeholder="Username">
      <input type="password" name="password" placeholder="Password">
      <?php require_once __DIR__ . "/alerts.php"; ?>
      <button type="submit">Login</button>
    </form>

    <div class="switch-link">
      <span>Don’t have an account?</span>
      <a href="/vanilla_blog/register">Sign up</a>
    </div>
  </div>
</body>

</html>