CREATE TABLE c_experiment (
			  `id` int(9) NOT NULL AUTO_INCREMENT,
			  `vl_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `vl_tele` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `vl_email` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `exp_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `exp_ort` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `exp_vps` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `exp_vpsnum` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `exp_geld` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `exp_geldnum` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `exp_zusatz` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `exp_sessions` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `exp_mail` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `exp_start` date DEFAULT NULL,
			  `exp_end` date DEFAULT NULL,
			  `vpn_name` int(1) NOT NULL,
			  `vpn_geschlecht` int(1) NOT NULL,
			  `vpn_gebdat` int(1) NOT NULL,
			  `vpn_fach` int(1) NOT NULL,
			  `vpn_semester` int(1) NOT NULL,
			  `vpn_adresse` int(1) NOT NULL,
			  `vpn_tele1` int(1) NOT NULL,
			  `vpn_tele2` int(1) NOT NULL,
			  `vpn_email` int(1) NOT NULL,
			  `vpn_ifreward` int(1) NOT NULL,
			  `vpn_ifbereits` int(1) NOT NULL,
			  `vpn_ifbenach` int(1) NOT NULL,
			  `visible` int(1) NOT NULL,
			  `max_vp` smallint(5) unsigned NOT NULL,
			  `terminvergabemodus` enum('automatisch','manuell') NOT NULL DEFAULT 'manuell',
			  `show_in_list` enum('true','false') NOT NULL DEFAULT 'true',
			  `session_duration` int(10) unsigned NOT NULL DEFAULT '0',
			  `max_simultaneous_sessions` smallint(5) unsigned NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE c_session (
			  `id` int(4) NOT NULL AUTO_INCREMENT,
			  `exp` int(3) NOT NULL,
			  `tag` date NOT NULL,
			  `session_s` time NOT NULL,
			  `session_e` time NOT NULL,
			  `maxtn` int(3) NOT NULL,
			  `virtualtn` int(3) NOT NULL,
			  `remind` int(1) NOT NULL,
			  `lab_id` smallint(5) unsigned NOT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `exp_tag_start_end_lab` (`exp`,`tag`,`session_s`,`session_e`,`lab_id`),
			  KEY `lab_id` (`lab_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE c_vpn (
			  `id` int(4) NOT NULL AUTO_INCREMENT,
			  `exp` int(3) NOT NULL,
			  `vorname` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `nachname` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `geschlecht` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `gebdat` date NOT NULL,
			  `fach` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `semester` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `anschrift` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `telefon1` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `telefon2` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `geldvps` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `andere` varchar(4) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `weitere` varchar(4) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `termin` int(4) NOT NULL,
			  `anruf` int(1) NOT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `exp_email` (`exp`,`email`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE c_lab (
			  `id` int(4) NOT NULL AUTO_INCREMENT,
			  `label` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `address` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `room_number` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
			  `capacity` int(3) NOT NULL,
			  `active` enum('true','false') NOT NULL DEFAULT 'false',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE c_exp2lab (
			  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
			  `exp_id` smallint(5) unsigned NOT NULL,
			  `lab_id` smallint(5) unsigned NOT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `exp_id_lab_id` (`exp_id`,`lab_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;
