#include "socklib/utils.h"

uint32_t sc::str::at(std::string str, int loc) {
    const char *front = str.c_str(), *back = front + str.length(), *ptr = front;
    uint32_t ret = 0;
    for(; loc >= 0; loc--) {
        if(ptr == back) break;
        ret = utf8::next(ptr, back);
    }
    return loc == 0 ? ret : 0;
}

std::string sc::str::transformBytes(std::string str, sc::str::transformFunc func) {
    std::string ret = str;
    const char *ptr = str.c_str(), *lptr = ptr;
    const char *start = ptr, *end = ptr + str.length();
    while(ptr != end) {
        uint32_t c = utf8::next(ptr, end);
        if(sc::str::getCharSize(c) == 1) {
            ret[lptr - start] = func((char)c);
        }
        lptr = ptr;
    }
    return ret;
}

std::string sc::str::tolower(std::string str) {
    return sc::str::transformBytes(str, ::tolower);
}

std::string sc::str::toupper(std::string str) {
    return sc::str::transformBytes(str, ::toupper);
}

int sc::str::length(std::string str) {
    return utf8::distance(str.begin(), str.end());
}

bool sc::str::valid(std::string str) {
    return utf8::is_valid(str.begin(), str.end());
}

std::string sc::str::fix(std::string str) {
    utf8::replace_invalid(str.begin(), str.end(), std::back_inserter(str));
    return str;
}

std::string sc::str::substr(std::string str, int start, int end) {
    if(start > end) return "";
    return sc::str::substring(str, start, end == -1 ? -1 : end - start);
}

std::string sc::str::substring(std::string str, int start, int length) {
    if(length == 0) return "";
    else {
        const char *front = str.c_str(), *back = front + str.length();
        const char *pstart = front, *pend;

        while(start != 0 && pstart != back) {
            utf8::next(pstart, back);
            start--;
        }
        if(pstart == back) return "";

        if(length > 0) {
            pend = pstart;
            while(length != 0 && pend != back) {
                utf8::next(pend, back);
                length--;
            }
        } else pend = back;
        return str.substr(pstart - front, pend - pstart);
    }
}

std::vector<std::string> sc::str::split(std::string str, char delim, int count) {
    count--;
    std::stringstream ss(str);
    auto ret = std::vector<std::string>();
    std::string buffer;
    while(count != 0) {
        if(!std::getline(ss, buffer, delim)) break;
        ret.push_back(buffer);
        count--;
    }
    if(std::getline(ss, buffer)) ret.push_back(buffer);
    return ret;
}

std::vector<std::string> sc::str::split(std::string str, std::string delim, int count) {
    count-=1;
    auto ret = std::vector<std::string>();
    std::size_t pos = 0, lastpos = 0;
    while((pos = str.find(delim, pos)) != std::string::npos && count != 0) {
        if(pos - lastpos > 0)
            ret.push_back(str.substr(lastpos, pos - lastpos));
        else
            ret.push_back("");

        pos += delim.length();
        lastpos = pos;
        count--;
    }
    ret.push_back(str.substr(lastpos, std::string::npos));
    return ret;
}

std::string sc::str::join(std::vector<std::string> arr, std::string delim, int count) {
    std::string ret;
    for(int i = 0; i < arr.size(); i++) {
        if(count == 0) break;
        ret += (i == 0 ? "" : delim) + arr[i];
        count--;
    }
    return ret;
}

std::string sc::str::trim(std::string str) {
    return sc::str::btrim(sc::str::ftrim(str));
}

std::string sc::str::ftrim(std::string str) {
    if(str.length() == 0) {
        return "";
    } else {
        const char *front = str.c_str();
        const char *ptr = front;

        do {
            uint32_t c = utf8::next(ptr, front + str.length());
            char ch = (char)c;
            if(sc::str::getCharSize(c) > 1 || (!isspace(ch) && !iscntrl(ch))) {
                utf8::prior(ptr, front);
                break;
            }
        } while(ptr != front + str.length());

        return ptr == front + str.length() ? "" : str.substr(ptr-front);
    } 
}

std::string sc::str::btrim(std::string str) {
    if(str.length() == 0) {
        return "";
    } else {
        const char *back = str.c_str() + str.length();
        const char *ptr = back;

        do {
            uint32_t c = utf8::prior(ptr, str.c_str());
            char ch = (char)c;
            if(sc::str::getCharSize(c) > 1 || (!isspace(ch) && !iscntrl(ch))) {
                utf8::next(ptr, back);
                break;
            }
        } while(ptr != str.c_str());

        return ptr == str.c_str() ? "" : str.substr(0, ptr - str.c_str());
    }
}

short sc::str::getCharSize(uint32_t c) {
    return c <= 0xFF        ? 1 :
          (c <= 0xFFFF        ? 2 :
          (c <= 0xFFFFFF    ? 3 :
          (c <= 0xFFFFFFFF    ? 4 : 5)));
}

std::string sc::net::htonl(uint32_t hostlong) {
    std::string ret = "aaaa";
    
    for(int i = 0; i < 4; i++)
        ret[3 - i] = (hostlong & (0xFF << 8 * i)) >> 8 * i;

    return ret;
}

std::string sc::net::htons(uint16_t hostshort) {
    std::string ret = "aa";

    ret[0] = (hostshort & 0xFF00) >> 8;
    ret[1] =  hostshort & 0x00FF;

    return ret;
}

uint32_t sc::net::ntohl(std::string netlong) {
    if(netlong.size() < 4) return 0;

    uint32_t ret = 0;
    for(int i = 0; i < 4; i++)
        ret = ret | ((netlong[3 - i] & 0xFF) << 8 * i);

    return ret;
}

uint16_t sc::net::ntohs(std::string netshort) {
    if(netshort.size() < 2) return 0;

    uint32_t ret = 0;
    ret = ((netshort[0] & 0xFF) << 8) | (netshort[1] & 0xFF);
    return ret;
}

std::string sc::net::packTime() {
    return packTime(std::chrono::system_clock::now());
}

std::string sc::net::packTime(std::chrono::time_point<std::chrono::system_clock> t) {
    time_t tmp = std::chrono::system_clock::to_time_t(t);
    struct tm utc;

#ifdef _WIN32
    gmtime_s(&utc, &tmp);
#else
    gmtime_r(&tmp, &utc);
#endif
    
    int year = utc.tm_year - 115;
    if(year > 2047) year = 2047;
    if(year < -2048) year = -2048;

    std::stringstream ss;
    ss << (char)((year & 0xFF0) >> 4) << (char)(((year & 0xF) << 4) | utc.tm_mon) << (char)(utc.tm_mday - 1)
       << (char)utc.tm_hour << (char)utc.tm_min << (char)utc.tm_sec;
    
    return ss.str();
}