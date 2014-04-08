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

// ***COPYRIGHT RELATED***
define('IMPRINT_CONTENTS_FULLNAME', '');
define('IMPRINT_CONTENTS_ADDITION_1', '');
define('IMPRINT_CONTENTS_ADDITION_2', '');
define('IMPRINT_CONTENTS_COUNTRY', '');
define('IMPRINT_CONTENTS_STREET', '');
define('IMPRINT_CONTENTS_ZIPCODE', '');
define('IMPRINT_CONTENTS_CITY', '');
define('IMPRINT_CONTENTS_PHONE', '');
define('IMPRINT_CONTENTS_FAX', '');
define('IMPRINT_CONTENTS_EMAIL', '');
define('IMPRINT_CONTENTS_LINK', '');

define('IMPRINT_TECHNICAL_FULLNAME', '');
define('IMPRINT_TECHNICAL_ADDITION_1', '');
define('IMPRINT_TECHNICAL_ADDITION_2', '');
define('IMPRINT_TECHNICAL_COUNTRY', '');
define('IMPRINT_TECHNICAL_STREET', '');
define('IMPRINT_TECHNICAL_ZIPCODE', '');
define('IMPRINT_TECHNICAL_CITY', '');
define('IMPRINT_TECHNICAL_PHONE', '');
define('IMPRINT_TECHNICAL_FAX', '');
define('IMPRINT_TECHNICAL_EMAIL', '');
define('IMPRINT_TECHNICAL_LINK', '');

define('ID_OBFUSCATION_SALT','xxx');
