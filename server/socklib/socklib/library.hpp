#ifndef CCLIBH
#define CCLIBH

#ifdef _WIN32
#include <windows.h>
#define LIBHANDLE HINSTANCE
#define FUNCPTR FARPROC
#else
#include <stdlib.h>
#include <dlfcn.h>
#define LIBHANDLE void*
#define FUNCPTR void*
#endif

#include "stdcc.hpp"

namespace sc {
	class Library {
		LIBHANDLE lib;
	public:
		LIBPUB Library(char *file);
		LIBPUB bool Load(char *file);
		LIBPUB bool IsLoaded();
		LIBPUB FUNCPTR GetSymbol(char *sym);
		LIBPUB void Unload();
	};
}

#endif