<?php
if (!session_id()) {
    session_start();
}

$authSrcId = $_SESSION['authSrcId'];
$baseurlpath = SimpleSAML_Configuration::getInstance()->getBaseURL();

$loginUrl = "/$baseurlpath" . "module.php/core/authenticate.php?as=$authSrcId";
$as = new SimpleSAML_Auth_Simple('ldapTozny');

if (empty($_SESSION['secret_enrollment_qr_url'])){ // || !$as->isAuthenticated()) {
    $_SESSION['msg'] = 'Provisioning information unavailable.';
    header('Location: ' . $loginUrl);
}

$new = $_SESSION['provisioned']==="new_device" ? "new" : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>Add a new device</title>
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="tozny.css"/>
    <meta name="robots" content="noindex, nofollow"/>
</head>
<body>
<img src="tozny.png" class="logo center-block" alt="Tozny Logo"/>
<div class="container" id="provision">
    <div class="col-sm-offset-4 col-sm-4">
        <h4>Add a new device</h4>
        <p class="instruct">
            Scan the QR code below using the Tozny app on your device.
            Once scanned, your <?= $new ?> device will be ready for use.
        </p>
    </div>
</div>
<img src="<?= $_SESSION['secret_enrollment_qr_url'] ?>" class="qr center-block" alt="Tozny Provisioning QR"/>

<div class="container">
    <div class="col-md-offset-4 col-sm-4 copyright">Copyright &copy; 2014<a href="http://www.tozny.com/">Tozny</a></div>
</div>
<!-- <?= $_SESSION['provisioned'] ?> -->
</body>
</html>