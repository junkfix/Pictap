BEGIN TRANSACTION;
CREATE TABLE config (
	key TEXT NOT NULL UNIQUE,
	value TEXT NOT NULL
);
CREATE TABLE devs (
	devid	INTEGER NOT NULL,
	dev	TEXT UNIQUE COLLATE NOCASE,
	PRIMARY KEY(devid)
);
CREATE TABLE states (
	stateid	INTEGER NOT NULL,
	state	TEXT NOT NULL UNIQUE,
	PRIMARY KEY(stateid)
);
CREATE TABLE users (
	userid	INTEGER NOT NULL,
	username	TEXT NOT NULL UNIQUE COLLATE NOCASE,
	password	TEXT NOT NULL,
	role	INTEGER NOT NULL,
	token	TEXT NOT NULL,
	PRIMARY KEY(userid)
);
CREATE TABLE tags (
	tagid	INTEGER NOT NULL,
	tag	TEXT NOT NULL COLLATE NOCASE,
	cat	INTEGER NOT NULL DEFAULT 0,
	PRIMARY KEY(tagid),
	UNIQUE(tag COLLATE NOCASE)
);
CREATE TABLE tagfiles (
	fileid	INTEGER NOT NULL,
	tagid	INTEGER NOT NULL,
	FOREIGN KEY(fileid) REFERENCES files(fileid) ON DELETE CASCADE,
	FOREIGN KEY(tagid) REFERENCES tags(tagid) ON DELETE CASCADE,
	UNIQUE(fileid,tagid)
);
CREATE TABLE albumfiles (
	albumid	INTEGER NOT NULL,
	fileid	INTEGER NOT NULL,
	FOREIGN KEY(albumid) REFERENCES albums(albumid) ON DELETE CASCADE,
	FOREIGN KEY(fileid) REFERENCES files(fileid) ON DELETE CASCADE,
	UNIQUE(albumid,fileid)
);
CREATE TABLE files (
	fileid	INTEGER NOT NULL,
	dirid	INTEGER NOT NULL,
	file	INTEGER NOT NULL,
	ft	INTEGER NOT NULL DEFAULT 0,
	sz	INTEGER NOT NULL DEFAULT 0,
	mt	INTEGER NOT NULL DEFAULT 0,
	tk	INTEGER NOT NULL DEFAULT 0,
	fps	INTEGER NOT NULL DEFAULT 0,
	dur	INTEGER NOT NULL DEFAULT 0,
	w	INTEGER NOT NULL DEFAULT 0,
	h	INTEGER NOT NULL DEFAULT 0,
	ori	INTEGER NOT NULL DEFAULT 0,
	lat	INTEGER NOT NULL DEFAULT 0,
	lon	INTEGER NOT NULL DEFAULT 0,
	kw	INTEGER NOT NULL DEFAULT 0,
	th	INTEGER NOT NULL DEFAULT 2,
	star	INTEGER NOT NULL DEFAULT 0,
	locationid	INTEGER DEFAULT NULL,
	devid	INTEGER DEFAULT NULL,
	PRIMARY KEY(fileid),
	FOREIGN KEY(locationid) REFERENCES locations(locationid) ON DELETE SET NULL,
	FOREIGN KEY(devid) REFERENCES devs(devid) ON DELETE SET NULL,
	FOREIGN KEY(dirid) REFERENCES dirs(dirid) ON DELETE CASCADE,
	UNIQUE(dirid,file)
);
CREATE TABLE dirs (
	dirid	INTEGER NOT NULL,
	dir	TEXT NOT NULL UNIQUE,
	mt	INTEGER NOT NULL DEFAULT 0,
	sz	INTEGER NOT NULL DEFAULT 0,
	qt	INTEGER NOT NULL DEFAULT 0,
	parentid	INTEGER DEFAULT NULL,
	thm	INTEGER DEFAULT NULL,
	PRIMARY KEY(dirid),
	FOREIGN KEY(thm) REFERENCES files(fileid) ON DELETE SET NULL,
	FOREIGN KEY(parentid) REFERENCES dirs(dirid) ON DELETE CASCADE
);
CREATE TABLE albums (
	userid	INTEGER NOT NULL,
	albumid	INTEGER NOT NULL,
	name	TEXT NOT NULL,
	qt	INTEGER NOT NULL DEFAULT 0,
	mtime	INTEGER NOT NULL DEFAULT 0,
	share	TEXT DEFAULT NULL UNIQUE,
	family	INTEGER NOT NULL DEFAULT 0,
	thm	INTEGER DEFAULT NULL,
	PRIMARY KEY(albumid),
	FOREIGN KEY(thm) REFERENCES files(fileid) ON DELETE SET NULL
);
CREATE TABLE locations (
	locationid	INTEGER NOT NULL,
	countryid	INTEGER NOT NULL,
	stateid	INTEGER NOT NULL,
	lat	REAL NOT NULL,
	lon	REAL NOT NULL,
	location	TEXT NOT NULL,
	PRIMARY KEY(locationid),
	FOREIGN KEY(countryid) REFERENCES countries(countryid),
	FOREIGN KEY(stateid) REFERENCES states(stateid),
	UNIQUE(lat,lon)
);
CREATE TABLE countries (
	countryid	INTEGER NOT NULL,
	country	TEXT NOT NULL UNIQUE,
	PRIMARY KEY(countryid)
);
INSERT INTO dirs (dirid,dir) VALUES (1,'');
COMMIT;
