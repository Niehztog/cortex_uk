<?php
require_once __DIR__ . '/../include/class/user/AccessControl.class.php';
require_once __DIR__ . '/../include/class/service/SessionService.class.php';

$auth = new \AccessControl();
if (!$auth->mayAccessExpControl() || !isset($_REQUEST['action'])) {
    header('Content-type: application/json');
    echo json_encode(false);
    exit;
}

$result = 'success';
$action = $_REQUEST['action'];
$sessionService = new \SessionService();

try {
    switch($action) {
        case 'calendar':
            $result = $sessionService->getDataForCalendar((int)$_GET['id'], $_GET['purpose']);
            break;
        case 'list':
            $result = $sessionService->getDataForTable((int)$_REQUEST['expid']);
            break;
        case 'delete':
            $sessionService->delete((int)$_POST['id']);
            break;
        case 'change':
            $sessionService->change(
                (int)$_POST['expid'],
                (int)$_POST['change_id'],
                $_POST['change_termin'],
                $_POST['change_session_s'],
                $_POST['change_session_e'],
                $_POST['change_maxtn']
            );
            break;
        case 'add':
            $sessionService->add(
                (int)$_POST['expid'],
                $_POST['hinzu_termin'],
                $_POST['hinzu_session_s'],
                $_POST['hinzu_session_e'],
                $_POST['hinzu_maxtn']
            );
            break;

        default:
            throw new \InvalidArgumentException(sprintf('unknown value for "action": %1$s', var_export($action, true)));
    }
} catch (\Exception $e) {
    $result = $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($result);
