// PLATFORM INDEPENDENT SHARED RUNTIME LIBRARY IMPLEMENTATION
// for interface, see library.hpp

#include "socklib/library.hpp"
#ifdef _WIN32 // DLL loading

sc::Library::Library(char *file) {
	this->lib = NULL;
	this->Load(file);
}

bool sc::Library::Load(char *file) {
	if(this->lib != NULL)
		this->Unload();
	this->lib = LoadLibrary(file);
	return this->IsLoaded();
}


FUNCPTR sc::Library::GetSymbol(char *sym) {
	if(this->lib != NULL)
		return GetProcAddress(this->lib, sym);
	else
		return NULL;
}

bool sc::Library::IsLoaded() {
	return this->lib != NULL;
}

void sc::Library::Unload() {
	if(this->lib != NULL) {
		FreeLibrary(this->lib);
		this->lib = NULL;
	}
}

#else // Shared Library (ELF or whatever) loading

sc::Library::Library(char *file) {
	this->lib = NULL;
	this->Load(file);
}

bool sc::Library::Load(char *file) {
	if(this->lib != NULL)
		this->Unload();
	this->lib = dlopen(file, RTLD_LAZY);
	return this->IsLoaded();
}


FUNCPTR sc::Library::GetSymbol(char *sym) {
	if(this->lib != NULL)
		return dlsym(this->lib, sym);
	else
		return NULL;
}

bool sc::Library::IsLoaded() {
	return this->lib != NULL;
}

void sc::Library::Unload() {
	if(this->lib != NULL) {
		dlclose(this->lib);
		this->lib = NULL;
	}
}

#endif