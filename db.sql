--
-- MySQL 5.5.27
-- Fri, 01 Mar 2013 03:40:59 +0000
--

CREATE TABLE `channel` (
   `username` varchar(100) not null,
   `display` varchar(100),
   `thumbnail` varchar(100),
   `updated` int(11),
   `checked` int(11) default '0',
   PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `item` (
   `user` varchar(16) not null,
   `video` varchar(11) not null,
   `channel` varchar(100),
   `state` int(11) default '0',
   PRIMARY KEY (`user`,`video`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `subscription` (
   `user` varchar(16) not null,
   `channel` varchar(100) not null,
   `new` int(11) default '0',
   `later` int(11) default '0',
   PRIMARY KEY (`user`,`channel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `user` (
   `username` varchar(16) not null,
   `hash` char(64),
   `token` text,
   `display` varchar(16),
   PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `video` (
   `video` varchar(11) not null,
   `title` text,
   `duration` int(11),
   `published` int(11),
   `channel` varchar(100),
   PRIMARY KEY (`video`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
