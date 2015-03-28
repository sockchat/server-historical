// WEBSOCKET INTERFACE
// For interface, see socket.hpp

#include "socklib/socket.hpp"
#include <iostream>

sc::WebSocket::WebSocket() : sc::Socket() {
	this->handshaked = false;
	this->fragment = "";
}

sc::WebSocket::WebSocket(sc::Socket sock) : sc::Socket(sock) {
	this->handshaked = false;
	this->fragment = "";
}

int sc::WebSocket::Handshake() {
	if(this->handshaked) return 0;

	std::string tmp; int status;
	if((status = Socket::Recv(tmp)) == 0) {
		return this->Handshake(tmp) ? 0 : -1;
	} else return status;
}

bool sc::WebSocket::Handshake(std::string headers) {
	if(this->handshaked) return true;

	if(headers.compare(0, 3, "GET") != 0) return false;

	this->headers.clear();
	auto lines = str::split(headers, "\r\n");
	for(int i = 1; i < lines.size(); i++) {
		auto keyval = str::split(lines[i], ':', 2);
		if(keyval.size() == 2)
			this->headers[str::tolower(str::trim(keyval[0]))] = str::trim(keyval[1]);
	}

	//std::cout << this->headers["host"];

	if(this->headers.count("host") > 0 && this->headers.count("upgrade") > 0 &&
		this->headers.count("connection") > 0 && this->headers.count("sec-websocket-key") > 0 &&
		this->headers.count("sec-websocket-version") > 0) {

		if(str::tolower(this->headers["upgrade"]) == "websocket" &&
			str::tolower(this->headers["connection"]).find("upgrade") != std::string::npos &&
			(this->headers["sec-websocket-version"] == "13")) {

			std::string shake =
				"HTTP/1.1 101 Switching Protocols\r\n"
				"Upgrade: websocket\r\n"
				"Connection: Upgrade\r\n"
				"Sec-WebSocket-Accept: ";
			shake += this->CalculateConnectionHash(this->headers["sec-websocket-key"]) + "\r\n\r\n";

			Socket::Send(shake);
		} else return false;
	} else return false;

	this->handshaked = true;
	return true;
}

int sc::WebSocket::Recv(std::string &str) {
	if(!this->handshaked) return -1;

	std::string buffer;
	int status; bool fin = false;
	while(!fin) {
		if((status = Socket::Recv(buffer)) != 0) return status;
		fin = (str[0] & 0x80) != 0;

		if((str[0] & 0x70) != 0) {
			this->Close();
			return -1;
		}

		bool mask = (str[1] &  0x80) != 0;
		uint8_t opcode = str[0] & 0x0F;
		switch(opcode) {
		case 0x1: // text frame start
		case 0x2: // binary frame start
			this->fragment = "";
		case 0x0: // continuation frame

			break;
		case 0x8: // ping frame, respond with pong frame

			break;
		case 0xA: // pong frame, do nothing
			break;
		default: // unknown opcode or closing opcode, close connection cleanly
			this->Close();
			return -1;
		}
	}

	str = this->fragment + buffer;
	return 0;
}

std::string sc::WebSocket::CalculateConnectionHash(std::string in) {
	in += "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";
	unsigned char hash[20];
	sha1::calc(in.c_str(), in.length(), hash);
	return std::string(base64_encode(hash, 20));
}

sc::WebSocket::Frame::Frame(std::string data, bool mask = false, Opcode type, bool fin) {
	this->data = data;
	this->mask = mask;
	this->type = type;
	this->fin = fin;
}

