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
#include "cthread.h"

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

	auto req = sc::HTTPRequest::Get("http://iihsalf.net/");
	std::cout << req.content;

	sc::Socket sock = sc::Socket();
	sc::Socket client;
	if(!sock.Init(6770)) {
		std::cout << "Could not open socket on port 6770! Error: " << std::endl;
		return -1;
	}
	sock.SetBlocking(false);

	int status;
	auto conns = std::map<std::string, ThreadContext*>();
	while(true) {
		if((status = sock.Accept(client)) == 0) {
			if(conns.count(client.GetIPAddress()) == 0) {
				auto tmp = new ThreadContext(new sc::Socket(client));
				conns[client.GetIPAddress()] = tmp;
				std::thread(connectionThread, tmp).detach();
			} else
				conns[client.GetIPAddress()]->PushSocket(new sc::Socket(client));
		} else if(status == -1) break;

		for(auto i = conns.begin(); i != conns.end(); ) {
			if(i->second->IsDone()) {
				delete i->second;
				i = conns.erase(i);
			} else ++i;
		}
	}

	WSACleanup();
	return 0;
}