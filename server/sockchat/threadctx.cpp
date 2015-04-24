#include "cthread.h"

ThreadContext::Connection::Connection(sc::Socket *sock) {
    this->sock = sock;
}

ThreadContext::ThreadContext(sc::Socket *sock) {
    this->pendingConns.push_back(Connection(sock));
}

void ThreadContext::Finish() {
    for(auto i = this->conns.begin(); i != this->conns.end(); )
        i = CloseConnection(i);
    this->done = true;
}

bool ThreadContext::IsDone() {
    return this->done;
}

void ThreadContext::PushSocket(sc::Socket *sock) {
    this->_pendingConns.lock();
    this->pendingConns.push_back(sock);
    this->_pendingConns.unlock();
}

void ThreadContext::HandlePendingConnections() {
    this->_pendingConns.lock();
    
    for(auto i = this->pendingConns.begin(); i != this->pendingConns.end(); ) {
        i->sock->SetBlocking(false);
        this->conns.push_back(*i);
        i = pendingConns.erase(i);
    }
    
    this->_pendingConns.unlock();
}

std::vector<ThreadContext::Connection>::iterator ThreadContext::CloseConnection(std::vector<Connection>::iterator i) {
    std::cout << i->sock->GetIPAddress() << " disconnected" << std::endl;
    i->sock->Close();
    delete i->sock;
    return this->conns.erase(i);
}