// PLATFORM INDEPENDENT TCP SOCKET INTERFACE
// For interface, see socket.hpp

#include "socklib/socket.hpp"
#ifdef _WIN32 // winsock implementation

sc::Socket::Socket() {
	this->blocking = true;
	this->type = ESOCKTYPE::UNINIT;
}

bool sc::Socket::Init(short port) {
	if(this->type != ESOCKTYPE::UNINIT) return false;

	WSADATA wdata;
	if(WSAStartup(MAKEWORD(2, 2), &wdata) != 0)
		return false;

	struct addrinfo *result;
	struct addrinfo hints;
	ZeroMemory(&hints, sizeof(hints));
	hints.ai_family = AF_INET;
	hints.ai_socktype = SOCK_STREAM;
	hints.ai_protocol = IPPROTO_TCP;
	hints.ai_flags = AI_PASSIVE;
	if(getaddrinfo(NULL, std::to_string(port).c_str(), &hints, &result) != 0) {
		WSACleanup();
		return false;
	}

	this->sock = socket(result->ai_family, result->ai_socktype, result->ai_protocol);
	if(this->sock == INVALID_SOCKET) {
		freeaddrinfo(result);
		WSACleanup();
		return false;
	}

	if(bind(this->sock, result->ai_addr, (int)result->ai_addrlen) == SOCKET_ERROR) {
		freeaddrinfo(result);
		closesocket(this->sock);
		WSACleanup();
		return false;
	}

	freeaddrinfo(result);
	if(listen(this->sock, SOMAXCONN) == SOCKET_ERROR) {
		closesocket(this->sock);
		WSACleanup();
		return false;
	}

	this->type = ESOCKTYPE::SERVER;
	return true;
}

bool sc::Socket::Init(char *addr, short port) {
	return false;

	if(this->type != ESOCKTYPE::UNINIT) return false;

	// TODO: client socket

	this->type = ESOCKTYPE::CLIENT;
	return true;
}

bool sc::Socket::Init(HSOCKET sock, HADDR addr, int addrlen) {
	if(this->type != ESOCKTYPE::UNINIT) return false;

	this->sock = sock;
	this->addr = addr;
	this->addrlen = addrlen;

	this->type = ESOCKTYPE::SERVERSPAWN;
	return true;
}

void sc::Socket::SetBlocking(bool block) {
	if(this->type == ESOCKTYPE::UNINIT) return;
	u_long blocking = block ? 0 : 1;
	ioctlsocket(this->sock, FIONBIO, &blocking);
	this->blocking = block;
}

bool sc::Socket::GetBlocking() {
	return this->blocking;
}

int sc::Socket::Accept(Socket &conn) {
	if(this->type != ESOCKTYPE::SERVER) return -1;

	HSOCKET newsock; SOCKADDR_IN newaddr = {0}; int newlen = sizeof(newaddr);
	newsock = accept(this->sock, (struct sockaddr *)&newaddr, &newlen);

	if(WSAGetLastError() == WSAEWOULDBLOCK)
		return 1;
	else if(newsock == INVALID_SOCKET) {
		this->Close();
		return -1;
	}

	conn = Socket();
	conn.Init(newsock, newaddr, newlen);
	return 0;
}

int sc::Socket::Recv(std::string &str) {
	if(this->type == ESOCKTYPE::UNINIT || this->type == ESOCKTYPE::SERVER) return -1;

	int get = recv(this->sock, this->recvbuf, SOCK_BUFLEN-1, 0);
	if(WSAGetLastError() == WSAEWOULDBLOCK)
		return 1;
	else if(get <= 0) {
		this->Close();
		return -1;
	}

	str = std::string(this->recvbuf, get);
	return 0;
}

int sc::Socket::Send(std::string str) {
	if(this->type == ESOCKTYPE::UNINIT || this->type == ESOCKTYPE::SERVER) return -1;

	int sent = send(this->sock, str.c_str(), str.length(), 0);
	if(sent == SOCKET_ERROR) {
		this->Close();
		return -1;
	}

	return 0;
}

int sc::Socket::GetLastError() {
	return WSAGetLastError();
}

void sc::Socket::Close() {
	shutdown(this->sock, SD_SEND);
	closesocket(this->sock);
	this->type = ESOCKTYPE::UNINIT;
}

sc::Socket::~Socket() {
	//delete[] this->recvbuf;
}

#else // posix (berkeley) socket implementation

#endif