#ifndef INILOADERH
#define INILOADERH

#include <fstream>
#include <map>
#include <string>
#include <sstream>
#include "utils.h"

namespace sc {
    class INI {
    private:
        LIBPUB sc::exception LoadError(std::ifstream *fp, std::string file, uint64_t line, std::string message) {
            if(fp != NULL)
                fp->close();

            if(line != 0) {
                return sc::exception(
                        sc::str::join({"Error in ", file, " on line ", std::to_string(line), ": ", message}, "")
                    );
            } else {
                return sc::exception(sc::str::join({"Error in ", file, ": ", message}, ""));
            }
        }
    public:
        LIBPUB struct compimap {
            bool operator() (const std::string &lhs, const std::string &rhs) const {
                return sc::str::tolower(lhs) < sc::str::tolower(rhs);
            }
        };

        LIBPUB class INIValueTable {
            std::map<std::string, std::string, compimap> map;
            bool readonly = false;
        public:
            LIBPUB struct cast_proxy {
                std::string str;
                cast_proxy(std::string const &str) : str(str) {}

                /*operator std::string() const {
                    return this->str;
                }*/

                operator bool() const {
                    if(this->str == "true" || this->str == "1")
                        return true;
                    else if(this->str == "false" || this->str == "0")
                        return false;
                    else
                        throw std::bad_cast();
                }

                template<typename T> T cast() const {
                    std::stringstream str;
                    str << this->str;

                    T retval;
                    if(!(str >> retval))
                        throw std::bad_cast();
                    else return retval;
                }

                template<
                    typename T
                    , typename Decayed = typename std::decay<T>::type
                    , typename = typename std::enable_if<
                        !std::is_same<
                            const char*
                            , Decayed
                        >::value
                        && !std::is_same<
                            std::allocator<char>
                            , Decayed
                        >::value
                        && !std::is_same<
                            std::initializer_list<char>
                            , Decayed
                        >::value
                    >::type
                > operator T() {
                    return (*this).cast<T>();
                }

                template<typename T> bool operator== (const T &comp) const {
                    return this->cast<T>() == comp;
                }

                template<typename T> bool operator!= (const T &comp) const {
                    return this->cast<T>() != comp;
                }

                template<typename T> bool operator> (const T &comp) const {
                    return this->cast<T>() > comp;
                }

                template<typename T> bool operator>= (const T &comp) const {
                    return this->cast<T>() >= comp;
                }

                template<typename T> bool operator< (const T &comp) const {
                    return this->cast<T>() < comp;
                }

                template<typename T> bool operator<= (const T &comp) const {
                    return this->cast<T>() <= comp;
                }
            };

            LIBPUB INIValueTable(bool readonly = false) { 
                this->readonly = readonly; 
                this->map = std::map<std::string, std::string, compimap>();
            }

            LIBPUB INIValueTable(INIValueTable table, bool readonly): INIValueTable(table.get(), readonly) {}

            LIBPUB INIValueTable(std::map<std::string, std::string> map, bool readonly = false) {
                this->readonly = readonly;
                this->map = std::map<std::string, std::string, compimap>();
                for(auto i = map.begin(); i != map.end(); ++i)
                    this->map[i->first] = i->second;
            }

            LIBPUB cast_proxy operator[] (std::string key);
            LIBPUB bool contains(std::string key) const;
            LIBPUB void set(std::string key, std::string value);
            LIBPUB void unset(std::string key);
            LIBPUB std::map<std::string, std::string> get();
            LIBPUB bool IsReadOnly();
        };

        LIBPUB enum ValueType { STRING, INTEGER, DOUBLE, BOOLEAN };

        LIBPUB INI() {
            this->map = std::map<std::string, INIValueTable, compimap>();
        }

        LIBPUB INI(std::string file, bool readonly = true);
        LIBPUB INI(std::string file, std::map<std::string, std::map<std::string, ValueType>> verify, bool readonly = true);
        LIBPUB INI(std::map<std::string, std::map<std::string, std::string>> map, bool readonly = false);

        LIBPUB void Add(std::vector<std::string> sections);
        LIBPUB void Add(std::map<std::string, std::map<std::string, std::string>> map);
        
        LIBPUB void Remove(std::vector<std::string> sections);
        LIBPUB void Remove(std::map<std::string, std::vector<std::string>> map);

        LIBPUB bool IsReadOnly();

        LIBPUB INIValueTable& operator[] (std::string section);

        LIBPUB void SaveToFile(std::string file);
    private:
        LIBPUB void LoadFile(std::string file);
        LIBPUB void LoadFile(std::string file,
            std::map<std::string, std::map<std::string, ValueType>> verify);

        bool readonly = false;
        std::map<std::string, INIValueTable, compimap> map;
    };
}

#endif