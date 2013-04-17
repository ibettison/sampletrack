<?php
/*
Audit.class.php - PHP Class to audit actions within a database system, it consists of Action Types, Actions, Identification, timestamps and audit details
Created By: 		Ian Bettison
Date:				11-04-2013
Version:			1.0
Type: 				static
*/
class audit {
	static private $audit_action_types;
	static private $audit_actions;
	static private $audit_identification;
	static private $audit_timestamp;
	static private $audit_database_table;
	static private $aat_id;
	static private $aactions_id;
	static private $ai_id;
	static private $at_id;
	static private $aaction_id;
	
	function __construct($types, $actions, array $identification ) {
		/* 
		$types list of audit types
		SECURITY | DATA AMENDMENT | UPLOAD
		
		$actions is a list of actions within the $types
		SECURITY - Login, Logout, New User, Failed Login, Deleted User
		DATA AMENDMENT - New Record, Deleted Record, Amended Record
		UPLOAD - Filename Uploaded
		
		$identification (type: array) the details of the user performing the actions
		*/
		self::$audit_action_types		= $types;
		self::$audit_actions				= $actions;
		self::$audit_identification		= $identification;		
		self::$aat_id  						= self::getActionTypesId(self::$audit_action_types);
		self::$aactions_id 					= self::getActionsId(self::$audit_actions);

		//uses mysqli_datalayer.php to write the identification audit record
		//dl::$debug=true;
		dl::insert("audit_identification", self::prepare_identification(self::$audit_identification));
		self::$ai_id 							= dl::getId(); // returns the id of the newly added identification record
		dl::insert("audit_timestamp", array("at_timestamp"=>date("Y-m-d H:i:s", strtotime("now"))));
		self::$at_id 							= dl::getId();
		dl::insert("audit_action", self::prepare_action());
		self::$aaction_id 					= dl::getId();
	}
	
	public static function create_action( array $action_array, $recordId) {
		/*array passed in the format
			array("table"=>"table_name", "values"=>array("key"=>"value", "key1"=>"value1", "key2"=>"value2", ...))
			the $recordId parameter captures the id of the record being modified
		*/
		self::$audit_database_table = $action_array["table"];
		foreach($action_array["values"] as $keys=>$values) {
			dl::insert("audit_details", self::prepare_details($keys, $values, self::$audit_database_table, $recordId) );
			$detail_id = dl::getId();
			dl::insert("audit_details_actions", self::prepare_details_actions($detail_id));
		}
	}
	
	private static function prepare_details_actions($id) {
		return(array("audit_action_id"=>self::$aaction_id, "audit_details_id"=>$id));
	}
	
	private static function prepare_details($key, $value, $table, $rec) {
		return(array("ad_key"=>$key, "ad_value"=>$value, "ad_tables"=>$table, "ad_record_id"=>$rec));
	}
	
	private static function prepare_action() {
		return(array("audit_actions_id"=>self::$aactions_id, "audit_identification_id"=>self::$ai_id, "audit_timestamp_id"=>self::$at_id));
	}
	
	private static function prepare_identification($id) {
		return(array("ai_userid"=>$id[0], "ai_username"=>$id[1]));
	}
	
	private static function getActionTypesId($aat) {
		$retVal = dl::select("audit_action_types", "aat_list = '".$aat."'");
		return $retVal[0]["aat_id"];
	}
	
	private static function getActionsId($aa) {
		$retVal = dl::select("audit_actions", "aa_list = '".$aa."'");
		return $retVal[0]["aa_id"];
	}
	
}
	
?>