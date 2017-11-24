<?php
require_once __DIR__ . '/UserAuth.class.php';

class AccessControl {

    private $userAuth;

    public function __construct() {
        $this->userAuth = new UserAuth();
    }

    public function isGuest() {
        return $this->userAuth->isGuest();
    }

    public function mayAccessExpControl() {
        return $this->userAuth->isEditor() || $this->userAuth->isAdmin();
    }

    public function mayAccessVpControl() {
        return $this->userAuth->isAdmin();
    }

    public function mayAccessLabControl() {
        return $this->userAuth->isEditor() || $this->userAuth->isAdmin();
    }

    public function mayEditExpLab() {
        return $this->userAuth->isAdmin();
    }

    public function mayEditExpTimeFrame() {
        return $this->userAuth->isAdmin();
    }

    public function mayEditLabInfo() {
        return $this->userAuth->isAdmin();
    }

}