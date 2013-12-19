<?php
/*
Plugin Name: Seqrd App Provision
Version: 0.1
Plugin URI: http://www.seqrd.com
Description: Provisions an app!
Author: Isaac Potoczny-Jones
Author URI: http://www.seqrd.com
*/

/* Copyright 2013 Isaac Potoczny-Jones All Rights Reserved */


add_action('admin_menu', 'seqrd_app_provision_add_management_page');
add_filter('check_password',  array ('SeqrdAppProvision', 'seqrd_app_provision_check_password_xml'), 10, 4);
add_filter( 'xmlrpc_methods', array ('SeqrdAppProvision', 'seqrd_new_xmlrpc_methods'));

//----------------------------------------------------------------------------
//		USER PHP FUNCTIONS
//----------------------------------------------------------------------------

if (!class_exists('SeqrdAppProvision')) {
  class SeqrdAppProvision {
    function testFunction() {
      return 'hello world';			
    }

    function seqrd_app_provision_check_password_xml ($check, $password, $hash, $user_id) {
      $seqrdUser = get_userdata( $user_id );
      //      echo "data: ".$check.":".$password.":".$hash.":".$user_id.":".$seqrdUser->xmlrpcPass;
      if ( defined('XMLRPC_REQUEST') && XMLRPC_REQUEST ) {
        // Check the custom set XML password.
	global $wp_hasher;
	if ( empty($wp_hasher) ) {
		require_once( ABSPATH . 'wp-includes/class-phpass.php');
		// By default, use the portable hash from phpass
		$wp_hasher = new PasswordHash(8, true);
	}
        $newCheck = $wp_hasher->CheckPassword($password, $seqrdUser->xmlrpcPass);

        return $newCheck;
      } else { 
        //This isn't an xml-rpc  request, so proceed with normal check.
        return $check;
      }
    }

    //This is a test xmlrpc function.
    function seqrd_app_provision_getTestPW( $args ) {
      global $wp_xmlrpc_server;
      $wp_xmlrpc_server->escape( $args );

      $blog_id  = $args[0];
      $username = $args[1];
      $password = $args[2];

      if ( ! $user = $wp_xmlrpc_server->login( $username, $password ) )
        return $wp_xmlrpc_server->error;

      return "passwords:".$password.":".$user->xmlrpcPass;
    }

    function seqrd_new_xmlrpc_methods( $methods ) {
      $methods['seqrd.getTestPW'] = array ('SeqrdAppProvision', 'seqrd_app_provision_getTestPW');
      return $methods;   
    }

  }
}

//----------------------------------------------------------------------------
//		USER OPTION PAGE FUNCTIONS
//----------------------------------------------------------------------------

function seqrd_app_provision_add_management_page() {
	if (function_exists('add_management_page')) {
		add_management_page('Seqrd App Provision', 'Seqrd App Provision', 'read',
			basename(__FILE__), 'seqrd_app_provision_options_page');
	}
}

function seqrd_app_provision_options_page() {
?>

<div class="wrap">
<h2>Seqrd App Provision</h2>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?page=' . basename(__FILE__); ?>&updated=true">
<fieldset class="options">

<p><em>Careful:</em> This plugin resets user passwords to something random.</p>

<?php
    
  $seqrdUser = wp_get_current_user();
  $user_id = $seqrdUser->ID;

  if ( current_user_can('edit_user', $user_id )) {
    $newPass = wp_generate_password();
    $hashedNewPass = wp_hash_password ($newPass);
    echo "Updating xmlrpc password for " . $seqrdUser->user_login . " to " . $newPass . "<br>";
    $worked = update_user_meta( $user_id, 'xmlrpcPass', $hashedNewPass);
    $seqrdMessage = array ( 'pkg' => 'org.wordpress.android',
                            'user' => $seqrdUser->user_login,
                            'url' => site_url(),
                            'pass' => $newPass
                            );
    $seqrdMessageJson = json_encode ($seqrdMessage);      
    $qrValue = urlencode("seqrdgetapp://".$seqrdMessageJson);
?>
<img src="/wordpress/wp-content/plugins/seqrd-app-provision/qr.php?codeValue=<?php echo $qrValue ?>">
<?php

  } else {
    echo "You cannot edit this profile.";
  }
?>

</fieldset>
<?php
}
?>
