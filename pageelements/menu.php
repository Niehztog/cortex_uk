<?php
require_once 'include/class/menu/Menu.class.php';

$currentLink = basename($_SERVER['PHP_SELF']) . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
$adminSites = array( 'admin.php', 'vpview.php' );
$adminMode = in_array(basename($_SERVER['PHP_SELF']), $adminSites);
$menu = new Menu($currentLink, $adminMode);

echo $menu;
   