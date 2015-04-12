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

typedef void(*modfunc)();

struct Connection {
	sc::Socket *sock;
	time_t conn;
	bool init;

	Connection(sc::Socket *sock) {
		this->sock = sock;
		time(&this->conn);
		this->init = false;
	}
};

void CloseConnection(std::list<Connection>::iterator &i, std::list<Connection> &l);

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

#ifdef _WIN32
	WSADATA wdata;
	if(WSAStartup(MAKEWORD(2, 2), &wdata) != 0)
		return false;
#endif

	sc::Socket sock = sc::Socket();
	sc::Socket client;
	if(!sock.Init(6770)) {
		std::cout << "Could not open socket on port 6770! Error: " << std::endl;
		return -1;
	}
	sock.SetBlocking(false);

	std::list<Connection> conns = std::list<Connection>();
	std::string in;
	int status;
	while(true) {
		if((status = sock.Accept(client)) == 0) {
			client.SetBlocking(false);
			conns.push_front(Connection(new sc::Socket(client)));
		} else if(status == -1) break;

		for(auto i = conns.begin(); i != conns.end();) {
			if((status = i->sock->Recv(in)) == 0) {
				if(!i->init) {
					if(in.compare(0, 3, "GET") == 0) {
						auto tmp = new sc::WebSocket(*(i->sock));
						delete i->sock;
						i->sock = tmp;

						if(((sc::WebSocket*)i->sock)->Handshake(in))
							i->init = true;
						else {
							CloseConnection(i, conns);
							continue;
						}
					} else if(in.compare(0, 3, "TCP") == 0)
						i->init = true;
					else {
						CloseConnection(i, conns);
						continue;
					}
				} else {
					std::cout << in << std::endl;
					i->sock->Send("1\ty\t2\talec\t#f00\t2\f1\f1\f1\f1\f1\f1\f1\f1\tLobby\t2000");
				}
				i++;
			} else if(status == -1)
				CloseConnection(i, conns);
		}
	}

	WSACleanup();
	return 0;
}

void CloseConnection(std::list<Connection>::iterator &i, std::list<Connection> &l) {
	i->sock->Close();
	delete i->sock;
	i = l.erase(i);
}