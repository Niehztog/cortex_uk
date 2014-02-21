<?php
// DB connection/login info
define('DB_HOST',		'localhost');	// HOST name/adress of the MySQL Database; can be
										// different from the host name where the PHP
										// files are stores
define('DB_USER',		'');			// Username for the MySQL Database
define('DB_PASSWORD',	'');			// Password for the MySQL Database

// database name
// ***REQUIRED***
define('DB_NAME',		'cortex');		// MySQL Database Name

// ***REQUIRED***
define( 'TABELLE_VERSUCHSPERSONEN'	, 'c_vpn'		);	// Table Names; change only if you change the name
define( 'TABELLE_EXPERIMENTE'		, 'c_cortex'	);	// manually in your Database
define( 'TABELLE_SITZUNGEN'		, 'c_sessions'	);	//
define( 'TABELLE_LABORE'			, 'c_lab'		);	//
define( 'TABELLE_EXP_TO_LAB'		, 'c_exp2lab'	);	//

define( 'CSS_OLD_STYLE', true );