<?php
    $loginurl = "http://sandbox.seqrd.com/simplesaml/module.php/core/authenticate.php?as=ldapSeqrd";
    session_start();

    if (empty($_SESSION['seqrd_session_id'])) {
        header('Location: ' . $loginurl);
    }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Authentication</title>

	<!-- seqrd stuff -->

    <link rel="stylesheet" type="text/css" href="seqrd.css" />
    
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="//sandbox.seqrd.com/api/interface/javascript/jquery.seqrd.js"></script>
    <script type="text/javascript">
    $(document).ready(function() {

            $('#qr_code_login').seqrd({
                'type': 'verify',
                'realm_key_id':'<?php echo $_SESSION['realm_key_id']; ?>',
                'session_id': '<?php echo $_SESSION['seqrd_session_id']; ?>',
                'qr_url': '<?php echo $_SESSION['qrUrl']; ?>',
                'mobile_url': 'http://what.is.this./',
                'form_type': 'custom',
                'form_id':'seqrd-form',
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
<img src="/simplesaml/resources/seqrd/logo-seqrd_white.png" class="logo" alt="Seqrd Logo" />
</a>
</div>
<div id="page_header">
        	Authentication        </div>
        <div class="clear"></div>
	</div>

	
		<div id="content">




<h2 class="main">Authentication</h2>


<!--  LOGIN PART OF THE SITE  -->

    <form action="/simplesaml/module.php/core/authenticate.php?as=ldapSeqrd" method="post" name="f">
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


<form method="post" action="/simplesaml/module.php/core/authenticate.php?as=ldapSeqrd" id="seqrd-form">
<input type="hidden" name="seqrd_action" value="seqrd_login">
<input type="hidden" name="auth_type" value="seqrd" />
<div id="qr_code_login"></div>
</form>
<a href="<?php echo $_SESSION['authUrl'];?>"><img src="logInWithBluetooth.png"></a>

<!--  END LOGIN PART OF THE SITE -->

        <hr />

        Copyright &copy; 2013 <a href="http://seqrd.com/">Seqrd</a>
        
        <br style="clear: right" />
    
    </div><!-- #content -->

</div><!-- #wrap -->

</body>
</html>
