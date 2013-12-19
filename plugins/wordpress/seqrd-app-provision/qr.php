<?php
  include_once('/var/www/library/phpqr/phpqrcode.php');
  header("Content-type: image/png");
  QRcode::png($_GET['codeValue']);
?>
