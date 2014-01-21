<?php
/**
 * @file
 * API hooks for SAML Service Provider module.
 */


/**
 * Alter the settings used when generating SAML requests.
 *
 * @param OneLogin_Saml_Settings $settings
 */
function hook_saml_sp_settings_alter(&$settings) {
  // Change the consuming URL to a custom endpoint.
  if ($settings->login_url == 'http://example.com/saml/foo') {
    $settings->spReturnUrl = url('saml/custom_action', array('absolute' => TRUE));
  }
}

/**
 * Provide default IDPs, exported to code.
 *
 * @return Array
 * Array of IDP objects, keyed by machine name.
 */
function hook_saml_sp_default_idps() {
  $saml_idp = new stdClass();
  $saml_idp->disabled = FALSE; /* Edit this to true to make a default saml_idp disabled initially */
  $saml_idp->api_version = 1;
  $saml_idp->machine_name = 'tozny';
  $saml_idp->name = 'Tozny';
  $saml_idp->app_name = 'globesherpa';
  $saml_idp->login_url = 'http://gstest.tozny.com/simplesaml/saml2/idp/SSOService.php';
  $saml_idp->x509_cert = '-----BEGIN CERTIFICATE-----
MIIDZTCCAk2gAwIBAgIJALPNe0+STJopMA0GCSqGSIb3DQEBBQUAMEkxCzAJBgNV
BAYTAlVTMQ8wDQYDVQQIDAZPcmVnb24xETAPBgNVBAcMCFBvcnRsYW5kMRYwFAYD
VQQKDA1TRVFSRCBTQU5EQk9YMB4XDTEzMDYxMDIyMDcyMVoXDTIzMDYxMDIyMDcy
MVowSTELMAkGA1UEBhMCVVMxDzANBgNVBAgMBk9yZWdvbjERMA8GA1UEBwwIUG9y
dGxhbmQxFjAUBgNVBAoMDVNFUVJEIFNBTkRCT1gwggEiMA0GCSqGSIb3DQEBAQUA
A4IBDwAwggEKAoIBAQDaFYOp6+8m8nqYLQxoZovGMvjqPSFC/Hqkt3odnGnZ20nV
wpcWqnOl3B295dt+do7qInSCYcfM3PDl8gpahVfhdsD8PXIXtLIAfsegA9vkfhq0
qg+VCHM2ANoTGTTp/0jo65pyBSZu0vufS0vkssQRExWmdNwwhsLxh9IvNxLsMgxI
zypOcIO5D8pMasFAG21S9k4sq+9ACHR7uVuMJNx9wx4khkN0cX5MF0D0ED4NhaAn
O3JyFXYbfUPPV2OdtE7+OzxcBVOVhueLO5qccNQP7B3fmgZOpl8bCQseQCzIPic5
m7IvCqQNd0g2Y7LpCuRBqIygiztCc0TgD3vUt7yPAgMBAAGjUDBOMB0GA1UdDgQW
BBSiw8FRhnv/y5JNSsEWnjzK4eUVhzAfBgNVHSMEGDAWgBSiw8FRhnv/y5JNSsEW
njzK4eUVhzAMBgNVHRMEBTADAQH/MA0GCSqGSIb3DQEBBQUAA4IBAQB+T3s2S5D9
xCQxH3KM/BatQzNclnn+8R7MHLrDM89PjOE3VUAh0/gYDcZs7HnmHyg14vOzgrVh
pD2GgWc/hn1eWRbrSQOM9mlBfpj4uI4DiPTvwfaHTi/PpC1CesY/bL6ItjxG1JLH
HaTl52FXTDAz3tibDcjzCJkg72w9D460ByjYDEq4sfOEEXHtw545Z5PclUAmzbFQ
NRYer7rXauarp796lfdKMAfbSJ5Mo2kKQxNdeMThF+nITZQ6gfvpRHkx9Spk1MhS
pMuN3X0zyHu4dVz9dJ1DwKbAROISKdbY1OSBaqOsWCX+boIvsBUvkdfsk5GckiBF
td27LaUm52RF
-----END CERTIFICATE-----';

  $saml_sp_idps[$saml_idp->machine_name] = $saml_idp;

  return $saml_sp_idps;
}
