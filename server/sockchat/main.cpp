#include <iostream>
#include <thread>
#include <list>
#include <vector>
#include <map>
#include <locale>
#include <time.h>
#include "socklib/socket.hpp"
#include "socklib/library.hpp"
#include "socklib/utils.h"
#include "socklib/ini.h"
#include "cthread.h"

sc::Socket sock = sc::Socket();

#ifdef _WIN32
#define WIN32_LEAN_AND_MEAN
#include <Windows.h>
BOOL ctrlHandler(DWORD ctrlno) {
    sock.Close();
    WSACleanup();
    exit(0);
}
#else
#include <signal.h>
void sigHandler(int signo) {
    sock.Close();
    exit(0);
}
#endif

int main() {
    /*int c = 0;
    std::thread t(shit, 2, 3, std::ref(c));
    std::cout << c << std::endl;
    sc::Library lib("core.dll");
    modfunc f = (modfunc)lib.GetSymbol("initMod");
    f();*/

    //std::string hash = sha1::hash("x3JJHMbDL1EzLkh9GBhXDw==258EAFA5-E914-47DA-95CA-C5AB0DC85B11");
    //std::string str = sc::str::tolower("x3JJHMbDL1 € EzLkh9GBh¢XDw==");

    //auto ef = sc::str::split("fu\nck", '\n');

    //std::cout << calculateConnectionHash(str);

    //std::cout << base64_decode("HSmrc0sMlYUkAGmm5OPpG2HaGWk=");

#ifdef _WIN32
    SetConsoleCtrlHandler((PHANDLER_ROUTINE)&ctrlHandler, TRUE);
    WSADATA wdata;
    if(WSAStartup(MAKEWORD(2, 2), &wdata) != 0)
        return false;
#else
    signal(SIGINT, sigHandler);
    signal(SIGTERM, sigHandler);
    signal(SIGABRT, sigHandler);
#endif

    auto req = sc::HTTPRequest::Get("http://chat.flashii.net/", {
        {"view", "auth"},
        {"arg1", "303"},
        {"arg2", "2ef2094972cc00f0860cd85e96a243eaac67497d"}
    });
    std::cout << req.content;

    //auto req = sc::HTTPRequest::Get("http://aroltd.com/");

    //std::string wowo = sc::net::packTime();

    sc::INI test;
    try {
        test = sc::INI("c_config.ini", {
            {"socket", {
                {"client_root", sc::INI::STRING},
                {"port",        sc::INI::INTEGER}
            }},
            {"structure", {
                {"backlog_length",  sc::INI::INTEGER},
                {"default_channel", sc::INI::STRING}
            }},
            {"limits", {
                {"max_conns_per_ip",        sc::INI::INTEGER},
                {"max_channel_depth",       sc::INI::INTEGER},
                {"max_channel_name_length", sc::INI::INTEGER},
                {"max_username_length",     sc::INI::INTEGER},
                {"max_message_length",      sc::INI::INTEGER},
                {"max_idle_time",           sc::INI::INTEGER}
            }}
        });
    } catch(std::exception &e) {
        std::cout << e.what() << std::endl;
        return -1;
    }

    std::string a = test["socket"]["port"];

    sc::Socket client;
    if(!sock.Init(6770)) {
        std::cout << "Could not open socket on port 6770! Error: " << std::endl;
        return -1;
    }
    sock.SetBlocking(false);

    int status;
    auto conns = std::map<std::string, ThreadContext*>();
    while(true) {
        if((status = sock.Accept(client)) == 0) {
            if(conns.count(client.GetIPAddress()) == 0) {
                auto tmp = new ThreadContext(new sc::Socket(client));
                conns[client.GetIPAddress()] = tmp;
                std::thread(connectionThread, tmp).detach();
            } else
                conns[client.GetIPAddress()]->PushSocket(new sc::Socket(client));
        } else if(status == -1) break;

        for(auto i = conns.begin(); i != conns.end(); ) {
            if(i->second->IsDone()) {
                delete i->second;
                i = conns.erase(i);
            } else ++i;
        }
    }

    sock.Close();

#ifdef _WIN32
    WSACleanup();
#endif

    return 0;
}