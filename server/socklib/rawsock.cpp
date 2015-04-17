#include "socklib/socket.hpp"

sc::RawSocket::RawSocket(Socket sock) : sc::Socket(sock) {}

int sc::RawSocket::Recv(std::string &str, uint32_t size) {
	return Socket::Recv(str, size);
}