#ifndef SCMESSAGEH
#define SCMESSAGEH

#include <string>
#include <vector>
#include <stdint.h>
#include "stdcc.hpp"
#include "socket.hpp"

namespace sc {
	class Message {
		bool legal = true;
		std::string raw;
		uint16_t id;
		std::vector<std::string> parts;
	public:
		LIBPUB Message();
		LIBPUB Message(std::string raw);
		LIBPUB Message(uint16_t id, std::vector<std::string> parts);


	};
}

#endif