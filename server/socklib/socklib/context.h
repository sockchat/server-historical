#ifndef SOCKUSERH
#define SOCKUSERH

#include <string>
#include <vector>
#include <stack>
#include <map>
#include <stdint.h>
#include <mutex>
#include "msg.h"
#include "ini.h"

namespace sc {
    class Channel;
    class Context;

    class Ban {
        std::pair<int64_t, bool> id;
        std::pair<std::string, bool> ip;
        std::pair<std::string, bool> username;
        std::pair<std::chrono::time_point<std::chrono::steady_clock>, bool> expire;
    public:
        enum BanType { USERBAN, IPBAN };

        Ban() {}
        Ban(User u, BanType type = USERBAN);
        // duration is in minutes
        Ban(User u, uint64_t duration, BanType type = USERBAN);
        Ban(std::pair<int64_t, bool> id, std::pair<std::string, bool> ip, std::pair<std::string, bool> username,
            std::pair<std::chrono::time_point<std::chrono::steady_clock>, bool> expire);

        bool CheckUser(User u, bool *isExpired = NULL);
        bool IsExpired();
    };

    class User {
    public:
        class Permissions {
            uint16_t rank;
            uint8_t stdperms[4];
            std::map<std::string, uint8_t> custom;
        public:
            Permissions();
            Permissions(std::string permstr);
            Permissions(uint16_t rank, uint8_t stdperms[4], std::map<std::string, uint8_t> custom);
        };

        User()/* : id(-1), bot(false)*/;

        int64_t GetId() const;
        std::string GetIp() const;
        bool IsBot() const;

        std::string GetUsername() const;
        std::string GetOriginalUsername() const;
        std::string GetOriginalUnsanitizedUsername() const;

        void SetUsername(std::string username);
        void ResetUsername();

        std::string GetColor() const;
        std::string GetOriginalColor() const;

        void SetColor(std::string color);
        void ResetColor();

        Permissions GetPermissions() const;
        Permissions GetOriginalPermissions() const;

        void SetPermissions(Permissions perms);
        void ResetPermissions();



        friend Context;
    private:
        User(int64_t id, std::string ip, std::string username, std::string color,
             Permissions permissions = Permissions(), bool bot = false);

        const int64_t id;
        const bool bot;

        const std::string ip;

        std::string username; // clean username
        std::string _username; // original clean username
        std::string __username; // unsanitized version of original username
        std::mutex username_m;

        std::string color; // user color
        std::string _color; // original user color
        std::mutex color_m;

        Permissions permissions; // user permissions
        Permissions _permissions; // original user permissions
        std::mutex permissions_m;

        const std::vector<std::string> args;

        std::map<uint32_t, Channel*> channels = std::map<uint32_t, Channel*>();
        std::mutex channels_m;

        std::stack<std::pair<sc::Message, bool>> actions = std::stack<std::pair<sc::Message, bool>>();
        std::mutex actions_m;
    };

    class Channel {
        Channel *parent = NULL;
        std::mutex parent_m;

        std::vector<Channel*> children = std::vector<Channel*>();
        std::mutex children_m;

        const uint32_t id;
        std::string name;
        std::mutex name_m;

        std::vector<User*> users = std::vector<User*>();
        std::mutex users_m;

        std::vector<std::tuple<uint64_t, std::string, bool>> moderators
            = std::vector<std::tuple<uint64_t, std::string, bool>>();
        std::mutex moderators_m;
    public:
        Channel();
        Channel(uint32_t id, std::string name, Channel *parent);
        Channel(uint32_t id, std::string name, Channel *parent,
                std::vector<Channel*> children);
        Channel(uint32_t id, std::string name, Channel *parent,
                std::vector<std::pair<uint64_t, std::string>> moderators);
        Channel(uint32_t id, std::string name, Channel *parent,
                std::vector<std::pair<uint64_t, std::string>> moderators,
                std::vector<Channel*> children);
        Channel(uint32_t id, std::string name, Channel *parent,
                std::vector<Channel*> children,
                std::vector<std::pair<uint64_t, std::string>> moderators);


    };

    class Context {
        static sc::INI config;

        static std::vector<Ban> bans;
        static std::mutex bans_m;

        static std::map<int64_t, User*> users;
        static std::mutex users_m;

        static std::map<uint32_t, Channel*> channelMap;
        static std::mutex channelMap_m;

        static Channel rootChannel;
        static std::mutex rootChannel_m;
    public:
        class Auth {

        };

        static void Init();

        static User* AddNewUser(User u);
        static Channel* AddNewChannel(Channel c, std::string path);

        static User* GetUserById(int64_t id);
        static User* GetUserByName(std::string name);
        static User* GetUserByOriginalName(std::string name);
        static User* GetUserByUnsanitizedName(std::string name);

        static void Ban();
        static bool IsUserBanned(User u);

        static Channel* GetChannelById(uint32_t id);
        static Channel* GetChannelByPath(std::string path);

        static std::string GetAbsolutePath(std::string relpath, Channel *from);

        friend Ban;
        friend User;
        friend Channel;
    };
}

#endif