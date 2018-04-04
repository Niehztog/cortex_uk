<?php
require_once __DIR__ . '/../include/class/DatabaseFactory.class.php';
$dbf = new DatabaseFactory();
$mysqli = $dbf->get();

$method = isset($_GET['method']) ? $_GET['method'] : null;

if('list' === $method) {
	$sql = sprintf(
		'SELECT id, exp_name AS name FROM %1$s'
		, TABELLE_EXPERIMENTE
	);
}
elseif('single' === $method) {
	$id = isset($_GET['id']) ? $_GET['id'] : null;
	$sql = sprintf(
		'SELECT		vl_name,
					vl_tele,
					vl_email,
					exp_name,
					exp_ort,
					exp_vps,
					exp_vpsnum,
					exp_geld,
					exp_geldnum,
					exp_zusatz,
					exp_mail,
					IF("0000-00-00"=exp_start,"",DATE_FORMAT(exp_start, "%%d.%%m.%%Y")) AS exp_start,
					IF("0000-00-00"=exp_end,"",DATE_FORMAT(exp_end, "%%d.%%m.%%Y")) AS exp_end,
					vpn_name,
					vpn_geschlecht,
					vpn_gebdat,
					vpn_fach,
					vpn_semester,
					vpn_adresse,
					vpn_tele1,
					vpn_tele2,
					vpn_email,
					vpn_ifreward,
					vpn_ifbereits,
					vpn_ifbenach,
					visible,
					max_vp,
					terminvergabemodus,
					show_in_list,
					session_duration,
					max_simultaneous_sessions
		 FROM		%1$s
		 WHERE		id = %2$d'
		, TABELLE_EXPERIMENTE
		, $id
	);
}
else {
	exit('Falscher Scriptaufruf');
}

$result = $mysqli->query($sql);

$return = array();
while($data = $result->fetch_assoc()) {
	$return[] = $data;
}

header('Content-type: application/json');
echo json_encode($return);