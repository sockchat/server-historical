#include "cthread.h"

void connectionThread(sc::Socket *sock) {
	sock->SetBlocking(false);

	while(true) {

	}

	while(true) {

	}

	sock->Close();
	delete sock;
}