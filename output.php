<?php

require_once('common.php');

main();

function main() {
	$db = return_db_connection();
	$date = '2010-04-26';
	
	$total = return_total($db, $date);
	$personal = return_peronal_info($db, $date);
	
	// Set header content type
	header("Content-type:text/javascript");
	
	echo $total;
	echo '
	';
	echo $personal['firstname'];
	echo '
	';
	echo $personal['city'];
	echo '
	';
	echo $personal['state'];
	
}

function return_peronal_info($db, $date) {
	
	$query = sprintf("SELECT FirstName, City, State FROM exported WHERE GiftDate > '%s'", $date);
	$personal = $db->get_results($query, OBJECT);

	$result['firstname'] = 'var FirstName = new Array(';
	$result['city'] = 'var City = new Array(';
	$result['state'] = 'var State = new Array(';

	foreach($personal as $contact) {
		$result['firstname'] .= "'" . addslashes($contact->FirstName) . "'" . ',';
		$result['city'] .= "'" . addslashes(trim($contact->City)) . "'" . ',';
		$result['state'] .= "'" . $contact->State . "'" . ',';
	}
	
	$result['firstname'] = substr($result['firstname'], 0, strlen($result['firstname']) - 1) . ');
';
	$result['city'] = substr($result['city'], 0, strlen($result['city']) - 1) . ');
';
	$result['state'] = substr($result['state'], 0, strlen($result['state']) - 1) . ');
';
	
	return $result;

}

function return_total($db, $date) {
	$query = sprintf("SELECT sum(GiftAmount) FROM exported WHERE GiftDate > '%s'", $date);
	
	$total = $db->get_var($query);
	
	return 'var Total = ' . $total . ';';
}

?>