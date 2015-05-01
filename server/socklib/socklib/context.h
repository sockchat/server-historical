#ifndef SOCKCTXH
#define SOCKCTXH

#include <string>
#include <vector>
#include <queue>
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
        LIBPUB enum BanType { USERBAN, IPBAN };

        LIBPUB Ban() {}
        LIBPUB Ban(User u, BanType type = USERBAN);
        // duration is in minutes
        LIBPUB Ban(User u, uint64_t duration, BanType type = USERBAN);
        LIBPUB Ban(std::pair<int64_t, bool> id, std::pair<std::string, bool> ip, std::pair<std::string, bool> username,
            std::pair<std::chrono::time_point<std::chrono::steady_clock>, bool> expire);

        LIBPUB bool CheckUser(User u, bool *isExpired = NULL);
        LIBPUB bool IsExpired();
    };

    struct MessageFlags {
        bool flags[5];

        MessageFlags() : MessageFlags(true, false, false, true, true) {}

        MessageFlags(bool boldName, bool italicName, bool underlineName,
                     bool colon, bool alert) {
            flags[0] = boldName; flags[1] = italicName;
            flags[2] = underlineName; flags[3] = colon;
            flags[4] = alert;
        }

        std::string Get() const {
            std::stringstream ss;
            for(int i = 0; i < 5; i++)
                ss << flags[i] ? "1" : "0";
            return ss.str();
        }
    };

    class User {
    public:
        class Permissions {
            uint16_t rank;
            uint8_t stdperms[4];
            std::map<std::string, uint8_t> custom;
        public:
            LIBPUB enum UserType           { ILLEGAL = -1, NORMAL, MODERATOR, ADMIN };
            LIBPUB enum CreateChannelType  { ILLEGAL = -1, DISABLED, TEMPORARY, PERMANENT };

            LIBPUB Permissions();
            LIBPUB Permissions(std::string permstr);
            LIBPUB Permissions(uint16_t rank, uint8_t stdperms[4], std::map<std::string, uint8_t> custom);

            LIBPUB uint16_t GetRank() const;
            LIBPUB void SetRank(uint8_t rank);

            LIBPUB UserType GetUserType() const;
            LIBPUB void SetUserType(UserType type);

            LIBPUB CreateChannelType GetCreateChannelType() const;
            LIBPUB void SetCreateChannelType() const;

            LIBPUB bool CanViewLogs() const;
            LIBPUB void CanViewLogs(bool can);

            LIBPUB bool CanChangeUsername() const;
            LIBPUB void CanChangeUsername(bool can);

            LIBPUB bool CheckCustomPermission(std::string name) const;
            LIBPUB uint8_t GetCustomPermission(std::string name);
            LIBPUB void SetCustomPermission(std::string name, uint8_t value);

            LIBPUB std::string Get();
            LIBPUB operator std::string() const;
        };

        LIBPUB User()/* : id(-1), bot(false)*/;

        // constructor for a legitimate user
        LIBPUB User(int64_t id, std::string ip, std::string username, std::string color,
                    Permissions permissions = Permissions());

        // constructor for a bot
        LIBPUB User(std::string username, std::string color);

        LIBPUB int64_t GetId() const;
        LIBPUB std::string GetIp() const;
        LIBPUB bool IsBot() const;

        LIBPUB std::string GetUsername() const;
        LIBPUB std::string GetOriginalUsername() const;
        LIBPUB std::string GetOriginalUnsanitizedUsername() const;

        LIBPUB void SetUsername(std::string username);
        LIBPUB void ResetUsername();

        LIBPUB std::string GetColor() const;
        LIBPUB std::string GetOriginalColor() const;

        LIBPUB void SetColor(std::string color);
        LIBPUB void ResetColor();

        LIBPUB Permissions GetPermissions() const;
        LIBPUB Permissions GetOriginalPermissions() const;

        LIBPUB void SetPermissions(Permissions perms);
        LIBPUB void ResetPermissions();


        LIBPUB void Send(sc::Message msg);
        LIBPUB void Send(User u, std::string message, sc::MessageFlags flags = sc::MessageFlags(),
                         std::string sockstamp = "", uint64_t msgId = 0);

        LIBPUB typedef std::queue<std::pair<sc::Message, bool>> ActionQueue;
        LIBPUB void HookActionQueue(ActionQueue *aq);
        LIBPUB void UnhookActionQueue(ActionQueue *aq);

        friend Channel;
        friend Context;
    private:
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

        std::vector<ActionQueue *> actions = std::vector<ActionQueue *>();
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

        std::string password = "";
        std::mutex password_m;

        uint16_t minRank = 0;
        std::mutex minRank_m;

        std::map<int64_t, User*> users = std::map<int64_t, User*>();
        std::mutex users_m;

        std::vector<std::tuple<uint64_t, std::string, bool>> moderators
            = std::vector<std::tuple<uint64_t, std::string, bool>>();
        std::mutex moderators_m;
    public:
        LIBPUB Channel();
        LIBPUB Channel(uint32_t id, std::string name, Channel *parent);
        LIBPUB Channel(uint32_t id, std::string name, Channel *parent,
                       std::vector<Channel*> children);
        LIBPUB Channel(uint32_t id, std::string name, Channel *parent,
                       std::vector<std::pair<uint64_t, std::string>> moderators);
        LIBPUB Channel(uint32_t id, std::string name, Channel *parent,
                       std::vector<std::pair<uint64_t, std::string>> moderators,
                       std::vector<Channel*> children);
        LIBPUB Channel(uint32_t id, std::string name, Channel *parent,
                       std::vector<Channel*> children,
                       std::vector<std::pair<uint64_t, std::string>> moderators);

        // deletes without checking user permissions
        LIBPUB bool Delete();
        // only deletes if user fulfills required permissions
        LIBPUB bool Delete(User u);

        LIBPUB bool Create(std::string relpath, bool recursive = false);

        LIBPUB enum JoinError { OK, WRONG_PASSWORD, WRONG_RANK };
        LIBPUB JoinError Join(User u, std::string pwdGuess = "");
        LIBPUB JoinError Join(User u, std::string langid, std::string pwdGuess = "");

        LIBPUB void Leave(User u, std::string langid = "leave");

        LIBPUB bool IsChannelOwner(User u);
        LIBPUB bool IsChannelModerator(User u);
        LIBPUB bool IsInChannel(User u);

        LIBPUB enum PromotionType { NORMAL, MODERATOR, OWNER };
        // promotes user without checking if requesting user has ability to
        LIBPUB void Promote(User u, PromotionType type);
        // promotes user only if requesting user has permission to do so
        LIBPUB bool Promote(User u, PromotionType type, User requestingUser);

        LIBPUB void Rename(std::string name);
        LIBPUB bool Rename(std::string name, User u);

        LIBPUB bool Move(Channel *newParent);
        LIBPUB bool Move(Channel *newParent, User u);

        LIBPUB void SetRank(uint16_t rank);
        LIBPUB bool SetRank(uint16_t rank, User u);

        LIBPUB void Broadcast(sc::Message msg);
        LIBPUB void Broadcast(User u, std::string message, sc::MessageFlags flags = sc::MessageFlags(),
                              std::string sockstamp = "", uint64_t msgId = 0);
    };

    class Context {
        LIBPUB static sc::INI config;

        LIBPUB static uint64_t msgId;
        LIBPUB static std::mutex msgId_m;

        LIBPUB static std::vector<Ban> bans;
        LIBPUB static std::mutex bans_m;

        LIBPUB static std::map<int64_t, User*> users;
        LIBPUB static std::mutex users_m;

        LIBPUB static std::map<uint32_t, Channel*> channelMap;
        LIBPUB static std::mutex channelMap_m;

        LIBPUB static Channel rootChannel;
        LIBPUB static std::mutex rootChannel_m;
    public:
        class Auth {
            LIBPUB static std::string RawRequest(std::map<std::string, std::string> post);
            LIBPUB static User GenerateUserFromString(std::string str);
        public:
            LIBPUB static User Authenticate(std::vector<std::string> args);
            LIBPUB static User Authenticate(std::vector<std::vector<std::string>> args);
            LIBPUB static User Validate(int64_t id, std::string username);
            LIBPUB static bool Reserved();
        };

        LIBPUB static void Init();

        LIBPUB static User* AddNewUser(User u);
        LIBPUB static Channel* AddNewChannel(Channel c, std::string path);

        LIBPUB static uint64_t GetNextMessageId();
        LIBPUB static uint64_t GetCurrentMessageId();

        LIBPUB static User* GetUserById(int64_t id);
        LIBPUB static User* GetUserByName(std::string name);
        LIBPUB static User* GetUserByOriginalName(std::string name);
        LIBPUB static User* GetUserByUnsanitizedName(std::string name);

        LIBPUB static bool IsUserBanned(User u);
        LIBPUB static void CullExpiredBans();

        LIBPUB static Channel* GetChannelById(uint32_t id);
        LIBPUB static Channel* GetChannelByPath(std::string path);

        LIBPUB static std::string GetAbsolutePath(std::string relpath, Channel *from);

        friend Ban;
        friend User;
        friend Channel;
    };
}

#endif