<?php

/**********************************
 * Sphere API Class: Version 0.1
 *********************************/


// We need include to NuSoap class here because this class depends on the NuSoap Class
require_once('nusoap.php');

class connect_api {

	// class members
	private $soap_client; // NuSoap client object
	private $wsdl_url; // String
	private $debug_mode = 0; //Debug mode, set to 1 to see the feedback from each methods
	
	
	// constructor
	public function __construct($wsdl_url, $login_info = array(), $client_options = array()) {
		
		// wsdl url
		$this->wsdl_url = $wsdl_url;
		
		// construct the soap client
		$this->soap_client = new soapclientnusoap($this->wsdl_url, true);
		
		// for some client options such as soap defencoding = 'UTF8'
		// or decode_utf8 = false
		foreach($client_options as $key => $value) {
			$this->soap_client->$key = $value;
		}
		
		
		$this->login($login_info);
		
	}
	
	/*******************************
	 * The generic methods for 
	 * GET and SET methods
	 ******************************/
	public function __set($varible, $value) {
		$this->$varible = $value;
	}
	
	public function __get($varible) {
		return $this->$varible;
	}
	
	/************************************
	 * The login function is only called
	 * to login the user
	 ************************************/
	private function login($login_info) {
		
		$loginResult = $this->soap_client->call('Login', array('parameters' => array('request' => $login_info)));
		
		// Set session ID in the session header for subsequent Api calls
		$sessionID=$loginResult['LoginResult']['SessionID'];
		
		$sessionHeader =
		"<SessionHeader xmlns=\"http://schema.kintera.com/API/\"><SessionID>"
		.$sessionID.
		"</SessionID></SessionHeader>" ;
		$this->soap_client->setHeaders($sessionHeader);
	
	}
	
	// public methods, Mostly CRUD Operations
	
	/*****************************************
	 * The Connect API retrieve method
	 * $id_type: ex. ContactProfileIdentifier
	 * $id: ex. array('ContactID => 12345')
	 * $soap_client: will be deprecated later
	 ****************************************/
	public function retrieve_method($id_type, $id = array()) {

		$property = new soapval('propertySet', 'AllProperties', array(), false, 'tns');
		$id = new soapval('id', $id_type, $id, false, 'tns');
		$param = array('id' => $id, 'propertySet' => $property );
		$retrieveResult = $this->soap_client->call('Retrieve', array('parameters' => $param));
		$profile = $retrieveResult['RetrieveResult'];
		
		return $profile;
	}
		
	/*****************************************
	 * The Connect API query method
	 * $query_array: ex. array('QueryText' = 'SELECT * FROM ContactProfile', 
	 * 'PageSize' => 100, 'PageNumber => 1)
	 * $soap_client: will be deprecated later
	 ****************************************/		
	public function query_method($query_array = array()) {
	
		$queryRequest = new soapval('request', 'QueryRequest', $query_array, false, 'tns');
		$param = array('request' => $queryRequest);
		$queryResponse = $this->soap_client->call('Query', array('parameters' => $param));
		
		$queryResult = $queryResponse['QueryResult'];
		
		return $queryResult;
	
	}
	
	/*****************************************
	 * The Connect API create method
	 * $data_fields: ex. array('FirstName' => 'John', 'LastName => 'Smith');
	 * $entity: ex. ContactProfile
	 * $soap_client: will be deprecated later
	 ****************************************/	
	public function create_method($data_fields = array(), $entity) {
	
		$param = array('entity' => new soapval('entity', $entity, $data_fields, false, 'tns'));
		$createResult = $this->soap_client->call('Create', array('parameters' => $param));
		
		if($this->debug_mode == 0) {
			return $createResult;
		}
		// debug
		else {
			$contactID = $createResult['CreateResult']['ContactID'];

            $property = new soapval('propertySet', 'AllProperties', array(), false, 'tns');
            $id = new soapval('id', 'ContactProfileIdentifier', array('ContactID' => $contactID), false, 'tns');
            $param = array('id' => $id, 'propertySet' => $property );
            $retrieveResult = $client->call('Retrieve', array('parameters' => $param));
            
            return $retrieveResult;
		
		}
	
	// used later to debug the created result
	
// Print the ID of the created Contact
//             $contactID = $createResult['CreateResult']['ContactID'];
//             echo "ContactID is $contactID</br>";
//             
//             //Retrive the contact back, and print result
//             $property = new soapval('propertySet', 'AllProperties', array(), false, 'tns');
//             $id = new soapval('id', 'ContactProfileIdentifier', array('ContactID' => $contactID), false, 'tns');
//             $param = array('id' => $id, 'propertySet' => $property );
//             $retrieveResult = $client->call('Retrieve', array('parameters' => $param));
//             $profile = $retrieveResult['RetrieveResult'];
//             echo "
//             <h2>Profile:</h2><pre>
//             FirstName: {$profile['FirstName']}</br>
//             LastName: {$profile['LastName']}</br>
//             Email: {$profile['Email']}</br>
//             EmailFormat: {$profile['EmailFormat']}</br>
//             Gender: {$profile['Gender']}</br>
//             </pre>
//             ";
	}
	

	/*****************************************
	 * The Connect API update method
	 * $update_data: ex. array('FirstName' => 'John', 'LastName => 'Smith');
	 * $entity: ex. ContactProfile
	 * $soap_client: will be deprecated later
	 ****************************************/		
	public function update_method($update_data = array(), $entity) {
	            
            // Make service Update call
            $update_entity = new soapval('entity', $entity, $update_data, false, 'tns');
            $param = array('entity' => $update_entity);
            $result = $this->soap_client->call('Update', array('parameters' => $param));
            
            return $result;
    
	// used later for debugging
    
// Retrive the contact back, and print result to verify the update call
//             $property = new soapval('propertySet', 'AllProperties', array(), false, 'tns');
//             $id = new soapval('id', 'ContactProfileIdentifier', array('ContactID' => $contactID), false, 'tns');
//             $param = array('id' => $id, 'propertySet' => $property );
//             $retrieveResult = $client->call('Retrieve', array('parameters' => $param));
//             $profile = $retrieveResult['RetrieveResult'];
//             echo "
//             <h2>Profile:</h2><pre>
//             FirstName: {$profile['FirstName']}</br>
//             LastName: {$profile['LastName']}</br>
//             Email: {$profile['Email']}</br>
//             EmailFormat: {$profile['EmailFormat']}</br>
//             Gender: {$profile['Gender']}</br>
//             </pre>
//             ";
		
	}
	
	/*****************************************
	 * The Connect API update method
	 * $entity_type: ex. 'ContactProfileIdentifier'
	 * $entity_id: ex. array('ContactID' => 12345678);
	 * $soap_client: will be deprecated later
	 ****************************************/		
	public function delete_method($entity_type, $entity_id = array()) {
		//Delete the Contact record
		$id = new soapval('id', $entity_type, $entity_id, false, 'tns');
		$param = array('id' => $id);
		$this->soap_client->call('Delete', array('parameters' => $param));	
		
		// Maybe have some feedback
	}
	
	// testing static function
	public static function hello() {
		echo "hello world";
	}

} // end of connect_api class

?>