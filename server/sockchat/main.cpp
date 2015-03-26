#include <iostream>
#include <thread>
#include <list>
#include <vector>
#include <map>
#include <locale>
#include <time.h>
#include "socklib/socket.hpp"
#include "socklib/library.hpp"
#include "socklib/utils.h"
#include "socklib/sha1.h"
#include "socklib/base64.h"

typedef void(*modfunc)();

std::string calculateConnectionHash(std::string in);

struct Connection {
	enum TYPE { UNINIT, WEBSOCK, RAWSOCK };

	sc::Socket sock;
	std::map<std::string, std::string> headers;
	TYPE type;
	time_t conn;

	Connection(sc::Socket sock) {
		this->sock = sock;
		this->headers = std::map<std::string, std::string>();
		time(&this->conn);
		this->type = TYPE::UNINIT;
	}
};

int main() {
	/*int c = 0;
	std::thread t(shit, 2, 3, std::ref(c));
	std::cout << c << std::endl;
	sc::Library lib("core.dll");
	modfunc f = (modfunc)lib.GetSymbol("initMod");
	f();*/

	//std::string hash = sha1::hash("x3JJHMbDL1EzLkh9GBhXDw==258EAFA5-E914-47DA-95CA-C5AB0DC85B11");
	//std::string str = str::tolower("x3JJHMbDL1 € EzLkh9GBh¢XDw==");

	//auto ef = str::split("fu\nck", '\n');

	//std::cout << calculateConnectionHash(str);

	//std::cout << base64_decode("HSmrc0sMlYUkAGmm5OPpG2HaGWk=");

	sc::Socket sock = sc::Socket();
	sc::Socket client;
	if(!sock.Init(6770)) {
		std::cout << "Could not open socket on port 6770!" << std::endl;
		return -1;
	}
	sock.SetBlocking(false);

	std::list<Connection> conns = std::list<Connection>();
	std::string in;
	int status;
	while(true) {
		if((status = sock.Accept(client)) == 0) {
			conns.push_back(Connection(client));
			/*std::cout << client.Recv(in);
			std::cout << in << std::endl;
			client.Send("negroid");
			client.Close();*/
		} else if(status == -1) break;

		/*for(int i = 0; i < conns.size(); i++) {
			//if((status = conns[i]))
			conns.
		}*/

		for(auto i = conns.begin(); i != conns.end();) {
			if((status = i->sock.Recv(in)) == 0) {
				if(i->type == Connection::TYPE::UNINIT) {
					if(in.compare(0, 3, "GET") == 0) {
						auto headers = str::split(in, "\r\n");

					} else if(in.compare(0, 3, "TCP") == 0) {
						i->type = 
					} else {
						i = conns.erase(i);
						continue;
					}
				} else {

				}
				i++;
			} else if(status == -1)
				i = conns.erase(i);
		}
	}

	WSACleanup();
	return 0;
}

std::string calculateConnectionHash(std::string in) {
	in += "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";
	unsigned char hash[20];
	sha1::calc(in.c_str(), in.length(), hash);
	return std::string(base64_encode(hash, 20));
}