<?php
$session = SimpleSAML_Session::getInstance();

$secret_enrollment_qr_url = $session->getData('ldapTozny.session', 'secret_enrollment_qr_url');
$provisioned = $session->getData('ldapTozny.session', 'provisioned');
$provisioned = ($provisioned === "new_device") ? "new" : "";
$login_css     = $session->getData('ldapTozny.session', 'login_css');
$login_title   = $session->getData('ldapTozny.session', 'login_title');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title><?= $login_title ?>: Add a new device</title>
    <link rel="stylesheet" type="text/css" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?= $login_css ?>" />
    <meta name="robots" content="noindex, nofollow"/>
</head>
<body>
<img src="tozny.png" class="logo center-block" alt="Tozny Logo"/>
<div class="container" id="provision">
    <div class="col-sm-offset-4 col-sm-4">
        <h4>Add a new device</h4>
        <p class="instruct">
            Scan the QR code below using the Tozny app on your device.
            Once scanned, your <?= $provisioned ?> device will be ready for use.
        </p>
    </div>
</div>
<img src="<?= $secret_enrollment_qr_url ?>" class="qr center-block" alt="Tozny Provisioning QR"/>

<div class="container">
    <div class="col-md-offset-4 col-sm-4 copyright">Copyright &copy; 2014<a href="http://www.tozny.com/">Tozny</a></div>
</div>
</body>
</html>