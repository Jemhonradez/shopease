<footer class="footer">
  <div class="container">


    <div class="footer-main">
      <h3>shopease</h3>
      <p>Thank you for shopping with us! Your support means the world. If you have any questions or need assistance, please don't hesitate to reach out. Don't forget to follow us on social media for the latest updates and exclussive offers. Happy Shopping!</p>
      <p>&copy; <?php echo date("Y"); ?> Shopease. All rights reserved.</p>
    </div>

    <div class="footer-side">

      <div>
        <h3>Navigation</h3>
        <ul>
          <li><a href="/women">Women</a></li>
          <li><a href="/men">Men</a></li>
        </ul>
      </div>
      <div>
        <h3>About</h3>
        <ul>
          <li>User agreement</li>
          <li>Privacy Policy</li>
        </ul>
      </div>

    </div>

  </div>

</footer>

<?php
$currentPage = isset($_GET['route']) ? $_GET['route'] : 'index';
$currentPage = preg_replace('/[^a-z0-9-_]/i', '', $currentPage);
?>

<script src="/assets/js/main.js"></script>
<script src="/assets/js/navbar.js"></script>

<?php
$excludedPages = ['admin', 'category'];

if (!in_array($currentPage, $excludedPages)) {
  if (file_exists("assets/js/pages/{$currentPage}.js")): ?>
    <script src="/assets/js/pages/<?php echo $currentPage; ?>.js"></script>
<?php endif;
}
?>

<script>
  const lenis = new Lenis();

  function raf(time) {
    lenis.raf(time);
    requestAnimationFrame(raf);
  }
  requestAnimationFrame(raf);
</script>

</body>

</html>