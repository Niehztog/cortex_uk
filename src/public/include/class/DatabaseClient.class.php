<?php
require_once __DIR__ . '/DatabaseFactory.class.php';

/**
 * Class DatabaseClient
 */
class DatabaseClient {

    /**
     * @return mysqli
     */
	protected function getDatabase() {
		$dbf = new DatabaseFactory();
		return $dbf->get();
	}
	
}