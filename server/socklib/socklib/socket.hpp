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
#include <fcntl.h>
#include <sys/types.h> 
#include <sys/socket.h>
#include <netdb.h>
#include <arpa/inet.h>
#include <netinet/in.h>
#include <errno.h>
#define HSOCKET int
#define HADDR struct sockaddr_in
#endif

#include <map>
#include <string>
#include <random>
#include <time.h>
#include <iomanip>
#include "stdcc.hpp"
#include "utils.h"
#include "sha1.h"
#include "base64.h"
#include "msg.h"

namespace sc {
    class Socket {
    public:
        LIBPUB enum ESOCKTYPE { SERVER, SERVERSPAWN, CLIENT, UNINIT };

        LIBPUB Socket();

        LIBPUB bool Init(short port);
        LIBPUB bool Init(std::string addr, uint16_t port);
        LIBPUB bool Init(HSOCKET sock, HADDR addr, int addrlen);
        
        LIBPUB std::string GetIPAddress();

        LIBPUB void SetBlocking(bool block);
        LIBPUB bool GetBlocking();

        LIBPUB void SetTimeout(int seconds);

        // all of the following return -1 on error, 0 on success, and 1 if the nonblocking process would block
        LIBPUB virtual int Accept(Socket &conn);
        LIBPUB virtual int Recv(std::string &str, uint32_t size = SOCK_BUFLEN);
        LIBPUB virtual int Send(std::string str);

        LIBPUB virtual void Close();

        LIBPUB int GetLastError();

        LIBPUB virtual ~Socket();
    protected:
        HSOCKET sock;
        HADDR addr;
        int addrlen;
        bool blocking;
        char recvbuf[SOCK_BUFLEN];
        ESOCKTYPE type;
    };

    class HTTPRequest {
    private:
        struct URL {
            std::string protocol;
            std::string target;
            std::string resource;
            uint16_t port;
        };

        struct compimap {
            bool operator() (const std::string &lhs, const std::string &rhs) const {
                return sc::str::tolower(lhs) < sc::str::tolower(rhs);
            }
        };

        LIBPUB static uint16_t GetPortFromProtocol(std::string protocol);
        LIBPUB static URL DecipherURL(std::string url);
        LIBPUB static std::string URIEscapeCharacter(uint32_t c);
    public:
        class Response {
        private:
            LIBPUB void Error(int status = -1);
        public:
            enum ESTATUSCODE { OK = 200, FORBIDDEN = 403, NOTFOUND = 404, INTERR = 500 };
            int status;
            std::map<std::string, std::string, compimap> headers;
            std::string content;

            LIBPUB Response();
            LIBPUB Response(std::string raw, std::string action = "HTTP");
            LIBPUB Response(int status, std::map<std::string, std::string, compimap> headers, std::string content);

            LIBPUB bool IsValid();
        };

        LIBPUB static std::string EncodeURI(std::string uri, bool spaceIsPlus = false);
        LIBPUB static std::string EncodeURIComponent(std::string comp, bool spaceIsPlus = false);
        LIBPUB static std::string EncodeURIComponentStrict(std::string comp, bool spaceIsPlus = false);

        LIBPUB static Response Raw(std::string action, std::string url, std::map<std::string, std::string, compimap> headers = std::map<std::string, std::string, compimap>(), std::string body = "");

        LIBPUB static Response Get(std::string url, std::map<std::string, std::string, compimap> data = std::map<std::string, std::string, compimap>(), std::map<std::string, std::string, compimap> headers = std::map<std::string, std::string, compimap>());

        LIBPUB static Response Post(std::string url, std::map<std::string, std::string, compimap> data, std::map<std::string, std::string, compimap> headers = std::map<std::string, std::string, compimap>());
    };

    class RawSocket : public Socket {
        std::string buffer = "";
    public:
        LIBPUB RawSocket(Socket sock);

        LIBPUB int Recv(std::string &str, uint32_t size = SOCK_BUFLEN);
    };

    class WebSocket : public Socket {
        bool handshaked;
        sc::HTTPRequest::Response header;
        std::string fragment;
        std::string buffer;

        LIBPRIV std::string CalculateConnectionHash(std::string in);
    public:
        LIBPUB WebSocket();
        LIBPUB WebSocket(Socket sock);

        LIBPUB bool Handshake(std::string data = "");

        LIBPUB int Recv(std::string &str, uint32_t size = SOCK_BUFLEN);
        LIBPUB int Send(std::string str);

        LIBPUB void Close();

        LIBPUB ~WebSocket();

        class Frame {
        public:
            LIBPUB union MaskData {
                uint32_t block;
                uint8_t bytes[4];
            };

            LIBPUB enum Opcode { CONTINUATION = 0x0, TEXT = 0x1, BINARY = 0x2, CLOSE = 0x8, PING = 0x9, PONG = 0xA };

            LIBPUB Frame();
            LIBPUB Frame(std::string data, bool mask = false, int type = Opcode::BINARY, bool fin = true, uint8_t rsv = 0x0);
            LIBPUB Frame(std::string data, uint8_t maskdata[4], bool mask = false, int type = Opcode::BINARY, bool fin = true, uint8_t rsv = 0x0);
            
            LIBPUB void SetOpcode(int opcode);
            LIBPUB int GetOpcode();
            
            LIBPUB void SetData(std::string data);
            LIBPUB std::string GetData();
            
            LIBPUB void SetMasked(bool mask);
            LIBPUB bool IsMasked();

            LIBPUB bool IsLegal();

            LIBPUB void GenerateMask();
            LIBPUB void SetMask(uint8_t mask[4]);
            LIBPUB void SetMask(uint32_t mask);
            LIBPUB MaskData GetMask();

            LIBPUB void SetFin(bool fin);
            LIBPUB bool IsFin();
            
            LIBPUB std::string Get();

            LIBPUB static Frame ErrorFrame();
            LIBPUB static Frame FromRaw(std::string raw);
            LIBPUB static std::vector<Frame> GenerateFrameset(std::vector<std::string> data, bool mask = false, int opcode = Opcode::TEXT);
        private:
            int opcode;
            std::string data;
            bool mask;
            MaskData maskdata;
            uint8_t rsv;
            bool fin;
            bool legal = true;
        };
    };
}

#endif