// PLATFORM INDEPENDENT TCP SOCKET AND GENERAL WEB SOCKET INTERFACE
// For implementation, see socket.cpp

#ifndef SOCKETH
#define SOCKETH

#define SOCK_BUFLEN 2048
#ifdef _WIN32
#include <winsock2.h>
#include <ws2tcpip.h>
#include <stdlib.h>
#include <string>
#pragma comment (lib, "ws2_32.lib")
#define HSOCKET SOCKET
#define HADDR SOCKADDR_IN
#else
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <sys/types.h> 
#include <sys/socket.h>
#include <netinet/in.h>
#define HSOCKET int
#endif
#include "stdcc.hpp"

namespace sc {
	class Socket {
	public:
		LIBPUB enum ESOCKTYPE { SERVER, CLIENT, UNINIT };

		LIBPUB Socket();

		LIBPUB bool Init(short port);
		LIBPUB bool Init(char *addr, short port);
		LIBPUB bool Init(HSOCKET sock, HADDR addr, int addrlen);
		
		LIBPUB void SetBlocking(bool block);
		LIBPUB bool GetBlocking();

		// all of the following return -1 on error, 0 on success, and 1 if the nonblocking process would block
		LIBPUB int Accept(Socket &conn);
		LIBPUB int Recv(std::string &str);
		LIBPUB int Send(std::string str);

		LIBPUB void Close();

		LIBPUB int GetLastError();

		LIBPUB ~Socket();
	protected:
		HSOCKET sock;
		HADDR addr;
		int addrlen;
		bool blocking;
		bool ready;
		char recvbuf[SOCK_BUFLEN];
		ESOCKTYPE type;
	};

	class WebSocket : public Socket {

	};

	class HTTPRequest {
		Socket sock;
	public:

	};
}

#endif