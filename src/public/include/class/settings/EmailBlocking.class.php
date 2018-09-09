<?php
require_once __DIR__ . '/../DatabaseFactory.class.php';

/**
 * Class EmailBlocking
 */
class EmailBlocking {

    /**
     * @var MySQLi
     */
    private $mysqli;

    /**
     * UserAuth constructor.
     */
    public function __construct() {
        $dbf = new DatabaseFactory();
        $this->mysqli = $dbf->get();
    }

    /**
     * vorhandene blockierungen auslesen
     *
     * @return array
     */
    public function getBlockList() {
        $stmt = $this->mysqli->prepare('SELECT id, email, created FROM ' . TABELLE_BLOCKED_EMAIL . ' ORDER BY id ASC');
        if(!$stmt->execute() || !($res = $stmt->get_result())) {
            throw new RuntimeException($this->mysqli->error);
        }
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Prüfen ob gegebene Email Adresse blockiert ist
     *
     * @return array
     * @throws \RuntimeException
     */
    public function isBlocked($email) {
        $stmt = $this->mysqli->prepare('SELECT 1 FROM ' . TABELLE_BLOCKED_EMAIL . ' WHERE email = ?');
        if(false === $stmt) {
            throw new RuntimeException($this->mysqli->error);
        }
        $stmt->bind_param('s', $email);
        if(!$stmt->execute() || !($res = $stmt->get_result())) {
            throw new RuntimeException($this->mysqli->error);
        }
        $row = $res->fetch_row();
        return $row[0] === 1;
    }

    public function addBlock($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(sprintf('Die Eingabe (%1$s) scheint keine valide Email Adresse zu enthalten', $email));
        }

        $stmt = $this->mysqli->prepare('INSERT IGNORE INTO ' . TABELLE_BLOCKED_EMAIL . ' (email) VALUES(?)');
        $stmt->bind_param('s', $email);
        $res = $stmt->execute();
        if(!$res) {
            $err = $this->mysqli->error;
            $stmt->close();
            throw new RuntimeException($err);
        }
        $stmt->close();
        return $res;
    }

    public function delBlock($id) {
        $stmt = $this->mysqli->prepare('DELETE FROM ' . TABELLE_BLOCKED_EMAIL . ' WHERE id = ?');
        $stmt->bind_param('i', $id);
        $res = $stmt->execute();
        if(!$res) {
            $err = $this->mysqli->error;
            $stmt->close();
            throw new RuntimeException($err);
        }
        $stmt->close();
        return $res;
    }

}