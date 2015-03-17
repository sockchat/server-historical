// PLATFORM INDEPENDENT THREADING INTERFACE
// For interface, see thread.hpp

#include "thread.hpp"
#ifdef _WIN32 // windows-specific threading

sc::Thread

#else // posix threading

#endif
// TODO: check to see if other threading architectures need support
