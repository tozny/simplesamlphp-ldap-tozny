<?php
$session = SimpleSAML_Session::getInstance();

$base_url_path = SimpleSAML_Configuration::getInstance()->getBaseURL();
$auth_id       = $session->getData('ldapTozny.session', 'auth_id');
$error_message = $session->getData('ldapTozny.session', 'error_message');
$realm_key_id  = $session->getData('ldapTozny.session', 'realm_key_id');
$api_url       = $session->getData('ldapTozny.session', 'api_url');
$login_title   = $session->getData('ldapTozny.session', 'login_title');
$login_css     = $session->getData('ldapTozny.session', 'login_css');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title><?= $login_title ?></title>
    <link rel="stylesheet" type="text/css" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="https://s3-us-west-2.amazonaws.com/tozny/production/interface/javascript/v2/tozny.css" />
    <link rel="stylesheet" type="text/css" href="<?= $login_css ?>" />
    <meta name="robots" content="noindex, nofollow"/>
</head>
<body>
<div id="wrapper">

<div class="container errors">
<?php if (!empty($error_message)): ?>
    <h3 class="error-msg col-sm-12"><?= $error_message ?></h3>
    <?php $session->deleteData('ldapTozny.session', 'error_message'); ?>
<?php endif ?>
</div>

<div class="container" id="login">

    <div id="content" class="col-sm-12">
        <div class="overlay"></div>
        <div class="row">
            <div class="col-md-12 text-center header">
                <h1><?= $login_title ?></h1>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-4 col-sm-offset-1">
                <div class="login-box">
                    <form action="/<?= $base_url_path ?>module.php/core/authenticate.php" method="post" name="f"  class="form-horizontal" role="form">
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
                                <input type="submit" id="install-btn" class="btn btn-primary col-sm-12" value="Login"/>
                            </div>
                        </div>
                        <input type="hidden" name="as" value="<?= $auth_id ?>"/>
                        <input type="hidden" name="auth_type" value="ldap"/>
                    </form>
                </div>
            </div>

            <div class="col-sm-offset-1 col-sm-4">
                <div class="row" id="toz-form">
                    <div class="col-sm-12">
                        <form method="post" action="/<?= $base_url_path ?>module.php/core/authenticate.php" id="tozny-form">
                            <input type="hidden" name="as" value="<?= $auth_id ?>"/>
                            <input type="hidden" name="tozny_action" value="tozny_login"/>
                            <input type="hidden" name="auth_type" value="tozny"/>
                            <div id="qr_code_login" class="center-block"></div>
                        </form>
                    </div>
                </div>
                <div class="row" id="new-device">
                    <div class="col-sm-12">
                        <form action="#provision" method="get">
                            <button type="submit" id="add-device-btn" class="btn btn-link col-sm-offset-2 col-sm-8">+Add a new device</button>
                            <input type="hidden" name="as" value="<?= $auth_id ?>"/>
                        </form>
                    </div>
                </div>

            </div>

        </div>

        <footer>
            <div class="row">
                <div class="col-md-offset-4 col-sm-4 copyright">Copyright &copy; 2014<a target="_blank" href="http://www.tozny.com/">Tozny</a></div>
            </div>
        </footer>
    </div>
    <!-- #content -->

</div>
<div class="container" id="provision">
    <div class="overlay"></div>
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
        <form action="/<?= $base_url_path ?>module.php/core/authenticate.php" method="post" name="f" id="g" class="form-horizontal" role="form">
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
            <input type="hidden" name="as" value="<?= $auth_id ?>"/>
            <input type="hidden" name="auth_type" value="ldap-provision"/>
        </form>
    </div>

    <footer>
        <div class="row">
            <div class="col-md-offset-4 col-sm-4 copyright">Copyright &copy; 2014<a target="_blank" href="http://www.tozny.com/">Tozny</a></div>
        </div>
    </footer>
</div>
</div>
<!-- #wrap -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.js"></script>
<?php // TODO: default is to point to production jquery.tozny.js, make a developer config setting to point to local version ?>
<!--<script src="--><?//= $_SESSION['api_url'] . 'interface/javascript/jquery.tozny.js' ?><!--"></script>-->
<script src="https://s3-us-west-2.amazonaws.com/tozny/production/interface/javascript/v2/jquery.tozny.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        if (window.location.hash === '#provision') {
            $('#provision').show();
            $('#login').hide();
            $('#qr_code_login').empty();
            provisionFormHandler();
        } else {
            $('#provision').hide();
            $('#login').show();
            $('#qr_code_login').tozny({
                'style':'box',
                'theme':'clear-light',
                'type': 'login',
                'realm_key_id': '<?= $realm_key_id ?>',
                'api_url': '<?= $api_url . 'index.php' ?>',
                'form_type': 'custom',
                'form_id': 'tozny-form',
                'debug': false
            });
        }
    });
</script>
</body>
</html>
