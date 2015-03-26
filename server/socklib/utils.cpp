#include "socklib/utils.h"

uint32_t str::at(std::string str, int loc) {
	const char *front = str.c_str(), *back = front + str.length(), *ptr = front;
	uint32_t ret = 0;
	for(; loc >= 0; loc--) {
		if(ptr == back) break;
		ret = utf8::next(ptr, back);
	}
	return loc == 0 ? ret : 0;
}

std::string str::transformBytes(std::string str, str::transformFunc func) {
	std::string ret = str;
	const char *ptr = str.c_str(), *lptr = ptr;
	const char *start = ptr, *end = ptr + str.length();
	while(ptr != end) {
		uint32_t c = utf8::next(ptr, end);
		if(str::getCharSize(c) == 1) {
			ret[lptr - start] = func((char)c);
		}
		lptr = ptr;
	}
	return ret;
}

std::string str::tolower(std::string str) {
	return str::transformBytes(str, ::tolower);
}

std::string str::toupper(std::string str) {
	return str::transformBytes(str, ::toupper);
}

int str::length(std::string str) {
	return utf8::distance(str.begin(), str.end());
}

bool str::valid(std::string str) {
	return utf8::is_valid(str.begin(), str.end());
}

std::string str::fix(std::string str) {
	utf8::replace_invalid(str.begin(), str.end(), std::back_inserter(str));
	return str;
}

std::string str::substr(std::string str, int start, int end) {
	if(start > end) return "";
	return str::substring(str, start, end == -1 ? -1 : end - start);
}

std::string str::substring(std::string str, int start, int length) {
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

std::vector<std::string> str::split(std::string str, char delim) {
	std::stringstream ss(str);
	auto ret = std::vector<std::string>();
	std::string buffer;
	while(std::getline(ss, buffer, delim))
		ret.push_back(buffer);
	return ret;
}

std::vector<std::string> str::split(std::string str, std::string delim) {
	auto ret = std::vector<std::string>();
	std::size_t pos = 0, lastpos = 0;
	while((pos = str.find(delim, pos)) != std::string::npos) {
		if(pos - lastpos > 0)
			ret.push_back(str.substr(lastpos, pos - lastpos));
		else
			ret.push_back("");

		pos += delim.length();
		lastpos = pos;
	}
	ret.push_back(str.substr(lastpos, std::string::npos));
	return ret;
}

std::string str::trim(std::string str) {
	return str::btrim(str::ftrim(str));
}

std::string str::ftrim(std::string str) {
	if(str.length() == 0) {
		return "";
	} else {
		const char *front = str.c_str();
		const char *ptr = front;

		do {
			uint32_t c = utf8::next(ptr, front + str.length());
			char ch = (char)c;
			if(str::getCharSize(c) > 1 || (!isspace(ch) && !iscntrl(ch))) {
				utf8::prior(ptr, front);
				break;
			}
		} while(ptr != front + str.length());

		return ptr == front + str.length() ? "" : str.substr(ptr-front);
	} 
}

std::string str::btrim(std::string str) {
	if(str.length() == 0) {
		return "";
	} else {
		const char *back = str.c_str() + str.length();
		const char *ptr = back;

		do {
			uint32_t c = utf8::prior(ptr, str.c_str());
			char ch = (char)c;
			if(str::getCharSize(c) > 1 || (!isspace(ch) && !iscntrl(ch))) {
				utf8::next(ptr, back);
				break;
			}
		} while(ptr != str.c_str());

		return ptr == str.c_str() ? "" : str.substr(0, ptr - str.c_str());
	}
}

short str::getCharSize(uint32_t c) {
	return c <= 0xFF		? 1 :
		  (c <= 0xFFFF		? 2 :
		  (c <= 0xFFFFFF	? 3 :
		  (c <= 0xFFFFFFFF	? 4 : 5)));
}