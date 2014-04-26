php-mud-server
==============


Brainstorm:
- When a new connection is received, it dispatches a NEW\_PLAYER\_EVENT
- A player is a class that contains a CommunicationBridge.
- The CommunicationBridge contains the socket itself, and is able to send messages to the player.
- When a message is received, it dispatches an INCOMING\_PLAYER\_MESSAGE\_EVENT.
- The app converts the message into a command and pushes it into a command stack.
- There is a thread, it is the clock, and dispatches a NEW\_TURN\_EVENT every second.
- When a NEW\_TURN\_EVENT is received, the app will collect all the commands in the stack, makes whatever it needs to, and sends the proper messages to the player.