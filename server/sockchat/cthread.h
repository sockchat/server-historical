#ifndef CTHREADH
#define CTHREADH

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

void connectionThread(sc::Socket *sock);

#endif