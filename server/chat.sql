BEGIN TRANSACTION;
CREATE TABLE `logs` (
	`id`	INTEGER PRIMARY KEY AUTOINCREMENT,
	`timestamp`	INTEGER,
	`username`	TEXT,
	`message`	TEXT
);
CREATE TABLE `bans` (
	`uid`	INTEGER,
	`username`	TEXT,
	`ip`	TEXT,
	`expiration`	INTEGER
);
COMMIT;
