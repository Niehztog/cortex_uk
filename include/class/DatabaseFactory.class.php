<?php
require_once __DIR__ . '/../config.php';

class DatabaseFactory {
	
	private static $mysqli;
	
	public function get() {
		
		if( null === self::$mysqli ) {
			self::$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
			
			self::$mysqli->query(
				"SET	character_set_results = 'utf8',
						character_set_client = 'utf8',
						character_set_connection = 'utf8',
						character_set_database = 'utf8',
						character_set_server = 'utf8'"
			);
			
			if (self::$mysqli->connect_errno) {
				throw new RuntimeException( sprintf( "Connect failed: %s\n", self::$mysqli->connect_error));
			}
		}
		return self::$mysqli;
		
	}
	
}