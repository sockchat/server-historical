#include "stdcc.hpp"
#include "socklib/thread.hpp"
#include <iostream>

extern "C" LIBPUB void initMod() {
	sc::Thread::get();
}