<?php
require_once __DIR__ . '/../../config.php';

class UserAuth {

    private $remoteUser;

    private static $adminSites = array( 'admin.php', 'vpview.php' );

    const REMOTE_USER_LIMNITED_ACCESS = REMOTE_USER_LIMNITED_ACCESS;

    const ACCESS_LEVEL_GUEST = 1;

    const ACCESS_LEVEL_EDITOR = 2;

    const ACCESS_LEVEL_ADMIN = 3;

    public function __construct() {
        if(isset($_SERVER['REMOTE_USER'])) {
            $this->setRemoteUser($_SERVER['REMOTE_USER']);
        }
    }

    public function isGuest() {
        return self::ACCESS_LEVEL_GUEST === $this->getAccessLevel();
    }

    public function isEditor() {
        return self::ACCESS_LEVEL_EDITOR === $this->getAccessLevel();
    }

    public function isAdmin() {
        return self::ACCESS_LEVEL_ADMIN === $this->getAccessLevel();
    }

    public function getAccessLevel() {
        $user = $this->getRemoteUser();
        if(!empty($user) /*&& in_array(basename($_SERVER['PHP_SELF']), self::$adminSites)*/) {
            if(self::REMOTE_USER_LIMNITED_ACCESS === $user) {
                return self::ACCESS_LEVEL_EDITOR;
            }
            return self::ACCESS_LEVEL_ADMIN;
        }
        return self::ACCESS_LEVEL_GUEST;
    }

    private function setRemoteUser($user) {
        $this->remoteUser = $user;
    }

    private function getRemoteUser() {
        return $this->remoteUser;
    }

}