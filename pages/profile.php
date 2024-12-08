<?php
session_start();
?>

<main>
  <div class="edit-details card">
    <h3>Edit account details</h3>
    <form id="editForm" class="form-container">
      <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
      <div class="form-item">
        <label for="name">Full name</label>
        <input type="text" name="name" />
      </div>
      <div class="form-item">
        <label for="username">Username</label>
        <input type="text" name="username" />
      </div>
      <div class="form-item">
        <label for="contact_no">Phone number</label>
        <input type="tel" name="contact_no" />
      </div>
      <div class="form-item">
        <label for="address">Address</label>
        <input type="text" name="address" />
      </div>
      <div class="form-item">
        <label for="password">Password</label>
        <div class="flex-col">
          <input type="password" name="currentPassword" placeholder="Current password" />
          <input type="password" name="newPassword" placeholder="New password" />
        </div>
      </div>
      <div class="line"></div>
      <div class="btn-container">
        <button type="button" class="secondary-cta-btn" onclick="showPopup()">Cancel</button>
        <button type="submit" class="primary-cta-btn">Save changes</button>
      </div>
    </form>
  </div>
  <section class="profile-wrapper --first">
    <h1 class="title">Welcome back <?php echo ucfirst($_SESSION['username']) ?></h1>
    <div class="account-details">
      <h3>Account Details</h3>
      <div class="card">
        <div class="space-btwn">
          <h4>Profile & Security</h4>
          <p class="clickable" onclick="showPopup()">Edit</p>
        </div>
        <div class="item">
          <p class="title">Full Name</p>
          <p class="desc" name="name">Loading...</p>
        </div>
        <div class="item">
          <p class="title">Username</p>
          <p class="desc" name="username">Loading...</p>
        </div>
        <div class="item">
          <p class="title">Phone number</p>
          <p class="desc" name="contact_no">Loading...</p>
        </div>
        <div class="item">
          <p class="title">Address</p>
          <p class="desc" name="address">Loading...</p>
        </div>
        <div class="item">
          <p class="title">Password</p>
          <p class="desc" name="password">*********</p>
        </div>
      </div>
    </div>
    </div>
    <div>
      <button type="button" class="primary-cta-btn" onclick="logout()">Logout</button>
    </div>
  </section>
</main>