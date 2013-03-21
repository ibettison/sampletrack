<?php 
session_start();
date_default_timezone_set("Europe/London");


/* index.php 
Creation Date:12/07/2011
updated by : Ian Bettison
Purpose : To manage the Fetal Medicines database.*/

define( "LIBRARYPATH", "./library/" );
define( "LOCALIMAGEPATH", "/images/" );
define( "IMAGEPATH", "./library/images/" );
define( "CSSPATH", "./css/" );
define( "CLASSPATH", "./library/classes/" );
define( "JAVAPATH", "./library/js/");

require(LIBRARYPATH.'mysqli_datalayer.php');
require(CLASSPATH.'field.class.php');
require(CLASSPATH.'permission.class.php');
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

// Check the access to the Sample tracking database and return parsed JSON with confirmation and permission settings.
if($_GET["func"] == "ipadAccess"){ 
	$email													= strtolower(addslashes($_GET["email_address"]));
	$id 														= dl::select("user", "user_email_address='".$email."'");
	$password											= $_GET["password"];
	$login 													= new login( "user_id", $id[0]["user_id"], "user", "security", "security_password", $password, $id[0]["confirmed"]);
	if( $login->check_password() ) {
		//the userid and password are ok need to get the permissions
		$permissions 									= new permission( "user_types", "user_types_name_id", $id[0]["user_id"], "user_id", "permission_id" );
		$settings 											= $permissions->get_permissions( "permission_user", "permissions", "permission_name", "permission_value" );
		$_SESSION["settings"] 						= $settings;
		if(!$login->check_confirmation()) { 		//NOT Confirmed
			$_SESSION["notConfirmed"] 			= true;
			$_SESSION["user_email"] 				= $id[0]["user_email_address"];
			$_SESSION["loggedIn"]					= "false";
		}else{ 												//CONFIRMED
			$_SESSION["notConfirmed"]			= false;
			$_SESSION["loggedIn"] 				= "true";
			$_SESSION["userId"] 					= $id[0]["user_id"];
			$_SESSION["user_name"] 			= $id[0]["user_name"];
			$_SESSION["user_email"] 				= $id[0]["user_email_address"];
			$_SESSION["loggedInTime"] 			= date("d-m-Y H:i:s");
		}
		$credentials = array("login"=>$_SESSION["loggedIn"], "user"=>$id[0]["user_name"], "permission"=>$settings);	
	}else{
		$credentials = array("login"=>"unconfirmed");
	}
	echo json_encode($credentials);
}

if($_GET["func"] == "getLists"){
	$lists = dl::select("samples_list", "sl_status = 'Outstanding'");
	foreach($lists as $list) {
		$customer = dl::select("customers", "c_id = ". $list["customer_id"]);
		$custList[] =array("customer"=>$customer[0]["c_name"], "dates"=>$list["sl_date_uploaded"]);
		$dates[]=array($list["sl_date_uploaded"]);
	}
	$jsonReturn= array("customers"=>$custList);
	echo json_encode($jsonReturn);
}

if($_GET["func"] == "ipadScanToWeb"){ 
	dl::delete("web_barcode");
	dl::insert("web_barcode", array("wb_barcode"=>$_GET["scan"]));
	$scanned = array("scanned"=>"true");
	echo json_encode($scanned);
}
?>