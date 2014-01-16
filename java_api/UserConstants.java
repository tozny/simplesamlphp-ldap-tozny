package com.tozny.API;
import java.lang.Object;
import java.util.Collections;
import org.apache.commons.*;

public class UserConstants {
	public String[] optInputArgs = {"username", "name", "birthdate", "email"};
	
	public String[] returnArgsCommon = {"user_id", "user_key_id", "realm_key_id", "realm_id", "logo_url", "info_url", "crypto_suite", "display_name"};
	
	//ArrayUtils.addAll in apache commons collection has a function to join arrays
	public String[] returnArgsDeferred = { "user_id", "user_key_id", "realm_key_id", "realm_id", "logo_url", "info_url", "crypto_suite", "display_name", "user_temp_key", "secret_enrollment_url", "secret_enrollment_qr_url" };
	
	public String[] returnArgsImmediate = { "user_id", "user_key_id", "realm_key_id", "realm_id", "logo_url", "info_url", "crypto_suite", "display_name", "user_secret"};

	public String[] returnArgsUserAddComp = { "user_id", "user_key_id", "user_secret", "realm_key_id", "realm_id", "logo_url", "info_url", "crypto_suite", "display_name"};
}
