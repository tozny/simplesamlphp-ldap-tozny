#!/bin/bash

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

LDAPSEQRD_DEB_DIR=/tmp/simplesamlphp-ldapSeqrd

LDAPSEQRD_DIR=$SCRIPT_DIR/../../plugins/simplesamlphp/ldapSeqrd

rm -rf $LDAPSEQRD_DEB_DIR
mkdir -p $LDAPSEQRD_DEB_DIR/DEBIAN
mkdir -p $LDAPSEQRD_DEB_DIR/usr/share/simplesamlphp/modules/

cp $SCRIPT_DIR/control   $LDAPSEQRD_DEB_DIR/DEBIAN
cp -r $LDAPSEQRD_DIR $LDAPSEQRD_DEB_DIR/usr/share/simplesamlphp/modules/