#ifndef SCMESSAGEH
#define SCMESSAGEH

#include <string>
#include <vector>
#include <stdint.h>
#include "stdcc.hpp"
#include "utils.h"

namespace sc {
    class Message {
        bool legal = true;
        
        size_t headerSize = 0, bodySize = 0;
        std::string raw = "";

        uint16_t id = 0;
        std::vector<std::string> parts = std::vector<std::string>();
    public:
        LIBPUB Message() { this->legal = false; };
        LIBPUB Message(std::string raw);
        LIBPUB Message(uint16_t id, std::vector<std::string> parts);

        LIBPUB bool Init(std::string raw);
        LIBPUB bool Init(uint16_t id, std::vector<std::string> parts);

        LIBPUB bool IsLegal();
        LIBPUB uint16_t GetID();
        LIBPUB std::vector<std::string> GetParts();
        LIBPUB std::string Get();

        LIBPUB size_t Size();
        LIBPUB size_t HeaderSize();
        LIBPUB size_t BodySize();
    };
}

#endif