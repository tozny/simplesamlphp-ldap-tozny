<?php
session_start();

$authSrcId = $_SESSION['authSrcId'];
$baseurlpath = SimpleSAML_Configuration::getInstance()->getBaseURL();

$loginUrl = "/$baseurlpath" . "module.php/core/authenticate.php?as=$authSrcId";

if (empty($_SESSION['tozny_session_id'])) {
    header('Location: ' . $loginUrl);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>Authentication</title>
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="tozny.css"/>
    <meta name="robots" content="noindex, nofollow"/>
</head>
<body>
<img src="tozny.png" class="logo center-block" alt="Tozny Logo"/>
<div class="container">
<?php
    if (!empty($_SESSION['msg'])) {
        echo '<h3 class="error-msg col-sm-12">' . $_SESSION['msg'] . '</h3>';
        unset($_SESSION['msg']);
    }
?>
</div>
<div class="container" id="login" style="display:none">

    <div id="content" class="col-sm-12">
        <div class="col-sm-offset-4 col-sm-4">
            <form action="/<?= $baseurlpath ?>module.php/core/authenticate.php?as=<?= $authSrcId ?>" method="post" name="f"  class="form-horizontal" role="form">
                <div class="form-group">
                    <label for="username" class="col-sm-4 control-label">Username</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control" tabindex="1" id="username" name="username" placeholder="">
                    </div>
                    <label for="password" class="col-sm-4 control-label">Password</label>
                    <div class="col-sm-12">
                        <input type="password" class="form-control" tabindex="2" id="password" name="password">
                    </div>
                    <div class="col-sm-offset-2 col-sm-8">
                        <input type="submit" id="install-btn" class="btn btn-default col-sm-12" value="Login"/>
                    </div>
                </div>
                <input type="hidden" name="auth_type" value="ldap"/>
            </form>
        </div>

        <div class="col-sm-offset-4 col-sm-4">
            <form method="post" action="/<?= $baseurlpath ?>module.php/core/authenticate.php?as=<?= $authSrcId ?>" id="tozny-form">
                <input type="hidden" name="tozny_action" value="tozny_login">
                <input type="hidden" name="auth_type" value="tozny"/>
                <div id="qr_code_login" class="center-block"></div>
            </form>
        </div>

        <div class="col-sm-offset-4 col-sm-4">
            <form action="#provision" method="get">

                <button type="submit" id="add-device-btn" class="btn btn-default col-sm-offset-2 col-sm-8">Add a new device</button>
            </form>
        </div>
    </div>
    <!-- #content -->

</div>
<div class="container" id="provision" style="display:none">
    <div class="col-sm-offset-4 col-sm-4">
        <h4> Add a new device</h4>
        <p class="instruct"> Before starting, please ensure that you have the Tozny App installed on your device.
            Enter your LDAP credentials below.
            After you have successfully authenticated, you will be presented with a QR code for adding your device.
            Please scan the QR code using the Tozny app on your device.
            After you have added you phone, click back to the login page and then scan the login QR code.
        </p>
    </div>
    <div class="col-sm-offset-4 col-sm-4">
        <form action="/<?= $baseurlpath ?>module.php/core/authenticate.php?as=<?= $authSrcId ?>" method="post" name="f"  class="form-horizontal" role="form">
            <div class="form-group">
                <label for="username" class="col-sm-4 control-label">Username</label>
                <div class="col-sm-12">
                    <input type="text" class="form-control" tabindex="1" id="username" name="username" placeholder="">
                </div>
                <label for="password" class="col-sm-4 control-label">Password</label>
                <div class="col-sm-12">
                    <input type="password" class="form-control" tabindex="2" id="password" name="password">
                </div>
                <div class="col-sm-offset-2 col-sm-8">
                    <input type="submit" id="install-btn" class="btn btn-default col-sm-12" value="Login"/>
                </div>
            </div>
            <input type="hidden" name="auth_type" value="ldap-provision"/>
        </form>
    </div>
</div>
<div class="container">
    <div class="col-md-offset-4 col-sm-4 copyright">Copyright &copy; 2014<a href="http://www.tozny.com/">Tozny</a></div>
</div>

<!-- #wrap -->
<!--<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>-->
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
<script src="<?= $_SESSION['api_url'] . 'interface/javascript/jquery.tozny.js' ?>"></script>
<script src="tozny.js"></script>
<script type="text/javascript">
        init({
            'type': 'verify',
            'realm_key_id': '<?= $_SESSION['realm_key_id']; ?>',
            'session_id': '<?= $_SESSION['tozny_session_id']; ?>',
            'qr_url': '<?= $_SESSION['qrUrl']; ?>',
            'api_url': '<?= $_SESSION['api_url'] . 'index.php' ?>',
            'loading_image': '<?= $_SESSION['api_url'] ?>interface/javascript/images/loading.gif',
            'login_button_image': '<?= $_SESSION['api_url'] ?>interface/javascript/images/click-to-login-black.jpg',
            'mobile_url': '<?= $_SESSION['mobile_url']; ?>',
            'form_type': 'custom',
            'form_id': 'tozny-form',
            'debug': true
        });
</script>
</body>
</html>
