<?php
// admin/includes/nav.php
?>
<div class="sidebar">
  <a class="<?php echo (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'active' : ''; ?>">
    Dashboard
  </a>
<a href="bikes.php"
     class="<?php echo (basename($_SERVER['PHP_SELF']) === 'bikes.php') ? 'active' : ''; ?>">
    Manage Bikes
  </a>
  <a href="orders.php"
     class="<?php echo (basename($_SERVER['PHP_SELF']) === 'orders.php') ? 'active' : ''; ?>">
    Manage Orders
  </a>
</div>
