// PLATFORM INDEPENDENT THREADING INTERFACE
// For interface, see thread.hpp

#include "socklib/thread.hpp"
#ifdef _WIN32 // windows-specific threading

sc::Thread::Thread(sc::Thread::threadFunc f, bool start, void *args) {
	this->thread = 0;
	this->func = f;
	if(start)
		this->Spawn(args);
}

void sc::Thread::Set(sc::Thread::threadFunc f) {
	this->func = f;
}

bool sc::Thread::Spawn(void *args) {
	if(!sc::Thread::IsRunning()) {
		this->thread = (HANDLE)_beginthreadex(NULL, 0, this->func, args, 0, NULL);
		return this->thread == 0;
	} else return false;
}

bool sc::Thread::IsRunning() {
	return this->thread == 0 ? false : WaitForSingleObject(this->thread, 0) == WAIT_TIMEOUT;
}

void sc::Thread::Join() {
	if(this->IsRunning()) {
		WaitForSingleObject(this->thread, INFINITE);
		CloseHandle(this->thread);
		this->thread = 0;
	}
}

void sc::Thread::Kill() {
	if(this->IsRunning()) {
		TerminateThread(this->thread, -1);
		this->thread = 0;
	}
}

#else // posix threading

sc::Thread::Thread(sc::Thread::threadFunc f, bool start, void *args) {
	this->thread = 0;
	this->func = f;
	if(start)
		this->Spawn(args);
}

void sc::Thread::Set(sc::Thread::threadFunc f) {
	this->func = f;
}

bool sc::Thread::Spawn(void *args) {
	if(!sc::Thread::IsRunning()) {
		return pthread_create(&this->thread, 0, this->func, NULL, args) == 0;
	} else return false;
}

bool sc::Thread::IsRunning() {
	return this->thread == 0 ? false : pthread_kill(this->thread, 0) == 0;
}

void sc::Thread::Join() {
	if(this->IsRunning()) {
		pthread_join(this->thread, NULL);
		this->thread = 0;
	}
}

void sc::Thread::Kill() {
	if(this->IsRunning()) {
		pthread_kill(this->thread, SIGTERM);
		this->thread = 0;
	}
}

#endif
// TODO: check to see if other threading architectures need support
