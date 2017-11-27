<?php
require_once __DIR__ . '/../../config.php';

/**
 * Class UserAuth
 */
class UserAuth {

    /**
     * @var
     */
    private $remoteUser;

    /**
     * @var array
     */
    private static $adminSites = array( 'admin.php', 'vpview.php' );

    /**
     * @var string
     */
    const REMOTE_USER_LIMNITED_ACCESS = REMOTE_USER_LIMNITED_ACCESS;

    /**
     * @var string
     */
    const ACCESS_LEVEL_GUEST = 1;

    /**
     * @var string
     */
    const ACCESS_LEVEL_EDITOR = 2;

    /**
     * @var string
     */
    const ACCESS_LEVEL_ADMIN = 3;

    /**
     * UserAuth constructor.
     */
    public function __construct() {
        if(isset($_SERVER['REMOTE_USER'])) {
            $this->setRemoteUser($_SERVER['REMOTE_USER']);
        }
    }

    /**
     * @return bool
     */
    public function isGuest() {
        return self::ACCESS_LEVEL_GUEST === $this->getAccessLevel();
    }

    /**
     * @return bool
     */
    public function isEditor() {
        return self::ACCESS_LEVEL_EDITOR === $this->getAccessLevel();
    }

    /**
     * @return bool
     */
    public function isAdmin() {
        return self::ACCESS_LEVEL_ADMIN === $this->getAccessLevel();
    }

    /**
     * @return string
     */
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

    /**
     * @param $user
     */
    private function setRemoteUser($user) {
        $this->remoteUser = $user;
    }

    /**
     * @return mixed
     */
    private function getRemoteUser() {
        return $this->remoteUser;
    }

}