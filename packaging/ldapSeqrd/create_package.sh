#!/bin/bash

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

LDAPTOZNY_DEB_DIR=/tmp/simplesamlphp-ldapTozny

LDAPTOZNY_DIR=$SCRIPT_DIR/../../plugins/simplesamlphp/ldapTozny

rm -rf $LDAPTOZNY_DEB_DIR
mkdir -p $LDAPTOZNY_DEB_DIR/DEBIAN
mkdir -p $LDAPTOZNY_DEB_DIR/usr/share/simplesamlphp/modules/

cp $SCRIPT_DIR/control   $LDAPTOZNY_DEB_DIR/DEBIAN
cp -r $LDAPTOZNY_DIR $LDAPTOZNY_DEB_DIR/usr/share/simplesamlphp/modules/

fakeroot $SCRIPT_DIR/finish_package.sh $LDAPTOZNY_DEB_DIR $SCRIPT_DIR/../..