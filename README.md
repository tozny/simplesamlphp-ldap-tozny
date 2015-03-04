simplesamlphp-ldap-tozny
========================

A SimpleSAMLphp fork of the LDAP plugin to include Tozny authentication of LDAP users. 

Features

 * The ability to authenticate your LDAP users using the Tozny Authenticator app or with traditional LDAP Username & Passwords.
 * Provisioning self-service. Your LDAP users can provision their mobile devices to user Tozny without assistance from technical support.
 * Provision multiple devices per user.
 * Integrate with thousands of SAML 2.0 enabled services, like Google Apps, SugarCRM, Jive, etc.
 * Simple, well maintained codebase. Forked from the SimpleSAMLphp LDAP module.

Dependencies: 

The Tozny LDAP module requires the Tozny PHP SDK. You can find it [here](https://github.com/tozny/sdk-php)

Building
--------
We include a Makefile which uses the included Dockerfile to stand up a ubuntu 14.04 server, assemble the sources into a .deb file for easy install. To build the package type:
```
make package
```
