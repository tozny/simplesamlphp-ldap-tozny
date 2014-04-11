//
//  SeqrdBluetoothAppDelegate.m
//  SeqrdBluetooth
//
//  Created by joe on 12/1/13.
//  Copyright 2013 __MyCompanyName__. All rights reserved.
//

#import "SeqrdBluetoothAppDelegate.h"

@implementation SeqrdBluetoothAppDelegate

@synthesize window;

- (void)applicationWillFinishLaunching:(NSNotification *)aNotification {
    // We get opened via a url click -- call getUrl
    [[NSAppleEventManager sharedAppleEventManager]
     setEventHandler:self
     andSelector:@selector(getUrl:withReplyEvent:)
     forEventClass:kInternetEventClass
     andEventID:kAEGetURL];
    
    // We get opened via double click -- call install
    [[NSAppleEventManager sharedAppleEventManager]
     setEventHandler:self
     andSelector:@selector(install:withReplyEvent:)
     forEventClass:kCoreEventClass
     andEventID:kAEOpenApplication];
}

- (void)applicationWillTerminate:(NSNotification *)notification {
}

- (void)getUrl:(NSAppleEventDescriptor *)event
withReplyEvent:(NSAppleEventDescriptor *)replyEvent
{
    SeqrdBluetoothInterface *sbi = [SeqrdBluetoothInterface new];
    
	// Get the URL
	NSString *urlStr = [[event paramDescriptorForKeyword:keyDirectObject] stringValue];
	
	NSLog(@"Thing was here: '%@'", urlStr);
	
	BOOL ret = [sbi connectToServer];
	
	if (ret) {
        NSLog(@"Connected to '%@'", [sbi remoteDeviceName]);
	
        [sbi sendData:(void *)[urlStr UTF8String] length:strlen([urlStr UTF8String])];
	
        [sbi disconnectFromServer];
    }
    
    [sbi release];
    
    [NSApp terminate:self];
}

- (void)install:(NSAppleEventDescriptor *)event
withReplyEvent:(NSAppleEventDescriptor *)replyEvent
{
	NSLog(@"Install!");
	// Make us the default seqrd handler
	CFStringRef bundleID = (CFStringRef)[[NSBundle mainBundle] bundleIdentifier];
	LSSetDefaultHandlerForURLScheme(CFSTR("seqrdauth"), bundleID);
	LSSetDefaultHandlerForURLScheme(CFSTR("seqrdenroll"), bundleID);
	
    /*
    NSError *msg = [NSError errorWithDomain:SeqrdErrorDomain
                    code:1
                    userinfo:[NSDictionary dictionaryWithObjectsAndKeys:@"NSLocalizedDescriptionKey", @"installmsg", nil]];
    
    [NSAlert alertWithError:msg];
    
    [msg release];
    */
    [NSApp terminate:self];
}

@end
