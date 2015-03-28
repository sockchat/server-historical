#ifndef UTILSH
#define UTILSH

#include "stdcc.hpp"
#include "utf8.h"
#include <cstdint>
#include <sstream>
#include <string>
#include <vector>
#include <algorithm>

class str {
public:
	typedef int(transformFunc)(int);

	LIBPUB static uint32_t at(std::string str, int loc);
	LIBPUB static std::string transformBytes(std::string str, transformFunc func);
	LIBPUB static std::string tolower(std::string str);
	LIBPUB static std::string toupper(std::string str);
	LIBPUB static int length(std::string str);
	LIBPUB static bool valid(std::string str);
	LIBPUB static std::string fix(std::string str);
	LIBPUB static std::string substr(std::string str, int start, int end = -1);
	LIBPUB static std::string substring(std::string str, int start, int length = -1);
	LIBPUB static std::vector<std::string> split(std::string str, char delim, int count = -1);
	LIBPUB static std::vector<std::string> split(std::string str, std::string delim, int count = -1);
	LIBPUB static std::string join(std::vector<std::string> arr, std::string delim, int count = -1);
	LIBPUB static std::string trim(std::string str);
	LIBPUB static std::string ftrim(std::string str);
	LIBPUB static std::string btrim(std::string str);
	LIBPRIV static short getCharSize(uint32_t);
};

#endif