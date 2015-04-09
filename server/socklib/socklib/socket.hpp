// PLATFORM INDEPENDENT TCP SOCKET AND GENERAL WEB SOCKET INTERFACE
// For implementation, see socket.cpp

#ifndef SOCKETH
#define SOCKETH

#define SOCK_BUFLEN 2048
#ifdef _WIN32
#include <winsock2.h>
#include <ws2tcpip.h>
#include <stdlib.h>
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

#include <map>
#include <string>
#include <random>
#include "stdcc.hpp"
#include "utils.h"
#include "socklib/sha1.h"
#include "socklib/base64.h"

namespace sc {
	class Socket {
	public:
		LIBPUB enum ESOCKTYPE { SERVER, SERVERSPAWN, CLIENT, UNINIT };

		LIBPUB Socket();

		LIBPUB bool Init(short port);
		LIBPUB bool Init(char *addr, short port);
		LIBPUB bool Init(HSOCKET sock, HADDR addr, int addrlen);
		
		LIBPUB void SetBlocking(bool block);
		LIBPUB bool GetBlocking();

		// all of the following return -1 on error, 0 on success, and 1 if the nonblocking process would block
		LIBPUB virtual int Accept(Socket &conn);
		LIBPUB virtual int Recv(std::string &str);
		LIBPUB virtual int Send(std::string str);

		LIBPUB virtual void Close();

		LIBPUB int GetLastError();

		LIBPUB ~Socket();
	protected:
		HSOCKET sock;
		HADDR addr;
		int addrlen;
		bool blocking;
		char recvbuf[SOCK_BUFLEN];
		ESOCKTYPE type;
	};

	class WebSocket : public Socket {
		bool handshaked;
		std::map<const std::string, std::string> headers;
		std::string fragment;

		LIBPRIV std::string CalculateConnectionHash(std::string in);
	public:
		LIBPUB WebSocket();
		LIBPUB WebSocket(Socket sock);

		LIBPUB int Handshake();
		LIBPUB bool Handshake(std::string headers);

		LIBPUB int Recv(std::string &str);
		LIBPUB int Send(std::string str);

		class Frame {
		public:
			LIBPUB union MaskData {
				uint32_t block;
				uint8_t bytes[4];
			};

			LIBPUB enum Opcode { CONTINUATION = 0x0, TEXT = 0x1, BINARY = 0x2, CLOSE = 0x8, PING = 0x9, PONG = 0xA };

			LIBPUB Frame();
			LIBPUB Frame(std::string data, bool mask = false, int type = Opcode::TEXT, bool fin = true, uint8_t rsv = 0x0);
			LIBPUB Frame(std::string data, uint8_t maskdata[4], bool mask = false, int type = Opcode::TEXT, bool fin = true, uint8_t rsv = 0x0);
			
			LIBPUB void SetType(int type);
			LIBPUB int GetType();
			
			LIBPUB void SetData(std::string data);
			LIBPUB std::string GetData();
			
			LIBPUB void SetMasked(bool mask);
			LIBPUB bool IsMasked();

			LIBPUB void GenerateMask();
			LIBPUB void SetMask(uint8_t mask[4]);
			LIBPUB void SetMask(uint32_t mask);
			LIBPUB MaskData GetMask();

			LIBPUB void SetFin(bool fin);
			LIBPUB bool IsFin();
			
			LIBPUB std::string Get();

			LIBPUB static Frame FromRaw(std::string raw);
			LIBPUB static std::vector<Frame> GenerateFrameset(std::vector<std::string> data, bool mask = false, int opcode = Opcode::TEXT);
		private:
			int opcode;
			std::string data;
			bool mask;
			MaskData maskdata;
			uint8_t rsv;
			bool fin;
		};
	};

	class HTTPRequest {
		Socket sock;
		bool ready;
	};
}

#endif