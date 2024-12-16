<?php
session_start();

$user_type = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'guest';
?>

<script>
  var userType = "<?php echo $user_type; ?>";

  if (userType !== "admin") {
    alert("You're not authorized to view this page.")
    window.location.href = "/";
  }
</script>

<main id="admin">
  <div class="admin-wrapper">
    <section class="container --first" id="admin">
      <h2>Dashboard</h2>
      <div class="flex-row align-center">
        <!-- <div class="quick-link card" onclick="window.location.href = '/admin/user-management'">
          <div class="icon-badge">
            <i class="ti ti-user-cog"></i>
          </div>
          <div class="vertical-line"></div>
          <div>
            <h4 class="title">User management</h4>
            <p class="desc">Create, view, edit and delete users from the site</p>
          </div>
        </div> -->
        <div class="quick-link card" onclick="window.location.href = '/admin/product-management'">
          <div class="icon-badge">
            <i class="ti ti-basket-cog"></i>
          </div>
          <div class="vertical-line"></div>
          <div>
            <h4 class="title">Product management</h4>
            <p class="desc">Check stocks and edit their product details</p>
          </div>
        </div>
      </div>
    </section>
    <div class="line"></div>
    <section class="reports">

    </section>
  </div>
</main>