<?php

// error reporting level
error_reporting(E_ERROR | E_PARSE); 
set_time_limit(0);


// include the class file
require_once('lib/connect_api.class.php');
require_once('common.php');

// **************************************
// construct the new connect_api object
// **************************************
$wsdl_url = 'KinteraConnect.wsdl';
$login_info = array(
	'LoginName' => 'Kintera User Name',
	'Password' => 'Kintera User Password',
);

// ********************************************
// Define the query key to run the data export
// ********************************************
define('EXPORT_KEY', 'some password');

$client_options = array(
	'soap_defencoding' => 'UTF-8',
	'decode_utf8' => false,
);

// new connect api object
$api = new connect_api($wsdl_url, $login_info, $client_options);


if($_SERVER['REQUEST_METHOD'] == 'GET' && $_GET['key'] == EXPORT_KEY) {

  main($api, $global_num_pages, $global_page_size);

} else {
	die('No access here');
}

// run time function call
function main($api, $global_num_pages, $global_page_size) {

	$paymentDetails = _return_paymentDetails($api, $global_num_pages, $global_page_size);

	$ContactPayment = _return_ContactPayment($paymentDetails);
	$contact_profile = _return_ContactProfile($api, $ContactPayment);
	$source_code = _return_ContactProfile($api, $ContactPayment, 'SourceCode');
	$data = _final_clean_up($contact_profile, $paymentDetails, $cc_data, $cc_number, $source_code);

	$db = return_db_connection();
		
	insert($data, $db, 1);
	
}
/*===============================================================================================
 * FUNCTION LIST BELOW
 *===============================================================================================
 */

 /*
  * INSERT the data into the database
  */
 function insert($export_data, $db, $global_log_toggle) {
	
	sort($export_data);
	// export data into database
	for($i=0;$i<count($export_data);$i++) {
	
		if($db->query('SELECT paymentID FROM exported WHERE paymentID = ' . $export_data[$i]['CallerTID']) == TRUE) {
			// Do Nothing
			// export_log('paymentID: ' . $export_data[$i]['CallerTID'] . ' exists, export skipped', $global_log_toggle);
		}
		else {
			$query_string = sprintf("INSERT INTO exported (paymentID, CallerTID, Prefix, FirstName, MiddleName, LastName, Suffix, Address1, Address2, City, State, Zip, Plus4, Email, Phone_Home, Phone_Work, Employer, Occupation, GiftDate, GiftAmount, Card_Name_on, CardNo, Card_Exp_Month, Card_Exp_Year, More_in_XML, export_timestamp) VALUES (%d, %d, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', %f, '%s', '%s', '%s', '%s', '%s', %d)", $export_data[$i]['CallerTID'], $export_data[$i]['CallerTID'], _es($export_data[$i]['Prefix']), _es($export_data[$i]['FirstName']), _es($export_data[$i]['MiddleName']), _es($export_data[$i]['LastName']), _es($export_data[$i]['Suffix']), _es($export_data[$i]['Address1']), _es($export_data[$i]['Address2']), _es($export_data[$i]['City']), $export_data[$i]['State'], $export_data[$i]['Zip'], $export_data[$i]['Plus4'], _es($export_data[$i]['Email']), $export_data[$i]['Phone_Home'], $export_data[$i]['Phne_Work'], _es($export_data[$i]['Employer']), _es($export_data[$i]['Occupation']), $export_data[$i]['GiftDate'], $export_data[$i]['GiftAmount'], $export_data[$i]['Card_Name_on'], $export_data[$i]['CardNo'], $export_data[$i]['Card_Exp_Month'], $export_data[$i]['Card_Exp_Year'], _es($export_data[$i]['More_in_XML']), time());
	
	// echo $query_string . '<br /><br />';
	
			 if($db->query($query_string)) {
				 export_log('paymentID: ' . $export_data[$i]['CallerTID'] . ' exported', $global_log_toggle);
			 }
			 else {
				 export_log('data set export failed', $global_log_toggle);
				 die('data export failed');
			 }		
		} // end of big else

	} // end of for	
	
	echo "program terminated";
} // end of the function
 

/*
 * Return in Ascending order the payment details (paymentid, contactid)
 * Advance the page marker after the data is fetched
 */
function _return_paymentDetails($api, $global_num_pages, $global_page_size) {

	// Getting a list of paged contact ID
	$marker = get_record_marker();
	// $marker = 1;
	
	$ix = 1;
	// PaymentDetails
	$data = array();

	for($ix = $marker; $ix < $marker + $global_num_pages; $ix++) {
		$PaymentDetails_query = array('QueryText' => "SELECT PaymentID, ContactID, PaymentType, DateReceived, TotalAmount FROM PaymentItem ORDER BY DateReceived ASC", 'PageSize' => $global_page_size,
		'PageNumber' => $ix,
		);
		
		$query_result = $api->query_method($PaymentDetails_query);
		$data[] = $query_result['Records']['Record'];
	}
	$ix -= 1;
	
	// Exit the program if no data is fetched
	if(empty($data)) {
		exit('No new data fetched');
	}
	
	// merge the contactID data
	$_data = array();
	
	for($i = 0; $i < count($data); $i++) {
		foreach($data[$i] as $paged_data_array) {
				$_data[] = $paged_data_array;
			}
	}
	if(count($_data) == ($global_page_size * $global_num_pages)) {
		$ix += 1;	
	}
	
	// to determine if the marker should be advanced
	// if the record count is the same as global_num_pages * global-page_size
	_set_pageNumber($global_page_size, $global_num_pages, count($_data), $ix);
	
	return $_data;
} // end of function

/*
 * Create an array containing paymentID + ContactID
 */
function _return_ContactPayment($paymentDetails) {
	
	$combined_array = array();
	for($i = 0; $i < count($paymentDetails); $i++) {
		$combined_array[$paymentDetails[$i]['PaymentID']] = $paymentDetails[$i]['ContactID'];
	}
	
	return $combined_array;

} // end of function

/*
 * Fetch the contact profile of the payment
 * Can also be used to fetch custom profile
 */
function _return_ContactProfile($api, $combined_array = array(), $return_type = 'ContactProfile') {

	$data = array();
	
	foreach($combined_array as $PaymentID => $ContactID) {
		
		switch($return_type) {
			case 'ContactProfile':
				$contact_id = array('ContactID' => $ContactID);
				$data[] = $api->retrieve_method('ContactProfileIdentifier', $contact_id);			
			break;
			
			case 'CC_data':
				$_CustomID = array('ContactID' => $ContactID, 'FieldID' => '4967129');
				$data[] = $api->retrieve_method('ContactCustomProfileFieldIdentifier', $_CustomID);
			break;
			
			case 'CC_number':
				$_CustomID = array('ContactID' => $ContactID, 'FieldID' => '4971244');
				$data[] = $api->retrieve_method('ContactCustomProfileFieldIdentifier', $_CustomID);
			break;
			
			case 'SourceCode':
				$_CustomID = array('ContactID' => $ContactID, 'FieldID' => '4998121');
				$data[] = $api->retrieve_method('ContactCustomProfileFieldIdentifier', $_CustomID);
			break;
			
			default:
				$contact_id = array('ContactID' => $ContactID);
				$data[] = $api->retrieve_method('ContactProfileIdentifier', $contact_id);
			
		}
	
	}// end of foreach
	
	return $data;
	
}

/*
 * Clean up the data and ready it to be exported
 */
function _final_clean_up($ContactProfileData, $payment_details, $CreditCardData, $CreditCardNumber, $SourceCode) {
	for($i=0; $i<count($ContactProfileData); $i++) {
		foreach($ContactProfileData[$i] as $field => $value) {
		
			unset($ContactProfileData[$i]['HeadquarterFlag']);
			unset($ContactProfileData[$i]['Sic']);
			unset($ContactProfileData[$i]['NumberOfEmployees']);
			unset($ContactProfileData[$i]['OptInDirectoryFlag']);
			unset($ContactProfileData[$i]['OrganizationType']);
			unset($ContactProfileData[$i]['IsIndividualFlag']);
			unset($ContactProfileData[$i]['Gender']);
			unset($ContactProfileData[$i]['EmailFormat']);
			unset($ContactProfileData[$i]['DateUpdated']);
			unset($ContactProfileData[$i]['CreateDate']);
			unset($ContactProfileData[$i]['HeadquarterFlag']);
			unset($ContactProfileData[$i]['BillingCountry']);
			unset($ContactProfileData[$i]['BillingState']);
			unset($ContactProfileData[$i]['MarketingSourceID']);
			unset($ContactProfileData[$i]['CelebrityFlag']);
			unset($ContactProfileData[$i]['ActiveFlag']);
			unset($ContactProfileData[$i]['DoNotEmailFlag']);
			unset($ContactProfileData[$i]['DoNotPhoneFlag']);
			unset($ContactProfileData[$i]['DoNotDirectMailFlag']);
			unset($ContactProfileData[$i]['YearGraduated']);
			unset($ContactProfileData[$i]['DeceasedFlag']);
			unset($ContactProfileData[$i]['Age']);
			unset($ContactProfileData[$i]['FullName']);
			unset($ContactProfileData[$i]['Birthdate']);
			unset($ContactProfileData[$i]['BillingAddressLine1']);
			unset($ContactProfileData[$i]['BillingAddressLine2']);
			unset($ContactProfileData[$i]['BillingCity']);
			unset($ContactProfileData[$i]['BillingZip']);
			unset($ContactProfileData[$i]['BillingProvince']);
			unset($ContactProfileData[$i]['AlternateID']);
			unset($ContactProfileData[$i]['SpecialFlag']);	
			unset($ContactProfileData[$i]['PrimaryAddressCountry']);	
			
			// Clean up data from our end, zip code, state
			$ContactProfileData[$i]['State'] = $ContactProfileData[$i]['PrimaryAddressState'];
			
			$ContactProfileData[$i]['Zip'] = return_zip($ContactProfileData[$i]['PrimaryAddressZip']);
			
			$ContactProfileData[$i]['Plus4'] = return_plus4($ContactProfileData[$i]['PrimaryAddressZip']);	
			
			// paymentID
			$ContactProfileData[$i]['PaymentID'] = $payment_details[$i]['PaymentID'];
			
			// paymentType
			$ContactProfileData[$i]['PaymentType'] = $payment_details[$i]['PaymentType'];
			
			// cleanup the payment date
			$ContactProfileData[$i]['PaymentDate'] = split_date($payment_details[$i]['DateReceived']);
			
			// Adding the payment amount and payment date the our dataset
			$ContactProfileData[$i]['TotalAmount'] = $payment_details[$i]['TotalAmount'];
			
			// $ContactProfileData[$i]['PaymentType'] = return_matched_cc_type($CreditCardData[$i]['Value']);
			$ContactProfileData[$i]['PaymentType'] = 'VM';
			
			// $ContactProfileData[$i]['CC_Number'] = $CreditCardNumber[$i]['Value'];
			$ContactProfileData[$i]['CC_Number'] = '************0000';
			
			// Chang is here: Adding source code
			$ContactProfileData[$i]['SourceCode'] = $SourceCode[$i]['Value'];

			} // end of foreach
			
	} // end of for
	
	return _return_export_data($ContactProfileData);
}

/*
 * Final Cleanup that will allow this data to be called remotely
 */
 function _return_export_data($data = array()) {
 	
 	// this is the data set that we are preparing
 	$export_data = array();
 	
 	for($i=0;$i<count($data);$i++) {
 	
		if($data[$i] != '') {
				$export_data[$i]['token'] = "E65A214317DD1C51E91BBFC104C77864";
				$export_data[$i]['CallerCode'] = 'nrcc_eMotive'; 			
				$export_data[$i]['CallerTID'] = $data[$i]['PaymentID'];
				$export_data[$i]['Prefix'] = $data[$i]['Title'];
				$export_data[$i]['FirstName'] = $data[$i]['FirstName'];
				$export_data[$i]['MiddleName'] = $data[$i]['MiddleName'];
				$export_data[$i]['LastName'] = $data[$i]['LastName'];
				$export_data[$i]['Suffix'] = $data[$i]['Suffix'];
				$export_data[$i]['Address1'] = $data[$i]['PrimaryAddressLine1'];
				$export_data[$i]['Address2'] = $data[$i]['PrimaryAddressLine2'];
				$export_data[$i]['City'] = $data[$i]['PrimaryAddressCity'];
				$export_data[$i]['State'] = return_state($data[$i]['PrimaryAddressState']);
				$export_data[$i]['Zip'] = rtrim($data[$i]['PrimaryAddressZip']);
				$export_data[$i]['Plus4'] = rtrim($data[$i]['Plus4']);
				$export_data[$i]['Email'] = $data[$i]['Email'];
				$export_data[$i]['Phone_Home'] = $data[$i]['Phone'];
				$export_data[$i]['Phone_Work'] = $data[$i]['BusinessPhone'];
				$export_data[$i]['Employer'] = $data[$i]['Company'];
				$export_data[$i]['Occupation'] = $data[$i]['JobTitle'];
				$export_data[$i]['GiftDate'] = $data[$i]['PaymentDate'];
				$export_data[$i]['GiftAmount'] = $data[$i]['TotalAmount'];
				$export_data[$i]['Card_Name_on'] = 'N/A';
				$export_data[$i]['CardNo'] = $data[$i]['CC_Number'];
				$export_data[$i]['Card_Exp_Month'] = '';
				$export_data[$i]['Card_Exp_Year'] = '';
				$export_data[$i]['More_in_XML'] = "<more><SOURCE_CODE>" . (($data[$i]['SourceCode'] == '') ? 'E10B4S' :$data[$i]['SourceCode']) . "</SOURCE_CODE><SOURCE_DESC>JB4S online donations</SOURCE_DESC><PAYTYPE>" . $data[$i]['PaymentType'] . "</PAYTYPE>" . "<APVCODE>" . $data[$i]['PaymentID'] . "</APVCODE></more>";
 		}
 	}
 	return $export_data; 
 }


/*
 * Set the page number marker according to our rules
 */
function _set_pageNumber($global_page_size, $global_num_pages, $paymentDetails_count, $ix) {
	// Total number of records we are looking at against how many we have actually fetched
	// if the difference is less than 1 page, we just record the ix - 1 page number, if 2 page, we
	// record ix -2, etc etc.
	// for example: we have 60 records, we are looking at 2 * 100 at once, the difference is 
	// 140. 140/100 = 1.4, it is greater than 1 but less than 2 so we set marker at ix -2
	
	//var_dump($global_page_size);
	//var_dump($global_num_pages);
	//var_dump($paymentDetails_count);
	
	$record_difference_mutiplier = (($global_page_size * $global_num_pages) - $paymentDetails_count) / $global_page_size;
	
	// echo '<p>record difference: ' . $record_difference_mutiplier . '</p>';
	
	/***************************
	 * Setting the page marker
	 **************************/
		
	// if not gap in the recording
	if($record_difference_mutiplier == 0) {
		set_record_marker($ix);
		echo 'normal page marker';
	}
	else {
	// use big number >_<
		for($iy = 1; $iy < 10000; $iy++) {
			if($record_difference_mutiplier < $iy) {
				set_record_marker($ix - $iy);
				break;
			}
		}
	}
} // end of function

// get the date format into mm-dd-yyyy
function split_date($date_string) {
	return substr($date_string, 0, 10);
}

// return the first part of the zip code
function return_zip($zip_code = NULL) {

	// case 1: zip code is in the format of xxxxx-xxxx
	// case 2: zip code is in the format of xxxxxxxxx
	if(strstr($zip_code, '-') != false || strlen($zip_code) > 5) {
		return substr($zip_code, 0, 5);
	}
	else {
		return $zip_code;
	}

}

// return the second part of the zip code if there's one
function return_plus4($zip_code = NULL) {
	if(strstr($zip_code, '-') != false) {
		return substr($zip_code, 6);
	}
	elseif(strstr($zip_code, '-') == false || strlen($zip_code) > 5) {
		return substr($zip_code, 5);
	}
}

// match the payment type
function return_matched_cc_type($cc_type) {
	$matched_type = '';
	
	switch($cc_type) {
	
	case 'Visa':
	case 'MasterCard':
	$matched_type = 'VM';
	break;
	
	case 'American Express':
	$matched_type = 'AX';
	break;
	
	case 'Discover':
	$matched_type = 'DS';
	break;
	
	default:
	$matched_type = 'Unknown Payment Type';
	break;
	}
	
	return $matched_type;
}

// return state abbrivation      
function return_state($full_state = 'Unknown') {
	
	$abbriv = '';
	
	switch($full_state) {

		case 'Alabama':
		$abbriv = 'AL';
		break;
		
		case 'Alaska':	
		$abbriv = 'AK';
		break;
		
		case 'Arizona': 	
		$abbriv = 'AZ';
		break;
		
		case 'Arkansas':
		$abbriv = 'AR';
		break;
		
		case 'California':
		$abbriv = 'CA';
		break;
		
		case 'Colorado':
		$abbriv = 'CO';
		break;
		
		case 'Connecticut':
		$abbriv = 'CT';
		break;
		
		case 'Delaware':
		$abbriv = 'DE';
		break;
		
		case 'Florida': 	
		$abbriv = 'FL';
		break;
		
		case 'Georgia': 	
		$abbriv = 'GA';
		break;
		
		case 'Hawaii': 	
		$abbriv = 'HI';
		break;
		
		case 'Idaho': 	
		$abbriv = 'ID';
		break;
		
		case 'Illinois': 	
		$abbriv = 'IL';
		break;
		
		case 'Indiana': 	
		$abbriv = 'IN';
		break;
		
		case 'Iowa': 	
		$abbriv = 'IA';
		break;
		
		case 'Kansas': 	
		$abbriv = 'KS';
		break;
		
		case 'Kentucky': 	
		$abbriv = 'KY';
		break;
		
		case 'Louisiana': 	
		$abbriv = 'LA';
		break;
		
		case 'Indiana': 	
		$abbriv = 'IN';
		break;
		
		case 'Maine': 				
		$abbriv = 'ME';
		break;
		
		case 'Maryland': 	
		$abbriv = 'MD';
		break;
		
		case 'Massachusetts': 	
		$abbriv = 'MA';
		break;
		
		case 'Michigan': 	
		$abbriv = 'MI';
		break;		
		
		case 'Minnesota':
		$abbriv = 'MN';
		break;
		
		case 'Mississippi':	
		$abbriv = 'MS';
		break;
		
		case 'Missouri': 	
		$abbriv = 'MO';
		break;
		
		case 'Montana':
		$abbriv = 'MT';
		break;
		
		case 'Nebraska':
		$abbriv = 'NE';
		break;
		
		case 'Nevada':
		$abbriv = 'NV';
		break;
		
		case 'New Hampshire':
		$abbriv = 'NH';
		break;
		
		case 'New Jersey':
		case 'NewJersey':
		$abbriv = 'NJ';
		break;
		
		case 'New Mexico': 	
		$abbriv = 'NM';
		break;
		
		case 'New York': 	
		$abbriv = 'NY';
		break;

		case 'North Carolina':
		$abbriv = 'NC';
		break;
		
		case 'North Dakota':	
		$abbriv = 'ND';
		break;
		
		case 'Ohio': 	
		$abbriv = 'OH';
		break;
		
		case 'Oklahoma':
		$abbriv = 'OK';
		break;
		
		case 'Oregon':
		$abbriv = 'OR';
		break;
		
		case 'Pennsylvania':
		$abbriv = 'PA';
		break;
		
		case 'Rhode Island':
		$abbriv = 'RI';
		break;
		
		case 'South Carolina':
		$abbriv = 'SD';
		break;
		
		case 'Tennessee': 	
		$abbriv = 'TN';
		break;
		
		case 'Texas': 	
		$abbriv = 'TX';
		break;
		
		case 'Utah': 	
		$abbriv = 'UT';
		break;
		
		case 'Vermont': 	
		$abbriv = 'VT';
		break;
		
		case 'Virginia': 	
		$abbriv = 'VA';
		break;
		
		case 'Washington': 	
		$abbriv = 'WA';
		break;
		
		case 'West Virginia': 	
		$abbriv = 'WV';
		break;
		
		case 'Wisconsin': 	
		$abbriv = 'WI';
		break;
		
		case 'Wyoming': 	
		$abbriv = 'WV';
		break;
		
		case 'DistrictofColumbia':
		$abbriv = 'DC';
		break;
		
		default: $abbriv = 'NA';
	}
	
	return $abbriv;
}

?>