#!/bin/bash

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

TOZNYAUTH_DEB_DIR=/tmp/simplesamlphp-toznyauth

TOZNYAUTH_DIR=$SCRIPT_DIR/../../plugins/simplesamlphp/toznyauth

rm -rf $TOZNYAUTH_DEB_DIR
mkdir -p $TOZNYAUTH_DEB_DIR/DEBIAN
mkdir -p $TOZNYAUTH_DEB_DIR/usr/share/simplesamlphp/modules/

cp $SCRIPT_DIR/control   $TOZNYAUTH_DEB_DIR/DEBIAN
cp -r $TOZNYAUTH_DIR $TOZNYAUTH_DEB_DIR/usr/share/simplesamlphp/modules/

fakeroot $SCRIPT_DIR/finish_package.sh $TOZNYAUTH_DEB_DIR $SCRIPT_DIR/../..