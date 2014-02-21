<?php

if(isset($_GET['menu']) && 'lab' === $_GET['menu']) {
	//Labore verwalten
	require_once 'backend/lab_index_frame.php';
}
elseif(isset($_GET['expid'])) {
	//Infos für Experiment Anzeigen
	//Änderungen an Experiment ausführen
	require_once 'backend/ad_exp_info.php';
}
else {
	//neues Experiment eintragen
	require_once 'backend/ad_exp_create.php';
}

?>
