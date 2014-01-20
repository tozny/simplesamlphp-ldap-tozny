<?php
/**
 * simplesamlphp-modules/seqrdauth/constants.php
 *
 * @package default
 */


require_once "lib/Auth/Source/ToznyRemoteUserAPI.php";
require_once "lib/Auth/Source/ToznyRemoteRealmAPI.php";


/*
 * TODO In reality, we would look up the priv/pub keys
 * from the site API, using the master key to authenticate.
 */

$secrets = array(
    'ROCKSTAR' => 'DEADBEEF2',
);


?>
