// PLATFORM INDEPENDENT THREADING INTERFACE
// For implementation, see thread.cpp

#ifndef THREADH
#define THREADH

#ifdef _WIN32
#include <Windows.h>
#include <process.h>
#define HTHREAD HANDLE
#else
#include <stdlib.h>
#include <pthread.h>
#define HTHREAD pthread_t
#endif

#include "stdcc.hpp"
#include <iostream>

namespace sc {
	class Thread {
		typedef void*(threadFunc)(void*);
		threadFunc func;
		HTHREAD thread;
	public:
		LIBPUB Thread(threadFunc f, bool start = false, void *args = NULL);
		LIBPUB void Set(threadFunc f);
		LIBPUB bool Spawn(void *args = NULL);
		LIBPUB bool IsRunning();
		LIBPUB void Join();
		LIBPUB void Kill();
	};
}

#endif