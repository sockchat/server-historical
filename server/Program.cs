using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using SuperWebSocket;
using SuperSocket;
using SuperSocket.SocketBase;

namespace server {
    class Program {
        static private Dictionary<int, User> connectedUsers = new Dictionary<int, User>();

        static void Main(string[] args) {
            WebSocketServer sock = new WebSocketServer();
            sock.Setup(1212);
            sock.NewMessageReceived += sock_NewMessageReceived;
            sock.SessionClosed += sock_SessionClosed;
            sock.Start();
        }

        static string PackMessage(int msgid, params string[] parts) {
            return ((char)msgid) + String.Join(((char)255) + "", parts);
        }

        static long CalculateEpoch() {
            return CalculateEpoch(DateTime.UtcNow);
        }

        static long CalculateEpoch(DateTime t) {
            return (long)(t - (new DateTime(1970, 1, 1, 0, 0, 0))).TotalSeconds;
        }

        static void Broadcast(string msg) {
            foreach(User u in connectedUsers.Values) {
                u.sock.Send(msg);
            }
        }

        static void Broadcast(string name, string msg) {
            Broadcast(PackMessage(2, "" + CalculateEpoch(), name, msg));
        }

        static void sock_SessionClosed(WebSocketSession session, CloseReason value) {
            foreach(User u in connectedUsers.Values) {
                if(u.sock.RemoteEndPoint == session.RemoteEndPoint) {
                    connectedUsers.Remove(u.id);
                }
            }
        }

        static bool UsernameInUse(string name) {
            foreach(User u in connectedUsers.Values) {
                if(u.username.ToLower() == name.ToLower())
                    return true;
            }
            return false;
        }

        static void sock_NewMessageReceived(WebSocketSession session, string value) {
            int msgid = value[0];
            value = value.Substring(1);
            string[] parts = value.Split((char)255);

            switch(msgid) {
                case 1:
                    if(!UsernameInUse(parts[0])) {
                        for(int i = 0; ; i++) {
                            if(!connectedUsers.ContainsKey(i)) {
                                Broadcast(PackMessage(1, "" + i, "" + CalculateEpoch(), parts[0]));
                                connectedUsers.Add(i, new User(i, parts[0], session));
                                session.Send(PackMessage(1, "y", "" + i));
                                break;
                            }
                        }
                    } else {
                        session.Send(PackMessage(1, "n", "Username is taken."));
                    }
                    break;
                case 2:
                    int uid = Int32.Parse(parts[0]);

                    if(connectedUsers.ContainsKey(uid)) {
                        if(connectedUsers[uid].sock.RemoteEndPoint == session.RemoteEndPoint) {
                            if(parts[1].Trim() != "") {
                                if(parts[1].Trim()[0] == '/')
                                    Broadcast(parts[0], parts[1]);
                                else {
                                    // handle commands
                                }
                            }
                        }
                    }
                    break;
            }
        }
    }
}
