<?php

if($_SERVER['REQUEST_METHOD'] == 'GET') {
	die('You do not have access to this page, this incident is reported');
}

include_once('lib/LIB_http.php');

$result = http_get('http://www.yoursite.com/kintera/export.php?key=your_key', '');

print_r($result);

?>