#include <iostream>
#include "socklib/thread.hpp"
#include "socklib/library.hpp"

void shit(int a, int b, int &c) {
	c = a + b;
}

typedef void(*modfunc)();

int main() {
	sc::Thread::cool(22);
	sc::Library lib("core.dll");
	modfunc f = (modfunc)lib.GetSymbol("initMod");
	sc::Thread::get();
	f();
	while(true);
	return 0;
}