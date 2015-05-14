#include "socklib/context.h"

void sc::User::Permissions::Error() {
    this->rank = 0xFFFF;
    for(int i = 0; i < 4; i++)
        this->stdperms[i] = 0xFF;

    this->custom = std::map<std::string, uint8_t>();
}

sc::User::Permissions::Permissions() {
    Error();
}

sc::User::Permissions::Permissions(std::string permstr) {
    auto perms = str::split(permstr, ",");
    if(perms.size() < 5) {
        Error();
        return;
    }

    this->rank = std::stoi(perms[0]);
    for(int i = 1; i < 5; i++)
        this->stdperms[i - 1] = std::stoi(perms[i]);

    this->custom = std::map<std::string, uint8_t>();
    for(int i = 5; i < perms.size(); i++) {
        auto cperm = str::split(perms[i], "=", 2);
        if(cperm.size() != 2)
            continue;
        this->custom[cperm[0]] = std::stoi(cperm[1]);
    }
}

sc::User::Permissions::Permissions(uint16_t rank, uint8_t stdperms[4],
                                   std::map<std::string, uint8_t> custom) {
    this->rank = rank;
    for(int i = 0; i < 4; i++)
        this->stdperms[i] = stdperms[i];
    this->custom = custom;
}

uint16_t sc::User::Permissions::GetRank() const {
    return this->rank;
}

void sc::User::Permissions::SetRank(uint16_t rank) {
    this->rank = rank;
}

bool sc::User::Permissions::Bound(uint8_t check, uint8_t lower, uint8_t upper) const {
    return check >= lower && check <= upper;
}

sc::User::Permissions::UserType sc::User::Permissions::GetUserType() const {
    return Bound(this->stdperms[0], 0, 2) ? static_cast<UserType>(this->stdperms[0])
                                          : UserType::ILLEGAL;
}

void sc::User::Permissions::SetUserType(sc::User::Permissions::UserType type) {
    this->stdperms[0] = type;
}

bool sc::User::Permissions::CanViewLogs() const {
    return !(this->stdperms[1] == 0);
}

void sc::User::Permissions::CanViewLogs(bool can) {
    this->stdperms[1] = can ? 1 : 0;
}

bool sc::User::Permissions::CanChangeUsername() const {
    return !(this->stdperms[2] == 0);
}

sc::User::Permissions::CreateChannelType sc::User::Permissions::GetCreateChannelType() const {
    return Bound(this->stdperms[3], 0, 2) ? static_cast<CreateChannelType>(this->stdperms[1])
                                          : CreateChannelType::ILLEGAL;
}

void sc::User::Permissions::SetCreateChannelType(sc::User::Permissions::CreateChannelType type) {
    this->stdperms[3] = type;
}

bool sc::User::Permissions::CheckCustomPermission(std::string name) const {
    return this->custom.count(name) > 0;
}

uint8_t sc::User::Permissions::GetCustomPermission(std::string name) {
    if(CheckCustomPermission(name)) return this->custom[name];
    else return 0xFF;
}

void sc::User::Permissions::SetCustomPermission(std::string name, uint8_t value) {
    this->custom[name] = value;
}

std::string sc::User::Permissions::Get() {
    std::stringstream ss;
    for(int i = 0; i < 4; i++)
        ss << (i != 0 ? "," : "") << this->stdperms[i];

    if(this->custom.size() > 0) {
        for(auto i = this->custom.begin(); i != this->custom.end(); ++i)
            ss << "," << i->first << "=" << i->second;
    }

    return ss.str();
}

sc::User::Permissions::operator std::string() {
    return Get();
}

std::string sc::User::SanitizeUsername(std::string name) {
    // TODO this
    return name;
}

sc::User::User() : id(0), bot(false) {
    this->ip = "0";
}

sc::User::User(int64_t id, std::string ip, std::string username, 
               std::string color, Permissions permissions) {
    this->bot = false;

    this->id = id;
    this->ip = ip;
    this->__username = username;

    this->username = this->_username = SanitizeUsername(username);
    this->color = this->_color = color;
    this->permissions = this->_permissions = permissions;

    this->args = std::vector<std::string>();
}

sc::User::User(std::string username, std::string color) {
    this->bot = true;
    
    this->id = -2;
    this->ip = "127.0.0.1";
    this->__username = username;

    this->username = this->_username = SanitizeUsername(username);
    this->color = this->_color = color;
    this->permissions = Permissions();

    this->args = std::vector<std::string>();
}