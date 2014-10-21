<?php

/**
 * Example external authentication source.
 *
 * This class is an example authentication source which is designed to
 * hook into an external authentication system.
 *
 * To adapt this to your own web site, you should:
 * 1. Create your own module directory.
 * 2. Add a file "default-enable" to that directory.
 * 3. Copy this file and modules/exampleauth/www/resume.php to their corresponding
 *    location in the new module.
 * 4. Replace all occurrences of "exampleauth" in this file and in resume.php with the name of your module.
 * 5. Adapt the getUser()-function, the authenticate()-function and the logout()-function to your site.
 * 6. Add an entry in config/authsources.php referencing your module. E.g.:
 *        'myauth' => array(
 *            '<mymodule>:External',
 *        ),
 *
 * @package simpleSAMLphp
 * @version $Id$
 */

/*
 Requirements for an authentication source:

-   Must be derived from the `SimpleSAML_Auth_Source`-class.

-
    If a constructor is implemented, it must first call the parent
    constructor, passing along all parameters, before accessing any of
    the parameters. In general, only the $config parameter should be
    accessed.

-
    The `authenticate(&$state)`-function must be implemented. If this
    function completes, it is assumed that the user is authenticated,
    and that the $state array has been updated with the user's
    attributes.

-
    If the `authenticate`-function does not return, it must at a later
    time call `SimpleSAML_Auth_Source::completeAuth` with the new
    state. The state must be an update of the array passed to the
    `authenticate`-function.

-
    No pages may be shown to the user from the `authenticate`-function.
    Instead, the state should be saved, and the user should be
    redirected to a new page. This must be done to prevent
    unpredictable events if the user for example reloads the page.

-
    No state information about any authentication should be stored in
    the authentication source object. It must instead be stored in the
    state array. Any changes to variables in the authentication source
    object may be lost.

-
    The authentication source object must be serializable. It may be
    serializes between being constructed and the call to the
    `authenticate`-function. This means that, for example, no database
    connections should be created in the constructor and later used in
    the `authenticate`-function.

 */
/**
 * LDAP + Tozny authentication source.
 *
 * @package simpleSAMLphp
 * @version $Id$
 */
class sspmod_ldapTozny2_Auth_Source_LDAPTozny extends SimpleSAML_Auth_Source {

    /**
     * A LDAP configuration object.
     */
    private $ldapConfig;

    private $toznyConfig;

    private $loginTitle;

    private $loginCSS;

    /**
     * Constructor for this authentication source.
     *
     * @param array $info  Information about this authentication source.
     * @param array $config  Configuration.
     */
    public function __construct($info, $config) {
        assert('is_array($info)');
        assert('is_array($config)');

        /* Call the parent constructor first, as required by the interface. */
        parent::__construct($info, $config);

        $this->authId = $info['AuthId'];
        $this->loginTitle = (!empty($config['loginTitle'])) ? $config['loginTitle'] : "set the 'loginTitle' property";
        $this->loginCSS = (!empty($config['loginCSS'])) ? $config['loginCSS'] : "tozny.css";

        $this->ldapConfig = new sspmod_ldapTozny2_ConfigHelper($config, 'Authentication source ' . var_export($this->authId, TRUE));
        $this->toznyConfig = new sspmod_ldapTozny2_ToznyHelper($config);
    }

    /**
     * WARNING: If this function returns, we assume the user is authenticated and that the state value has been updated with the user's attributes.
     *
     * Main entrypoint for this authentication source.
     * Performs only routing to delagates, based on the value of the 'auth_type' parameter.
     *
     * @param array &$state  Information about the current authentication.
     */
    public function authenticate (&$state) {
        $session = SimpleSAML_Session::getInstance();

        if (empty($_REQUEST['auth_type'])) {$this->redirectToAuthPage($session);}

        if ( $_REQUEST['auth_type'] === 'ldap-provision') {
            $this->handleLdapProvisionAttempt($session, $state);
        }
        else if ($_REQUEST['auth_type'] === 'ldap') {
            $this->handleLdapLoginAttempt($session, $state);
        }
        else if ($_REQUEST['auth_type'] === 'tozny') {
            $this->handleToznyLoginAttempt($session, $state);
        }
        else {
            $this->redirectToAuthPage($session);
        }
    }

    /**
     * Provisions a new device on either a new or existing Tozny user.
     *
     * @param array $session
     * @param array $attributes
     */
    public function handleLdapProvision(&$session, &$attributes) {
        $secret_enrollment_qr_url = NULL;
        $user_id = NULL;
        $provisioned =  NULL;

        foreach ($this->ldapConfig->searchAttributes as $search_attribute) {
            try { $user_id = $this->toznyConfig->userEmailExists($search_attribute); }
            catch (Exception $e ) {
                $log_message = sprintf("Unable to provision new device via LDAP; user tried %s: %s", $search_attribute, $e->getMessage());
                SimpleSAML_Logger::error($log_message);

                $user_message = 'Unable to provision new device via LDAP. An unexpected error occurred.';
                $this->redirectToAuthPage($session, $user_message, TRUE);
            }
            if ($user_id) break;
        }


        # if we found an existing user, add a new device.
        if ($user_id) {
            $new_device = NULL;
            try { $new_device = $this->toznyConfig->realmUserDeviceAdd($user_id); }
            catch (Exception $e) {
                $log_message = sprintf("Found an existing tozny user, however failed to add a new  device. user: %s; %s", json_encode($user_id), $e->getMessage());
                SimpleSAML_Logger::error($log_message);

                $user_message = 'Unable to provision new device via LDAP. Found an existing tozny user, however the attempt to add a new device has failed.';
                $this->redirectToAuthPage($session, $user_message, TRUE);
            }

            if (!$new_device || !array_key_exists('secret_enrollment_qr_url',$new_device)) {
                $log_message = sprintf("Found an existing tozny user, however new device was null, or did not contain the expected 'secret_enrollment_qr_url' property. user: %s; new_device:%s", json_encode($user_id), json_encode($new_device));
                SimpleSAML_Logger::error($log_message);

                $user_message = 'Unable to provision new device via LDAP. Found an existing tozny user, however we were unable to successfully add a device.';
                $this->redirectToAuthPage($session, $user_message, TRUE);
            }
            $secret_enrollment_qr_url = $new_device['secret_enrollment_qr_url'];
            $provisioned = "new_device";
        }

        # otherwise we need to create the user on the Tozny side, and send the ldap attributes over to Tozny as meta fields.
        else {
            $meta = array();
            foreach ($attributes as $key => $val) {
                if (!in_array($key,$this->ldapConfig->searchAttributes)) continue;
                $meta[$key] = $val;
            }

            $user = NULL;
            try { $user = $this->toznyConfig->userAdd(true, $meta); }
            catch (Exception $e) {
                $log_message = sprintf("Failed to add a new user & device. user_meta: %s; %s", json_encode($meta), $e->getMessage());
                SimpleSAML_Logger::error($log_message);

                $user_message = 'Unable to provision new device via LDAP. The attempt to add a new account & device has failed.';
                $this->redirectToAuthPage($session, $user_message, TRUE);
            }

            if (!$user || !array_key_exists('secret_enrollment_qr_url',$user)) {
                $log_message = sprintf("Created a new user, however new device was null, or did not contain the expected 'secret_enrollment_qr_url' property. user: %s;", json_encode($user));
                SimpleSAML_Logger::error($log_message);

                $user_message = 'Unable to provision new device via LDAP. Created a new account, however we were unable to successfully add a new device.';
                $this->redirectToAuthPage($session, $user_message, TRUE);
            }

            $secret_enrollment_qr_url = $user['secret_enrollment_qr_url'];
            $provisioned = "new_user";
        }

        $session->setData('ldapTozny.session', 'secret_enrollment_qr_url', $secret_enrollment_qr_url);
        $session->setData('ldapTozny.session', 'new_user', $provisioned);
        $this->redirectToProvisionPage($session);
    }

    /**
     * WARNING: This function should not return, we assume the user if successfully authenticated via LDAP will be redirected to the provision.php page where they will be presented with their new device QR.
     *
     * LDAP previsioning overview:
     *  1. Log the user into the LDAP system.
     *  2. if LDAP is active directory, check for account disabled or locked out.
     *  3. see if the Tozny API already has a user identified by one of the fields listed in the searh.attributes config value.
     *  4a. if yes, then create a new device on for the existing user
     *  4b. if no, then create a new Tozny user, with a new device.
     *  5. set the new user/device's secret_enrollment_qr_url in the SAML session
     *  6. redirect to provision.php
     */
    public function handleLdapProvisionAttempt(&$session, &$state) {
        try {
            /* Attempt LDAP login. */
            $username = $_REQUEST['username'];
            $password = $_REQUEST['password'];
            $attributes = $this->ldapConfig->login($username, $password);

            $this->checkActiveDirectoryPermissions($session, $attributes, $username);

            $this->handleLdapProvision($session, $attributes);
        } catch (SimpleSAML_Error_Error $e) {
            $log_message = sprintf("Error while handling LDAP provision attempt: %s;", $e->getMessage());
            SimpleSAML_Logger::error($log_message);

            $msg = 'Unable to login via LDAP. Error while provisioning new device.';
            $this->redirectToAuthPage($session, $msg, TRUE);
        } catch (Exception $e) {
            $log_message = sprintf("Error while handling LDAP provision attempt: %s;", $e->getMessage());
            SimpleSAML_Logger::error($log_message);

            $msg = 'Unable to login via LDAP. Error while provisioning new device.';
            $this->redirectToAuthPage($session, $msg, TRUE);
        }
    }

    /**
     * WARNING: If this function returns, we assume the user is allowed to login and that the SimpleSAML state value has been updated with the user's attributes.
     *
     * LDAP login overview:
     *  1. Log the user into the LDAP system.
     *  2. if LDAP is active directory, check for account disabled or locked out.
     *  3. set SimpleSAMLPHP state 'Attributes'.
     *
     */
    public function handleLdapLoginAttempt(&$session, &$state) {
        try {
            /* Attempt LDAP login. */
            $username = $_REQUEST['username'];
            $password = $_REQUEST['password'];
            $attributes = $this->ldapConfig->login($username, $password);

            $this->checkActiveDirectoryPermissions($session, $attributes, $username);

            $state['Attributes'] = $attributes;
            return;
        } catch (SimpleSAML_Error_Error $e) {
            $log_message = sprintf("Error while attempting to LDAP login.: %s;", $e->getMessage());
            SimpleSAML_Logger::error($log_message);

            $user_message = 'Unable to login via LDAP. Error while attempting to login.';
            $this->redirectToAuthPage($session, $user_message);
        }
    }

    /**
     * WARNING: If this function returns, we assume the user is allowed to login and that the SimpleSAML state value has been updated with the user's attributes.
     *
     * TOZNY login overview:
     *  1. verify integrety given 'tozny_signed_data' & 'tozny_signature' form values.
     *  2. ask TOZNY for the user record for the user_id stored in the signed data from step 1.
     *  3. look at the user record for the  'ldap_user_identity_tozny_field' property.
     *  4. bind to the LDAP server, query for attributes of the user.
     *  5. if LDAP is active directory, check for account disabled or locked out.
     *  6. set SimpleSAMLPHP state 'Attributes'.
     *
     */
    public function handleToznyLoginAttempt(&$session, &$state) {
        $session_data = $this->toznyConfig->verifyLogin($_REQUEST['tozny_signed_data'],$_REQUEST['tozny_signature']);

        if (empty($session_data)) {
            $log_message = sprintf("Tozny session data returned from verifyLogin() was empty. tozny_signed_data: %s; tozny_signature: %s;", $_REQUEST['tozny_signed_data'], $_REQUEST['tozny_signature']);
            SimpleSAML_Logger::error($log_message);

            $user_message = 'Unable to login via TOZNY. Incorrect invalid signature.';
            $this->redirectToAuthPage($session, $user_message);
        }
        else {
            $user_record = NULL;
            try {$user_record = $this->toznyConfig->userGet($session_data['user_id']);}
            catch (Exception $e) {
                $log_message = sprintf("Error retrieving TOZNY user record returned from userGet(). session_data: %s;", json_encode($session_data));
                SimpleSAML_Logger::error($log_message);

                $user_message = 'Unable to login via TOZNY. Invalid user record.';
                $this->redirectToAuthPage($session, $user_message);
            }
            if (empty($user_record)) {
                $log_message = sprintf("TOZNY user record returned from userGet() was empty. session_data: %s;", json_encode($session_data));
                SimpleSAML_Logger::error($log_message);

                $user_message = 'Unable to login via TOZNY. Invalid user record.';
                $this->redirectToAuthPage($session, $user_message);
            }
            else {
                if (!array_key_exists($this->toznyConfig->ldap_user_identity_tozny_field, $user_record) ||
                    empty($user_record[$this->toznyConfig->ldap_user_identity_tozny_field])) {
                    $log_message = sprintf("TOZNY user record did not contain '%s' record. user_record: %s;", $this->toznyConfig->ldap_user_identity_tozny_field, json_encode($user_record));
                    SimpleSAML_Logger::error($log_message);

                    $user_message = 'Unable to login via TOZNY. Invalid or missing LDAP user identification record.';
                    $this->redirectToAuthPage($session, $user_message);
                }
                else {
                    $attributes = NULL;
                    $user_id = $user_record[$this->toznyConfig->ldap_user_identity_tozny_field];

                    // attempt to bind to the LDAP server and retrieve the given user's attributes
                    try { $attributes = $this->ldapConfig->priv_login($user_id); }
                    catch (Exception $e) {
                        $log_message = sprintf("Error retrieving LDAP properties for user %s. user_record: %s;", $user_id , json_encode($user_record));
                        SimpleSAML_Logger::error($log_message);

                        $user_message = 'Unable to login via TOZNY. Unable to retrieve LDAP properties for TOZNY user.';
                        $this->redirectToAuthPage($session,$user_message);
                    }

                    $this->checkActiveDirectoryPermissions($session, $attributes, $user_id);

                    $state['Attributes'] = $attributes;
                    return;
                }
            }
        }
    }

    /**
     *
     * If this function returns, we assume the user is allowed to login.
     * if the Active Directory 'userAccountControl' property is set,
     * check if the user is disabled or locked out.
     *
     */
    public function checkActiveDirectoryPermissions (&$session, $attributes, $user_id) {
        if (sspmod_ldapTozny2_ActiveDirectoryHelper::lockedOut($attributes)) {
            $log_message = sprintf("User %s is currently locked out in Active Directory (see userAccountControl attribute). attributes: %s;", $user_id, json_encode($attributes));
            SimpleSAML_Logger::error($log_message);

            $user_message = 'Unable to login via TOZNY. Active Directory user is currently locked out';
            $this->redirectToAuthPage($session, $user_message);
        }
        if (sspmod_ldapTozny2_ActiveDirectoryHelper::accountDisabled($attributes)) {
            $log_message = sprintf("User %s is currently disabled in Active Directory (see userAccountControl attribute). attributes: %s;", $user_id, json_encode($attributes));
            SimpleSAML_Logger::error($log_message);

            $user_message = 'Unable to login via TOZNY. Active Directory user is currently disabled';
            $this->redirectToAuthPage($session, $user_message);
        }
    }

    public function redirectToAuthPage (&$session, $msg = NULL, $provision = FALSE) {

        $session->setData('ldapTozny.session', 'auth_id',      $this->authId);
        $session->setData('ldapTozny.session', 'api_url',      $this->toznyConfig->api_url);
        $session->setData('ldapTozny.session', 'realm_key_id', $this->toznyConfig->realm_key_id);
        $session->setData('ldapTozny.session', 'login_title',  $this->loginTitle);
        $session->setData('ldapTozny.session', 'login_css',    $this->loginCSS);

        if (!empty($msg)) {
            $session->setData('ldapTozny.session', 'error_message',  $msg);
        }

        $provision_hash = ($provision) ? '#provision' : '';

        $auth_page_url = SimpleSAML_Module::getModuleURL('ldapTozny2/authpage.php');


        // TODO: Use (Un)truested redirect return SimpleSAML_Utilities::redirectTrustedURL($authPageUrl, $params);
        SimpleSAML_Utilities::redirect($auth_page_url . $provision_hash);

        // The redirect function never returns, so we never get this far.
        assert('FALSE');
    }

    public function redirectToProvisionPage (&$session) {

        $session->setData('ldapTozny.session', 'login_title',  $this->loginTitle);
        $session->setData('ldapTozny.session', 'login_css',    $this->loginCSS);

        $provision_page_url = SimpleSAML_Module::getModuleURL('ldapTozny2/provision.php');
        SimpleSAML_Utilities::redirect($provision_page_url);

        // The redirect function never returns, so we never get this far.
        assert('FALSE');
    }

}

/**
 * A Helper class for loading Tozny libs, configuring the Tozny SDK, wrapping Tozny API calls,
 */
class sspmod_ldapTozny2_ToznyHelper {


    public $realm_key_id;
    public $api_url;
    public $ldap_user_identity_tozny_field;
    private $realm_secret_key;

    public function __construct($config) {
        assert('is_array($config)');

        $this->realm_key_id = $config['realm_key_id'];
        $this->realm_secret_key = $config['realm_secret_key'];
        $this->api_url = $config['api_url'];
        $this->ldap_user_identity_tozny_field = $config['ldap_user_identity_tozny_field'];

        // TODO: validate settings

        # locate the tozny-client library on the include path.
        $paths = explode(PATH_SEPARATOR, get_include_path());
        $foundClient = false;
        foreach ($paths as $path) {
            if (file_exists($path . '/ToznyRemoteUserAPI.php')) {
                $foundClient = true;
                break;
            }
        }
        # if we couldnt find it, add the /var/www/library/tozny_common directory exists and is readable, then add it to the include path.
        if (!$foundClient) {
            if (!file_exists('/var/www/library/tozny_client/ToznyRemoteUserAPI.php')) {
                throw new Exception(sprintf("Could not locate Tozny Client library. Is it on the include path and readable? include_path: %s", get_include_path()));
            }
            set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/library/tozny_client');
        }

        require_once "ToznyRemoteUserAPI.php";
        require_once "ToznyRemoteRealmAPI.php";

        $this->realm_api = new Tozny_Remote_Realm_API($this->realm_key_id, $this->realm_secret_key, $this->api_url);
    }

    public function verifyLogin ($signed_data, $signature) { return $this->realm_api->verifyLogin($signed_data, $signature); }
    public function userGet ($user_id) { return $this->realm_api->userGet($user_id); }
    public function userEmailExists ($email) { return $this->realm_api->userEmailExists($email); }
    public function realmUserDeviceAdd($user_id) { return $this->realm_api->realmUserDeviceAdd($user_id); }
    public function userAdd($defer, $meta) { return $this->realm_api->userAdd($defer, $meta); }
}

/**
 * Helper class for inspecting Active Directory permissions.
 *
 */
class sspmod_ldapTozny2_ActiveDirectoryHelper {
    // from: http://support.microsoft.com/kb/305144
    const SCRIPT                         = 0x00000001;
    const ACCOUNTDISABLE                 = 0x00000002;
    const HOMEDIR_REQUIRED               = 0x00000008;
    const LOCKOUT                        = 0x00000010;
    const PASSWD_NOTREQD                 = 0x00000020;
    const PASSWD_CANT_CHANGE             = 0x00000040;
    const ENCRYPTED_TEXT_PWD_ALLOWED     = 0x00000080;
    const TEMP_DUPLICATE_ACCOUNT         = 0x00000100;
    const NORMAL_ACCOUNT                 = 0x00000200;
    const INTERDOMAIN_TRUST_ACCOUNT      = 0x00000800;
    const WORKSTATION_TRUST_ACCOUNT      = 0x00001000;
    const SERVER_TRUST_ACCOUNT           = 0x00002000;
    const DONT_EXPIRE_PASSWORD           = 0x00010000;
    const MNS_LOGON_ACCOUNT              = 0x00020000;
    const SMARTCARD_REQUIRED             = 0x00040000;
    const TRUSTED_FOR_DELEGATION         = 0x00080000;
    const NOT_DELEGATED                  = 0x00100000;
    const USE_DES_KEY_ONLY               = 0x00200000;
    const DONT_REQ_PREAUTH               = 0x00400000;
    const PASSWORD_EXPIRED               = 0x00800000;
    const TRUSTED_TO_AUTH_FOR_DELEGATION = 0x01000000;
    const PARTIAL_SECRETS_ACCOUNT        = 0x04000000;

    /**
     * @param $haystack int The combined values to search for the needle within.
     * @param $needle int The flag to test for in the haystack.
     * @return bool True if the needle is in the haystack.
     */
    public static function test($haystack, $needle) {
        return (($haystack & $needle) == $needle);
    }

    public static function isScript($haystack) { return self::test($haystack, self::SCRIPT); }
    public static function isAccountDisable($haystack) { return self::test($haystack, self::ACCOUNTDISABLE); }
    public static function isHomedirRequired($haystack) { return self::test($haystack, self::HOMEDIR_REQUIRED); }
    public static function isLockout($haystack) { return self::test($haystack, self::LOCKOUT); }
    public static function isPasswdNotReqd($haystack) { return self::test($haystack, self::PASSWD_NOTREQD); }
    public static function isPasswdCantChange($haystack) { return self::test($haystack, self::PASSWD_CANT_CHANGE); }
    public static function isEncryptedTextPwdAllowed($haystack) { return self::test($haystack, self::ENCRYPTED_TEXT_PWD_ALLOWED); }
    public static function isTempDuplicateAccount($haystack) { return self::test($haystack, self::TEMP_DUPLICATE_ACCOUNT); }
    public static function isNormalAccount($haystack) { return self::test($haystack, self::NORMAL_ACCOUNT); }
    public static function isInterdomainTrustAccount($haystack) { return self::test($haystack, self::INTERDOMAIN_TRUST_ACCOUNT); }
    public static function isWorkstationTrustAccount($haystack) { return self::test($haystack, self::WORKSTATION_TRUST_ACCOUNT); }
    public static function isServerTrustAccount($haystack) { return self::test($haystack, self::SERVER_TRUST_ACCOUNT); }
    public static function isDontExpirePassword($haystack) { return self::test($haystack, self::DONT_EXPIRE_PASSWORD); }
    public static function isMnsLogonAccount($haystack) { return self::test($haystack, self::MNS_LOGON_ACCOUNT); }
    public static function isSmartcardRequired($haystack) { return self::test($haystack, self::SMARTCARD_REQUIRED); }
    public static function isTrustedForDelegation($haystack) { return self::test($haystack, self::TRUSTED_FOR_DELEGATION); }
    public static function isNotDelegated($haystack) { return self::test($haystack, self::NOT_DELEGATED); }
    public static function isUseDesKeyOnly($haystack) { return self::test($haystack, self::USE_DES_KEY_ONLY); }
    public static function isDontReqPreauth($haystack) { return self::test($haystack, self::DONT_REQ_PREAUTH); }
    public static function isPasswordExpired($haystack) { return self::test($haystack, self::PASSWORD_EXPIRED); }
    public static function isTrustedToAuthForDelegation($haystack) { return self::test($haystack, self::TRUSTED_TO_AUTH_FOR_DELEGATION); }
    public static function isPartialSecretsAccount($haystack) { return self::test($haystack, self::PARTIAL_SECRETS_ACCOUNT); }

    public static function lockedOut ($attributes) {
        return (!empty($attributes['userAccountControl'])) && sspmod_ldapTozny2_ActiveDirectoryHelper::isLockout($attributes['userAccountControl']);
    }
    public static function accountDisabled ($attributes) {
        return (!empty($attributes['userAccountControl'])) && sspmod_ldapTozny2_ActiveDirectoryHelper::isAccountDisable($attributes['userAccountControl']);
    }
}

