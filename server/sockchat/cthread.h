#ifndef CTHREADH
#define CTHREADH

#include <iostream>
#include <thread>
#include <list>
#include <vector>
#include <map>
#include <locale>
#include <time.h>
#include <mutex>
#include "socklib/socket.hpp"
#include "socklib/library.hpp"
#include "socklib/utils.h"

class ThreadContext {
public:
    struct Connection {
        sc::Socket *sock;
        bool initialized = false;

        Connection(sc::Socket *sock);
    };

    ThreadContext(sc::Socket *sock);

    void PushSocket(sc::Socket *sock);
    void HandlePendingConnections();

    std::vector<Connection>::iterator CloseConnection(std::vector<Connection>::iterator i);

    void Finish();
    bool IsDone();

    std::vector<Connection> conns = std::vector<Connection>();
private:
    std::vector<Connection> pendingConns = std::vector<Connection>();
    std::mutex _pendingConns;

    bool done = false;
};

void connectionThread(ThreadContext *ctx);
void interpretMessage(sc::Message msg);

#endif