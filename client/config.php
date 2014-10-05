<?php
$chat = array();

/* CHAT TITLE parameter
 *
 * Sets the title of the chat as it appears above the message list.
 * Takes a string.
 */
$chat["CHAT_TITLE"] = "Sock Chat";

/* INTEGRATION TYPE parameter
 *
 * Determines how the chat will integrate itself into existing architectures.
 * Behaves like an enumeration; takes an integer.
 *
 * Values:
 *  - 0: use built-in system
 *  - 1: use custom integration system (see CUSTOM INTEGRATION FILE parameter)
 *  - 2: use phpbb integration
 */
$chat["INTEGRATION"] = 2;

/* CUSTOM INTEGRATION FILE parameter
 *
 * Determines the custom header file used when doing integration. This will
 * only be referenced if the INTEGRATION TYPE parameter is set to 1.
 * Takes a string that represents a file in the ./headers directory.
 */
$chat["CINT_FILE"] = "";

/*
 *
 */