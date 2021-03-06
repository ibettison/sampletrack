<?php 
session_start();
date_default_timezone_set("Europe/London");


/* index.php 
Creation Date:12/07/2011
updated by : Ian Bettison
Purpose : To manage the Sample Tracking database.*/

define( "LIBRARYPATH", "./library/" );
define( "LOCALIMAGEPATH", "/images/" );
define( "IMAGEPATH", "./library/images/" );
define( "CSSPATH", "./css/" );
define( "CLASSPATH", "./library/classes/" );
define( "JAVAPATH", "./library/js/");

require(LIBRARYPATH.'mysqli_datalayer.php');
require(CLASSPATH.'field.class.php');
require(CLASSPATH.'audit.class.php');
require(CLASSPATH.'permission.class.php');
require('Classes/'.						'PHPExcel.php');
include('connection.php');
require(CLASSPATH.'login.class.php');
require(CLASSPATH.'form.class.php');
require(CLASSPATH.'tab.class.php');
require(CLASSPATH.'validate.class.php');
require(CLASSPATH.'admin.class.php');
require(CLASSPATH.'search.class.php');
require(CLASSPATH.'questionnaire.class.php');
require(CLASSPATH.'addnote.class.php');
require(CLASSPATH.'list.deletion.class.php');
// required if email function is available.
require(CLASSPATH.'phpmailer/class.phpmailer.php');
include("functions.php");
include( "validation_rules.php" );

if($_POST["func"]== "login") {
	$email											= strtolower(addslashes($_POST["email_address"]));
	$id 												= dl::select("user", "user_email_address='".$email."'");
	$password									= $_POST["password"];
	$login 											= new login( "user_id", $id[0]["user_id"], "user", "security", "security_password", $password, $id[0]["confirmed"]);
	if( $login->check_password() ) {
		//the userid and password are ok need to get the permissions
		$permissions 							= new permission( "user_types", "user_types_name_id", $id[0]["user_id"], "user_id", "permission_id" );
		$settings 									= $permissions->get_permissions( "permission_user", "permissions", "permission_name", "permission_value" );
		$_SESSION["settings"] 				= $settings;
		if(!$login->check_confirmation()) {
			$_SESSION["notConfirmed"] 	= true;
			$_SESSION["user_email"] 		= $id[0]["user_email_address"];
		}else{
			$_SESSION["notConfirmed"]	= false;
			$_SESSION["loggedIn"] 		= "true";
			$_SESSION["userId"] 			= $id[0]["user_id"];
			$_SESSION["user_name"] 	= $id[0]["user_name"];
			$_SESSION["user_email"] 		= $id[0]["user_email_address"];
			$_SESSION["loggedInTime"] 	= date("d-m-Y H:i:s");
			//audit who has logged in
			$audit = new audit("SECURITY", "LOGIN", array($_SESSION["userId"], $_SESSION["user_name"]));
		}
	}else{
			$audit = new audit("SECURITY", "FAILED LOGIN", array(0, $email));
	}
}

if($_POST["func"] == "check_height"){
	$_SESSION["screen_height"] = $_POST["height"];
}

if($_POST["func"] == "save_info_details") {
	$fields = array("name", "description");
	$values = array($_POST["infoType"], $_POST["infoDesc"]);
	$writeLn = array_combine($fields, $values);
	if($_POST["option"] == 'new') {
		//this is a new record
		//write the spreadsheet row into the database.
		dl::insert($_POST["table"], $writeLn);
		$itemId = dl::getId();
		$audit = new audit("DATA MANAGEMENT", "RECORD ADDED", array($_SESSION["userId"], $_SESSION["user_name"]));
		audit::create_action(array("table"=>$_POST["table"], "values"=>$writeLn), $itemId);
		echo "Record added";
	}else{
		dl::update("information_types", $writeLn, "id = ".$_POST["infoId"]);
		$itemId = $_POST["infoId"];
		$audit = new audit("DATA MANAGEMENT", "RECORD UPDATED", array($_SESSION["userId"], $_SESSION["user_name"]));
		audit::create_action(array("table"=>$_POST["table"], "values"=>$writeLn), $itemId);
		echo "Record Updated";
	}
}

if($_POST["func"] == "show_info_details") {	
	$info = dl::select($_POST["table"], "name = '".$_POST["typeSelected"]."'");
	$retArr = array("infoID"=>$info[0]["id"], "infoType"=>$info[0]["name"], "infoDesc"=>$info[0]["description"]);
	echo json_encode($retArr);
}

if($_POST["func"] == "delete_listId") {
		$audit = new audit("DATA MANAGEMENT", "RECORD DELETED", array($_SESSION["userId"], $_SESSION["user_name"]));
		audit::create_action(array("table"=>$_POST["table"], "values"=>array("id"=>$_POST["id"], "name"=>$_POST["type"], "description"=>$_POST["desc"])), $_POST["id"]);
		dl::delete($_POST["table"], "id = ".$_POST["id"]); 
}

if($_POST["func"] == "compare_spreadsheet"){
	dl::$debug=true;
	$matchedColumns = $_POST["list"];
	$additionalItems = $_POST["additions"];
	//need to take the first position from each array as it has included the <UL> title.
	array_shift($matchedColumns);
	array_shift($additionalItems);

	$fileName = $_POST["filename"];
	$customer = $_POST["customer"];
	$rowToRead = $_POST["excel_row"];
	//loop through the list and additions to get the columns numbers of the data to store.
	foreach($matchedColumns as $match) {
		$matchedId[] = substr($match, 0, strpos($match, " "));
	}
	foreach($additionalItems as $adds) {
		$addId[] = substr($adds, 0, strpos($adds, " "));
	}
	//get customer Id
	$cust_ID = dl::select("customers", "c_name ='".addslashes($customer)."'" );
	//write samples_list record
	dl::insert("samples_list", array("customer_id"=>$cust_ID[0]["c_id"], "sl_status"=>"Outstanding"));
	$last_id = dl::getId();
	$audit = new audit("DATA MANAGEMENT", "RECORD ADDED", array($_SESSION["userId"], $_SESSION["user_name"]));
	audit::create_action(array("table"=>"samples_list", "values"=>array("customer_id"=>$cust_ID[0]["c_id"], "sl_status"=>"Outstanding")), $last_id);
	//prepare sample_list_items
	$sl_fields = array("samples_list_id", "sli_customer_identifier","sli_description", "sli_pathology_no", "sli_date_sample_stored", "sli_SNOMED_code", "sli_subject_gender", "sli_subject_age", "sli_disease_state", "sli_sample_stage", "sli_study_name", "sli_adult_paediatric");
	
	require_once 'Classes/PHPExcel/IOFactory.php';
	//load the spreadsheet
	$objPHPExcel = PHPExcel_IOFactory::load($fileName); 
	$objWorksheet = $objPHPExcel->getActiveSheet();
	$highestRow = $objWorksheet->getHighestRow(); 
	for($i=$rowToRead+1; $i<=$highestRow; $i++) {
		foreach($matchedId as $cell ) {
			if(!empty($cell)) {
				$rowValues[] = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($cell, $i)->getValue();
			}else{
				$rowValues[] ="";
			}
		}	
		//add the samples_list ID to the front of the array;
		array_unshift($rowValues, $last_id);
		$writeLn = array_combine($sl_fields, $rowValues);
		//write the spreadsheet row into the database.
		dl::insert("sample_list_items", $writeLn);
		$itemId = dl::getId();
		$audit = new audit("DATA MANAGEMENT", "RECORD ADDED", array($_SESSION["userId"], $_SESSION["user_name"]));
		audit::create_action(array("table"=>"sample_list_items", "values"=>$writeLn), $itemId);
		$rowValues=array();
		
		//now need to gather the additional field information and store it in the additional_information field.
		$listString = "";
		if(!empty($addId)) {
			foreach($addId as $cell ) {
				$colHeading = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($cell, $rowToRead);
				if(!empty($cell) or $cell ==0) {
					$listString .= $colHeading ." = '".$objPHPExcel->getActiveSheet()->getCellByColumnAndRow($cell, $i)->getValue()."' \n";
				}
			}	
			//update the additional information field in the list items table.
			dl::update("sample_list_items", array("sli_additional_information"=>$listString), "sli_id = ".$itemId);
			$audit = new audit("DATA MANAGEMENT", "RECORD UPDATED", array($_SESSION["userId"], $_SESSION["user_name"]));
			audit::create_action(array("table"=>"sample_list_items", "values"=>array("sli_additional_information"=>$listString)), $itemId);
		}
	}
	echo "Spreadsheet data added to database.";
}

if($_POST["func"] == "checkMoveContainer") {
	getBarcodeDetail($_POST["conBarcode"]);
}

if($_POST["func"] == "checkToContainer") {
	getBarcodeDetail($_POST["conBarcode"]);
}

function getBarcodeDetail($bc){
	$container = dl::select("containers", "c_container_barcode = \"".$bc."\"");
	echo $container[0]["c_container_name"];
}

if($_POST["func"] == "moveContainer") {
	$move_bc 	= $_POST["container1"];
	$into_bc 		= $_POST["container2"];
	//need to get container types
	$conTemp1_ID = dl::select("containers", "c_container_barcode = '".$move_bc."'");
	$conTemp2_ID = dl::select("containers", "c_container_barcode = '".$into_bc."'");
	$conType1_ID = dl::select("container_templates", "ct_id = ". $conTemp1_ID[0]["container_templates_id"]);
	$conType2_ID = dl::select("container_templates", "ct_id = ". $conTemp2_ID[0]["container_templates_id"]);
	$sql = "select * from containers as c join 
	container_templates as ct on (c.container_templates_id=ct.ct_id) join 
	container_types as cty on (ct.container_types_id=cty.ct_id) left outer join 
	allowed_containers as ac on (cty.ct_id=ac.types_id) 
	where ac.allowed_ids = ".$conType1_ID[0]["container_types_id"]." and ac.types_id = ".$conType2_ID[0]["container_types_id"];
	$allowed = dl::getQuery($sql);
	if(!empty($allowed)) {
		dl::update("containers", array("locations_id"=>$conTemp2_ID[0]["c_id"]), "c_id =".$conTemp1_ID[0]["c_id"]);
		$audit = new audit("DATA MANAGEMENT", "RECORD UPDATED", array($_SESSION["userId"], $_SESSION["user_name"]));
		audit::create_action(array("table"=>"container_types", "values"=>array("locations_id"=>$conTemp2_ID[0]["c_id"])), $conTemp1_ID[0]["c_id"]);
		echo "The container has been stored.";
	}else{
		echo "This container is not authorised to store the container type.";
	}
}

if($_POST["func"] == "show_barcode") {
	$barcode = dl::select("web_barcode");
	if(empty($barcode)) {
		echo "<H4>There is no stored barcode</H4>";
	}else{
		echo "<H4>SCANNED BARCODE</H4>";
		echo "<ul>";
			echo "<li class='field'>";
			echo "<input id='barcode' name='barcode' class='wide text input' type='text' value='".$barcode[0]["wb_barcode"]."' placeholder='Barcode'' />";
			echo "</li>";
		echo "</ul>";
	}
}

if($_POST["func"] == "check_barcode") {
	$barcode = dl::select("web_barcode");
	$now = strtotime("now");
	$then = strtotime($barcode[0]["wb_timestamp"]);
	$time = $now - $then;
	if(date('i', $time) == 5) {
		//dl::delete("web_barcode");
	}
	if(empty($barcode)) {
		$retArr = array("found"=>"false", "time"=>date('i',$time), "value"=>"");
	}else{
		$retArr = array("found"=>"true", "time"=>date('i',$time), "value"=>$barcode[0]["wb_barcode"]);
	}
	echo json_encode($retArr);
}


if( $_POST["func"] == "Add_Note") {	
	addnote::add_notes_to($_POST["table"]);
	call_user_func($_POST["resetFunc"]);
}

if( $_POST["func"] == "Manage_Notes") {	
	addnote::manage_notes($_POST["table"], $_POST["linkid"]);
	call_user_func($_POST["resetFunc"]);
}

if( $_POST["func"] == "new_container_type") {	
	$fields = array("ct_name", "ct_detail", "ct_manufacturer");
	$values = array($_POST["typeName"],$_POST["conDescription"],$_POST["conManufacturer"]);
	$combine = array_combine($fields, $values);
	if($_POST["option"]== "new"){
		dl::insert("container_types", $combine);
		$newId = dl::getId();
		$audit = new audit("DATA MANAGEMENT", "RECORD ADDED", array($_SESSION["userId"], $_SESSION["user_name"]));
		audit::create_action(array("table"=>"container_types", "values"=>$combine), $newId);
		echo "New container type has been created";
	}else{
		$type = dl::select("container_types", "ct_name = '".$_POST["typeName"]."'");
		dl::update("container_types", $combine, "ct_id = ".$type[0]["ct_id"]);
		$newId = $type[0]["ct_id"];
		$audit = new audit("DATA MANAGEMENT", "RECORD UPDATED", array($_SESSION["userId"], $_SESSION["user_name"]));
		audit::create_action(array("table"=>"container_types", "values"=>$combine), $newId);
		echo "Container Type has been amended";
		$containers = dl::select("allowed_containers", "types_id = ".$newId);
		foreach($containers as $container) {
			$audit = new audit("DATA MANAGEMENT", "RECORD DELETED", array($_SESSION["userId"], $_SESSION["user_name"]));
			audit::create_action(array("table"=>"allowed_containers", "values"=>array("allowed_ids"=>$container["allowed_ids"], "types_id"=>$container["types_id"])), $container["a_id"]);
		}
		dl::delete("allowed_containers", "types_id = ".$newId); //remove existing allowed containers as you do not know if a container has been deselected
		
	}
	if(!empty($_POST["allowedContainers"])) {
		foreach($_POST["allowedContainers"] as $container){
			$c_id = dl::select("container_types", "ct_name = '".$container."'");
			if(!empty($c_id)) {
				if($_POST["option"] == "new") {
					dl::insert("allowed_containers", array("allowed_ids"=>$c_id[0]["ct_id"], "types_id"=>$newId));
					$newContainerId = dl::getId();
					$audit = new audit("DATA MANAGEMENT", "RECORD ADDED", array($_SESSION["userId"], $_SESSION["user_name"]));
					audit::create_action(array("table"=>"allowed_containers", "values"=>array("allowed_ids"=>$c_id[0]["ct_id"], "types_id"=>$newId)), $newContainerId);
				}else{
					dl::insert("allowed_containers", array("allowed_ids"=>$c_id[0]["ct_id"], "types_id"=>$_SESSION["containerTypeID"]));
					$newContainerId = dl::getId();
					$audit = new audit("DATA MANAGEMENT", "RECORD ADDED", array($_SESSION["userId"], $_SESSION["user_name"]));
					audit::create_action(array("table"=>"allowed_containers", "values"=>array("allowed_ids"=>$c_id[0]["ct_id"], "types_id"=>$_SESSION["containerTypeID"])), $newContainerId);
				}
			}
		}
	}
}

if( $_POST["func"] == "del_container_type") {
	$type = dl::select("container_types", "ct_name = '".$_POST["typeName"]."'");
	$containers = dl::select("allowed_containers", "types_id = ".$type[0]["ct_id"]);
	foreach($containers as $container) {
		$audit = new audit("DATA MANAGEMENT", "RECORD DELETED", array($_SESSION["userId"], $_SESSION["user_name"]));
		audit::create_action(array("table"=>"allowed_containers", "values"=>array("allowed_ids"=>$container["allowed_ids"], "types_id"=>$container["types_id"])), $container["a_id"]);
	}
	dl::delete("allowed_containers", "types_id = ".$type[0]["ct_id"]);
	$audit = new audit("DATA MANAGEMENT", "RECORD DELETED", array($_SESSION["userId"], $_SESSION["user_name"]));
	audit::create_action(array("table"=>"container_types", "values"=>array("ct_name"=>$type[0]["ct_name"], "ct_detail"=>$type[0]["ct_detail"]), "ct_manufacturer"=>$type[0]["ct_manufacturer"]), $type[0]["ct_id"]);
	dl::delete("container_types", "ct_name = '".$_POST["typeName"]."'");
	echo "Record has been deleted";
}

if( $_POST["func"] == "new_container_template") {	
	$types = dl::select("container_types", "ct_name = '".$_POST["conType"]."'");
	$fields = array("container_types_id", "ct_no_rows", "ct_no_columns", "ct_row_type", "ct_column_type", "ct_template_name");
	$values = array($types[0]["ct_id"], $_POST["conRows"],$_POST["conCols"],$_POST["conRowType"], $_POST["conColType"], $_POST["tempName"]);
	$combine = array_combine($fields, $values);
	dl::insert("container_templates", $combine);
	$newId = dl::getId();
	$audit = new audit("DATA MANAGEMENT", "RECORD ADDED", array($_SESSION["userId"], $_SESSION["user_name"]));
	audit::create_action(array("table"=>"container_templates", "values"=>$combine), $newId);
	echo "New container template has been created";
}

if( $_POST["func"] == "new_container") {	
	//check if the container already exists
	$containers = dl::select("containers", "c_container_barcode ='".$_POST["conBarcode"]."'");
	if($_POST["option"] == "new") {
		if(empty($containers)) {
			$templates = dl::select("container_templates", "ct_template_name = '".$_POST["conTemplate"]."'");
			$fields = array("container_templates_id", "c_container_name", "c_container_barcode");
			$values = array($templates[0]["ct_id"],$_POST["conName"],$_POST["conBarcode"]);
			$combine = array_combine($fields, $values);
			dl::insert("containers", $combine);
			$newId = dl::getId();
			$audit = new audit("DATA MANAGEMENT", "RECORD ADDED", array($_SESSION["userId"], $_SESSION["user_name"]));
			audit::create_action(array("table"=>"containers", "values"=>$combine), $newId);
			echo json_encode(array("message"=>'New container has been created'));
		}else{
			echo json_encode(array("message"=>'This barcoded container already exists, not created'));
		}
	}else{ //this is an edit
		$templates = dl::select("container_templates", "ct_template_name = '".$_POST["conTemplate"]."'");
		$fields = array("container_templates_id", "c_container_name", "c_container_barcode");
		$values = array($templates[0]["ct_id"],$_POST["conName"],$_POST["conBarcode"]);
		$combine = array_combine($fields, $values);
		dl::update("containers", $combine, "c_id = ". $_SESSION["containerID"]);
		$audit = new audit("DATA MANAGEMENT", "RECORD UPDATED", array($_SESSION["userId"], $_SESSION["user_name"]));
		audit::create_action(array("table"=>"containers", "values"=>$combine), $_SESSION["containerID"]);
		$containers = dl::select("containers");
		foreach($containers as $c){
			$list[]= $c["c_container_name"]." - ".$c["c_container_barcode"];
		}
		echo json_encode(array("list"=>$list,"message"=>'Container was changed'));
	}
}

if( $_POST["func"] == "getContainerValues") {	
	$container = dl::select("containers", "c_container_name = '".$_POST["container"]."'");
	$barcode = $container[0]["c_container_barcode"];
	$templateId = dl::select("containers", "c_container_barcode = \"".$barcode."\"");
	$template_name = dl::select("container_templates", "ct_id = ". $templateId[0]["container_templates_id"]);
	$_SESSION["containerID"]=$templateId[0]["c_id"]; //this needs to be set as both the barcode and container description could be changed so is required to identify the record.
	echo json_encode(array("template_name"=>$template_name[0]["ct_template_name"], "barcode"=>$barcode, "container"=>$container[0]["c_container_name"]));
}

if( $_POST["func"] == "getContainerTypeValues") {	
	$typeId = dl::select("container_types", "ct_name = \"".$_POST["con_type"]."\"");
	$_SESSION["containerTypeID"]=$typeId[0]["ct_id"]; //this needs to be set as bothe the barcode and container description could be changed so is required to identify the record.
	$allowed = dl::select("allowed_containers", "types_id = ".$typeId[0]["ct_id"]);
	foreach($allowed as $allow) {
		$ct = dl::select("container_types", "ct_id = ".$allow["allowed_ids"]);
		$allowed_containers[]=$ct[0]["ct_name"];
	}
	echo json_encode(array("container_desc"=>$typeId[0]["ct_detail"], "manufacturer"=>$typeId[0]["ct_manufacturer"], "allowed_containers"=>$allowed_containers));
}

if( $_POST["func"] == "getContainerTempValues") {	
	$tempId = dl::select("container_templates", "ct_template_name = \"".$_POST["con_temp_name"]."\"");
	$typeId = dl::select("container_types", "ct_id = ".$tempId[0]["container_types_id"]);
	echo json_encode(array("container_type"=>$typeId[0]["ct_name"], "rows"=>$tempId[0]["ct_no_rows"], "columns"=>$tempId[0]["ct_no_columns"], "row_type"=>$tempId[0]["ct_row_type"], "column_type"=>$tempId[0]["ct_column_type"]));
}

if( $_POST["func"] == "getCustomerValues") {	
	$customerId = dl::select("customers", "c_name = \"".$_POST["customer"]."\"");
	$cust_id = $customerId[0]["c_id"];
	$business =$customerId[0]["c_type_of_business"];
	$registration = $customerId[0]["c_registration_no"];
	$contacts = dl::select("contact_details", "customers_id = ". $customerId[0]["c_id"]);
	if(!empty($contacts)) {
		foreach($contacts as $contact) {
			$type = dl::select("contact_types", "id = ".$contact["contact_type_id"]);
			$contact_array[]= $type[0]["name"].",".nl2br($contact["cd_detail"].",".$contact["cd_id"]);
		}
	}
	echo json_encode(array("customerId"=>$cust_id, "business"=>$business, "registration"=>$registration, "contacts"=>$contact_array));
}

if( $_POST["func"] == "new_registration") {	
	//check if the container already exists
	//sample_registrations
	//get customer ID
	$customer = dl::select("customers", "c_name = \"".$_POST["customer"]."\"");
	$fields = array("customer_id","sr_contact_date","sr_requirements","sr_expected_delivery","sr_status");
	$values = array($customer[0]["c_id"],date("Y-m-d", mktime(0,0,0, substr($_POST["contact_date"],4,2), substr($_POST["contact_date"],1,2), substr($_POST["contact_date"],7,4))), $_POST["required"], date("Y-m-d",  mktime(0,0,0, substr($_POST["delivery_date"],4,2), substr($_POST["delivery_date"],1,2), substr($_POST["delivery_date"],7,4))), "New");
	$combine = array_combine($fields, $values);
	dl::insert("sample_registrations", $combine);
	$regId = dl::getId();
	$audit = new audit("DATA MANAGEMENT", "RECORD ADDED", array($_SESSION["userId"], $_SESSION["user_name"]));
	audit::create_action(array("table"=>"sample_registrations", "values"=>$combine), $regId);
	//registration_information
	$sample_types = dl::select("sample_types", "st_type='".$_POST["sample_type"]."'");
	$container_types = dl::select("container_types", "ct_name = '".$_POST["sample_container"]."'");
	$fields = array("sample_registrations_id", "sample_types_id", "containers_id", "ri_no_samples","ri_boxes", "ri_box_size", 
	"ri_temperature", "ri_catalogued", "ri_sample_information");
	$values = array($regId, $sample_types[0]["st_id"], $container_types[0]["ct_id"], $_POST["sample_no"], $_POST["boxes"], $_POST["sample_boxsize"], 
	$_POST["sample_temperature"], $_POST["samples_catalogued"], $_POST["sample_info"]);
	$combine = array_combine($fields, $values);
	dl::insert("registration_information", $combine);
	$newId = dl::getId();
	$audit = new audit("DATA MANAGEMENT", "RECORD ADDED", array($_SESSION["userId"], $_SESSION["user_name"]));
	audit::create_action(array("table"=>"registration_information", "values"=>$combine), $newId);
	//registration_payments
	$fields = array("rp_payment_reference", "rp_payment_details", "sample_registrations_id");
	$values = array($_POST["contact_paymentRef"], $_POST["contact_paymentDetails"], $regId);
	$combine = array_combine($fields, $values);
	dl::insert("registration_payments", $combine);
	$newId = dl::getId();
	$audit = new audit("DATA MANAGEMENT", "RECORD ADDED", array($_SESSION["userId"], $_SESSION["user_name"]));
	audit::create_action(array("table"=>"registration_payments", "values"=>$combine), $newId);
	//biobank services
	$ids = $_POST["ids"];
	$services = $_POST["services"];
	$serviceCount = 1;
	foreach($ids as $id) {
		if($services[$id-1] == "true") {
			dl::insert("biobank_services", array("service_list_id"=>$id, "registration_information_id"=>$regId));
			$newId = dl::getId();
			$audit = new audit("DATA MANAGEMENT", "RECORD ADDED", array($_SESSION["userId"], $_SESSION["user_name"]));
			audit::create_action(array("table"=>"biobank_services", "values"=>array("service_list_id"=>$id, "registration_information_id"=>$regId)), $newId);
		}
	}
	echo "New registration created...";
}

if($_POST["func"] == "new_contact_details") {
	$contact_types = dl::select("contact_types", "name = \"".$_POST["conType"]."\"");
	//add new details
	dl::insert("contact_details", array("contact_type_id"=>$contact_types[0]["id"], "cd_detail"=>$_POST["conDetail"]));
	$newId = dl::getId();
	$audit = new audit("DATA MANAGEMENT", "RECORD ADDED", array($_SESSION["userId"], $_SESSION["user_name"]));
	audit::create_action(array("table"=>"contact_details", "values"=>array("contact_type_id"=>$contact_types[0]["id"], "cd_detail"=>$_POST["conDetail"])), $newId);
	if($_POST["option"] == "new"){
		$details = dl::select("contact_details", "customers_id=0", "cd_id ASC"); //if no customer id is set then the details haven't been properly saved yet
	}else{
		$details = dl::select("contact_details", "customers_id=0 or customers_id =".$_POST["customer_id"], "cd_id ASC");
	}
	if(!empty($details)) {
		foreach($details as $detail) {
			$types = dl::select("contact_types", "id=".$detail["contact_type_id"] );
			echo "<list-content><div id='content-container'><div id='content-header'>".$types[0]["name"]."</div><div id='content' style='width:15em;'>".nl2br($detail["cd_detail"])."</div><div id='content-del'><a href='#' id='button".$detail["cd_id"]."' border='0'><img src='images/DeleteRed.png' /></a></div></div></list-content>";
		}
		echo "<br />";
		//now setup the delete function for the buttons passed back by the above jQuery
		foreach($details as $d) {
			?>
			<script type="text/javascript">
					$("#button<?php echo $d["cd_id"]?>").click( function (){
						var func = "del_contact_details";
						$.post(
							"ajax.php",
							{ func: func,
								option: '<?php echo $_POST["option"] ?>',
								customer_id: <?php echo $_POST["customer_id"] ?>,
								conId: <?php echo $d["cd_id"]?>
							},
							function (data)
							{
								$('#showContactDetails').html(data);
								$("#con_detail").val("");
								$('#contact_type').val("");
						});
					});
			</script>
	<?php
		}
	}
}
if($_POST["func"] == "del_contact_details") {
	dl::delete("contact_details", "cd_id= ".$_POST["conId"]);
	if($_POST["option"]== 'new') {
		$details = dl::select("contact_details", "customers_id=0", "cd_id ASC"); //if no customer id is set then the details haven't been properly saved yet
	}else{
		$details = dl::select("contact_details", "customers_id=0 or customers_id=".$_POST["customer_id"], "cd_id ASC");
	}
	if(!empty($details)) {
		foreach($details as $detail) {
			$types = dl::select("contact_types", "id=".$detail["contact_type_id"] );
			echo "<list-content><div id='content-container'><div id='content-header'>".$types[0]["name"]."</div><div id='content' style='width:15em;'>".nl2br($detail["cd_detail"])."</div><div id='content-del'><a href='#' id='button".$detail["cd_id"]."' border='0'><img src='images/DeleteRed.png' /></a></div></div></list-content>";
		}
		echo "<br />";
		//now setup the delete function for the buttons passed back by the above jQuery
		foreach($details as $d) {
			?>
			<script type="text/javascript">
					$("#button<?php echo $d["cd_id"]?>").click( function (){
						var func = "del_contact_details";
						$.post(
							"ajax.php",
							{ func: func,
								option: '<?php echo $_POST["option"] ?>',
								customer_id: <?php echo $_POST["customer_id"] ?>,
								conId: <?php echo $d["cd_id"]?>
							},
							function (data)
							{
								$('#showContactDetails').html(data);
								$("#con_detail").val("");
								$('#contact_type').val("");
						});
					});
			</script>
	<?php
		}
	}
}

if($_POST["func"] == "save_contact_details") {
	if($_POST["option"] == "new"){
		dl::insert("customers", array("c_name"=>$_POST["conCust"], "c_type_of_business"=>$_POST["conBus"], "c_registration_no"=>$_POST["conReg"]));
		$cust_id = dl::getId();
		$audit = new audit("DATA AMENDMENT", "RECORD ADDED", array($_SESSION["userId"], $_SESSION["user_name"]));
		audit::create_action(array("table"=>"customers", "values"=>array("c_name"=>$_POST["conCust"], "c_type_of_business"=>$_POST["conBus"], "c_registration_no"=>$_POST["conReg"])), $cust_id);
		dl::update("contact_details", array("customers_id"=>$cust_id), "customers_id=0");
	}else{
		dl::update("customers", array("c_name"=>$_POST["conCust"], "c_type_of_business"=>$_POST["conBus"], "c_registration_no"=>$_POST["conReg"]), "c_id = ".$_POST["customer_id"]);
		$cust_id = dl::getId();
		$audit = new audit("DATA AMENDMENT", "RECORD UPDATED", array($_SESSION["userId"], $_SESSION["user_name"]));
		audit::create_action(array("table"=>"customers", "values"=>array("c_name"=>$_POST["conCust"], "c_type_of_business"=>$_POST["conBus"], "c_registration_no"=>$_POST["conReg"])), $_POST["customer_id"]);
		dl::update("contact_details", array("customers_id"=>$_POST["customer_id"]), "customers_id=0");
	}
	echo "Customer Details have been saved...";
}

if($_POST["func"] == "new_location_details") {
	$location_types = dl::select("location_types", "name = \"".$_POST["locType"]."\"");
	//add new details
	dl::insert("location_details", array("location_type_id"=>$location_types[0]["id"], "l_detail"=>$_POST["locDetail"]));
	$newId = dl::getId();
	$audit = new audit("DATA MANAGEMENT", "RECORD ADDED", array($_SESSION["userId"], $_SESSION["user_name"]));
	audit::create_action(array("table"=>"location_details", "values"=>array("location_type_id"=>$location_types[0]["id"], "l_detail"=>$_POST["locDetail"])), $newId);
	$details = dl::select("location_details", "samples_list_id=0 or samples_list_id =".$_POST["samples_list_id"], "l_id ASC");
	if(!empty($details)) {
		foreach($details as $detail) {
			$types = dl::select("location_types", "id=".$detail["location_type_id"] );
			echo "<list-content><div id='content-container'><div id='content-header'>".$types[0]["name"]."</div><div id='content' style='width:15em;'>".nl2br($detail["l_detail"])."</div><div id='content-del'><a href='#' id='button".$detail["l_id"]."' border='0'><img src='images/DeleteRed.png' /></a></div></div></list-content>";
		}
		echo "<br />";
		//now setup the delete function for the buttons passed back by the above jQuery
		foreach($details as $d) {
			?>
			<script type="text/javascript">
					$("#button<?php echo $d["l_id"]?>").click( function (){
						var func = "del_location_details";
						$.post(
							"ajax.php",
							{ func: func,
								samples_list_id: <?php echo $_POST["samples_list_id"] ?>,
								locId: <?php echo $d["l_id"]?>
							},
							function (data)
							{
								$('#showDocumentDetails').html(data);
								$("#loc_detail").val("");
								$('#loc_type').val("#");
						});
					});
			</script>
	<?php
		}
	}
}

if($_POST["func"] == "del_location_details") {
	dl::delete("location_details", "l_id= ".$_POST["locId"]);
	$details = dl::select("location_details", "samples_list_id=0 or samples_list_id=".$_POST["samples_list_id"], "l_id ASC");
	if(!empty($details)) {
		foreach($details as $detail) {
			$types = dl::select("location_types", "id=".$detail["location_type_id"] );
			echo "<list-content><div id='content-container'><div id='content-header'>".$types[0]["name"]."</div><div id='content' style='width:15em;'>".nl2br($detail["l_detail"])."</div><div id='content-del'><a href='#' id='button".$detail["l_id"]."' border='0'><img src='images/DeleteRed.png' /></a></div></div></list-content>";
		}
		echo "<br />";
		//now setup the delete function for the buttons passed back by the above jQuery
		foreach($details as $d) {
			?>
			<script type="text/javascript">
					$("#button<?php echo $d["l_id"]?>").click( function (){
						var func = "del_location_details";
						$.post(
							"ajax.php",
							{ func: func,
								samples_list_id: <?php echo $_POST["samples_list_id"] ?>,
								locId: <?php echo $d["l_id"]?>
							},
							function (data)
							{
								$('#showDocumentDetails').html(data);
								$("#loc_detail").val("");
								$('#loc_type').val("#");
						});
					});
			</script>
	<?php
		}
	}
}

if($_POST["func"] == "save_consent_details") {
	echo "<BR>";
	//lets determine if this is a new consent or an update
	$type = dl::select("consent_types", "name = '".$_POST["consentType"]."'");
	$fields = array("c_samples_list", "c_consent_date", "c_taken_by", "c_expiry_date", "c_consent_type_id");
	$values = array($_POST["samples_list_id"], date("Y-m-d", mktime(0, 0, 0, substr($_POST["consentDate"],3,2), substr($_POST["consentDate"],0,2), substr($_POST["consentDate"],6,4))), $_POST["takenBy"], date("Y-m-d", mktime(0, 0, 0, substr($_POST["consentExpiry"],3,2), substr($_POST["consentExpiry"],0,2), substr($_POST["consentExpiry"],6,4))), $type[0]["id"] );
	$writeLn = array_combine($fields, $values);
	$checkConsent = dl::select("consent", "c_samples_list = ".$_POST["samples_list_id"]);
	if(empty($checkConsent)) {
		dl::insert("consent", $writeLn);
		$consent_id = dl::getId();
		$audit = new audit("DATA AMENDMENT", "RECORD ADDED", array($_SESSION["userId"], $_SESSION["user_name"]));
		audit::create_action(array("table"=>"consent", "values"=>$writeLn), $consent_id);
		dl::update("location_details", array("samples_list_id"=>$consent_id), "samples_list_id=0");
	}else{
		dl::update("consent", $writeLn, "c_id = ".$checkConsent[0]["c_id"]);
		$cust_id = dl::getId();
		$audit = new audit("DATA AMENDMENT", "RECORD UPDATED", array($_SESSION["userId"], $_SESSION["user_name"]));
		audit::create_action(array("table"=>"consent", "values"=>$writeLn), $checkConsent[0]["c_id"]);
		dl::update("location_details", array("samples_list_id"=>$_POST["samples_list_id"]), "samples_list_id=0");
	}
	echo "Consent Details have been saved...";
}

if($_POST["func"] == "getSamplesListID") {
	$customer_name = substr($_POST["samples_list"], 0, strlen($_POST["samples_list"])-19);
	$uploadDate = substr($_POST["samples_list"], -19);
	$customers = dl::select("customers", "c_name = \"".$customer_name."\"");
	$sList = dl::select("samples_list", "customer_id=".$customers[0]["c_id"]." and sl_date_uploaded='".$uploadDate."'");
	$consent = dl::select("consent", "c_samples_list = ".$sList[0]["sl_id"]);
	$consent_type = dl::select("consent_types", "id = ". $consent[0]["c_id"]);
	if(!empty($consent)) {
		$consent_date = date("d/m/Y", mktime(0, 0, 0, substr($consent[0]["c_consent_date"],5,2), substr($consent[0]["c_consent_date"],8,2), substr($consent[0]["c_consent_date"],0,4)));
		$expiry_date = date("d/m/Y", mktime(0, 0, 0, substr($consent[0]["c_expiry_date"],5,2), substr($consent[0]["c_expiry_date"],8,2), substr($consent[0]["c_expiry_date"],0,4)));
		$locations = dl::select("location_details", "samples_list_id=".$sList[0]["sl_id"]);
		if(!empty($locations)) {
			foreach($locations as $loc) {
				$types = dl::select("location_types", "id=".$loc["location_type_id"]);
				$location_array[]= $types[0]["name"].",".nl2br($loc["l_detail"].",".$loc["l_id"]);
			}
			
		}
		$retArr = json_encode(array("listId"=>$sList[0]["sl_id"], "consent"=>$consent_date, "taken_by"=>$consent[0]["c_taken_by"], "expiry"=>$expiry_date, "consent_type"=> $consent_type[0]["name"], "location_info"=>$location_array));
		echo $retArr;
	}else{
		$retArr = json_encode(array("listId"=>"", "consent"=>"", "taken_by"=>"", "expiry"=>"", "consent_type"=>"#", "location_info"=>array()));
		echo $retArr;
	}
}


if($_POST["func"] == "getContainerDetails") {
	setContainerDetails();
}

function setContainerDetails() {
	$sql = "select * from containers as c 
	join container_templates as ct on (c.container_templates_id=ct.ct_id)
	where c_container_barcode = \"".$_POST["conBarcode"]."\"";
	$containers 		= dl::getQuery($sql);
	if(!empty($containers)) {
		echo "Container Name: ". $containers[0]["c_container_name"]."<BR>";
		$noCols 		= $containers[0]["ct_no_columns"];
		$noRows		= $containers[0]["ct_no_rows"];
		$rowType		= $containers[0]["ct_row_type"];
		$colType		= $containers[0]["ct_column_type"];
		//create the column titles
		echo "<div style='width:1em; font-size: 1em;text-align:center; padding:0.1em 0.25em; line-height: 1em;float:left;'></div>";
		for($i=1; $i<=$noCols; $i++){
			if($colType == "Alpha"){
				$content = chr(64+$i); //This will display the character string starting at 65 which is a capital (A)
				$alpha = "Column";
			}else{ 
				$alpha = "";
				$content = $i; //This will display the number;
			}
			if(strlen($i) == 1) {
				echo "<div style='width:1em; font-size:1em; line-height: 1em; float:left; text-align:center;margin:4px 4px; padding: 0 0.25em 0 0.25em;'>$content</div>";
			}else{
				echo "<div style='width:1em; font-size:1em; line-height: 1em; float:left; text-align:center;margin:4px 4px; padding: 0 0.2em 0 0;'>$content</div>";
			}
		}
		echo "<div style='clear:both;'></div>"; //end of the column 
		//create the rows
		for($j=1; $j<=$noRows; $j++){
			if($rowType == "Alpha"){
				$content = chr(64+$j);
				$alpha = "Row";
			}else{
				$alpha = "";
				$content = $j;
			}
			echo "<div style='width:1em; font-size:1em; text-align: right; padding: 0.1em 0.25em; line-height: 1em; float:left;'>$content</div>";
			for($k=1; $k<=$noCols; $k++){
				if($alpha == "Row") {
					$row = chr(64+$j);
				}else{
					$row = $j;
				}
				if($alpha == "Column") {
					$column = chr(64+$k);
				}else{
					$column = $k;
				}
				$sql = "select * from container_locations as cl join 
					container_positions as cp on (cl.container_positions_id=cp.cp_id) join 
					containers as c on (c.c_id=cl.containers_id) 
					where c_container_barcode = \"".$_POST["conBarcode"]."\" and cp_row = '".$row."' and cp_column = '".$column."'";
				$check_container = dl::getQuery($sql);
				if(!empty($check_container)) {
					$sampleBC = dl::select("samples", "s_id = ".$check_container[0]["samples_id"]);
					echo "<div class='box-cell-full' id='$j-$k' title='".$sampleBC[0]["s_number"]."'></div>";
					$stored = true;
				}else{
					echo "<div class='box-cell-empty' id='$j-$k'></div>";
					$stored = false;
				}
				?>
				<script>
					$("#<?php echo $j.'-'.$k ?>").click(function(){
						var func = "checkSampleSelected";
						var containerPos = "<?php echo $row.'-'.$column ?>";
					$.post(
						"ajax.php",
						{ func: func
						},
						function (data)
						{
							var json = $.parseJSON(data);
							var sessionVar = json.sampleSelected;
								<?php if($stored) {?>
								$("#dialog2").dialog({ 
									modal: true,
									height: 400,
									width: 500,
									autoOpen: true,
									title: 'Remove Sample',
								<?php }else{?>
								$("#dialog1").dialog({ 
									modal: true,
									height: 300,
									width: 500,
									autoOpen: true,
									title: 'Confirmation',
								<?php }?>
									buttons: [
									<?php if(!$stored){?>
									{
										id:		'btOk',
										text:		'OK',
										click:	function(){
										var func = 'add_sample';										
											$.post(
											"ajax.php",
											{ func: func,
											  sampleSelected: sessionVar,
											  containerPosition: containerPos,
											  container_id: $("#container_bc").val(),
											  barcode: $("#stored_bc").val()
											},
											function (data)
											{
												$("#trayLoc").html(data);
												if(sessionVar !== ""){
													$("#<?php echo $j.'-'.$k ?>").css("background-color", "#efefef");
													var func = 'refresh_list';										
													$.post(
													"ajax.php",
													{ func: func,
													sampleVal: $("#sample_listing").val()
													},
													function (data)
													{
														$("#show_samples").html(data);
														var func = 'refresh_container';										
														$.post(
														"ajax.php",
														{ func: func,
															conBarcode: $("#container_bc").val()
														},
														function (data)
														{
															$("#container_details").html(data);
														});
													});	
												}
											});										
											$(this).dialog("destroy");
										}
									},
									<?php }else{?>
									{
										id: 		'btRemove',
										text: 	'Remove',
										click: 	function() {
											var func = 'Remove';
											$.post(
											"ajax.php",
											{ func: func,
												containerPosition: containerPos,
												container_id: $("#container_bc").val(),
												note: $("#rem_note").val(),
												barcode: $("#rem_stored_bc").val()
											},
											function (data)
											{
												$("#trayLoc").html(data);
													$("#<?php echo $j.'-'.$k ?>").css("background-color", "#ccc");
													var func = 'refresh_list';										
													$.post(
													"ajax.php",
													{ func: func,
													sampleVal: $("#sample_listing").val()
													},
													function (data)
													{
														$("#show_samples").html(data);
														var func = 'refresh_container';										
														$.post(
														"ajax.php",
														{ func: func,
															conBarcode: $("#container_bc").val()
														},
														function (data)
														{
															$("#container_details").html(data);
														});	
													});	
											});
											$(this).dialog("destroy");
										}
									},
									<?php }?>
									{
										id: 		'btCancel',
										text:		'Cancel',
										click:	function() {
											alert("Cancelled");
											$(this).dialog("destroy");
										} 
									}]
								});
						});
					});
					$("#<?php echo $j.'-'.$k ?>").mouseover(function(){
						$("#trayLoc").text("<?php echo $row.'-'.$column ?>");
					});
				</script>
				<?php
			}
			echo "<div style='clear: both;'></div>"; //end of columns
		}
		echo "<div id='trayLoc'></div>";
		echo "<div id='sampleStore'></div>";
	}else{
		echo "Container not found please register this container.";
	}
}

if($_POST["func"] == "checkSampleContainer") {
	$container = dl::select("containers", "c_container_barcode = \"".$_POST["conBarcode"]."\"");
	if(!empty($container)) {
		echo "Container Type - ".$container[0]["c_container_name"]."<BR/>";		
	}
}

if($_POST["func"] == "add_sample") {
	$position = $_POST["containerPosition"];
	$sample_id = substr($_POST["sampleSelected"],9, strlen($_POST["sampleSelected"]));
	$barcode = $_POST["barcode"];
	//find container type ID of the container being stored
	$barcodeId = dl::select("containers", "c_container_barcode = '".$_POST["barcode"]."'");
	$templatesId = dl::select("container_templates", "ct_id = ".$barcodeId[0]["container_templates_id"]);
	$conId = dl::select("container_types", "ct_id = ".$templatesId[0]["container_types_id"]);
	$containerIN = $conId[0]["ct_id"]; //container type being stored
	
	$barcodeId = dl::select("containers", "c_container_barcode = '".$_POST["container_id"]."'");
	$templatesId = dl::select("container_templates", "ct_id = ".$barcodeId[0]["container_templates_id"]);
	$conId = dl::select("container_types", "ct_id = ".$templatesId[0]["container_types_id"]);
	$containerStore = $conId[0]["ct_id"]; //container type to store in
	$container_id = $_POST["container_id"];
	$conId = dl::select("containers", "c_container_barcode = '".$container_id."'");
	//need to check if the barcode container type can be stored in the container_id.
	$sql = "select * from containers as c join 
	container_templates as ct on (c.container_templates_id=ct.ct_id) join 
	container_types as cty on (ct.container_types_id=cty.ct_id) left outer join 
	allowed_containers as ac on (cty.ct_id=ac.types_id) 
	where ac.allowed_ids = ".$containerIN." and ac.types_id = ".$containerStore;
	$container_allowed = dl::getQuery($sql);
	if(!empty($container_allowed)) { //this container is allowed to be placed in the type of container
		if($sample_id!="") {
			$samples_list = dl::select("sample_list_items", "sli_id = ".$sample_id);
			$customer = dl::select("samples_list", "sl_id = ".$samples_list[0]["samples_list_id"]);
			$fields = array("s_type_id", "s_number", "customer_id", "samples_list_items_id", "s_status");
			$values = array("", $barcode,$customer[0]["customer_id"], $sample_id, "Stored" );
			$writeLn = array_combine($fields, $values);
			//check if sample record exists
			$sample = dl::select("samples", "s_number = '".$barcode."'");
			if(!empty($sample)) {
				if($sample[0]["s_status"] == 'Removed') { //check if the sample has been removed
					dl::update("samples", $writeLn, "s_number = '".$barcode."'");
					$update_id = dl::select("samples", "s_number = '".$barcode."'");
					$newId = dl::getId();
					$audit = new audit("DATA MANAGEMENT", "RECORD UPDATED", array($_SESSION["userId"], $_SESSION["user_name"]));
					audit::create_action(array("table"=>"samples", "values"=>$writeLn), $update_id[0]["s_id"]);
					$saved_sample_id = $sample[0]["s_id"];
					//there must be a sample_orphan record so delete it
					dl::delete("sample_orphans", "sample_id=".$sample[0]["s_id"]);
					$newId = dl::getId();
					$audit = new audit("DATA MANAGEMENT", "RECORD DELETED", array($_SESSION["userId"], $_SESSION["user_name"]));
					audit::create_action(array("table"=>"sample_ophans", "values"=>"sample_id=".$sample[0]["s_id"]), $newId);
					$sample_update = true;
				}else{
					$sample_update = false;
					?>
					<script>
						$(document).ready(function() {
						alert("THIS SAMPLE HAS A LOCATION\n\nPlease remove it from it's existing location before relocating it.\nYou may do this by selecting the 'empty space' in the container and\nselecting to remove the sample.");
						});
					</script>
					<?php
				}
			}else{
				dl::insert("samples", $writeLn);
				$saved_sample_id = dl::getId();
				$audit = new audit("DATA MANAGEMENT", "RECORD ADDEDD", array($_SESSION["userId"], $_SESSION["user_name"]));
				audit::create_action(array("table"=>"samples", "values"=>$writeLn), $saved_sample_id);
				$sample_update = true;
			}
			if($sample_update) { //The sample has been updated so save the container position/ location and location history
				dl::insert("container_positions", array("cp_row"=>substr($position,0, strpos($position,"-")), "cp_column"=>substr($position, strpos($position,"-")+1, strlen($position))));
				$container_position_id = dl::getId();
				$audit = new audit("DATA MANAGEMENT", "RECORD ADDEDD", array($_SESSION["userId"], $_SESSION["user_name"]));
				audit::create_action(array("table"=>"container_positions", "values"=>array("cp_row"=>substr($position,0, strpos($position,"-")), "cp_column"=>substr($position, strpos($position,"-")+1, strlen($position)))), $container_position_id);
				
				$findContainer = dl::select("containers", "c_container_barcode = \"".$container_id."\"");
				dl::insert("container_locations", array("samples_id"=>$saved_sample_id, "containers_id"=>$findContainer[0]["c_id"], "container_positions_id"=>$container_position_id));
				$newId = dl::getId();
				$audit = new audit("DATA MANAGEMENT", "RECORD ADDED", array($_SESSION["userId"], $_SESSION["user_name"]));
				audit::create_action(array("table"=>"container_locations", "values"=>array("samples_id"=>$saved_sample_id, "containers_id"=>$findContainer[0]["c_id"], "container_positions_id"=>$container_position_id)), $newId);
				dl::update("containers", array("locations_id"=>$findContainer[0]["c_id"]), "c_container_barcode = '".$barcode."'");
				$update_id = dl::select("containers", "c_container_barcode = '".$barcode."'");
				$newId = dl::getId();
				$audit = new audit("DATA MANAGEMENT", "RECORD UPDATED", array($_SESSION["userId"], $_SESSION["user_name"]));
				audit::create_action(array("table"=>"containers", "values"=>array("locations_id"=>$findContainer[0]["c_id"])), $update_id[0]["c_id"]);
				$findContainer = dl::select("containers", "c_container_barcode = \"".$container_id."\"");
				$fields = array("locations_id", "lh_date", "lh_action");
				$values = array($findContainer[0]["c_id"], date('Y-m-d H:i:s'), "Added to container");
				$combine = array_combine($fields, $values);
				dl::insert("locations_history", $combine);
				$lh_id = dl::getId();
				$audit = new audit("DATA MANAGEMENT", "RECORD ADDED", array($_SESSION["userId"], $_SESSION["user_name"]));
				audit::create_action(array("table"=>"locations_history", "values"=>$combine), $lh_id);
				$samples = dl::select("samples", "s_number = '".$barcode."' and samples_list_items_id = ".$sample_id);
				$s_id = $samples[0]["s_id"];
				dl::insert("location_history_samples", array("location_history_id"=>$lh_id, "samples_id"=>$s_id));
				$newId = dl::getId();
				$audit = new audit("DATA MANAGEMENT", "RECORD ADDED", array($_SESSION["userId"], $_SESSION["user_name"]));
				audit::create_action(array("table"=>"location_history_samples", "values"=>array("location_history_id"=>$lh_id, "samples_id"=>$s_id)), $newId);
			}
			$_SESSION["selectedSample"]=""; //reset the session variable
		}else{
		?>
		<script>
			$(document).ready(function() {
			alert("You must select a sample from the Samples List");
			});
		</script>
		<?php
		}
	}else{
	?>
		<script>
			$(document).ready(function() {
			alert("The container you have chosen is not authorised to store the samples container.\r\nPlease examine the container type and add this container to the list of authorised \r\ncontainers.");
			});
		</script>
		<?php
	}
}

if($_POST["func"] == "save_sample_locations") {
	$list = dl::select("samples_list", "sl_date_uploaded = \"".substr($_POST["sampleVal"], -19)."\"");
	$list_id = $list[0]["sl_id"];
	$customer_id = $list[0]["customer_id"];
	$container = dl::select("containers", "c_container_barcode =\"". $_POST["container"]."\"");
	if(!empty($_POST["container"]) and !empty($_POST["sampleVal"])) {
		//check if this is an association or a full catalogue
		if($_POST["uncatalogued"] == "true") {
			//need to add all of the samples in the samples_list_items table to the samples table with s_number = 0 and link the  container_locations table with container_position_id = 0
			// then need to mark the list as complete in the samples_list table
			$sample_items = dl::select("sample_list_items", "samples_list_id = ".$list_id);
			foreach($sample_items as $item) {
				dl::insert("samples", array("s_type_id"=>0,"s_number"=>0,"customer_id"=>$customer_id, "samples_list_items_id"=>$item["sli_id"], "s_status"=>"Stored"));
				$samplesId = dl::getId();
				$audit = new audit("DATA MANAGEMENT", "RECORD ADDED", array($_SESSION["userId"], $_SESSION["user_name"]));
				audit::create_action(array("table"=>"samples", "values"=>array("s_type_id"=>0,"s_number"=>0,"customer_id"=>$customer_id, "samples_list_items_id"=>$item["sli_id"], "s_status"=>"Stored")), $samplesId);
				dl::insert("container_locations", array("samples_id"=>$samplesId, "containers_id"=>$container[0]["c_id"], "container_positions_id"=>0));
				$newId = dl::getId();
				$audit = new audit("DATA MANAGEMENT", "RECORD ADDED", array($_SESSION["userId"], $_SESSION["user_name"]));
				audit::create_action(array("table"=>"container_locations", "values"=>array("samples_id"=>$samplesId, "containers_id"=>$container[0]["c_id"], "container_positions_id"=>0)), $newId);
			}
			echo "The samples have been added to the container in an uncatalogued state.";
			dl::update("samples_list", array("sl_status"=>"Complete"), "sl_id = ".$list_id);
			$newId = dl::getId();
			$audit = new audit("DATA MANAGEMENT", "RECORD UPDATED", array($_SESSION["userId"], $_SESSION["user_name"]));
			audit::create_action(array("table"=>"samples_list", "values"=>array("sl_status"=>"Complete")), $newId);
		}else{
			//need to check if all of the samples in the list have been catalogued
			// if yes then the list can be marked as completed
			//if not then can reply with a message
			$listSamples = dl::select("sample_list_items", "samples_list_id = ".$list_id);
			$notFound = false;
			if(!empty($listSamples)) {
				foreach($listSamples as $ls) {
					$samplesId = dl::select("samples", "samples_list_items_id = ".$ls["sli_id"]);
					$container_loc = dl::select("container_locations", "samples_id = ".$samplesId[0]["s_id"]);
					if(empty($container_loc)) {
						$notFound = true;
					}
				}
				if(!$notFound) {
					//looks like the container has been filled with all the samples lets update the samples_list table
					dl::update("samples_list", array("sl_status"=>"Complete"), "sl_id = ".$list_id);
					$newId = dl::getId();
					$audit = new audit("DATA MANAGEMENT", "RECORD UPDATED", array($_SESSION["userId"], $_SESSION["user_name"]));
					audit::create_action(array("table"=>"samples_list", "values"=>array("sl_status"=>"Complete")), $list_id);
					echo "The samples list has been completed all samples have been catalogued.";
				}else{
					echo "Not all samples have been catalogued for this samples list.";
				}
			}else{
				echo "No samples from this list have been catalogued.";
			}
		}
	}else{
		echo "The samples list and container barcode are required items. Please select them to proceed.";
	}
}


if($_POST["func"] == "Remove") {
	//search samples with the barcode
	$samples = dl::select("samples", "s_number = '".$_POST["barcode"]."'");
	//store the samples ID
	$s_id = $samples[0]["s_id"];
	//add a sample_orphans record for the removed sample
	dl::insert("sample_orphans", array("sample_id"=>$s_id));
	$newId = dl::getId();
	$audit = new audit("DATA MANAGEMENT", "RECORD ADDED", array($_SESSION["userId"], $_SESSION["user_name"]));
	audit::create_action(array("table"=>"sample_orphans", "values"=>array("sample_id"=>$s_id)), $newId);
	//get host container id
	$host = dl::select("containers", "c_container_barcode ='".$_POST["container_id"]."'");
	//add locations_history record
	dl::insert("locations_history", array("locations_id"=>$host[0]["c_id"], "lh_date"=>date('Y-m-d H:i:s'), "lh_action"=>"Removed from container"));
	$lh_id = dl::getId();
	$audit = new audit("DATA MANAGEMENT", "RECORD ADDED", array($_SESSION["userId"], $_SESSION["user_name"]));
	audit::create_action(array("table"=>"locations_history", "values"=>array("locations_id"=>$host[0]["c_id"], "lh_date"=>date('Y-m-d H:i:s'), "lh_action"=>"Removed from container")), $lh_id);
	dl::insert("location_history_samples", array("location_history_id"=>$lh_id, "samples_id"=>$s_id));
	$lhs_id = dl::getId();
	$audit = new audit("DATA MANAGEMENT", "RECORD ADDED", array($_SESSION["userId"], $_SESSION["user_name"]));
	audit::create_action(array("table"=>"location_history_samples", "values"=>array("location_history_id"=>$lh_id, "samples_id"=>$s_id)), $lh_id);
	
	//if there is a note then save it
	if(!empty($_POST["note"])) {
		dl::insert("notes", array("note"=>$_POST["note"]));
		$n_id = dl::getId();
		$audit = new audit("DATA MANAGEMENT", "RECORD ADDED", array($_SESSION["userId"], $_SESSION["user_name"]));
		audit::create_action(array("table"=>"notes", "values"=>array("note"=>$_POST["note"])), $n_id);
		dl::insert("location_history_notes", array("note_id"=>$n_id, "locations_history_id"=>$lhs_id));
		$newId = dl::getId();
		$audit = new audit("DATA MANAGEMENT", "RECORD ADDED", array($_SESSION["userId"], $_SESSION["user_name"]));
		audit::create_action(array("table"=>"location_history_notes", "values"=>array("note_id"=>$n_id, "locations_history_id"=>$lhs_id)), $newId);
	}
	//use the samples ID to find the container location
	$containerPos = dl::select("container_locations", "samples_id =".$s_id);
	//get the location position ID
	$containerPos_id = $containerPos[0]["container_positions_id"];
	//delete the container location
	dl::delete("container_locations", "samples_id =".$s_id);
	$newId = dl::getId();
	$audit = new audit("DATA MANAGEMENT", "RECORD DELETED", array($_SESSION["userId"], $_SESSION["user_name"]));
	audit::create_action(array("table"=>"container_locations", "values"=>array("samples_id" =>$s_id)), $newId);
	//delete the container position
	dl::delete("container_positions", "cp_id=". $containerPos_id);
	$newId = dl::getId();
	$audit = new audit("DATA MANAGEMENT", "RECORD DELETED", array($_SESSION["userId"], $_SESSION["user_name"]));
	audit::create_action(array("table"=>"container_positions", "values"=>array("cp_id"=> $containerPos_id)), $newId);
	$container = dl::select("containers", "c_container_barcode = '".$_POST["barcode"]."'");
	dl::update("containers", array("locations_id"=>0), "c_id=".$container[0]["c_id"]);
	$newId = dl::getId();
	$audit = new audit("DATA MANAGEMENT", "RECORD UPDATED", array($_SESSION["userId"], $_SESSION["user_name"]));
	audit::create_action(array("table"=>"containers", "values"=>array("locations_id"=>0)), $container[0]["c_id"]);
	dl::update("samples", array("s_status"=>"Removed"), "s_id = ".$s_id);
	$newId = dl::getId();
	$audit = new audit("DATA MANAGEMENT", "RECORD UPDATED", array($_SESSION["userId"], $_SESSION["user_name"]));
	audit::create_action(array("table"=>"samples", "values"=>array("s_status"=>"Removed")), $s_id);
	$_SESSION["selectedSample"]=""; //reset the session variable
}


if($_POST["func"] == "checkSampleSelected") {
	echo json_encode(array("sampleSelected"=>$_SESSION["selectedSample"]));
}

if($_POST["func"] == "refresh_list") {
		show_samples();
}

if($_POST["func"] == "refresh_container") {
		setContainerDetails();
}

if($_POST["func"] == "display_samples") {
		show_samples();
}

function show_samples() {
	$list = dl::select("samples_list", "sl_date_uploaded = \"".substr($_POST["sampleVal"], -19)."\"");
	$samples = dl::select("sample_list_items", "samples_list_id =".$list[0]["sl_id"]);
	if(!empty($samples)) {
		echo "<ul id='sampleList'>";
		foreach($samples as $sample) {
			//check samples table for this sample there may be a record but will show up if the status is 'Removed'
			$check_sample = dl::select("samples", "samples_list_items_id = ". $sample["sli_id"]." and s_status = 'Stored'");
			if(empty($check_sample)) {
				echo "<li id='sampleId-".$sample["sli_id"]."' style='list-style-type:none; font-size:0.75em; padding:0.25em; height: 2em; margin: -0.5em 0; cursor:pointer; width:150%' class='samples'><div style='width: 100px; overflow:hidden; float:left; white-space:nowrap;'>".$sample["sli_customer_identifier"]."</div><div style='width: 230px; overflow:hidden; float:left; white-space:nowrap; margin-right:0.25em;'>".$sample["sli_description"]."</div><div style='width: 100px; overflow:hidden; float:left;'>".$sample["sli_pathology_no"]."</div><div style='width: 100px; overflow:hidden; float:left;'>".$sample["sli_SNOMED_code"]."</div></li><BR>";
				?>
					<script>
					$("#sampleId-<?php echo $sample["sli_id"]?>").selectable({
						selected: function (event, ui) {
						$("#sampleList li").each(function(){
							$(this).css("color", "#666");
						})
						$(this).css("color", "#578ccc");
						var textToSend = $(this).attr("id");
						var func = "setSampleDetails";
						$.post(
							"ajax.php",
							{ func: func,
								sendSampleText: textToSend
							},
							function (data)
							{
								$('#trayLoc').html(data);
							});
					}
				});
				
					</script>
				<?php
			}
		}
		echo "</ul>";
	}
}

if($_POST["func"] == "setSampleDetails") {

	$_SESSION["selectedSample"] = $_POST["sendSampleText"];
	echo "Session has been set to ".$_POST["sendSampleText"];

}
if($_POST["func"] == "saveBarcodeSettings") {
	$fields = array("pbs_prefix", "pbs_number", "pbs_suffix", "pbs_name");
	$values = array($_POST["bcPrefix"],$_POST["bcNumber"],$_POST["bcSuffix"],$_POST["bcName"]);
	$writeln = array_combine($fields, $values);
	dl::insert("print_barcode_settings", $writeln);
	$newId = dl::getId();
	$audit = new audit("DATA MANAGEMENT", "RECORD ADDED", array($_SESSION["userId"], $_SESSION["user_name"]));
	audit::create_action(array("table"=>"print_barcode_settings", "values"=>$writeln), $newId);
	echo "Created print settings...";

}

if($_POST["func"] == "saveBarcodeTemplate") {
	//get id from barcode types
	$types = dl::select("print_barcode_types", "pbt_name = \"".$_POST["bcType"]."\"");
	//get id from barcode settings 
	$settings = dl::select("print_barcode_settings", "pbs_name = \"".$_POST["bcSettings"]."\"");
	//get if readable required
	if($_POST["readLabel"]=="true") {
		$rl="true";
	}else{
		$rl="false";
	}
	$fields = array("positionX", "positionY", "barcode_height", "barcode_width", "label_height", "label_width", "barcode_types_id", "barcode_settings_id", "template_name","readable_label");
	$values = array($_POST["posx"],$_POST["posy"],$_POST["bcHeight"],$_POST["bcWidth"],$_POST["lHeight"], $_POST["lWidth"], $types[0]["pbt_id"], $settings[0]["pbs_id"], $_POST["bcTempName"], $rl);
	$writeln = array_combine($fields, $values);
	dl::insert("print_template", $writeln);
	$newId = dl::getId();
	$audit = new audit("DATA MANAGEMENT", "RECORD ADDED", array($_SESSION["userId"], $_SESSION["user_name"]));
	audit::create_action(array("table"=>"print_template", "values"=>$writeln), $newId);
	echo "Created print template...";

}

if($_POST["func"] == "showTemplateValues") {
	$sql = "select * from print_template join print_barcode_settings on (barcode_settings_id=pbs_id)
	where template_name ='".$_POST["selTemplate"]."'";
	$selected = dl::getQuery($sql);
	$field = new fields("Barcode Prefix", "text", "greyInput", "15", $selected[0]["pbs_prefix"], "bcPrefix");
	echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
	echo "<span class='form_field'>".$field->show_field()."</span><BR>";
	$field = new fields("Last Barcode Printed", "text", "greyInput", "20", $selected[0]["pbs_number"], "bcNumber");
	echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
	echo "<span class='form_field'>".$field->show_field()."</span><BR>";
	$field = new fields("Barcode Suffix", "text", "greyInput", "15",$selected[0]["pbs_suffix"], "bcSuffix");
	echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
	echo "<span class='form_field'>".$field->show_field()."</span><BR>";
	
	if($_POST["option"]!=='re-print') {
		?>
		<script>
		$("#bcNumber").prop('disabled', true);
		</script>
		<?php
	}	
	
	?>
	<script>
	$("#bcPrefix").prop('disabled', true);
	$("#bcSuffix").prop('disabled', true);
	</script>
	<?php
}

if($_POST["func"] == "updatePrintValue") {
	if($_POST["option"] !== "re-print") {
		$sql = "select * from print_template join print_barcode_settings on (barcode_settings_id=pbs_id)
		where template_name ='".$_POST["selTemplate"]."'";
		$selected = dl::getQuery($sql);
		dl::update("print_barcode_settings", array("pbs_number"=>$_POST["bcnumber"]), "pbs_id = ".$selected[0]["pbs_id"]);
		$newId = dl::getId();
		$audit = new audit("DATA MANAGEMENT", "RECORD UPDATED", array($_SESSION["userId"], $_SESSION["user_name"]));
		audit::create_action(array("table"=>"print_barcode_settings", "values"=>array("pbs_number"=>$_POST["bcnumber"])), $selected[0]["pbs_id"]);
	}
}

if($_POST["func"] == "calculatePrints") {
	$sql = "select * from print_template join print_barcode_settings on (barcode_settings_id=pbs_id)
	where template_name ='".$_POST["selTemplate"]."'";
	$selected = dl::getQuery($sql);
	$strLen = strlen($_POST["barcodeNo"]);
	$labels = $_POST["barcodeNo"];
	$lastVal = (int)$labels + $_POST["noPrints"];
	$len = strlen($lastVal);	
	for($i=$len; $i<$strLen; $i++) {
		$lastBarcode .= "0";
	}
	$lastBarcode = $lastBarcode.$lastVal;
	$field = new fields("Last Barcode", "text", "greyInput", "20", $lastBarcode, "lastBC");
	echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
	echo "<span class='form_field'>".$field->show_field()."</span><BR>";
	
	?>
	<script>
	$("#lastBC").prop('disabled', true);
	</script>
	<?php
}

if($_POST["func"] == "print_labels") {
	include(CLASSPATH."/barcode/php-barcode.php");
	require(CLASSPATH."/barcode/sample php/fpdf.php");
	$sql = "select * from print_template join print_barcode_settings on (barcode_settings_id=pbs_id)
	where template_name ='".$_POST["selTemplate"]."'";
	$selected = dl::getQuery($sql);
	//-------------------setup template ------------------------------//
	$x = $selected[0]["positionX"];
	$y = $selected[0]["positionY"];
	$bcHeight = $selected[0]["barcode_height"];
	$bcWidth = $selected[0]["barcode_width"];
	$labelHeight = $selected[0]["label_height"];
	$labelWidth = $selected[0]["label_width"];
	$types = dl::select("print_barcode_types", "pbt_id = ".$selected[0]["barcode_types_id"]);
	$barcodeType = $types[0]["pbt_name"];
	$prefix = $selected[0]["pbs_prefix"];
	$suffix = $selected[0]["pbs_suffix"];
	$angle= '0';
	$black = '000000';
	//------------------ end of Setup ---------------------------------//
	$pdf = new FPDF('P', 'mm', array($labelWidth,$labelHeight));
	$strLen = strlen($selected[0]["pbs_number"]);
	$labels = $selected[0]["pbs_number"];
	$firstVal = $labels;	
	for($p=1; $p<=$_POST["noPrints"]; $p++){
		$lastVal = (int)$labels + $p;
		$len = strlen($lastVal);	
		$lastBarcode='';
		for($i=$len; $i<$strLen; $i++) {
			$lastBarcode .= "0";
		}
		$num = $firstVal+$p;
		$Barcode = $prefix.$lastBarcode.$num;
		if(!empty($suffix)) {
			$Barcode .= "-".$suffix;
		}
		$pdf->AddPage();  
		$data = Barcode::fpdf($pdf, $black, $x, $y, $angle, $barcodeType, array('code'=>$Barcode), $bcWidth, $bcHeight);
	}
	$pdf->Output();
}

if($_POST["func"] == "findCustomer") {
	$customer = dl::select("customers", "c_name = \"".$_POST["customer"]."\"");
	$cust_id = $customer[0]["c_id"];
	$sql = "select * from contact_details join contact_types on (contact_type_id=id) 
	where customers_id = ".$cust_id;
	$customerDetails = dl::getQuery($sql);
	if(!empty($customerDetails)) {
		foreach( $customerDetails as $cd ){
			switch($cd["name"]) {
				case "email":
					$jsEmail = $cd["cd_detail"];
					break;
				case "Contact Name":
					$jscontact = $cd["cd_detail"];
					break;
				case "Phone":
					$jsPhone = $cd["cd_detail"];
					break;
			}
		}
		echo json_encode(array("email"=>$jsEmail, "ContactName"=>$jscontact, "Phone"=>$jsPhone));
	}
}

if($_POST["func"] == "audit_filter") {
	$sql = "select audit_action.aa_id, audit_actions_id, audit_identification_id, audit_timestamp_id from audit_action";
	$where = " where ";
	if(!empty($_POST["from"]) and !empty($_POST["to"])){
		$date_from = date('Y-m-d', mktime(0,0,0, substr($_POST["from"],3,2), substr($_POST["from"],0,2), substr($_POST["from"], 6,4)));
		$date_to = date("Y-m-d", mktime(0,0,0, substr($_POST["to"],3,2), substr($_POST["to"],0,2), substr($_POST["to"], 6,4)));
		$sql .= " join audit_timestamp on (at_id=audit_timestamp_id)";
		$where .= " at_timestamp >= '".$date_from." 00:00:00' and at_timestamp <= '".$date_to." 23:59:59'";
	}
	//check the user id's
	if(!empty($_POST["user"])) {
		$user_ids = dl::select("audit_identification", "ai_username = '".$_POST["user"]."'");
		$sql .= " join audit_identification on (ai_id=audit_identification_id)";
		if($where == " where ") {
			$where .= " ai_userid = ".$user_ids[0]["ai_userid"];
		}else{
			$where .= " and ai_userid = ".$user_ids[0]["ai_userid"];
		}
	}
	//check actions
	if(!empty($_POST["action"])) {
		$action = dl::select("audit_actions", "aa_list = '".$_POST["action"]."'");
		$sql .= " join audit_actions as aa on (aa.aa_id=audit_actions_id)";
		if($where == " where ") {
			$where .= " audit_actions_id = ".$action[0]["aa_id"];
		}else{
			$where .= " and audit_actions_id = ".$action[0]["aa_id"];
		}
	}
	//check tables
	if(!empty($_POST["table"])) {
		$sql .= " join audit_details_actions as ada on (audit_action.aa_id=ada.audit_action_id) join audit_details on (ada.audit_details_id=ad_id)";
		if($where == " where ") {
			$where .= " ad_tables = '".$_POST["table"]."'";
		}else{
			$where .= " and ad_tables = '".$_POST["table"]."'";
		}
	}
	//check record ID
	if(!empty($_POST["table"])) {
		if($where == " where ") {
			$where .= " ad_record_id = '".$_POST["table"]."'";
		}else{
			$where .= " and ad_record_id = '".$_POST["record"]."'";
		}
	}
	if($where !== " where ") {
		$actions = dl::getQuery($sql.$where);
	}else{
		$actions = dl::getQuery($sql);
	}
	audit_report_body($actions);
}
?>