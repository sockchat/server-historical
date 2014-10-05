using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using SuperWebSocket;
using SuperSocket;
using SuperSocket.SocketBase;

namespace server {
    class User {
        public int id;
        public string username;
        public string color;
        public WebSocketSession sock;

        public User() { }

        public User(int i, string u, string c, WebSocketSession s) {
            id = i;
            username = u;
            color = c;
            sock = s;
        }
    }
}
