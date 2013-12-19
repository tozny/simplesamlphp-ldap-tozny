Seqrd as a SAML Service Provider
=====================

This package provides two modules:
- Seqrd SAML Service Provider API
- Seqrd SAML Drupal Login


The Seqrd SAML Drupal Login module specifically enables Drupal to become a "Service
Provider" for Seqrd, so users can authenticate to Drupal (without entering a
username or password) by delegating authenticate to Seqrd as the Identity
Provider.



<<<<<<< HEAD
=======
Dependencies
============

Requires the OneLogin SAML-PHP toolkit, downloaded to the 'saml_sp/lib' folder:

`cd lib`
`git clone https://github.com/onelogin/php-saml.git`

SimpleSamlPHP Configuration
===========================

First, configure your IDP in Drupal:

Name = Human readable name for IDP.

App Name: will be used in the IDP configuration. For example
"demoLocalDrupal"

IDP URL: e.g. http:///myIdp.example.com/simplesaml/saml2/idp/SSOService.php

x.509 certificate: Should correspond to the "certificate" field in
saml20-idp-hosted.php

You can generate certificates as described in the README for simplesamlphp:
http://simplesamlphp.org/docs/1.5/simplesamlphp-idp#section_6

e.g.: openssl req -new -x509 -days 3652 -nodes -out example.org.crt -keyout example.org.pem

Here's a sample config for saml20-sp-remote.php:

$metadata['demoLocalDrupal'] = array(
	'AssertionConsumerService' => 'http://mydrupal.example.com/drupal7/?q=saml/consume',
	'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:email',
	'simplesaml.nameidattribute' => 'uid',
	'simplesaml.attributes' => FALSE,
);
>>>>>>> ecb8ff4f093b7ec9d5ebe33af7513ed0bd6b8073
