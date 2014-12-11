<?php

$chat = array();

/* CHAT TITLE parameter
 *
 * Sets the title of the chat as it appears above the message list.
 * Takes a string.
 */
$chat["CHAT_TITLE"] = "Shock Cat Beta";

/* INTEGRATION TYPE parameter
 *
 * Determines how the chat will integrate itself into existing architectures.
 * Takes an integer represented by the INT_* defines.
 */
$chat["INTEGRATION"] = INT_PHPBB;

/* CUSTOM INTEGRATION FILE parameter
 *
 * Determines the custom header file used when doing integration. This will
 * only be referenced if the INTEGRATION TYPE parameter is set to INT_CUSTOM.
 * Takes a string that represents a file in the ./auth directory.
 */
$chat["CINT_FILE"] = "";

/* SERVER ADDRESS parameter
 *
 * Tells the socket where it should connect to. Use the format "ADDRESS:PORT".
 * But make sure not to include any protocol information, like sticking http://
 * or ws:// or any protocol prefix before the address.
 * Takes a string.
 *
 * EXAMPLE: "nas.moe:6770" represents a connection to the address 'nas.moe'
 * on port 6770.
 */
$chat["SERVER_ADDR"] = "aroltd.com:12120";

/* REDIRECT ADDRESS parameter
 *
 * Tells the architecture where to send a user when the socket errors or any
 * other circumstance where a redirect is necessary. This is typically a login
 * page or index page or anything of the sort.
 * Takes a string that's in the form of a proper URL (include http/https)
 */
$chat["REDIRECT_ADDR"] = "http://aroltd.com/phpBB3";

/* PING PERIOD parameter
 *
 * Tells the client how often to send the server a keepalive ping. Should not be
 * greater than five minutes / 300 seconds (as that is the default max idle time
 * on most browser implementation of web sockets).
 * Takes a number that represents seconds between ping pulses.
 */
$chat["PING_PERIOD"] = 60;

/* DEFAULT SOUND PACK parameter
 *
 * Tells the client which sound pack to select as the default pack for users that
 * have not set an alternate sound pack.
 * Takes a string that represents a folder in the ./sound directory
 */
$chat["DEFAULT_SPACK"] = "default";

/* DEFAULT STYLE parameter
 *
 * Tells the client which style to select as the default style for users that
 * have not set an alternate style.
 * Takes a string that represents the name of a stylesheet in the ./styles directory
 * without the .css extension
 */
$chat["DEFAULT_STYLE"] = "black";

/* DEFAULT LANGUAGE parameter
 *
 * Tells the client which language to select as the default language for users
 * that have not set an alternate language.
 * Takes a string that represents the name of a language directory in the
 * ./lang directory.
 */
$chat["DEFAULT_LANG"] = "en";