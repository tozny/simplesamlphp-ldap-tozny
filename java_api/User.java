package com.tozny.API;

import java.io.IOException;
import java.util.Dictionary;
import java.util.Hashtable;

import org.apache.http.HttpResponse;
import org.apache.http.client.ClientProtocolException;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.util.EntityUtils;
import org.json.JSONException;
import org.json.JSONObject;

import android.util.Log;

public class User {
	private String apiURI;
	private UserConstants constants;
	
	public User (String inApiURI) {
		this.apiURI=inApiURI;
		this.constants = new UserConstants();
	}
	
	public Dictionary<String,String> userAdd (String realm_pubkey, Boolean defer, Dictionary<String,String> opts) throws APIError {
    	String apiCall = apiURI
    			+ "?method=user.user_add"
    			+ "&realm_key_id=" + realm_pubkey
    			+ "&defer=" + defer;
    	Log.w("User.userAdd", "apiCall: " + apiCall);
    	// add optional params if present
    	for(int i=0; i<this.constants.optInputArgs.length; i++) {
    		String arg = this.constants.optInputArgs[i];
    		if (opts.get(arg) != null) {
    			apiCall += "&" + arg + "=" + opts.get(arg);
    		}
    	}
    	
    	JSONObject metadata = this.DoHttpRequest(apiCall);
    	
        String[] returnArgs = defer ? this.constants.returnArgsDeferred : this.constants.returnArgsImmediate;
    	if (! hasRequiredFields (metadata, returnArgs)) {
    		throw (new APIError());
    	}
    	
    	// convert JSON to Dictionary
    	
    	Dictionary<String,String> returnMetadata = new Hashtable<String,String>();
    	returnMetadata = convertJsonToArray(metadata, returnArgs);
    	
    	return (returnMetadata);
	}
	
	public Dictionary<String,String> userAddComplete(String realmKey, String userTempKey) {
		// for deferred user add
		String apiCall = apiURI
    			+ "?method=user.user_add_complete"
    			+ "&realm_key_id=" + realmKey
    			+ "&user_temp_key=" + userTempKey;
		
		
		
	}
	
	private JSONObject DoHttpRequest(String apiCall) throws APIError {
		Log.w("User.userAdd", "apiCall w opts: " + apiCall);
    	
        DefaultHttpClient httpclient = new DefaultHttpClient();
        HttpPost httppost = new HttpPost(apiCall);
        
        Log.w("User.userAdd", "after HttpPost");

        httppost.setHeader("ACCEPT", "application/json");
        HttpResponse httpResponse;
        JSONObject metadata = null;
        
    	try {
    		httpResponse = httpclient.execute(httppost);
    		Log.w("User.userAdd", "httpResponse: " + httpResponse);
            String apiReturn = EntityUtils.toString(httpResponse.getEntity());
            Log.w("User.userAdd", "apiReturn: " + apiReturn);
        	metadata = new JSONObject(apiReturn);
        	Log.w("User.userAdd", "metadata: " + metadata.toString());
    	} catch (ClientProtocolException e) {
    		Log.w("User.userAdd", "ClientProtocolException: " + e.getMessage());
    		e.printStackTrace();
    		throw (new APIError());
    	} catch (IOException e) {
    		Log.w("User.userAdd", "IOException: " + e.getMessage());
    		e.printStackTrace();
    		throw (new APIError());
    	} catch (JSONException e) {
    		Log.w("User.userAdd", "JSONException: " + e.getMessage());
    		e.printStackTrace();
    		throw (new APIError());
    	}
    	
    	return metadata;
	}
	
	private Dictionary<String,String> convertJsonToArray(JSONObject returnData, String[] returnArgs) {
		Dictionary<String,String> array = new Hashtable<String,String>();
		
		// already checked that the returnData has the expected arguments in returnArgs
		for (int i=0; i< returnArgs.length; i++) {
			try {
			array.put(returnArgs[i], returnData.get(returnArgs[i]).toString());
			}
			catch (JSONException ex){
				// hmm this is bad
			}
		}
		
		return array;
	}

	private boolean hasRequiredFields (JSONObject metadata, String[] fields) {
		
		if (metadata == null) {
			return (false);
		}
		
		for (int i=0; i< fields.length; i++) {
			try {
				if(metadata.get(fields[i]) == null) {
					return false;
				}
			}
			catch(JSONException ex) {
				return false;
			}
		}
		//TODO - iterate through 'fields' and verify that metadata has each 
		// with e.g. metadata.has("realm_pubkey");
		return true;
	}
	

}
