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
 * LDAP authentication source.
 *
 * See the ldap-entry in config-templates/authsources.php for information about
 * configuration of this authentication source.
 *
 * This class is based on www/auth/login.php.
 *
 * @package simpleSAMLphp
 * @version $Id$
 */
class sspmod_ldapTozny_Auth_Source_LDAPTozny extends SimpleSAML_Auth_Source {

    /**
     * A LDAP configuration object.
     */
    private $ldapConfig;


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

        $this->realm_key_id = $config['realm_key_id'];
        $this->realm_secret_key = $config['realm_secret_key'];
        $this->api_url = $config['api_url'];

        require_once "ToznyRemoteUserAPI.php";
        require_once "ToznyRemoteRealmAPI.php";

        set_include_path(get_include_path());


        $this->ldapConfig = new sspmod_ldap_ConfigHelper($config,
            'Authentication source ' . var_export($this->authId, TRUE));
    }


    /**
     * Attempt to log in using the given username and password.
     *
     * @param string $username  The username the user wrote.
     * @param string $password  The password the user wrote.
     * param array $sasl_arg  Associative array of SASL options
     * @return array  Associative array with the users attributes.
     */
    protected function login($username, $password, array $sasl_args = NULL) {
        assert('is_string($username)');
        assert('is_string($password)');

        $attributes = $this->ldapConfig->login($username, $password, $sasl_args);

        return $attributes;
    }

    /**
     * This function is called when the user starts a logout operation, for example
     * by logging out of a SP that supports single logout.
     *
     * @param array &$state  The logout state array.
     */
    public function logout(&$state) {
        assert('is_array($state)');

        if (!session_id()) {
            /* session_start not called before. Do it here. */
            session_start();
        }

        session_destroy();

        /*
         * If we need to do a redirect to a different page, we could do this
         * here, but in this example we don't need to do this.
         */
    }



    /**
     * Retrieve attributes for the user.
     *
     * @return array|NULL  The user's attributes, or NULL if the user isn't authenticated.
     */
    private function getUser() {

        /*
         * In this example we assume that the attributes are
         * stored in the users PHP session, but this could be replaced
         * with anything.
         */

        if (!session_id()) {
            /* session_start not called before. Do it here. */
            session_start();
        }

        if (!isset($_SESSION['uid'])) {
            /* The user isn't authenticated. */
            return NULL;
        }

        /*
         * Find the attributes for the user.
         * Note that all attributes in simpleSAMLphp are multivalued, so we need
         * to store them as arrays.
         */

        $attributes = array(
            'uid' => array($_SESSION['uid']),
        );
        if(isset($_SESSION['user_meta'])) {
            foreach ($_SESSION['user_meta'] as $key => $val) {
                if (in_array($key, ['user_id', 'return', 'status_code'])) {
                    continue;
                }
                if ($key == 'meta') {
                    if (is_array($val)) {
                        foreach ($val as $k => $v) {
                            $attributes['meta_'.$k] = array($v);
                        } 
                    }                   
                    continue;
                }
                $attributes[$key] = is_array($val) ? $val : array ($val);
            }
        }


        return $attributes;
    }

    private function sessionAttributes($attributes) {

        if (!is_array($attributes) || !array_key_exists('uid',$attributes)) return FALSE;

        if (!session_id()) {
            session_start();
        }
        $_SESSION['uid'] = $attributes['uid'];
        $_SESSION['user_meta'] = $attributes;
    }


    /**
     * Log in using an external authentication helper.
     *
     * @param array &$state  Information about the current authentication.
     */
    public function authenticate(&$state) {
        assert('is_array($state)');

        $attributes = $this->getUser();

        if ($attributes !== NULL) {
            /*
             * The user is already authenticated.
             *
             * Add the users attributes to the $state-array, and return control
             * to the authentication process.
             */
            $state['Attributes'] = $attributes;
            return;
        }

        /*
         * The user isn't authenticated. We therefore need to
         * send the user to the login page.
         */

        $auth = FALSE;
        $msg = '';
        $userApi = new Tozny_Remote_User_API($this->realm_key_id, $this->api_url);

        $state['LDAPTozny:AuthID'] = $this->authId;

        if (!empty($_REQUEST['auth_type']) && $_REQUEST['auth_type'] === 'ldap') {
            try {
                /* Attempt to log in. */
                $username = $_REQUEST['username'];
                $password = $_REQUEST['password'];
                $attributes = $this->login($username, $password);

                $this->sessionAttributes($attributes);
                $auth = TRUE;
            } catch (SimpleSAML_Error_Error $e) {
                /*
                 * Login failed. Return the error code to the login form, so that it
                 * can display an error message to the user.
                 */
                $msg = 'Unable to login via LDAP. Bad username/password.';
                // This is how it's done elsewhere... Its kinda of ugly so Im going to do it a prettier way
                //SimpleSAML_Auth_State::throwException($state,
                //    new SimpleSAML_Error_Exception('Unable to login via LDAP. Error code: '. $e->getErrorCode()));
            }
        } else if (!empty($_REQUEST['auth_type']) && $_REQUEST['auth_type'] === 'tozny') {
            $siteApi = new Tozny_Remote_Realm_API($this->realm_key_id, $this->realm_secret_key, $this->api_url);
            $missingRealm = FALSE;
            $noSetSession = FALSE;

            if (!empty($_SESSION['tozny_session_id'])) {
                $check = $userApi->checkSessionStatus($_SESSION['tozny_session_id']);
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
                        if (!empty($user['tozny_username'])) {
                            $_SESSION['uid'] = $user['tozny_username'];
                        } else {
                            $_SESSION['uid'] = $decoded['user_id'];
                        }

                        // If we make it here, we're auth. We'll redirect below
                        $auth = TRUE;
                    } else {
                        $msg = 'Error logging in with Tozny.';
                        //SimpleSAML_Auth_State::throwException($state,
                        //        new SimpleSAML_Error_Exception('Unable to match payload signature with private key.'));
                    }
                }
            } else {
                $msg = 'Error logging in with Tozny.';
                //SimpleSAML_Auth_State::throwException($state,
                //        new SimpleSAML_Error_Exception('Expected a session_id in payload.'));
            }
        }

        /*
         * First we add the identifier of this authentication source
         * to the state array, so that we know where to resume.
         */


        /*
         * We need to save the $state-array, so that we can resume the
         * login process after authentication.
         *
         * Note the second parameter to the saveState-function. This is a
         * unique identifier for where the state was saved, and must be used
         * again when we retrieve the state.
         *
         * The reason for it is to prevent
         * attacks where the user takes a $state-array saved in one location
         * and restores it in another location, and thus bypasses steps in
         * the authentication process.
         */
        $stateId = SimpleSAML_Auth_State::saveState($state, 'LDAPTozny:External');

        /*
         * Now we generate an URL the user should return to after authentication.
         * We assume that whatever authentication page we send the user to has an
         * option to return the user to a specific page afterwards.
         */
        $returnTo = SimpleSAML_Module::getModuleURL('ldapTozny/resume.php', array(
                    'State' => $stateId,
                    ));

        /*
         * The redirect to the authentication page.
         *
         */
        if (!$auth) {
            $challenge = $userApi->loginChallenge();

            if ($challenge['return'] == 'error') {
                // We should add better bailing code here, like the option to send a message to the auth page
                // which indicates an error in the tozny portion
                // XXX Im not sure what should happen here
                return;
            }

            $_SESSION['tozny_session_id'] = $challenge['session_id'];
            $_SESSION['qrUrl']            = $challenge['qr_url'];
            $_SESSION['realm_key_id']     = $this->realm_key_id;
            $_SESSION['mobile_url']       = $challenge['mobile_url'];
            $_SESSION['msg']              = $msg;
            $_SESSION['api_url']          = $this->api_url;
            $_SESSION['authSrcId']        = $this->authId;
            /*
             * Get the URL of the authentication page.
             *
             * Here we use the getModuleURL function again, since the authentication page
             * is also part of this module, but in a real example, this would likely be
             * the absolute URL of the login page for the site.
             */
            $authPage = SimpleSAML_Module::getModuleURL('ldapTozny/authpage.php');

            SimpleSAML_Utilities::redirect($authPage, array());
        } else {
            SimpleSAML_Utilities::redirect($returnTo, array());
        }

        /*
         * The redirect function never returns, so we never get this far.
         */
        assert('FALSE');
    }


    /**
     * Resume authentication process.
     *
     * This function resumes the authentication process after the user has
     * entered his or her credentials.
     *
     * @param array &$state  The authentication state.
     */
    public static function resume() {

        /*
         * First we need to restore the $state-array. We should have the identifier for
         * it in the 'State' request parameter.
         */
        if (!isset($_REQUEST['State'])) {
            throw new SimpleSAML_Error_BadRequest('Missing "State" parameter.');
        }
        $stateId = (string)$_REQUEST['State'];

        /*
         * Once again, note the second parameter to the loadState function. This must
         * match the string we used in the saveState-call above.
         */
        $state = SimpleSAML_Auth_State::loadState($stateId, 'LDAPTozny:External');

        /*
         * Now we have the $state-array, and can use it to locate the authentication
         * source.
         */
        $source = SimpleSAML_Auth_Source::getById($state['LDAPTozny:AuthID']);
        if ($source === NULL) {
            /*
             * The only way this should fail is if we remove or rename the authentication source
             * while the user is at the login page.
             */
            throw new SimpleSAML_Error_Exception('Could not find authentication source with id ' . $state[self::AUTHID]);
        }

        /*
         * Make sure that we haven't switched the source type while the
         * user was at the authentication page. This can only happen if we
         * change config/authsources.php while an user is logging in.
         */
        if (! ($source instanceof self)) {
            throw new SimpleSAML_Error_Exception('Authentication source type changed.');
        }


        /*
         * OK, now we know that our current state is sane. Time to actually log the user in.
         *
         * First we check that the user is acutally logged in, and didn't simply skip the login page.
         */
        $attributes = $source->getUser();
        if ($attributes === NULL) {
            /*
             * The user isn't authenticated.
             *
             * Here we simply throw an exception, but we could also redirect the user back to the
             * login page.
             */
            throw new SimpleSAML_Error_Exception('User not authenticated after login page.');
        }

        /*
         * So, we have a valid user. Time to resume the authentication process where we
         * paused it in the authenticate()-function above.
         */

        $state['Attributes'] = $attributes;
        SimpleSAML_Auth_Source::completeAuth($state);

        /*
         * The completeAuth-function never returns, so we never get this far.
         */
        assert('FALSE');
    }



}
?>
