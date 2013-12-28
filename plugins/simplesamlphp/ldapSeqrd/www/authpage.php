<?php
/**
 *
 * @package default
 */


/**
 * Login page for SEQRD API.
 *
 * @package simpleSAMLphp
 * @version $Id$
 */
if (!isset($_REQUEST['ReturnTo'])) {
    throw new SimpleSAML_Error_Exception('Missing ReturnTo parameter.');
}

$returnTo = $_REQUEST['ReturnTo'];

session_start();

// We logged in with ldap
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_REQUEST['auth_type'] == 'ldap') {
    // Ldap login failed
    if (empty($_SESSION['uid'])) {
        ; //wrong ldap username and password, add message to display here
    // Ldap login worked
    } else {
        header('Location: ' . $returnTo);
        exit();
    }
}

/*
 * The following piece of code would never be found in a real authentication page. Its
 * purpose in this example is to make this example safer in the case where the
 * administrator of * the IdP leaves the exampleauth-module enabled in a production
 * environment.
 *
 * What we do here is to extract the $state-array identifier, and check that it belongs to
 * the exampleauth:External process.
 */


if (!preg_match('@State=(.*)@', $returnTo, $matches)) {
    die('Invalid ReturnTo URL for this example.');
}


/*
 * The loadState-function will not return if the second parameter does not
 * match the parameter passed to saveState, so by now we know that we arrived here
 * through the exampleauth:External authentication page.
 */
$stateId = urldecode($matches[1]);
$state = SimpleSAML_Auth_State::loadState($stateId, 'LDAPSeqrd:External');


/*
 * This code handles the login response.
 */

require_once $state['LDAPSeqrd:seqrd_import']. "/SeqrdRemoteUserAPI.php";
require_once $state['LDAPSeqrd:seqrd_import']. "/SeqrdRemoteRealmAPI.php";

set_include_path(get_include_path() . PATH_SEPARATOR . $state['LDAPSeqrd:tiqr_directory']);

require_once "Tiqr/OATH/OCRAWrapper.php";

$realm_key_id = $state['LDAPSeqrd:realm_key_id'];
$secret = $state['LDAPSeqrd:realm_secret_key'];
$userApi = new SEQRD_Remote_User_API($realm_key_id, $state['LDAPSeqrd:api_url']);
$siteApi = new SEQRD_Remote_Realm_API($realm_key_id, $secret, $state['LDAPSeqrd:api_url']);
$missingRealm = FALSE;

$noSetSession = FALSE;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_SESSION['seqrd_session_id'])) {
        $check = $userApi->checkSessionStatus($_SESSION['seqrd_session_id']);
        if (!empty($check['status']) && $check['status'] === 'pending') {
            //Pended too long.
        }
        if (!empty ($check['return']) && $check['return'] === 'error') {
            //Invalid login, give them a new code
        } else if (!empty($check['signature'])) {
            //Should be logged in
            $decoded = $siteApi->checkSigGetData($check);
            if ($decoded) {
                $user = $siteApi->userGet($decoded['user_id']);
                $_SESSION['user_meta'] = array();
                foreach ($user as $key => $val) {
                    if (in_array(strtolower($key), ['user_id', 'return', 'status_code'])) {
                        continue;
                    } else {
                        $_SESSION['user_meta'][$key] = $val;
                    }
                }
                if (!empty($user['seqrd_username'])) {
                    $_SESSION['uid'] = $user['seqrd_username'];
                } else {
                    $_SESSION['uid'] = $decoded['user_id'];
                }

                header('Location: ' . $returnTo);
                exit();
            } else {
                SimpleSAML_Auth_State::throwException($state,
                        new SimpleSAML_Error_Exception('Unable to match payload signature with private key.'));
            }
        }
    } else {
        SimpleSAML_Auth_State::throwException($state,
                new SimpleSAML_Error_Exception('Expected a session_id in payload.'));
    }
}

/*
 * Fetch a login challenge, extract the session ID and the QR code
 *
 */
$challenge = $userApi->loginChallenge();

//Save the session ID for later when we receive the response.
$_SESSION['seqrd_session_id'] = $challenge['session_id'];
$qrURL = $challenge['qr_url'];
$authUrl = "seqrdauth://sandbox.seqrd.com/api/"
           . "?s=" . $challenge['session_id']
           . "&c=" . $challenge['challenge']
           . "&r=" . $challenge['realm_key_id'];

/*
 * If we get this far, we need to show the login page to the user.
 */
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
                'realm_key_id':'<?php echo $realm_key_id; ?>',
                'session_id': '<?php echo $challenge['session_id']; ?>',
                'qr_url': '<?php echo $qrURL; ?>',
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

<input type="hidden" name="ReturnTo" value="<?php echo htmlspecialchars($returnTo); ?>">
<input type="hidden" name="auth_type" value="ldap" />
    </form>


<form method="post" action="?" id="seqrd-form">
<input type="hidden" name="ReturnTo" value="<?php echo htmlspecialchars($returnTo); ?>">
<input type="hidden" name="realm_key_id" value="<?php echo htmlspecialchars($realm_key_id); ?>">
<input type="hidden" name="seqrd_action" value="seqrd_login">
<input type="hidden" name="auth_type" value="seqrd" />
<div id="qr_code_login"></div>
</form>
<?php echo "<a href=\"" . $authUrl . "\"><img src=\"logInWithBluetooth.png\"></a>" ?> 

<!--  END LOGIN PART OF THE SITE -->

        <hr />

        Copyright &copy; 2013 <a href="http://seqrd.com/">Seqrd</a>
        
        <br style="clear: right" />
    
    </div><!-- #content -->

</div><!-- #wrap -->

</body>
</html>
