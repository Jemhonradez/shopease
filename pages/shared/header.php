<!DOCTYPE html>
<html lang="en">

<?php
$currentPage = isset($_GET['route']) ? $_GET['route'] : 'index';
$currentPage = preg_replace('/[^a-z0-9-_]/i', '', $currentPage);
?>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shopease</title>

  <!-- always include this style in every page -->
  <link rel="stylesheet" href="/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.24.0/dist/tabler-icons.min.css">
  <link rel="stylesheet" href="https://unpkg.com/lenis@1.1.18/dist/lenis.css">

  <script src="https://unpkg.com/lenis@1.1.18/dist/lenis.min.js"></script>
  <script src="https://unpkg.com/@supabase/supabase-js@2"></script>
  <script>
    const supabaseClient = supabase.createClient(
      "https://suqveoqemcidqyshjxdz.supabase.co",
      "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InN1cXZlb3FlbWNpZHF5c2hqeGR6Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTczMzIzOTUxNiwiZXhwIjoyMDQ4ODE1NTE2fQ.yDVhzOm5diBQ_jwhQOu_ZPyoc4CyuyyLwz4lnT13ma0"
    );
  </script>
</head>

<!-- of course we need the navbar -->
<?php
include 'pages/shared/navbar.php';
?>

<body data-page="<?php echo $currentPage; ?>">