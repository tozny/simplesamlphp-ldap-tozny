#!/bin/bash

export PLUGIN_DIR="/var/simplesamlphp/modules/ldapTozny/"
rm -r $PLUGIN_DIR*
cp -r ./ldapTozny/* $PLUGIN_DIR
