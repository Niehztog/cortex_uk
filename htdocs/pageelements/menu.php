<?php
require_once 'include/class/menu/Menu.class.php';

$currentLink = basename($_SERVER['PHP_SELF']) . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
$menu = new Menu($currentLink);

echo $menu;