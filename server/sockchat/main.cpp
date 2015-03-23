#include <iostream>
#include <thread>
#include "socklib/socket.hpp"
#include "socklib/library.hpp"

void shit(int a, int b, int &c) {
	c = a + b;
}

typedef void(*modfunc)();

int main() {
	/*int c = 0;
	std::thread t(shit, 2, 3, std::ref(c));
	std::cout << c << std::endl;
	sc::Library lib("core.dll");
	modfunc f = (modfunc)lib.GetSymbol("initMod");
	f();*/
	sc::Socket sock = sc::Socket();
	sc::Socket client;
	if(!sock.Init(6770)) {
		std::cout << sock.GetLastError();
	}
	std::string in;
	while(true) {
		if(sock.Accept(client) == 0) {
			client.Recv(in);
			std::cout << in << std::endl;
			client.Send("negroid");
			client.Close();
		} else {
			std::cout << sock.GetLastError() << std::endl;
			break;
		}
	}

	WSACleanup();
	while(true);
	return 0;
}