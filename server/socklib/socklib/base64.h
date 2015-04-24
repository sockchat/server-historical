#ifndef BASE64H
#define BASE64H
#include "stdcc.hpp"
#include <string>

extern "C" {
    LIBPUB std::string base64_encode(unsigned char const*, unsigned int len);
    LIBPUB std::string base64_decode(std::string const& s);
}

#endif