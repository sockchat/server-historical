#include "socklib/socket.hpp"

sc::HTTPRequest::Response::Response() {
	this->Error();
}

void sc::HTTPRequest::Response::Error(int status) {
	this->status = status;
	this->headers = std::map<std::string, std::string>();
	this->content = "";
}

sc::HTTPRequest::Response::Response(int status, std::map<std::string, std::string> headers, std::string content) {
	this->status = status;
	this->headers = headers;
	this->content = content;
}

sc::HTTPRequest::Response::Response(std::string raw, std::string action) {
	this->headers = std::map<std::string, std::string>();
	if(raw.substr(0, action.length()) == action) {
		auto lines = str::split(raw, "\r\n");
		auto statusline = str::split(lines[0], ' ');

		if(statusline.size() < 2) {
			this->Error(-1);
			return;
		}

		if(action == "HTTP") {
			try {
				this->status = std::stoi(statusline[1]);
			} catch(std::exception e) {
				this->Error(-2);
				return;
			}
		} else
			this->status = 0;

		bool headersFinished = false;
		int headersize = lines[0].length() + 2;
		for(int i = 1; i < lines.size() - 1; i++) {
			headersize += lines[i].length() + 2;
			if(str::trim(lines[i]) == "") {
				headersFinished = true;
				break;
			}

			auto keyval = str::split(lines[i], ":", 2);
			if(keyval.size() != 2) {
				this->Error(-2);
				return;
			} else
				this->headers[str::tolower(str::trim(keyval[0]))] = str::trim(keyval[1]);
		}

		if(!headersFinished) {
			this->Error(-1);
			return;
		}

		this->content =
			this->headers.count("content-length") == 0 ?
				raw.substr(headersize) :
				raw.substr(headersize, std::stoi(this->headers["content-length"]));

	} else this->Error(-2);
}

bool sc::HTTPRequest::Response::IsValid() {
	return this->status >= 0;
}

uint16_t sc::HTTPRequest::GetPortFromProtocol(std::string protocol) {
	if(protocol == "https") return 443;
	else return 80;
}

sc::HTTPRequest::URL sc::HTTPRequest::DecipherURL(std::string url) {
	URL ret;
	int pos;
	if((pos = url.find("://")) != std::string::npos) {
		ret.protocol = url.substr(0, pos);
		url = url.substr(pos + 3);
	} else ret.protocol = "http";

	if((pos = url.find('/')) != std::string::npos) {
		ret.target = url.substr(0, pos);
		ret.resource = url.substr(pos);
	} else {
		ret.target = url;
		ret.resource = "/";
	}

	if((pos = ret.target.find(':')) != std::string::npos) {
		ret.port = std::stoi(ret.target.substr(pos + 1));
		ret.target = ret.target.substr(0, pos);
	} else
		ret.port = GetPortFromProtocol(ret.protocol);

	return ret;
}

std::string sc::HTTPRequest::EncodeURI(std::string uri) {
	return uri;
}

std::string sc::HTTPRequest::EncodeURIComponent(std::string comp) {
	return comp;
}

sc::HTTPRequest::Response sc::HTTPRequest::Raw(std::string action, std::string url, std::map<std::string, std::string> headers, std::string body) {
	auto urlparts = DecipherURL(url);

	if(urlparts.protocol == "http") {
		std::string request = action + std::string(" ") + urlparts.resource + std::string(" HTTP/1.1\r\n")
			+ std::string("Host: ") + urlparts.target + "\r\n";
		for(auto it = headers.begin(); it != headers.end(); ++it)
			request += it->first + std::string(": ") + it->second;
		request += "\r\n";
		request += body;

		sc::Socket sock;
		sock.SetBlocking(true);
		sock.Init(urlparts.target, urlparts.port);
		sock.Send(request);

		sock.SetTimeout(60);
		Response tmp = Response();
		std::string buffer = "";
		while(tmp.status == -1) {
			if(sock.Recv(request) == -1) {
				sock.Close();
				return Response();
			}
			buffer += request;
			tmp = Response(buffer);
		}

		if(tmp.headers.count("content-length") > 0) {
			while(tmp.content.length() < std::stoi(tmp.headers["content-length"])) {
				if(sock.Recv(request) == -1) {
					sock.Close();
					return Response();
				}
				tmp.content += request;
			}
			tmp.content = tmp.content.substr(0, std::stoi(tmp.headers["content-length"]));
		} else if(tmp.headers["transfer-encoding"] == "chunked") {
			request = tmp.content;
			tmp.content = "";
			std::string buffer = "";
			uint32_t size = UINT32_MAX;
			while(true) {
				if(size == UINT32_MAX) {
					auto divider = str::split(request, "\r\n", 2);
					size = std::stoi(str::split(divider[0], ";", 2)[0], 0, 16);
					if(size == 0) break;
					request = divider[1];
				}

				if(buffer.length() + request.length() >= size + 2) {
					tmp.content += (buffer + request).substr(0, size);
					if(buffer.length() + request.length() == size + 2) {
						if(sock.Recv(request) == -1) {
							sock.Close();
							return Response();
						}
					} else
						request = (buffer + request).substr(size + 2);
					buffer = "";
					size = UINT32_MAX;
				} else {
					buffer += request;
					if(sock.Recv(request) == -1) {
						sock.Close();
						return Response();
					}
				}
			}
		}

		sock.Close();

		return tmp;
	}
	
	return Response();
}

sc::HTTPRequest::Response sc::HTTPRequest::Get(std::string url, std::map<std::string, std::string> data, std::map<std::string, std::string> headers) {
	bool first = url.find('?') == std::string::npos;
	for(auto it = data.begin(); it != data.end(); ++it) {
		url += (first ? std::string("?") : std::string("&")) + EncodeURIComponent(it->first) + std::string("=") + EncodeURIComponent(it->second);
		first = false;
	}

	return Raw("GET", url, headers);
}