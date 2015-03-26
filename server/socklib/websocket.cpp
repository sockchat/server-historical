// WEBSOCKET INTERFACE
// For interface, see socket.hpp

#include "socklib/socket.hpp"

sc::WebSocket::WebSocket() {
	this->handshaked = false;
	Socket::Socket();
}

sc::WebSocket::WebSocket(sc::Socket sock) {
	this->handshaked = false;
	Socket::Socket(sock);
}