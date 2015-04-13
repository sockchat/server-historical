#include "socklib/socket.hpp"

sc::HTTPRequest::Response::Response() {
	this->Error();
}

void sc::HTTPRequest::Response::Error() {
	this->status = -1;
	this->headers = std::map<std::string, std::string>();
	this->content = "";
}

sc::HTTPRequest::Response::Response(int status, std::map<std::string, std::string> headers, std::string content) {
	this->status = status;
	this->headers = headers;
	this->content = content;
}

sc::HTTPRequest::Response::Response(std::string raw) {
	if(raw.substr(0, 8) == "HTTP/1.1") {
		auto lines = str::split(raw, "\r\n");
		auto statusline = str::split(lines[0], ' ');

		if(statusline.size() < 2) {
			this->Error();
			return;
		}

		this->status = std::stoi(statusline[1]);

		int headersize = lines[0].length() + 2;
		for(int i = 1; i < lines.size(); i++) {

		}
	} else this->Error();
}

sc::HTTPRequest::Response sc::HTTPRequest::Get(std::string url, std::map<std::string, std::string> headers) {

}