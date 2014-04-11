//
//  SeqrdBluetoothAppDelegate.h
//  SeqrdBluetooth
//
//  Created by joe on 12/1/13.
//  Copyright 2013 __MyCompanyName__. All rights reserved.
//

#import <Cocoa/Cocoa.h>
#import "SeqrdBluetoothInterface.h"


@interface SeqrdBluetoothAppDelegate : NSObject <NSApplicationDelegate> {
    NSWindow *window;
}

@property (assign) IBOutlet NSWindow *window;

// Handles incoming url
- (void)getUrl:(NSAppleEventDescriptor *)event
withReplyEvent: (NSAppleEventDescriptor *)replyEvent;

// Installs our uri handling code
- (void)install:(NSAppleEventDescriptor *)event
 withReplyEvent: (NSAppleEventDescriptor *)replyEvent;

@end
