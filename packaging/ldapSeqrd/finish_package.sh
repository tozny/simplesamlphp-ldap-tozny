DEB_DIR=$1
OUT_DIR=$2

echo "Building package from $DEB_DIR"

# adjust ownerships
chown -R root:root $DEB_DIR
chown -R www-data:www-data $DEB_DIR/usr/share/simplesamlphp/modules/ldapSeqrd

# finally build the package
dpkg-deb --build $DEB_DIR $OUT_DIR
