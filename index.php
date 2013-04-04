<?php 
session_start();
echo "<!DOCTYPE html>";
echo "<html xmlns='http://www.w3.org/1999/xhtml'>";
?>
<SCRIPT src			="library/js/jquery-1.8.2.min.js"></SCRIPT>
<script src				="library/js/jquery-ui/js/jquery-ui-1.10.0.custom.min.js" type="text/javascript"></script>
<SCRIPT language	="JavaScript" src="library/js/jquery.autoresize.js"></SCRIPT>
<SCRIPT language	="JavaScript" src="library/js/jquery-validation/jquery.validate.js"></SCRIPT>
<SCRIPT language	="JavaScript" src="library/js/jquery.flexipage.min.js"></SCRIPT>
<SCRIPT language	="JavaScript" src="library/js/jquery.spritely-0.6.1.js"></SCRIPT>
<SCRIPT language	="JavaScript" src="library/js/jquery-ui-multiselect-widget-master/src/jquery.multiselect.js"></SCRIPT>
<SCRIPT language	="JavaScript" src="library/js/jquery-ui-multiselect-widget-master/src/jquery.multiselect.filter.js"></SCRIPT>

<?php
date_default_timezone_set("Europe/London");


/* index.php 
Creation Date:9/11/2012
updated by : Ian Bettison
Purpose : To manage the creation of a Sample tracking system*/

define( "LIBRARYPATH", 			"library/" );
define( "LOCALIMAGEPATH", 	"images/" );
define( "IMAGEPATH", 			"library/images/" );
define( "CSSPATH", 				"css/" );
define( "CLASSPATH", 			"library/classes/" );
define( "JAVAPATH", 				"library/js/");

require(LIBRARYPATH.			'mysqli_datalayer.php');
require('Classes/'.						'PHPExcel.php');
require(CLASSPATH.				'field.class.php');
require(CLASSPATH.				'permission.class.php');
include(									'connection.php');
require(CLASSPATH.				'login.class.php');
require(CLASSPATH.				'form.class.php');
require(CLASSPATH.				'tab.class.php');
require(CLASSPATH.				'validate.class.php');
require(CLASSPATH.				'admin.class.php');
require(CLASSPATH.				'search.class.php');
require(CLASSPATH.				'questionnaire.class.php');
require(CLASSPATH.				'addnote.class.php');
require(CLASSPATH.				'list.deletion.class.php');

// required if email function is available.
require(CLASSPATH.				'phpmailer/class.phpmailer.php');
include(									"functions.php");

if($_GET["func"] == "logoff") {
	session_destroy();
	session_start();
	$_SESSION["loggedIn"] = "";
}

echo "<head>";

//echo "<meta http-equiv='X-UA-Compatible' content='IE=edge'>";
//echo "<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1' />"; 

echo "<title>Sample Tracking System</title> ";
echo "<link rel='StyleSheet' href='".JAVAPATH."jquery-ui/css/smoothness/jquery-ui-1.10.0.custom.css' type='text/css' media='screen'>";
echo "<link rel='stylesheet' href='".JAVAPATH."jquery-ui-multiselect-widget-master/jquery.multiselect.css>";
echo "<link rel='stylesheet' href='".JAVAPATH."jquery-ui-multiselect-widget-master/jquery.multiselect.filter.css>";
echo "<link rel='stylesheet/css' href='".CSSPATH."nav.css' TYPE='text/css' media='screen'>";
echo "<link rel='stylesheet/less' href='".CSSPATH."sts.less' TYPE='text/css' media='screen'>"; 
echo "<SCRIPT src='".JAVAPATH."less/less-1.3.0.min.js'></SCRIPT>";
//echo "<LINK REL='stylesheet/css' HREF='".JAVAPATH."jquery.ptTimeSelect.css' TYPE='text/css' media='screen'>";
//echo "<SCRIPT src='".JAVAPATH."jquery.ptTimeSelect.js'></SCRIPT>";

echo "<link REL='SHORTCUT ICON' HREF='".LOCALIMAGEPATH."favicon.ico'>"; 
echo "</head>";
$_SESSION["notConfirmed"] = false;
if(!isset($_SESSION["loggedIn"])) {
	$_SESSION["loggedIn"] = "";
}
if($_GET["func"] == "login") { // attempt to login
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
		}
	}
}	
if($_GET["func"] == "confirm") { // security confirmation
	$email	  										= strtolower(addslashes($_SESSION["user_email"]));
	$id 	 											= dl::select("user", "user_email_address='".$email."'");
	$password 									= $_POST["password"];
	$login 	  										= new login( "user_id", $id[0]["user_id"], "user", "security", "security_password", $password, $id[0]['confirmation']);
	if( $login->check_password() ) {
		// the password and email address have been confirmed
		// now need to update the password and the confirmation flag
		$encrypt = $login->get_salt();
		dl::update("user", array("confirmed"=>1), "user_email_address='".$email."'");
		dl::update("security", array("security_password"=>MD5($encrypt.$_POST["newPassword"])), "user_id=".$id[0]["user_id"]);
		//the userid and password are ok need to get the permissions
		$permissions 		  					= new permission( "user_types", "user_types_name_id", $id[0]["user_id"], "user_id", "permission_id" );
		$settings 			  						= $permissions->get_permissions( "permission_user", "permissions", "permission_name", "permission_value" );
		$_SESSION["settings"] 				= $settings;
		$_SESSION["loggedIn"]				= "true";
		$_SESSION["userId"] 				= $id[0]["user_id"];
		$_SESSION["user_name"] 		= $id[0]["user_name"];
		$_SESSION["user_email"] 			= $id[0]["user_email_address"];
		$_SESSION["loggedInTime"] 		= date("d-m-Y H:i:s");
		$message 			  					= new message("ian.bettison@ncl.ac.uk");
		$subject 			  						= "Sample Tracking System - Account Confirmation";
		$body    			  						= "<div style='font-family: arial; font-size: small;'>Dear ".$id[0]["user_name"]."<P>
		Your account has now been activated. You have confirmed your username and have changed your password, your password is not contained within this email for your security.<P>
		<P>If you have any problems please contact me on x4652 or DECT Phone 21552.<P>Thank you.
		<P>Ian Bettison.</div>";
		$message->set_message($subject, $body);
		$message->set_To( array(array("email"=>$email, "name"=>$id[0]["user_name"]) ));
		$message->send();
	}
}
echo "<body>";
echo "<div class='container'>";
echo "<div class='header-top'>";
echo "</div>";
echo "<div class='header'>";
echo "<div class='header-left'></div>";
if( $_SESSION["loggedIn"] == "true") {
	display_menus();
}
dropdown_loginForm();
echo "</div>";
if($_SESSION["loggedIn"] == "") {
	show_frontpage();
	echo "</div>";
}
if( $_SESSION["loggedIn"] == "true") {	
	if( $_GET["func"] == "manage_lists"){
		echo "<main><div id='container-main'>";
		managelists();
		echo "</div></main>";
	}elseif( $_GET["func"] == "administration"){
		echo "<main><div id='container-main'>";
		user_management();
		echo "</div></main>";
	}elseif( $_GET["func"] == "chooseList"){
		echo "<div class='main'>";
		disp_tabs(3);
		echo "</div>";
	}elseif( $_GET["func"] == "addPermission"){
		$users = new user();
		$users->write_permission();
		echo "<main><div id='container-main'>";
		user_management();
		echo "</div>";
		echo "</main>";
	}elseif( $_GET["func"] == "addType"){
		$users = new user();
		$users->write_usertype();
		echo "<main><div id='container-main'>";
		user_management();
		echo "</div>";
		echo "</main>";
	}elseif( $_GET["func"] == "addUser"){
		$users = new user();
		$body = "<div style='font-family: arial; font-size: small;'>Dear ".$_POST["user_name"]."<P>
						Your account has been created within the Sample Tracking System. You may <a href='http://sampletrack.ncl.ac.uk/'>login</a> to the system using your <b>email address</b> as the user id and the password:<P>
						<B>".$_POST["password"]."</B><P>After you login for the first time you will be asked to change your password, please create a password you will remember.</P><P>If you have any problems please contact Ian Bettison on x4652.<P>Thank you.
						<P>Ian Bettison.</div>";
		$users->setMessageContent("New user registration", $body);
		$users->write_user();
		echo "<main><div id='container-main'>";
		user_management();
		echo "</div>";
		echo "</main>";
	}elseif( $_GET["func"] == "manageUser"){
		$users = new user();
		echo "<main><div id='container-main'>";
		$users->manage_users("index.php?func=updateUsers");
		echo "</div>";
		echo "</main>";
	}elseif( $_GET["func"] == "managePermissions"){
		$users = new user();
		echo "<main><div id='container-main'>";
		$users->manage_permissions("index.php?func=updatePermissions");
		echo "</div>";
		echo "</main>";
	}elseif( $_GET["func"] == "updatePermissions"){
		$users = new user();
		if($_POST["Cancel"]!="Cancel") {
			$users->update_permissions();
		}
		echo "<main><div id='container-main'>";
		user_management();
		echo "</div>";
		echo "</main>";
	}elseif( $_GET["func"] == "manageUserTypes"){
		$users = new user();
		echo "<main><div id='container-main'>";
		$users->manage_types("index.php?func=updateTypes");
		echo "</div>";
		echo "</main>";
	}elseif( $_GET["func"] == "updateTypes"){
		$users = new user();
		if($_POST["Cancel"]!="Cancel") {
			$users->update_types();
		}
		echo "<main><div id='container-main'>";
		user_management();
		echo "</div>";
		echo "</main>";
	}elseif( $_GET["func"] == "updateUsers"){
		$users = new user();
		if($_POST["Cancel"]!="Cancel") {
			$users->update_users();
		}
		echo "<main><div id='container-main'>";
		user_management();
		echo "</div>";
		echo "</main>";
	}elseif( $_GET["func"] == "add_registration"){
		echo "<main><div id='container-main'>";
		add_registration();
		echo "</div>";
		echo "</main>";
	}elseif( $_GET["func"] == "upload_sheet"){
		echo "<main><div id='container-main'>";
		upload_excel();
		echo "</div>";
		echo "</main>";
	}elseif( $_GET["func"] == "uploadFile"){
		echo "<main><div id='container-main'>";
		uploadFile();
		echo "</div>";
		echo "</main>";
	}elseif( $_GET["func"] == "read_excel"){
		echo "<main><div id='container-main'>";
		readExcel();
		echo "</div>";
		echo "</main>";
	}elseif( $_GET["func"] == "new_container_template"){
		echo "<main><div id='container-main'>";
		new_container_template();
		echo "</div>";
		echo "</main>";
	}elseif( $_GET["func"] == "new_container_type"){
		echo "<main><div id='container-main'>";
		new_container_type("new");
		echo "</div>";
		echo "</main>";
	}elseif( $_GET["func"] == "edit_container_types"){
		echo "<main><div id='container-main'>";
		new_container_type("edit");
		echo "</div>";
		echo "</main>";
	}elseif( $_GET["func"] == "new_container"){
		echo "<main><div id='container-main'>";
		new_container("new");
		echo "</div>";
		echo "</main>";
	}elseif( $_GET["func"] == "edit_containers"){
		echo "<main><div id='container-main'>";
		new_container("edit");
		echo "</div>";
		echo "</main>";
	}elseif( $_GET["func"] == "new_customer"){
		echo "<main><div id='container-main'>";
		new_customer("new");
		echo "</div>";
		echo "</main>";
	}elseif( $_GET["func"] == "edit_customer"){
		echo "<main><div id='container-main'>";
		new_customer("edit");
		echo "</div>";
		echo "</main>";
	}elseif( $_GET["func"] == "accept_samples"){
		echo "<main><div id='container-main'>";
		accept_samples();
		echo "</div>";
		echo "</main>";
	}elseif( $_GET["func"] == "scan_in"){
		echo "<main><div id='container-main'>";
		scan_container('In');
		echo "</div>";
		echo "</main>";
	}elseif( $_GET["func"] == "scan_out"){
		echo "<main><div id='container-main'>";
		scan_container('Out');
		echo "</div>";
		echo "</main>";
	}elseif( $_GET["func"] == "new_barcode_settings"){
		echo "<main><div id='container-main'>";
		barcode_settings();
		echo "</div>";
		echo "</main>";
	}elseif( $_GET["func"] == "new_printer_template"){
		echo "<main><div id='container-main'>";
		printer_template();
		echo "</div>";
		echo "</main>";
	}elseif( $_GET["func"] == "print_barcodes"){
		echo "<main><div id='container-main'>";
		print_barcodes("new labels");
		echo "</div>";
		echo "</main>";
	}elseif( $_GET["func"] == "reprint_label"){
		echo "<main><div id='container-main'>";
		print_barcodes("re-print");
		echo "</div>";
		echo "</main>";
	}else{
		if($_GET["func"]   == "new") {
			unset($_SESSION["Patient_No"] );
		}
		show_homePage();
		
	}
	echo "<div id='sidepanel'>";	
	echo "</div>";
	echo "<div id='sidepanelIcon'>";
	echo "</div>";	
}

?>
<script>
	$(document).ready(function(){
		checkBarcode();
		 $( "#sidepanelIcon" ).click(function() {
			if($('#sidepanel').is(':visible')) {
				$('#sidepanel').hide("slide", {
					direction: "right"
				}, 500);
			}else{
				$('#sidepanel').show("slide", {
					direction: "right"
				}, 500);
				$.post(
				"ajax.php",
				{ 
				func: 'show_barcode'
				},
				function (data)
				{
					$('#sidepanel').html(data);
				}
				);
			}
		}); 
	})
	
function checkBarcode(){
	$.post(
		"ajax.php",
		{ 
			func: 'check_barcode'
		},
		function (data)
			{
				var json = $.parseJSON(data);
				if(json.found) {
					$('#sidepanelIcon').css('background-image', 'url(images/scanIconColour.png)');
					$('#barcode').val(json.value);
				}else{
					$('#sidepanelIcon').css('background-image', 'url(images/scanIconGray.png)');
				}
			}
	);
	setTimeout(checkBarcode, 5000);
}
		
</script>
<?php
//start of footer
echo "<div class='footer'>";
	echo "<div class='footer-left'>";
		echo "Newcastle Biomedicine Biobank<BR>";
		echo "Sample Tracking System";
	echo "</div>";
	echo "<div class='footer-right'></div>";
	
echo "</div>"; //end of footer div
echo "<div class='footer-copyright'>";
	echo "Copyright &copy 2012 Newcastle University";
	echo "</div>";
echo "</body>";
echo "</html>";
//lets remove the connection to the database here as it is connecting everytime anyway
dl::closedb();
?>