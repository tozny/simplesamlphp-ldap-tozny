<?php
    session_start();

    $authSrcId = $_SESSION['authSrcId'];
    $baseurlpath = SimpleSAML_Configuration::getInstance()->getBaseURL();

    $loginUrl = "/$baseurlpath" . "module.php/core/authenticate.php?as=$authSrcId";

    if (empty($_SESSION['tozny_session_id'])) {
        header('Location: ' . $loginUrl);
    }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Authentication</title>

	<!-- tozny stuff -->

    <link rel="stylesheet" type="text/css" href="tozny.css" />
    
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="<?= $_SESSION['api_url'] . 'interface/javascript/jquery.tozny.js'?>"></script>
    <script type="text/javascript">
    $(document).ready(function() {

            $('#qr_code_login').tozny({
                'type': 'verify',
                'realm_key_id':'<?= $_SESSION['realm_key_id']; ?>',
                'session_id': '<?= $_SESSION['tozny_session_id']; ?>',
                'qr_url': '<?= $_SESSION['qrUrl']; ?>',
                'api_url': '<?= $_SESSION['api_url'] . 'index.php' ?>',
                'mobile_url': '<?= $_SESSION['mobile_url']; ?>',
                'form_type': 'custom',
                'form_id':'tozny-form',
                'debug':true
                });

            });
    </script>

<meta name="robots" content="noindex, nofollow" />


</head>
<body id="">

<div id="wrap">

<div id="header">
<div id="logo">
<a style="text-decoration: none; color: white" href="/">
<img src="tozny.png" class="logo" alt="Tozny Logo" />
</a>
</div>
<div id="page_header">
        	Authentication        </div>
        <div class="clear"></div>
	</div>

	
		<div id="content">




<h2 class="main">Authentication</h2>
<?php if (!empty($_SESSION['msg'])) {
    echo '<h3 class="error-msg">'.$_SESSION['msg'].'</h3>';

}
?>
<!--  LOGIN PART OF THE SITE  -->

    <form action="/<?= $baseurlpath ?>module.php/core/authenticate.php?as=<?= $authSrcId ?>" method="post" name="f">
    <table>
        <tr>
            <td style="padding: .3em;">Username</td>
            <td>
<input type="text" id="username" tabindex="1" name="username" value="" />           </td>
            <td style="padding: .4em;" rowspan="2">
                <input type="submit" tabindex="4" value="Login" />          </td>
        </tr>
        <tr>
            <td style="padding: .3em;">Password</td>
            <td><input id="password" type="password" tabindex="2" name="password" /></td>
        </tr>


    </table>

<input type="hidden" name="auth_type" value="ldap" />
    </form>


<form method="post" action="/<?= $baseurlpath ?>module.php/core/authenticate.php?as=<?= $authSrcId ?>" id="tozny-form">
<input type="hidden" name="tozny_action" value="tozny_login">
<input type="hidden" name="auth_type" value="tozny" />
<div id="qr_code_login"></div>
</form>

<!--  END LOGIN PART OF THE SITE -->

        <hr />

        Copyright &copy; 2013 <a href="http://tozny.com/">Tozny</a>
        
        <br style="clear: right" />
    
    </div><!-- #content -->

</div><!-- #wrap -->

</body>
</html>
