#!/bin/bash

export PLUGIN_DIR="/var/simplesamlphp/modules/ldapSeqrd/"
rm -r $PLUGIN_DIR*
cp -r ./ldapSeqrd/* $PLUGIN_DIR
