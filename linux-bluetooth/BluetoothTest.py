#!/usr/bin/python

import sys
import bluetooth

target_name = "IPJ Galaxy Nexus"
nexus_address = "38:0A:94:2F:6A:AD"
target_address = None

# ----- Now send some data

#uuid = "1e0ca4ea-299d-4335-93eb-27fcfe7fa848"
muuid = "e57d0310-8f7f-11e2-9e96-0800200c9a66"
if len(sys.argv) > 1 :
    msg = sys.argv[1]
else:
   msg = "Hello, SEQRD."

# Note: If find_service doesn't have the address param, it will look
# for discovrable devices. So the first time we pair, set the android
# into discovery mode and *don't* pass the address. When we discover
# an Android that advertises our special service via the muuid, then
# we should remembr that address. Subsequently, connect with the
# address. In this case, the Android device does *not* need to be in
# discovery mode, and the whole thing happens much faster.

service_matches = bluetooth.find_service( uuid = muuid, address = nexus_address )
print service_matches

if len(service_matches) == 0:
    print "couldn't find the service"
    sys.exit(0)

first_match = service_matches[0]
port = first_match["port"]
name = first_match["name"]
host = first_match["host"]

print "connecting to \"%s\" on %s" % (name, host)

sock=bluetooth.BluetoothSocket( bluetooth.RFCOMM )
sock.connect((host, port))
sock.send(msg)
sock.close()

