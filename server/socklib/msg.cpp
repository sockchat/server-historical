#include "socklib/msg.h"

sc::Message::Message(std::string raw) {
	this->Init(raw);
}

sc::Message::Message(uint16_t id, std::vector<std::string> parts) {
	this->Init(id, parts);
}

bool sc::Message::Init(std::string raw) {
	if(raw.length() < 3)
		return (this->legal = false);
	
	this->id = nethelp::ntohs(raw);
	uint8_t segments = raw[2] & 0xFF;
	this->headerSize = 3;
	this->bodySize = 0;

	uint32_t *segs = new uint32_t[segments];
	for(uint8_t i = 0; i < segments; i++) {
		this->headerSize++;
		if(raw.length() < this->headerSize) {
			delete segs;
			return (this->legal = false);
		}

		segs[i] = raw[this->headerSize - 1] & 0xFF;
		if(segs[i] == 254) {
			if(raw.length() >= this->headerSize + 2)
				segs[i] = nethelp::ntohs(raw.substr(this->headerSize));
			else {
				delete segs;
				return (this->legal = false);
			}
			this->headerSize += 2;
		} else if(segs[i] == 255) {
			if(raw.length() >= this->headerSize + 4)
				segs[i] = nethelp::ntohl(raw.substr(this->headerSize));
			else {
				delete segs;
				return (this->legal = false);
			}
		}
		this->bodySize += segs[i];
	}

	if(raw.length() < this->headerSize + this->bodySize) {
		delete segs;
		return (this->legal = false);
	}

	this->raw = raw.substr(0, this->headerSize + this->bodySize);
	this->parts = std::vector<std::string>();

	size_t at = this->headerSize;
	if(raw.length() < at) {
		delete segs;
		return (this->legal = false);
	}

	for(uint8_t i = 0; i < segments; i++) {
		if(raw.length() < at + segs[i]) {
			delete segs;
			return (this->legal = false);
		}
		this->parts.push_back(raw.substr(at, segs[i]));
		at += segs[i];
	}

	delete segs;
	return (this->legal = true);
}

bool sc::Message::Init(uint16_t id, std::vector<std::string> parts) {
	this->id = id;
	if(parts.size() > 0xFF)
		parts = std::vector<std::string>(parts.begin(), parts.begin() + 255);
	this->parts = parts;

	std::string body = "";
	this->raw = nethelp::htons(id) + "a";

	uint8_t actualSize = 0;
	for(auto i = parts.begin(); i != parts.end(); ++i) {
		if(i->length() < 254)
			this->raw += (char)i->length();
		else if(i->length() <= 0xFFFF) {
			this->raw += (char)254;
			this->raw += nethelp::htons(i->length());
		} else if(i->length() <= 0xFFFFFFFF) {
			this->raw += (char)255;
			this->raw += nethelp::htonl(i->length());
		} else continue;

		++actualSize;
		body += *i;
	}

	raw[2] = actualSize;
	this->raw += body;
	return (this->legal = true);
}

bool sc::Message::IsLegal() {
	return this->legal;
}

uint16_t sc::Message::GetID() {
	return this->id;
}

std::vector<std::string> sc::Message::GetParts() {
	return this->parts;
}

std::string sc::Message::Get() {
	return this->raw;
}

size_t sc::Message::Size() {
	return this->headerSize + this->bodySize;
}

size_t sc::Message::HeaderSize() {
	return this->headerSize;
}

size_t sc::Message::BodySize() {
	return this->bodySize;
}