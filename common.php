<?

/*************************************
 * Configuration and common functions
 * for the API Bridge import/export
 ************************************/

// include the database class to use later
require_once('lib/shared/ez_sql_core.php');
require_once('lib/mysql/ez_sql_mysql.php');


/****************************************
 * Control Variables for the cron update
 ***************************************/ 
// how many pages we will be looking at
$global_num_pages = 1;

// how many records we are going to look at on each page, maximum 100
$global_page_size = 25;

// toggle logging or not, 1 for yes, 0 for no
$global_log_toggle = 1;


/****************************************
 * Database Configurations
 * Please configure them to be the one you want
 ***************************************/ 
define('DB_USER', 'database user name');
define('DB_PASS', 'database password');
define('DB_NAME', 'database name');
define('DB_HOST', 'database host address');



// Turn off error_reporting because there are nusoap deprecate warnings
// also turn off time limit so the script doesn't timeout
// error_reporting(E_ERROR | E_PARSE); 
set_time_limit(0);

/**********************
 * Shared Functions
 *********************/
 
 // log in the database
 function export_log($log_message = '', $log_toggle) {
 	if($log_toggle == 1) {
 	
		$db = return_db_connection();
		
		$db->query(sprintf("INSERT INTO log (log_message) VALUES ('%s')", $log_message));
 	
 	} // end of toggle
 }
 // 
 // mysql50-71.wc1.dfw1.stabletransit.com
 // 174.143.132.253
 function return_db_connection() {
 	$db = new ezSQL_mysql(DB_USER,DB_PASS,DB_NAME,DB_HOST);
 	
 	return $db;
 }
 
 function _es($string) {
	
	return mysql_escape_string(stripslashes($string));
	
 }
 
function set_record_marker($marker = 1) {
	$db = return_db_connection();

	if(!$db->query(sprintf("UPDATE page_marker SET value = %d WHERE id = 1", $marker))) {
		die('Failed setting page marker');
	}
}
  
function get_record_marker() {
	$db = return_db_connection();

	return $db->get_var("SELECT value FROM page_marker WHERE id = 1");
}