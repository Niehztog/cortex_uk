<?php
require_once 'include/class/user/AccessControl.class.php';
$auth = new AccessControl();

if(isset($_GET['menu']) && 'lab' === $_GET['menu']) {
    if(!$auth->mayAccessLabControl()) {
        header('HTTP/1.1 403 Forbidden');
        exit;
    }
	//Labore verwalten
	require_once 'backend/lab_index_frame.php';
}
elseif(isset($_GET['expid'])) {
    if(!$auth->mayAccessExpControl()) {
        header('HTTP/1.1 403 Forbidden');
        exit;
    }
	//Infos für Experiment Anzeigen
	//Änderungen an Experiment ausführen
	require_once 'backend/ad_exp_info.php';
}
else {
    if(!$auth->mayAccessExpControl()) {
        header('HTTP/1.1 403 Forbidden');
        exit;
    }
	//neues Experiment eintragen
	require_once 'backend/ad_exp_create.php';
}

?>
