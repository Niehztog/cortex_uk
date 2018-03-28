<?php
require_once __DIR__ . '/UserAuth.class.php';

/**
 * Class AccessControl
 */
class AccessControl {

    /**
     * @var UserAuth
     */
    private $userAuth;

    /**
     * AccessControl constructor.
     */
    public function __construct() {
        $this->userAuth = new UserAuth();
    }

    /**
     * @return bool
     */
    public function isGuest() {
        return $this->userAuth->isGuest();
    }

    /**
     * @return bool
     */
    public function mayAccessExpControl() {
        return $this->userAuth->isEditor() || $this->userAuth->isAdmin();
    }

    /**
     * @return bool
     */
    public function mayAccessVpControl() {
        return $this->userAuth->isAdmin();
    }

    /**
     * @return bool
     */
    public function mayAccessLabControl() {
        return $this->userAuth->isEditor() || $this->userAuth->isAdmin();
    }

    /**
     * @return bool
     */
    public function mayAccessSettings() {
        return $this->userAuth->isEditor() || $this->userAuth->isAdmin();
    }

    /**
     * @return bool
     */
    public function mayEditExpLab() {
        return $this->userAuth->isAdmin();
    }

    /**
     * @return bool
     */
    public function mayEditExpTimeFrame() {
        return $this->userAuth->isAdmin();
    }

    /**
     * @return bool
     */
    public function mayEditLabInfo() {
        return $this->userAuth->isAdmin();
    }

}