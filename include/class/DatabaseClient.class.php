<?php
require_once __DIR__ . '/DatabaseFactory.class.php';

class DatabaseClient {
	
	protected function getDatabase() {
		$dbf = new DatabaseFactory();
		return $dbf->get();
	}
	
}