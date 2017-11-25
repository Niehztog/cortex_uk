<?php
// DB connection/login info
define('DB_HOST',		'localhost');	// HOST name/adress of the MySQL Database; can be
										// different from the host name where the PHP
										// files are stores
define('DB_USER',		'cortex2');			// Username for the MySQL Database
define('DB_PASSWORD',	'cortex2');			// Password for the MySQL Database

// database name
// ***REQUIRED***
define('DB_NAME',		'psychlab');		// MySQL Database Name

// ***REQUIRED***
define( 'TABELLE_VERSUCHSPERSONEN'	, 'c2_vpn'		);	// Table Names; change only if you change the name
define( 'TABELLE_EXPERIMENTE'		, 'c2_cortex'	);	// manually in your Database
define( 'TABELLE_SITZUNGEN'		, 'c2_sessions'	);	//
define( 'TABELLE_LABORE'			, 'c2_lab'		);	//
define( 'TABELLE_EXP_TO_LAB'		, 'c2_exp2lab'	);	//

define('REMOTE_USER_LIMNITED_ACCESS','expra'); //username from htpasswd with access restrictions

define('ID_OBFUSCATION_SALT','xxx');
