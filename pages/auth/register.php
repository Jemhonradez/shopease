<main>
  <div class="login-wrapper">
    <div class="login-container">
      <h1 class="title">Sign Up</h1>
      <form action="../../api/auth.php" method="post" class="form-container" id="registerForm">
        <input type="hidden" name="action" value="register">
        <div class="form-item">
          <label for="name">Full name*</label>
          <input type="text" name="name" required />
        </div>
        <div class="form-item">
          <label for="username">Username*</label>
          <input type="text" name="username" required />
        </div>
        <div class="form-item">
          <label for="password">Password*</label>
          <input type="password" name="password" required />
        </div>
        <div class="form-item">
          <label for="gender">Gender*</label>
          <select type="text" name="gender" required>
            <option value="">Select gender</option>
            <option value="f">Female</option>
            <option value="m">Male</option>
          </select>
        </div>
        <div class="form-item">
          <label for="contact_no">Contact Number</label>
          <input type="tel" name="contact_no" />
        </div>
        <div class="form-item">
          <label for="address">Address</label>
          <input type="text" name="address" />
        </div>
        <button type="submit" class="submit primary-cta-btn">Create account</button>
      </form>
      <div class="line"></div>
      <p class="register-text">Already have an account? <a href="/login">Sign In</a></p>
    </div>
  </div>
</main>