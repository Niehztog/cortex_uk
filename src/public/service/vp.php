<?php
require_once __DIR__ . '/../include/functions.php';
require_once __DIR__ . '/../include/class/DatabaseFactory.class.php';
require_once __DIR__ . '/../include/class/user/AccessControl.class.php';
$auth = new AccessControl();
if(!$auth->mayAccessExpControl()) {
    header('Content-type: application/json');
    echo json_encode(false);
    exit;
}

$dbf = new DatabaseFactory();
$mysqli = $dbf->get();

/* WENN VP GELÖSCHT WURDE(N) */
/*---------------------------*/
if(isset($_POST['vpid']) && (isset($_POST['delvp'])||(isset($_POST['action']) && 'delete' === $_POST['action']))) {
    $delArray = castToIntArray($_POST['vpid']);

    $delete = sprintf( '
		DELETE FROM %1$s WHERE id IN (%2$s)'
        , TABELLE_VERSUCHSPERSONEN
        , implode(',', $delArray)
    );

    $result = $mysqli->query($delete);
    if(!$result) {
        $result = $mysqli->error;
    }
}

header('Content-type: application/json');
echo json_encode($result);