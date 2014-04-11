#import <IOBluetooth/objc/IOBluetoothDevice.h>
#import <IOBluetooth/objc/IOBluetoothRFCOMMChannel.h>
#import <IOBluetooth/objc/IOBluetoothSDPServiceRecord.h>
#import <IOBluetooth/objc/IOBluetoothSDPUUID.h>

#import <IOBluetoothUI/objc/IOBluetoothDeviceSelectorController.h>

#import <CoreFoundation/CoreFoundation.h>

#define SeqrdErrorDomain @"com.seqrd.SeqrdBluetooth"

@class IOBluetoothRFCOMMChannel;

@interface SeqrdBluetoothInterface : NSObject
{
    IOBluetoothRFCOMMChannel    *mRFCOMMChannel;
}
@property (nonatomic, assign, readonly) NSAppleEventManager *em;
@property (nonatomic, assign, readonly) NSData *SeqrdUUID;

// Connection Method:
// returns TRUE if the connection was successful:
- (BOOL)connectToServer;

// Disconnection:
// closes the channel:
- (void)disconnectFromServer;

// Returns the name of the device we are connected to
// returns nil if not connection:
- (NSString *)remoteDeviceName;

// Send Data method
// returns TRUE if all the data was sent:
- (BOOL)sendData:(void*)buffer length:(UInt32)length;



@end