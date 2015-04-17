#include "cthread.h"

void connectionThread(ThreadContext *ctx) {
	ctx->HandlePendingConnections();

	int status;
	std::string in;
	while(ctx->conns.size() > 0) {
		ctx->HandlePendingConnections();

		for(auto i = ctx->conns.begin(); i != ctx->conns.end(); ) {
			if(!i->initialized) {
				if((status = i->sock->Recv(in, 3)) == 0) {
					if(in == "GET") {
						auto tmp = new sc::WebSocket(*(i->sock));
						delete i->sock;
						i->sock = tmp;

						if(((sc::WebSocket*)i->sock)->Handshake(in))
							i->initialized = true;
						else {
							i = ctx->CloseConnection(i);
							continue;
						}
					} else if(in == "TCP") {
						auto tmp = new sc::RawSocket(*(i->sock));
						delete i->sock;
						i->sock = tmp;
						i->initialized = true;
					}  else {
						i = ctx->CloseConnection(i);
						continue;
					}
				} else if(status == -1) {
					i = ctx->CloseConnection(i);
					continue;
				}
				++i;
			} else {
				if((status = i->sock->Recv(in)) == -1) {
					i = ctx->CloseConnection(i);
					continue;
				} else if(status == 0) {
					std::cout << i->sock->GetIPAddress() << ": " << in << std::endl;
				}
				++i;
			}
		}
	}

	ctx->Finish();
}