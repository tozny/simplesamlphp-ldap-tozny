#!/bin/bash

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

LDAPTOZNY_DEB_DIR=/tmp/simplesamlphp-ldapTozny

LDAPTOZNY_DIR=$SCRIPT_DIR/../../

rm -rf $LDAPTOZNY_DEB_DIR
mkdir -p $LDAPTOZNY_DEB_DIR/DEBIAN
mkdir -p $LDAPTOZNY_DEB_DIR/usr/share/simplesamlphp/modules/ldapTozny2

cp $SCRIPT_DIR/control $LDAPTOZNY_DEB_DIR/DEBIAN
cp    $LDAPTOZNY_DIR/default-enable $LDAPTOZNY_DEB_DIR/usr/share/simplesamlphp/modules/ldapTozny2
cp    $LDAPTOZNY_DIR/LICENSE        $LDAPTOZNY_DEB_DIR/usr/share/simplesamlphp/modules/ldapTozny2
cp -r $LDAPTOZNY_DIR/docs           $LDAPTOZNY_DEB_DIR/usr/share/simplesamlphp/modules/ldapTozny2
cp -r $LDAPTOZNY_DIR/lib            $LDAPTOZNY_DEB_DIR/usr/share/simplesamlphp/modules/ldapTozny2
cp -r $LDAPTOZNY_DIR/www            $LDAPTOZNY_DEB_DIR/usr/share/simplesamlphp/modules/ldapTozny2


fakeroot $SCRIPT_DIR/finish_package.sh $LDAPTOZNY_DEB_DIR $SCRIPT_DIR/../..
