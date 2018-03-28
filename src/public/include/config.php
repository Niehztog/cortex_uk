<?php
// DB connection/login info
define('DB_HOST',		'database');	// HOST name/adress of the MySQL Database; can be
										// different from the host name where the PHP
										// files are stores
define('DB_USER',		'root');			// Username for the MySQL Database
define('DB_PASSWORD',	'test');			// Password for the MySQL Database

// database name
// ***REQUIRED***
define('DB_NAME',		'cortex');		// MySQL Database Name

// ***REQUIRED***
define( 'TABELLE_VERSUCHSPERSONEN'	, 'c_vpn'		);	// Table Names; change only if you change the name
define( 'TABELLE_EXPERIMENTE'		, 'c_experiment');	// manually in your Database
define( 'TABELLE_SITZUNGEN'		    , 'c_session'	);	//
define( 'TABELLE_LABORE'			, 'c_lab'		);	//
define( 'TABELLE_EXP_TO_LAB'		, 'c_exp2lab'	);	//
define( 'TABELLE_BLOCKED_EMAIL'		, 'c_blocked_email'	);	//

define('REMOTE_USER_LIMNITED_ACCESS','expra'); //username from htpasswd with access restrictions

define('ID_OBFUSCATION_SALT','xxx');
