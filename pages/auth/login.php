<?php
session_start();

if (isset($_SESSION['user_id'])) {
  $username = $_SESSION['username'];
  $user_type = $_SESSION['user_type'];
  header('Location: /profile');
  exit();
}
?>

<main>
  <div class="login-wrapper">
    <div class="login-container">
      <h1 class="title">Sign In</h1>
      <form action="../../api/auth.php" method="post" class="form-container" id="loginForm">
        <input type="hidden" name="action" value="login">
        <div class="form-item">
          <label for="username">Username</label>
          <input type="text" name="username" required />
        </div>
        <div class="form-item">
          <label for="password">Password</label>
          <input type="password" name="password" required />
        </div>
        <button type="submit" class="submit primary-cta-btn">Sign In</button>
      </form>
      <div class="line"></div>
      <p class="register-text">New to shopease? <a href="/register">Create an account</a></p>
    </div>
  </div>
</main>