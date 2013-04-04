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
		echo "The container has been stored.";
	}else{
		echo "This container is not authorised to store the container type.";
	}
}

if($_POST["func"] == "show_barcode") {
	$barcode = dl::select("web_barcode");
	if(empty($barcode)) {
		echo "<P>There is no stored barcode</P>";
	}else{
		echo "<P>STORED BARCODE</P>";
		$field = new fields("Barcode", "text", "greyInput", "30", $barcode[0]["wb_barcode"], "barcode");
		echo "<BR><span class='form_field'>".$field->show_field()."</span><BR>";
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
		$retArr = array("found"=>false, "time"=>date('i',$time), "value"=>"");
	}else{
		$retArr = array("found"=>true, "time"=>date('i',$time), "value"=>$barcode[0]["wb_barcode"]);
	}
	echo json_encode($retArr);
}

if($_POST["func"] == "setMessageId"){
	$ajax_message = dl::select("sms_message_appointment", "ma_id = ". $_POST["mId"]);
	if(!empty($ajax_message)) {
		$show = dl::select("sms_messages", "m_id = ". $ajax_message[0]["message_id"] );
		echo "<B>".$show[0]["m_short_title"]."</B><BR><BR>";
		echo $show[0]["m_message"];
	}
}

if($_POST["func"] == "delMessageId"){
	$delete = dl::select("sms_message_appointment", "ma_id = ". $_POST["dId"]);
	dl::delete("sms_appointments", "a_id = ".$delete[0]["appointment_id"]);
	dl::delete("sms_sent_messages", "message_appointment_id = ". $_POST["dId"]);
	dl::delete("sms_message_appointment", "ma_id = ".$_POST["dId"]);
	appointment_body();
}

if($_POST["func"] == "show_message") {
	$ajax_message = dl::select("sms_messages", "m_short_title = '".$_POST["messVal"]."'");
	if(!empty($ajax_message)){
		echo "<fieldset><legend>Message to be sent</legend>";
		echo $ajax_message[0]["m_message"];
		echo "</fieldset>";
	}
}

if( $_POST["func"] == "delete_patient" ){
	$mobile = substr($_POST["del"],0,strpos($_POST["del"]," "));
	//get patient Id
	$patient = dl::select("sms_patient_contact_details", "pcd_mobile_number = '".$mobile."'");
	if(!empty($patient)){
		$appointments = dl::select("sms_message_appointment", "patient_id = ". $patient[0]["pcd_id"]);
		foreach($appointments as $appts) {
			dl::delete("sms_appointments", "a_id = ".$appts["appointment_id"]);
		}
		dl::delete("sms_message_appointment", "patient_id = ". $patient[0]["pcd_id"]);
		dl::delete("sms_patient_contact_details", "pcd_id = ".$patient[0]["pcd_id"]);
		echo "Deleted";
	}else{
		echo "Not Found";
	}
	
}

if( $_POST["func"] == "delete_message" ) {
	$messages = dl::select( "sms_messages", "m_short_title = '".$_POST["del"]."'" );
	//now need to check if the message is queued to send. If not we will delete it
	$sql = "select * from sms_messages as m
		join sms_message_appointment as ma on (m.m_id=ma.message_id)
		left join
		sms_sent_messages as sm on (ma.ma_id=sm.message_appointment_id)
		where
		sm.sm_timestamp is NULL 
		and ma.message_id = ".$messages[0]["m_id"];
	$messQueued = dl::getQuery($sql);
	if(empty($messQueued)) {
		//we can safely delete the message
		dl::delete("sms_messages", "m_id = ". $messages[0]["m_id"]);
		echo "Deleted Message...";
	}else{
		echo "The message is queued for delivery, not deleted...";
	}
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
		echo "New container type has been created";
	}else{
		dl::update("container_types", $combine, "ct_id = ",$_SESSION["containerTypeID"]);
		$newId = $_SESSION["containerTypeID"];
		echo "Container Type has been amended";
		dl::delete("allowed_containers", "types_id = ".$newId); //remove existing allowed containers as you do not know if a container has been deselected
	}
	if(!empty($_POST["allowedContainers"])) {
		foreach($_POST["allowedContainers"] as $container){
			$c_id = dl::select("container_types", "ct_name = '".$container."'");
			if(!empty($c_id)) {
				dl::insert("allowed_containers", array("allowed_ids"=>$c_id[0]["ct_id"], "types_id"=>$newId));
			}
		}
	}
}

if( $_POST["func"] == "new_container_template") {	
	$types = dl::select("container_types", "ct_name = '".$_POST["conType"]."'");
	$fields = array("container_types_id", "ct_no_rows", "ct_no_columns", "ct_row_type", "ct_column_type", "ct_template_name");
	$values = array($types[0]["ct_id"], $_POST["conRows"],$_POST["conCols"],$_POST["conRowType"], $_POST["conColType"], $_POST["tempName"]);
	$combine = array_combine($fields, $values);
	dl::insert("container_templates", $combine);
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
		$containers = dl::select("containers");
		foreach($containers as $c){
			$list[]= $c["c_container_name"]." - ".$c["c_container_barcode"];
		}
		echo json_encode(array("list"=>$list,"message"=>'Container was changed'));
	}
}

if( $_POST["func"] == "getContainerValues") {	
	$pos = strpos($_POST["container"], "-")+2; //add 2 to this as there is a space after the minus (-) sign
	$container = substr($_POST["container"], 0, $pos-3);
	$barcode = substr($_POST["container"], $pos, strlen($_POST["container"]));
	$templateId = dl::select("containers", "c_container_barcode = \"".$barcode."\"");
	$template_name = dl::select("container_templates", "ct_id = ". $templateId[0]["container_templates_id"]);
	$_SESSION["containerID"]=$templateId[0]["c_id"]; //this needs to be set as bothe the barcode and container description could be changed so is required to identify the record.
	echo json_encode(array("template_name"=>$template_name[0]["ct_template_name"], "barcode"=>$barcode, "container"=>$container));
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

if( $_POST["func"] == "getCustomerValues") {	
	$customerId = dl::select("customers", "c_name = \"".$_POST["customer"]."\"");
	$cust_id = $customerId[0]["c_id"];
	$business =$customerId[0]["c_type_of_business"];
	$registration = $customerId[0]["c_registration_no"];
	$contacts = dl::select("contact_details", "customers_id = ". $customerId[0]["c_id"]);
	if(!empty($contacts)) {
		foreach($contacts as $contact) {
			$type = dl::select("contact_types", "ct_id = ".$contact["contact_type_id"]);
			$contact_array[]= $type[0]["ct_type"].",".nl2br($contact["cd_detail"].",".$contact["cd_id"]);
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
	//registration_information
	$sample_types = dl::select("sample_types", "st_type='".$_POST["sample_type"]."'");
	$container_types = dl::select("container_types", "ct_name = '".$_POST["sample_container"]."'");
	$fields = array("sample_registrations_id", "sample_types_id", "containers_id", "ri_no_samples","ri_boxes", "ri_box_size", 
	"ri_temperature", "ri_catalogued", "ri_sample_information");
	$values = array($regId, $sample_types[0]["st_id"], $container_types[0]["ct_id"], $_POST["sample_no"], $_POST["boxes"], $_POST["sample_boxsize"], 
	$_POST["sample_temperature"], $_POST["samples_catalogued"], $_POST["sample_info"]);
	$combine = array_combine($fields, $values);
	dl::insert("registration_information", $combine);
	//registration_payments
	$fields = array("rp_payment_reference", "rp_payment_details", "sample_registrations_id");
	$values = array($_POST["contact_paymentRef"], $_POST["contact_paymentDetails"], $regId);
	$combine = array_combine($fields, $values);
	dl::insert("registration_payments", $combine);
	//biobank services
	$ids = $_POST["ids"];
	$services = $_POST["services"];
	$serviceCount = 1;
	foreach($ids as $id) {
		if($services[$id-1] == "true") {
			dl::insert("biobank_services", array("service_list_id"=>$id, "registration_information_id"=>$regId));
		}
	}
	echo "New registration created...";
}

if($_POST["func"] == "new_contact_details") {
	$contact_types = dl::select("contact_types", "ct_type = \"".$_POST["conType"]."\"");
	//add new details
	dl::insert("contact_details", array("contact_type_id"=>$contact_types[0]["ct_id"], "cd_detail"=>$_POST["conDetail"]));
	if($_POST["option"] == "new"){
		$details = dl::select("contact_details", "customers_id=0", "cd_id ASC"); //if no customer id is set then the details haven't been properly saved yet
	}else{
		$details = dl::select("contact_details", "customers_id=0 or customers_id =".$_POST["customer_id"], "cd_id ASC");
	}
	if(!empty($details)) {
		foreach($details as $detail) {
			$types = dl::select("contact_types", "ct_id=".$detail["contact_type_id"] );
			echo "<list-content><div id='content-container'><div id='content-header'>".$types[0]["ct_type"]."</div><div id='content' style='width:15em;'>".nl2br($detail["cd_detail"])."</div><div id='content'><a href='#' id='button".$detail["cd_id"]."' border='0'><img src='images/DeleteRed.png' /></a></div></div></list-content>";
		}
		echo "<br />";
		//now setup the delete function for the buttons passed back by the above jQuery
		foreach($details as $d) {
			?>
			<script type="text/javascript">
					$("#button<?php echo $d["cd_id"]?>").live("click", function (){
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
			$types = dl::select("contact_types", "ct_id=".$detail["contact_type_id"] );
			echo "<list-content><div id='content-container'><div id='content-header'>".$types[0]["ct_type"]."</div><div id='content' style='width:15em;'>".nl2br($detail["cd_detail"])."</div><div id='content'><a href='#' id='button".$detail["cd_id"]."' border='0'><img src='images/DeleteRed.png' /></a></div></div></list-content>";
		}
		echo "<br />";
	}
}
if($_POST["func"] == "save_contact_details") {
	if($_POST["option"] == "new"){
		dl::insert("customers", array("c_name"=>$_POST["conCust"], "c_type_of_business"=>$_POST["conBus"], "c_registration_no"=>$_POST["conReg"]));
		$cust_id = dl::getId();
		dl::update("contact_details", array("customers_id"=>$cust_id), "customers_id=0");
	}else{
		dl::update("customers", array("c_name"=>$_POST["conCust"], "c_type_of_business"=>$_POST["conBus"], "c_registration_no"=>$_POST["conReg"]), "c_id = ".$_POST["customer_id"]);
		dl::update("contact_details", array("customers_id"=>$_POST["customer_id"]), "customers_id=0");
	}
	echo "Customer Details have been saved...";
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
		echo "<div style='width:1em; font-size: 1.25em;text-align:center; padding:0.1em 0.25em; line-height: 1em;float:left;'></div>";
		for($i=1; $i<=$noCols; $i++){
			if($colType == "Alpha"){
				$content = chr(64+$i); //This will display the character string starting at 65 which is a capital (A)
				$alpha = "Column";
			}else{ 
				$alpha = "";
				$content = $i; //This will display the number;
			}
			echo "<div style='width:1em; font-size:1.25em; line-height: 1em; float:left; text-align:center;margin:2px 2px; padding: 0 0.25em 0 0.25em;'>$content</div>";
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
			echo "<div style='width:1em; font-size:1.25em; text-align: right; padding: 0.1em 0.25em; line-height: 1em; float:left;'>$content</div>";
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
					echo "<div id='$j-$k' style='width:1em; font-size:1.25em; line-height: 1em; float:left;margin:2px 2px; padding: 0.5em 0.25em 0.5em 0.25em; background-color: #efefef;' title='".$sampleBC[0]["s_number"]."'></div>";
					$stored = true;
				}else{
					echo "<div id='$j-$k' style='width:1em; font-size:1.25em; line-height: 1em; float:left;margin:2px 2px; padding: 0.5em 0.25em 0.5em 0.25em; background-color: #ccc;'></div>";
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
			echo "<BR /><BR />"; //end of columns
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
					$saved_sample_id = $sample[0]["s_id"];
					//there must be a sample_orphan record so delete it
					dl::delete("sample_orphans", "sample_id=".$sample[0]["s_id"]);
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
			}
			if($sample_update) { //The sample has been updated so save the container position/ location and location history
				dl::insert("container_positions", array("cp_row"=>substr($position,0, strpos($position,"-")), "cp_column"=>substr($position, strpos($position,"-")+1, strlen($position))));
				$container_position_id = dl::getId();
				$findContainer = dl::select("containers", "c_container_barcode = \"".$container_id."\"");
				dl::insert("container_locations", array("samples_id"=>$saved_sample_id, "containers_id"=>$findContainer[0]["c_id"], "container_positions_id"=>$container_position_id));
				dl::update("containers", array("locations_id"=>$findContainer[0]["c_id"]), "c_container_barcode = '".$barcode."'");
				$findContainer = dl::select("containers", "c_container_barcode = \"".$container_id."\"");
				$fields = array("locations_id", "lh_date", "lh_action");
				$values = array($findContainer[0]["c_id"], date('Y-m-d H:i:s'), "Added to container");
				$combine = array_combine($fields, $values);
				dl::insert("locations_history", $combine);
				$lh_id = dl::getId();
				$samples = dl::select("samples", "s_number = '".$barcode."' and samples_list_items_id = ".$sample_id);
				$s_id = $samples[0]["s_id"];
				dl::insert("location_history_samples", array("location_history_id"=>$lh_id, "samples_id"=>$s_id));
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

if($_POST["func"] == "Remove") {
	//search samples with the barcode
	$samples = dl::select("samples", "s_number = '".$_POST["barcode"]."'");
	//store the samples ID
	$s_id = $samples[0]["s_id"];
	//add a sample_orphans record for the removed sample
	dl::insert("sample_orphans", array("sample_id"=>$s_id));
	//get host container id
	$host = dl::select("containers", "c_container_barcode ='".$_POST["container_id"]."'");
	//add locations_history record
	dl::insert("locations_history", array("locations_id"=>$host[0]["c_id"], "lh_date"=>date('Y-m-d H:i:s'), "lh_action"=>"Removed from container"));
	$lh_id = dl::getId();
	dl::insert("location_history_samples", array("location_history_id"=>$lh_id, "samples_id"=>$s_id));
	$lhs_id = dl::getId();
	//if there is a note then save it
	if(!empty($_POST["note"])) {
		dl::insert("notes", array("note"=>$_POST["note"]));
		$n_id = dl::getId();
		dl::insert("location_history_notes", array("note_id"=>$n_id, "locations_history_id"=>$lhs_id));
	}
	//use the samples ID to find the container location
	$containerPos = dl::select("container_locations", "samples_id =".$s_id);
	//get the location position ID
	$containerPos_id = $containerPos[0]["container_positions_id"];
	//delete the container location
	dl::delete("container_locations", "samples_id =".$s_id);
	//delete the container position
	dl::delete("container_positions", "cp_id=". $containerPos_id);
	$container = dl::select("containers", "c_container_barcode = '".$_POST["barcode"]."'");
	dl::update("containers", array("locations_id"=>0), "c_id=".$container[0]["c_id"]);
	dl::update("samples", array("s_status"=>"Removed"), "s_id = ".$s_id);
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
		echo "<ul id='ui-state-default'>";
		foreach($samples as $sample) {
			//check samples table for this sample there may be a record but will show up if the status is 'Removed'
			$check_sample = dl::select("samples", "samples_list_items_id = ". $sample["sli_id"]." and s_status = 'Stored'");
			if(empty($check_sample)) {
				echo "<li id='sampleId-".$sample["sli_id"]."' style='list-style-type:none; padding:0.25em; height: 2em; margin: -0.5em 0; cursor:pointer; width:150%' class='ui-state-default'><div style='width: 100px; overflow:hidden; float:left; white-space:nowrap;'>".$sample["sli_customer_identifier"]."</div><div style='width: 230px; overflow:hidden; float:left; white-space:nowrap; margin-right:0.25em;'>".$sample["sli_description"]."</div><div style='width: 100px; overflow:hidden; float:left;'>".$sample["sli_pathology_no"]."</div><div style='width: 100px; overflow:hidden; float:left;'>".$sample["sli_SNOMED_code"]."</div></li><BR>";
				?>
					<script>
					$("#sampleId-<?php echo $sample["sli_id"]?>").selectable({
						selected: function (event, ui) {
						$("samples li").each(function(){
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
	$sql = "select * from contact_details join contact_types on (contact_type_id=ct_id) 
	where customers_id = ".$cust_id;
	$customerDetails = dl::getQuery($sql);
	if(!empty($customerDetails)) {
		foreach( $customerDetails as $cd ){
			switch($cd["ct_type"]) {
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
?>