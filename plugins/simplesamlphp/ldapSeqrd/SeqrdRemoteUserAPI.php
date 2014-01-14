<?php
/**
 * SeqrdRemoteUserAPI.php
 *
 * @package default
 */


/**
 * The Remote SEQRD User API.
 *
 * This is the interface for the PHP Remote API for SEQRD's login system.
 *
 * PHP version 5
 *
 * LICENSE: Copyright SEQRD LLC, All Rights Reserved
 *
 * @category   Security
 * @package    SEQRD
 * @author     Isaac Potoczny-Jones <ijones@seqrd.com>
 * @copyright  2013 SEQRD LLC
 * @version    git: $Id$
 * @link       https://www.seqrd.com
 * @since      File available since Release 1.0
 */

/**
 * The Remote SEQRD User API
 *
 * This is the interface for the PHP Remote User API for SEQRD's login system.
 *
 * @category   Security
 * @package    SEQRD
 * @author     Isaac Potoczny-Jones <ijones@seqrd.com>
 * @copyright  2013 SEQRD LLC
 * @link       https://www.seqrd.com
 * @since      Class available since Release 1.0
 */
class SEQRD_Remote_User_API
{

    /**
     * The Realm Key ID that this user is interacting with.
     * Usually a random string.
     *
     * @access private
     * @var string
     */
    private $_realm_key_id;

    /**
     * The Challenge package, once loginChallenge has been called.
     *
     * @access private
     * @var SEQRD_Challenge
     */
    private $_challenge;
    private $_api_url;



    /**
     * Build this class based on the remote site's key ID.
     *
     * @param unknown $in_realm_key_id
     * @param unknown $in_api_url      (optional)
     */
    function __construct( $in_realm_key_id, $in_api_url = NULL)
    {

        $this->_realm_key_id = $in_realm_key_id;

        if ($in_api_url) {
            $this->_api_url = $in_api_url;
        } else {
            $apiTmp = getenv("API_URL");
            if ($apiTmp != false) {
                $this->_api_url = $apiTmp;
            } else {
                //TODO: Error
            }
        }
    }


    /**
     * Returns data about this realm.
     *
     * @return Array with realm_id, logo_url, info_url, display_name.
     */
    function realmGet()
    {
        $decodedValue = $this->rawCall(['method' => 'user.realm_get'
            , 'realm_key_id'  => $this->_realm_key_id]);
        return $decodedValue;
    }


    /**
     * Return the login challenge for this realm. Can return an error
     * if the realm does not exist.
     *
     * @return SEQRD_Challenge | error
     */
    function loginChallenge()
    {
        $decodedValue = $this->rawCall (['method' => 'user.login_challenge',
            'realm_key_id' => $this->_realm_key_id]);
        //TODO: Handle error
        $this->_challenge = $decodedValue;
        return $decodedValue;
    }



    /**
     * Add this user to the given realm.
     *
     * @param string  $defer    (optional) Whether to use deferred enrollment. Defaults false.
     * @param unknown $metadata (optional)
     * @return The SEQRD_API_User object if successful.
     */
    function userAdd($defer = 'false', $metadata = NULL, $pub_key = NULL)
    {
        $args = ['method'       => 'user.user_add',
                 'defer'        => $defer,
                 'pub_key'      => $pub_key,
                 'realm_key_id' => $this->_realm_key_id];

        if (!empty($metadata)) {
            $extras = self::base64UrlEncode(json_encode($metadata));
            $args['extra_fields'] = $extras;
        }

        $user_arr = $this->rawCall($args);
        //TODO: Handle errors

        return $user_arr;
    }


    /**
     * For deferred user enrollment, complete the enrollment
     *
     * @param string  $user_temp_key The temporary user key
     * @return The new user data.
     */
    function userAddComplete($user_temp_key)
    {
        $newUser = $this->rawCall(['method'       => 'user.user_add_complete'
            , 'user_temp_key' => $user_temp_key
            , 'realm_key_id' => $this->_realm_key_id]);
        return $newUser;
    }


    /**
     * Check whether this session is expired, failed, or succeeded.
     *
     * @param string  $session_id
     * @return The status json object.
     */
    function checkSessionStatus($session_id)
    {
        $check = $this->rawCall (['method'     => 'user.check_session_status'
            , 'session_id' => $session_id
            , 'realm_key_id' => $this->_realm_key_id]);
        return $check;
    }


    /**
     * Get the QR code for the add_complete call
     *
     * @param string  $user_temp_key
     * @return A string representing a PNG of the QR code. Use imagecreatefromstring to convert this to an image resource.
     */
    function qrAddComplete($user_temp_key)
    {
        $args = ['method'        => 'user.qr_add_complete'
        , 'user_temp_key' => $user_temp_key
        , 'realm_key_id'  => $this->_realm_key_id];
        $url = $this->_api_url . "?" . http_build_query($args);
        $strImg = file_get_contents($url);
        return $strImg;
    }


    /**
     * Get the QR code representing the login_challenge from previously
     * callin guser.login_challenge
     *
     * @return A string representing a PNG of the QR code. Use imagecreatefromstring to convert this to an image resource.
     */
    function qrLoginChallenge()
    {
        return $this->qrLoginChallengeRaw($this->_challenge);
    }


    /**
     * Get the QR code representing the supplied login_challenge
     *
     * @param string  $challenge The cryptographic challenge
     * @return A string representing a PNG of the QR code. Use imagecreatefromstring to convert this to an image resource.
     */
    function qrLoginChallengeRaw($challenge)
    {
        $args = ['method'        => 'user.qr_login_challenge'
        , 'challenge'     => $challenge['challenge']
        , 'session_id'    => $challenge['session_id']
        , 'realm_key_id'  => $this->_realm_key_id];
        $url = $this->_api_url . "?" . http_build_query($args);
        $strImg = file_get_contents($url);
        return $strImg;
    }


    /**
     * Internal function to convert an array into a query and issue it
     * then decode the results.
     *
     * @param array   $args an associative array for the call
     * @return array either with the response or an error
     */
    function rawCall(array $args)
    {
        $url = $this->_api_url . "?" . http_build_query($args);
        $encodedResult = file_get_contents($url);
        return json_decode($encodedResult, true);
    }


    /**
     * encode according to rfc4648 for url-safe base64 encoding
     *
     *
     * @param string  $data The data to encode
     * @return The encoded data
     */
    static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

}// SEQRD_Remote_User_API class

?>
