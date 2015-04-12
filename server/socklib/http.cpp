#include "socklib/socket.hpp"

sc::HTTPRequest::Response::Response(std::string raw) {
	if(raw.substr(0, 8) == "HTTP/1.1") {
		auto lines = str::split(raw, "\r\n");
		auto statusline = str::split(lines[0], " ");
	}
}

sc::HTTPRequest::Response sc::HTTPRequest::Get(std::string url, std::map<std::string, std::string> headers = std::map<std::string, std::string>()) {

}