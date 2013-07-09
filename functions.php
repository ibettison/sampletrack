<?php 
function check_permissions( $accessRight ){
	/*pass the accessRight to this function to check if the user has the right to perform the process
	the permissions passed are:
	View Reports
	View Patients
	Add Patients
	Add Users
	Manage Lists
	*/
	//serch the array for the access right passed
	$permissionValue = 0;
	//find the actual element
	foreach( $_SESSION["settings"] as $settings ) {
		if( $settings["permission_name"] == $accessRight) {
			$permissionValue = $settings["permission_value"];
		}
	}
	return $permissionValue;
}

function create_tabs( $tabnames, $selectedtab=0) {
	$tabs = new tabs($tabnames, "tabId", $selectedtab);
	echo "<div id='tabId'>";
		echo $tabs->create_tabs();
		foreach( $tabnames as $tnames ) {
			$tabs->show_content($tnames["link"], $tnames["func"]);
		}
	echo "</div>";
}


function dropdown_loginForm(){
	include("validation_rules.php");
	echo "<nav>";
	if($_SESSION["loggedIn"]=="") {
		echo "<ul id='login-ul'>";
			echo "<li id='login'>";
				echo "<a id='login-trigger' href='#'>";
					if($_SESSION["notConfirmed"]) {
						echo "Confirm Login <span> &#x25BC </span>";
					}else{
						echo "<img src='images/loginIconRed.png' align='middle' /> System Login <span> &#x25BC </span>";
					}
				echo "</a>";
				echo "<div id='login-content'>";
					if(!$_SESSION["notConfirmed"]) {	
						$form = new form( "login_form", "index.php?func=login");
						echo $form->show_form();			
						validation::validate_form("login_form");
						echo "<div id='inputs'>";
							$field = new fields("Email Address", "text", "greyInput", 30, "", "email_address", "type your email address");
							echo "<span>".$field->show_prompt()."</span>";
							echo "<span class='form_field'>".$field->show_field()."</span><BR />";
							validation::add_rules("email_address", $rule_email_required);
							$field = new fields("Password", "password", "greyInput", 30, "", "password");
							echo "<span>".$field->show_prompt()."</span>";
							echo "<span class='form_field'>".$field->show_field()."</span><BR /><br />";
							validation::add_rules("password", $rule_minlength6_required);
						echo "</div>";
					}else{
						$form = new form( "login_form", "index.php?func=confirm");
						echo $form->show_form();			
						validation::validate_form("login_form");
						echo "<div id='inputs'>";
							$field = new fields("Password", "password", "greyInput", 30, "", "password");
							echo "<span>".$field->show_prompt()."</span>";
							echo "<span class='form_field'>".$field->show_field()."</span><BR /><br />";
							validation::add_rules("password", $rule_minlength6_required);
							$field = new fields("New Password", "password", "greyInput", 30, "", "newPassword");
							echo "<span'>".$field->show_prompt()."</span>";
							echo "<span class='form_field'>".$field->show_field()."</span><BR /><br />";
							validation::add_rules("password", $rule_minlength6_required);
							$field = new fields("Confirm Password", "password", "greyInput", 30, "", "confirmPassword");
							echo "<span>".$field->show_prompt()."</span>";
							echo "<span class='form_field'>".$field->show_field()."</span><BR /><br />";
							validation::add_rules("password", $rule_minlength6_required);
						echo "</div>";
					}
					echo "<div id='actions'>";
							$button = new fields("submit Button", "submit", "bluebutton", 20, "Login", "submit");
							echo $button->show_field();
						echo "</div>";
					echo $form->close_form();
				echo "</div>";                    
			echo "</li>";
		echo "</ul>";
	}else{
		echo "<ul id='loggedin-ul'>";
			echo "<li id='loggedin'>";
				echo "<a id='loggedin-trigger' href='#'>";
					echo "<img src='images/loginIconGreen.png' align='middle' /> ". $_SESSION["user_name"]."<span> &#x25BC </span>";
				echo "</a>";
				echo "<div id='loggedin-content'>";
						if( check_permissions("Add Users") ) {
							echo "<a id='login-menu' href='index.php?func=administration'>";
							echo "User Administration";
							echo "</a>";
						}
						if( check_permissions("Manage Lists") ) {
							echo "<a id='login-menu' href='index.php?func=manage_lists'>";
							echo "List Management";
							echo "</a>";
						}
						if( check_permissions("View Audit") ) {
							echo "<a id='login-menu' href='index.php?func=audit_report'>";
							echo "View Audit Report";
							echo "</a>";
						}
						echo "<a id='login-menu' href='index.php?func=logoff'>";
							echo "Log Out";
						echo "</a>";
						echo "<div id='login-div'>";
							echo "Logged in ".$_SESSION["loggedInTime"];
						echo "</div>";
				echo "</div>";                    
			echo "</li>";
		echo "</ul>";
	}
echo "</nav>";
?>
<script>
$(document).ready(function(){
	$('#login-trigger').click(function(){
		$(this).next('#login-content').slideToggle();
		$(this).toggleClass('active');	
		$("#login-content").blur(function(){
				$("#login-content").fadeOut("slow", 0);
		});
		
		if ($(this).hasClass('active')) $(this).find('span').html(' &#x25B2;')
			else $(this).find('span').html(' &#x25BC;')
		});
		$('#loggedin-trigger').click(function(){
		$(this).next('#loggedin-content').slideToggle();
		$(this).toggleClass('active');					
		$("#loggedin-content").blur(function(){
				$("#loggedin-content").fadeOut("slow", 0);
		});
		if ($(this).hasClass('active')) $(this).find('span').html(' &#x25B2;')
			else $(this).find('span').html(' &#x25BC;')
		})
});
</script>
<?php
}

function show_frontpage() {
?>
<div class="navbar" gumby-fixed="top" id="nav">
    <div class="row">
      <a class="toggle" gumby-trigger="#nav > .row > ul" href="#"><i class="icon-menu"></i></a>
	  <h1 class="three columns logo ">
      <a href="#">
        <img src="img/sampletrack.png" gumby-retina />
      </a>
    </h1>
	  <div class="push_eight">
      <ul class="nine columns">
        <li>
          <a href="#">Login</a>
          <div class="dropdown">
            <ul>
               <li class="field" style="padding-left: 2em;"> <input id="email_address" class="xwide email input" type="email" placeholder="Email input" /></li>
				<li class="field" style="padding-left: 2em;"> <input id="password" class="xwide password input" type="password" placeholder="Password input" /></li>
            </ul>
			<div class="medium default btn icon-right entypo icon-user"><a id="login-trigger" href="#">Login</a></div>
          </div>
        </li>
      </ul>
	  </div>
    </div>
  </div>
  <script>
 $(document).ready(function(){
	$('#login-trigger').click(function(){		
		var func = "login";
			$.post(
				"ajax.php",
				{ func: func,
				email_address: $("#email_address").val(),
				password: $("#password").val(),
				},
				function (data)
				{
				$("#header-top").html(data);
				window.location.href = "index.php";
			});
	});
});
</script>
<?php
		echo "<front_page>";
			echo "<div class='row'>";
				echo "<div class='twelve columns'>";	
				echo "<div id='picture-wrapper'>";
					echo "<div id='picture-background1'>";
							echo "<H1>Newcastle Biomedicine Biobank<BR>Sample Tracking System</H1>";
							echo"<div id='text-holder'>";
								echo "<div id='text-display'>";
									echo "The Newcastle Biomedicine Biobank Sample Tracking System login screen allows a registered user to login and manage samples, record new sample registration requests, process sample withdrawals,
									manage deliveries, create and re-freeze or process transportation of Aliquots. The system will record the complete lifecycle of the sample, recording where it came from, its location, 
									any Aliquot creations and their storage locations; recording all requests and deliveries against all samples. Finally the system will record publication, invention events regarding samples that have been stored in the 
									Biobank.";
								echo "</div>";
							echo "</div>";
							/*echo "<BR><B>SCANNING</B><BR><BR>
							The SAMPLE TRACKING SYSTEM uses mobile technology to scan items into and out of the Newcastle Biobank. The system works in real time and stores the sample in containers. Containers can be anything, 
							from the room, the freezer, the shelf or the phial the sample resides in. All containers are linked so scanning a container identifies where it is and what it is automatically, you can even tell where it has been 
							and when it was moved. The system is centrally controlled by a built-in Audit system so every movement is monitored and can be traced back to date and time of the action and the individual performing the action.";
							*/
					echo "</div>";
					echo "<div id='picture-background2'>";
							echo "<H1>Newcastle Biomedicine Biobank<BR>Sample Tracking System</H1>";
							echo "<div id='image-holder'><img src='images/Group of iOS devices.png' /></div>";
							echo"<div id='text-holder'>";
								echo "<div id='text-display' style='padding-left: 18em;'>";
									echo "The Newcastle Biomedicine Biobank Sample Tracking System uses iOS technology to scan the sample and container barcodes. The app for the iPhone and iPad communicates
									with the tracking system and allows the incorporation of the scanned barcode into the web pages' barcode fields. A scanned container can be scanned into and out of another container with 
									every container being able to be stored within another container following logical controls, you can see that this is a simple yet intuitive way to manage the sample locations.";
								echo "</div>";
							echo "</div>";
							/*echo "<BR><B>SCANNING</B><BR><BR>
							The SAMPLE TRACKING SYSTEM uses mobile technology to scan items into and out of the Newcastle Biobank. The system works in real time and stores the sample in containers. Containers can be anything, 
							from the room, the freezer, the shelf or the phial the sample resides in. All containers are linked so scanning a container identifies where it is and what it is automatically, you can even tell where it has been 
							and when it was moved. The system is centrally controlled by a built-in Audit system so every movement is monitored and can be traced back to date and time of the action and the individual performing the action.";
							*/
					echo "</div>";
					echo "<div id='picture-background3'>";
							echo "<H1>Newcastle Biomedicine Biobank<BR>Sample Tracking System</H1>";
							echo"<div id='text-holder'>";
								echo "<div id='text-display'>";
									echo "The Newcastle Biomedicine Biobank Sample Tracking System has been developed by Newcastle University to manage their vast array of samples and is being used by the Newcastle Biobank
									which is a Nationally reknowned location, providing sample storage for a host of other Universities and Industries througout the North east of England. All aspects of sample registration and consent monitoring are included in the 
									system along with transferring samples to other locations and registering Aliquots against existing samples.";
								echo "</div>";
							echo "</div>";
							/*echo "<BR><B>SCANNING</B><BR><BR>
							The SAMPLE TRACKING SYSTEM uses mobile technology to scan items into and out of the Newcastle Biobank. The system works in real time and stores the sample in containers. Containers can be anything, 
							from the room, the freezer, the shelf or the phial the sample resides in. All containers are linked so scanning a container identifies where it is and what it is automatically, you can even tell where it has been 
							and when it was moved. The system is centrally controlled by a built-in Audit system so every movement is monitored and can be traced back to date and time of the action and the individual performing the action.";
							*/
					echo "</div>";
				echo "</div>";
				echo "<div id='leftArrow'></div>";
				echo "<div id='rightArrow'></div>";
			echo "</div>";
		echo "</div>"; 
		echo "</front_page>";
		?>
		<script>
		$(document).ready(function(){
			var clicks = 0
			clickRight(clicks);
			$("#leftArrow").click( function() {
				$("#picture-background3").show();
				$("#picture-background2").show();
				switch (clicks) {
					case 0:
						
						$("#picture-background1").animate({
							left: "-2600px"
						}, 800);
						$("#picture-background2").animate({
							left: "-1300px"
						}, 800);
						$("#picture-background3").animate({
							left: "0"
						}, 800);
						clicks=2;
					break;
					case 1:
						$("#picture-background1").animate({
							left: "0px"
						}, 400);
						$("#picture-background2").animate({
							left: "1300px"
						}, 400);
						$("#picture-background3").animate({
							left: "2600px"
						}, 400);
						clicks--;
						hidePicture("#picture-background3",0);
						hidePicture("#picture-background2",300);
					break;
					case 2:
						$("#picture-background1").animate({
							left: "-1300px"
						}, 400);
						$("#picture-background2").animate({
							left: "0"
						}, 400);
						$("#picture-background3").animate({
							left: "1300px"
						}, 400);
						clicks--
						
						hidePicture("#picture-background3", 400);
					break;
				}
			});
			$("#rightArrow").click( function() {
				switch (clicks) {
					case 2:
						$("#picture-background2").show();
						$("#picture-background3").show();
						$("#picture-background1").animate({
							left: "0"
						}, 800);
						$("#picture-background2").animate({
							left: "1300px"
						}, 800);
						$("#picture-background3").animate({
							left: "2600px"
						}, 800);
						clicks=0;
						hidePicture("#picture-background3", 400);
						hidePicture("#picture-background2", 800);
					break;
					case 1:
						$("#picture-background3").show();
						$("#picture-background1").animate({
							left: "-2600px"
						}, 400);
						$("#picture-background2").animate({
							left: "-1300px"
						}, 400);
						$("#picture-background3").animate({
							left: "0px"
						}, 400);
						clicks++;
					break;
					case 0:
						$("#picture-background2").show();
						$("#picture-background1").animate({
							left: "-1300px"
						}, 400);
						$("#picture-background2").animate({
							left: "0"
						}, 400);
						$("#picture-background3").animate({
							left: "1300"
						}, 400);
						clicks++;
						hidePicture("#picture-background3", 400);
					break;
				}
			});
		});
		
		function clickRight(clicks) {
			setTimeout(function(){
				switch (clicks) {
					case 2:
						$("#picture-background2").show();
						$("#picture-background3").show();
						$("#picture-background1").animate({
							left: "0"
						}, 800);
						$("#picture-background2").animate({
							left: "1300px"
						}, 800);
						$("#picture-background3").animate({
							left: "2600px"
						}, 800);
						clicks=0;
						hidePicture("#picture-background3", 400);
						hidePicture("#picture-background2", 800);
					break;
					case 1:
						$("#picture-background3").show();
						$("#picture-background1").animate({
							left: "-2600px"
						}, 400);
						$("#picture-background2").animate({
							left: "-1300px"
						}, 400);
						$("#picture-background3").animate({
							left: "0px"
						}, 400);
						clicks++;
					break;
					case 0:
						$("#picture-background2").show();
						$("#picture-background1").animate({
							left: "-1300px"
						}, 400);
						$("#picture-background2").animate({
							left: "0"
						}, 400);
						$("#picture-background3").animate({
							left: "1300"
						}, 400);
						clicks++;
						hidePicture("#picture-background3", 400);
					break;
				}
				clickRight(clicks);
			}, 20000);
		}
		
		function hidePicture(pic, delay) {
			setTimeout(function () {
				$(pic).hide();
			}, delay);
		}
		</script>
		<?php
}

function show_homePage(){
	echo "<div class='container'>";
	echo "<home_page>";
	echo "<form>";
	echo "<div class='row'>";
		echo "<fieldset class='six columns'>";
			echo "<legend>Recent Registration</legend>"; 
			echo "<div class='row'>";
				echo "<div class='one columns'><img src='images/registrations_icon.png'></div><p class='eleven columns'>Listed are the recent Registrations showing the expected delivery information</p>";
			echo "</div>";
			echo "<div class='row'>";
				echo "<div class='five columns titles'>Customer</div>";
				echo "<div class='four columns titles'>Expected Delivery</div>";
				echo "<div class='three columns titles'>No. of Samples</div>";
				echo "<hr class='line'>";
			echo "</div>";
			
			checkRegistrations();
		echo "</fieldset>";
		echo "<fieldset class='six columns'>";
			echo "<legend><div class='legend_colour'>Outstanding Lists</div></legend>"; 
			echo "<div class='row'>";
				echo "<div class='one columns'><img src='images/lists_icon.png'></div><p class='eleven columns'>The Samples list have been uploaded and are awaiting the sample delivery.</p>";
			echo "</div>";
			echo "<div class='row'>";
				echo "<div class='titles five columns'>Customer</div>";
				echo "<div class='titles four columns'>Date uploaded</div>";
			echo "<hr class='line'>";
			echo "</div>";
			
			checkLists();
		echo "</fieldset>";
	echo "</div>";
	echo "<div class='row'>";
		echo "<fieldset class='six columns'>";
			echo "<legend><div class='legend_colour'>Outstanding Requests</div></legend>"; 
			echo "<div class='row'>";
				echo "<div class='one columns'><img src='images/requests_icon.png'></div><p class='eleven columns'>Below is a list of the most recent requests for sample removals/creations.</p>";
			echo "</div>";
			echo "<div class='row'>";
				echo "<div class='titles five columns'>Customer</div>";
				echo "<div class='titles three columns'>Request made</div>";
				echo "<div class='titles four columns'>Destination</div>";
			echo "<hr class='line'>";
			echo "</div>";
			
			checkRequests();
		echo "</fieldset>";
		echo "<fieldset class=' six columns'>";
			echo "<legend><div class='legend_colour'>Recent Transfers</div></legend>"; 
			echo "<div class='row'>";
				echo "<div class='one columns'><img src='images/transfers_icon.png'></div><p class='eleven columns'>The most recent despatches to our customers. Select a transfer to edit its status.</p>";
			echo "</div>";
			echo "<div class='row'>";
				echo "<div class='titles five columns'>Customer</div>";
				echo "<div class='titles four columns'>Despatch Date</div>";
			echo "<hr class='line'>";
			echo "</div>";
			
			checkTransfers();
		echo "</fieldset>";
	echo "</div>";
	echo "</form>";
	echo "</home_page>";
	echo "</div>";
}

function checkRegistrations() {
	$sql = "select * from sample_registrations as sr
	join registration_information as ri on (sr.sr_id = ri.sample_registrations_id)
	join customers as c on (sr.customer_id=c.c_id)
	where
	sr_status = 'New' order by sr_expected_delivery ASC";
	$samples = dl::getQuery($sql);
	if(!empty($samples)) {
		$count = 0;
		
		foreach($samples as $sample){
			if(strlen($sample["c_name"]) >28 ) {
				$cust_name = substr($sample["c_name"],0,27)."...";
			}else{
				$cust_name = $sample["c_name"];
			}
			if($count < 3){
				echo "<div class='row twelve columns'><div class='printline five columns'>".$cust_name."</div><div class='printline four columns'>".date("d/m/Y", strtotime($sample["sr_expected_delivery"]))."</div><div class='printline three columns'>".$sample["ri_no_samples"]."</div></div>";	
			}
			$count++;
		}
		if($count == 1){
			echo "<p class='ten columns'>There is ".$count." recent registration.</p>";
		}else{
			echo "<p class='ten columns'>There are ".$count." recent registrations.</p>";
		}
		echo "<div class='medium pretty primary icon-left btn icon-pencil' id='m_registrations'><a href='#'>Manage Registrations</a></div>";
		?>
		<script>
		$(document).ready(function(){
			$('#m_registrations').click(function() {
				window.location.href = "index.php?func=manage_registrations";
			});
		});
		</script>
		<?php
	}else{
		echo "<p>There are no recent registrations.</p>";
	}
}

function checkLists() {
	$sql = "select * from samples_list 
	where
	sl_status = 'Outstanding' order by sl_date_uploaded DESC";
	$samples = dl::getQuery($sql);
	if(!empty($samples)) {
		$count = 0;
		foreach($samples as $sample){
			$customer = dl::select("customers", "c_id ='".$sample["customer_id"]."'");
			if(strlen($customer[0]["c_name"]) >28 ) {
				$cust_name = substr($customer[0]["c_name"],0,27)."...";
			}else{
				$cust_name = $customer[0]["c_name"];
			}
			if($count < 3){
				echo "<div class='row'><div class='printline five columns'>".$cust_name."</div><div class='printline seven columns'>".date("d/m/Y", strtotime($sample["sl_date_uploaded"]))."</div></div>";	
			}
			$count++;
		}
		if($count == 1){
			echo "<p>There is ".$count." outstanding list.</p>";
		}else{
			echo "<p>There are ".$count." outstanding lists.</p>";
		}
		echo "<div class='medium pretty primary icon-left btn icon-pencil' id='m_lists'><a href='#'>Manage Lists</a></div>";
		?>
		<script>
		$(document).ready(function(){
			$('#m_lists').click(function() {
				window.location.href = "index.php?func=outstanding_lists";
			});
		});
		</script>
		<?php
	}else{
		echo "<p>There are no outstanding lists.</p>";
	}
}

function checkRequests() {
	$sql = "select * from sample_registrations as sr
	join registration_information as ri on (sr.sr_id = ri.sample_registration_id)
	where
	sr_status = 'NEW' order by sr_expected_delivery DESC";
	$samples = dl::getQuery($sql);
	if(!empty($samples)) {
		$count = 0;
		foreach($samples as $sample){
			$customer = dl::select("customers", "c_id ='".$sample["customer_id"]."'");
			if(strlen($customer[0]["c_name"]) >28 ) {
				$cust_name = substr($customer[0]["c_name"],0,27)."...";
			}else{
				$cust_name = $customer[0]["c_name"];
			}
			if($count < 3){
				echo "<div class='row'><div class='printline five columns'>".$cust_name."</div><div class='printline seven columns'>".date("d/m/Y", strtotime($sample["sl_date_uploaded"]))."</div></div>";	
			}
			$count++;
		}
		if($count == 1){
			echo "<p>There is ".$count." outstanding list.</p>";
		}else{
			echo "<p>There are ".$count." outstanding lists.</p>";
		}
		echo "<div class='medium pretty primary icon-left btn icon-pencil' id='m_lists'><a href='#'>Manage Lists</a></div>";
		?>
		<script>
		$(document).ready(function(){
			$('#m_lists').click(function() {
				window.location.href = "index.php?func=outstanding_lists";
			});
		});
		</script>
		<?php
	}else{
		echo "<p>There are no recent requests.</p>";
	}
}

function checkTransfers() {
	$sql = "select * from sample_registrations as sr
	join registration_information as ri on (sr.sr_id = ri.sample_registration_id)
	where
	sr_status = 'NEW' order by sr_expected_delivery DESC";
	$samples = dl::getQuery($sql);
	if(!empty($samples)) {
		$count = 0;
		foreach($samples as $sample){
			$customer = dl::select("customers", "c_id ='".$sample["customer_id"]."'");
			if(strlen($customer[0]["c_name"]) >28 ) {
				$cust_name = substr($customer[0]["c_name"],0,27)."...";
			}else{
				$cust_name = $customer[0]["c_name"];
			}
			if($count < 3){
				echo "<div class='row'><div class='printline five columns'>".$cust_name."</div><div class='printline seven columns'>".date("d/m/Y", strtotime($sample["sl_date_uploaded"]))."</div></div>";	
			}
			$count++;
		}
		if($count == 1){
			echo "<p>There is ".$count." outstanding list.</p>";
		}else{
			echo "<p>There are ".$count." outstanding lists.</p>";
		}
		echo "<div class='medium pretty info icon-left btn icon-pencil' id='m_lists'><a href='#'>Manage Lists</a></div>";
		?>
		<script>
		$(document).ready(function(){
			$('#m_lists').click(function() {
				window.location.href = "index.php?func=outstanding_lists";
			});
		});
		</script>
		<?php
	}else{
		echo "<p>There are no recent transfers.</p>";
	}
}

function upload_excel() {
echo "<form ENCTYPE='multipart/form-data' id='upload_form' method='post' action='index.php?func=uploadFile'>";
echo "<data_entry>";
	echo "<div class='row'>";
		echo "<fieldset class='six columns'>";
			echo "<legend><div id='legend_colour'>Upload Spreadsheet</div></legend>"; 
			echo "<p>Enter the file to upload and extract the sample information from. Make sure there are no empty columns before the headings start.</p>";
		echo "<ul>";
			echo "<li class=' append field'>";
				echo "<input name='frmFile' id='file_selector' class='wide text input' type='file' />";
				echo "<div id='select_file'>";
					echo "<input id='file_value' class='xwide text input' type='text' placeholder='Choose the file to upload'' />";
					echo "<span id='link_index' class='adjoined'>Browse...</span>";
				echo "</div>";
			echo "</li>";
			echo "<li class='field'>";
			echo "<input id='excel_row' name='excel_row' class='wide text input' type='text' placeholder='Enter the excel title row no.'' />";
			echo "</li>";
			echo "<li class='default label'>Existing Customers</li>";
			echo "<li class='field'>";
				echo "<div class='picker'>";
					echo "<select id='customer' name='customer_list'>";
						$customers = dl::select("customers");
						foreach($customers as $c) {
							$custs[]= $c["c_name"];
						}
						echo "<option value='#' selected disabled>Select an existing customer...</option>";
						foreach($custs as $cust) {
							echo "<option>$cust</option>";
						}
					echo "</select>";
				echo "</div>";
			echo "</li>";
		echo "</ul>";
		?>
		<script>
		$("#file_selector").change( function(event, ui) { 
			$("#file_value").val($("#file_selector").val());
				
		});
		</script>
		<?php
			$button = new fields("submit Button", "submit", "bluebutton", 10, "Upload File","submit");
			echo $button->show_field();
		echo "</fieldset>";	
	echo "</div>";
	echo "</data_entry>";

echo "</form>";
}
function uploadFile(){
	if($_FILES['frmFile']['error'] == 0) {
		$filename = str_replace(" ", "_", basename($_FILES['frmFile']['name']));
		echo "<BR>Filename = ".$filename;
		$ext = substr($filename, strrpos($filename, '.') +1);
		$file = substr($filename, 0, strpos($filename,"."));
		$allowedExtensions = array("xls","xlsx","xlsm");
		$_SESSION["filename"] = array('name'=>$file.".".$ext);
		if(in_array($ext, $allowedExtensions)) {
			$newname = dirname(__FILE__).'/documents/'.$file.".".$ext;
			echo "<BR>".$newname, $_FILES['frmFile']['tmp_name'];
				if(move_uploaded_file($_FILES['frmFile']['tmp_name'], $newname)) {
					$_SESSION["upload"]="File uploaded";
					?>
					<script>
						window.location.href = "index.php?func=read_excel&file=<?php echo "documents/".$filename ?>&customer=<?php echo $_POST["customer_list"]?>&excel_row=<?php echo $_POST["excel_row"]?>";
					</script>
					<?php
					die();
					return($newname);
				}else{
					$_SESSION["upload"]="File not uploaded.";
				}
		}else{
			$_SESSION["upload"]= "Cannot upload this file type!!!";
		}
	}else{
		$_SESSION["upload"]= "No File uploaded.";
	}
	echo $_SESSION["upload"];
	return(false);
}

function readExcel() {
	require_once 'Classes/PHPExcel/IOFactory.php';
	$fileName = $_GET["file"];
	$customer = $_GET["customer"];
	$rowToRead = $_GET["excel_row"];
	//load the spreadsheet
	$objPHPExcel = PHPExcel_IOFactory::load($fileName); 
	$cell=0;
	while(true) {
			$rowValues = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($cell, $rowToRead)->getValue();
			if($rowValues == "") {
				break;
			}
			$rowVals[ ] = $rowValues; 
			$cell++;
	}
	?>
	<script>
	
	$(function() {
	 $( "#sortable, #sortable2").sortable({
		connectWith: ".connected",
		dropOnEmpty: true
	}).disableSelection();
	$('.connected').sortable({ cancel: 'li.heading' });
	});
	
	$(document).ready(function(){
	 $( "#sortable-deleted" ).sortable({
		connectWith: ".connected",
		dropOnEmpty: true,
		 update: function(event, ui) {
			$("#sortable-deleted").empty();
			var xOffset = 24;
			var yOffset = 24;
			// set the absolute postion of the poof animation <div>
			// uses e.pageX and e.pagY to get the cursor position
			// and offsets the poof animation from this point based on the xOffset and yOffset values set above
			$('#poof').css({
				left: event.pageX - xOffset + 'px',
				top: event.pageY - yOffset + 'px'
			}).show();
			$('#poof').sprite({
				fps:10, 
				no_of_frames: 5,
				on_last_frame: function(obj) {
					obj.spStop(true);
					obj.destroy();
				}
			});

			setTimeout("$('#poof').hide()", 500);
		}
	}).disableSelection();
	});

	</script>
	<?php 
	$rowCount=0;
	echo "<div class='row'>";
	echo "<H3>SPREADSHEET UPLOAD</H3>";
	echo "<div class='columns three'>";
		echo "<drag_and_drop>";
		echo "<ul id='sortable' class='connected' '>";
		echo "<li class='heading'>Match this...";
		foreach($rowVals as $row) {
			echo "<li class='info alert'>".$rowCount." ".$row."</li>";
			$rowCount++;
			if($rowCount == 11) {
				echo "</ul>";
				echo "</drag_and_drop>";
				echo "</div>";
				echo "<div class='columns three'>";
				echo "<drag_and_drop>";
				echo "<ul id='sortable2' class='connected' '>";
				echo "<li class='heading'>Additional information";
			}
			
		}
		echo "</ul>";
		echo "</drag_and_drop>";
	echo "</div>";

	echo "<div id='min-data' class='columns three'>";
		echo "<ul id='min-data-ul'>";
			echo "<li>With this...";
			echo "<li class='primary alert'> 0 CUSTOMER IDENTIFIER</li>";
			echo "<li class='primary alert'> 1 SAMPLE DESCRIPTION</li>";
			echo "<li class='primary alert'> 2 PATHOLOGY NUMBER</li>";
			echo "<li class='primary alert'> 3 DATE SAMPLE STORED</li>";
			echo "<li class='primary alert'> 4 SNOMED CODE</li>";
			echo "<li class='primary alert'> 5 SUBJECT GENDER</li>";
			echo "<li class='primary alert'> 6 SUBJECT AGE</li>";
			echo "<li class='primary alert'> 7 DISEASE STATE</li>";			
			echo "<li class='primary alert'> 8 SAMPLE STAGE</li>";
			echo "<li class='primary alert'> 9 STUDY NAME</li>";
			echo "<li class='primary alert'>10 ADULT OR PAEDIATRIC</li>";
		echo "</ul>";
		
		?>
	<script>
	$(document).ready(function() {
		$("#Process").click(function(event) {
			var func = "compare_spreadsheet";
			var listVals = [];
			var listAdditions=[];
			var listItems = $("#sortable li");
			listItems.each(function(idx, li) {
				listVals.push($(li).text());
			});
			listItems = $("#sortable2 li");
			listItems.each(function(idx, li) {
				listAdditions.push($(li).text());
			});
			$.post(
				"ajax.php",
				{ func: func,
					list: listVals,
					additions: listAdditions,
					customer: "<?php echo $customer ?>",
					excel_row: "<?php echo $rowToRead?>",
					filename: "<?php echo $fileName?>"
				},
				function (data)
				{
					$('#showMatching').html(data);
					$('#showMatching').delay(200).fadeOut(2000);
					$('#showMatching').show();
			});

		});
	})
	</script>
	<?php
	echo "</div>";
	echo "</div>";
	echo "<div class='row'>";
	echo "<div id='poof'></div>";
		echo "<ul id='sortable-deleted' class='connected'>";
		echo "</ul>";
		echo "<div id='blank' class='pretty medium primary btn icon-left icon-plus'><a href='#'>Add Blank</a></div> ";
		echo "<div id='Process' class='pretty medium primary btn icon-left icon-thumbs-up'><a href='#'>Process Spreadsheet</a></div><BR /><BR />";
	echo "<span id='showMatching'></span>";
	echo "</div>";
	
		?>
	<script>
	$("#blank").click(function (e) {
		e.preventDefault();
		var $li = $("<li class='info alert'/>").text("<SPACER>");
		$("#sortable").append($li);
		$("#sortable").sortable('refresh');
	});
	</script>
<?php
}

function display_menus() {
?>
<div class="navbar" id="nav">
    <div class="row">
      <a class="toggle" gumby-trigger="#nav > .row > ul" href="#"><i class="icon-menu"></i></a>
	  <h1 class="three columns logo ">
      <a href="index.php">
        <img src="img/sampletrack.png" gumby-retina />
      </a>
    </h1>
      <ul class="six columns">
        <li>
          <a href="#">Receipts</a>
          <div class="dropdown">
            <ul>
               <li><a href="index.php?func=add_registration">Add Registration</a></li>
				<li><a href="index.php?func=upload_sheet">Upload spreadsheet</a></li>
				<li><a href="index.php?func=accept_samples">Accept samples</a></li>
            </ul>
			
          </div>
        </li>
		<li>
          <a href="#">Requests</a>
          <div class="dropdown">
            <ul>
               <li><a href="index.php?func=add_registration">New Request</a></li>
				<li><a href="index.php?func=upload_sheet">Find Requests</a></li>
				<li><a href="index.php?func=accept_samples">Outstanding Requests</a></li>
				<li><a href="index.php?func=accept_samples">View transfer list</a></li>
				<li><a href="index.php?func=accept_samples">Find Creations</a></li>
            </ul>
			
          </div>
        </li>
		<li>
          <a href="#">New</a>
          <div class="dropdown">
            <ul>
               <li><a href="index.php?func=new_customer">New Customer</a></li>
				<li><a href="index.php?func=new_container">Container</a>
				<div class="dropdown">
					<ul>
							<li><a href="index.php?func=new_container">New Container</a></li>
							<li><a href="index.php?func=new_container_template">Container Template</a></li>
							<li><a href="index.php?func=new_container_type">Container Type</a></li>
					</ul>
				</div></li>
				<li><a href="#">Types</a>
				<div class="dropdown">
					<ul>
							<li><a href="index.php?func=type_request">Request Type</a></li>
							<li><a href="index.php?func=type_action">Action Type</a></li>
							<li><a href="index.php?func=type_sample">Sample Type</a></li>
							<li><a href="index.php?func=new_container_type">Container Type</a></li>
					</ul>
				</div></li>
				<li><a href="#">Printer</a>
				<div class="dropdown">
					<ul>
							<li><a href="index.php?func=new_printer_template">Print Template</a></li>
							<li><a href="index.php?func=new_barcode_settings">Barcode Settings</a></li>
					</ul>
				</div></li>
            </ul>
			
          </div>
        </li>
		<li>
          <a href="#">Edit</a>
          <div class="dropdown">
            <ul>
               <li><a href="index.php?func=edit_customer">Customers</a></li>
			   <li><a href="index.php?func=edit_containers">Containers</a>
			   <div class="dropdown">
					<ul>
							<li><a href="index.php?func=edit_containers">Edit Containers</a></li>
							<li><a href="index.php?func=edit_container_template">Edit Container Templates</a></li>
							<li><a href="index.php?func=edit_container_type">Edit Container Types</a></li>
					</ul>
				</div></li>
				<li><a href="#">Types</a>
				<div class="dropdown">
					<ul>
							<li><a href="index.php?func=edit_request_type">Request Types</a></li>
							<li><a href="index.php?func=edit_action_type">Action Types</a></li>
							<li><a href="index.php?func=edit_sample_type">Sample Types</a></li>
							<li><a href="index.php?func=edit_container_type">Container Types</a></li>
					</ul>
				</div></li>
            </ul>
          </div>
        </li>
		<li>
          <a href="#">Scan</a>
          <div class="dropdown">
            <ul>
               <li><a href="index.php?func=scan_in">Scan Container In</a></li>
			   <li><a href="index.php?func=scan_out">Scan Container Out</a></li>
            </ul>
          </div>
        </li>
		<li>
          <a href="#">Print Labels</a>
          <div class="dropdown">
            <ul>
               <li><a href="index.php?func=print_barcodes">Print Barcode Labels</a></li>
			   <li><a href="index.php?func=reprint_label">Re-print a label</a></li>
            </ul>
          </div>
        </li>
		<?php
		if(check_permissions("Add Users")){
		?>
		<li>
          <a href="#">Admin</a>
          <div class="dropdown">
            <ul>
               <li><a href="index.php?func=audit_report">Audit Report</a></li>
			   <li><a href="index.php?func=administration">User management</a></li>
            </ul>
          </div>
        </li>
		<?php 
		}
		?>
      </ul>
    </div>
  </div>
  <div id="logoffIcon"></div>
  <script>
 $(document).ready(function(){
	$("#logoffIcon").click( function(){
		window.location.href = "index.php?func=logoff";
	})
	$('#login-trigger').click(function(){
		var func = "login";
			$.post(
				"ajax.php",
				{ func: func,
				email_address: $("#email_address").val(),
				password: $("#password").val(),
				},
				function (data)
				{
				$("#header-top").html(data);
				window.location.href = "index.php";
			});
	});
});
</script>
<?php
/*echo "<div class='menuArea'>";
	echo "<menu>";
		echo "<div id='home'></div>";
		echo "<ul>";
			echo "<li id='heading3'>";
				echo "<a id='links3' href='#'>";
						echo "Add";
					echo "</a>";
				echo "<div id='menu-content3'>";
					echo "<a id='menu-item3' href='index.php?func=new_customer'>";
						echo "New customer";
					echo "</a>";
					echo "<HR><span>Container</span><BR>";
					echo "<a id='menu-item3' href='index.php?func=new_container'>";
						echo "New";
					echo "</a>";
					echo "<a id='menu-item3' href='index.php?func=new_container_template'>";
						echo "Template";
					echo "</a>";
					echo "<a id='menu-item3' href='index.php?func=new_container_type'>";
						echo "Type";
					echo "</a>";
					echo "<HR><span>Types</span><BR>";
					echo "<a id='menu-item3' href='index.php?func=type_request'>";
						echo "Request";
					echo "</a>";
					echo "<a id='menu-item3' href='index.php?func=type_action'>";
						echo "Action";
					echo "</a>";
					echo "<a id='menu-item3' href='index.php?func=type_sample'>";
						echo "Sample";
					echo "</a>";
					echo "<a id='menu-item3' href='index.php?func=new_container_type'>";
						echo "Container";
					echo "</a>";
					echo "<HR><span>Printer</span><BR>";
					echo "<a id='menu-item3' href='index.php?func=new_printer_template'>";
						echo "Print Template";
					echo "</a>";
					echo "<a id='menu-item3' href='index.php?func=new_barcode_settings'>";
						echo "Barcode Settings";
					echo "</a>";
				echo "</div>";
			echo "</li>";
		echo "</ul>";
		echo "<ul>";
			echo "<li id='heading4'>";
				echo "<a id='links4' href='#'>";
						echo "Edit";
					echo "</a>";
				echo "<div id='menu-content4'>";
					echo "<a id='menu-item4' href='index.php?func=edit_customer'>";
						echo "Customers";
					echo "</a>";
					echo "<a id='menu-item4' href='index.php?func=edit_containers'>";
						echo "Containers";
					echo "</a>";
					echo "<HR><span>Types</span><BR>";
					echo "<a id='menu-item4' href='index.php?func=edit_request_types'>";
						echo "Request";
					echo "</a>";
					echo "<a id='menu-item4' href='index.php?func=edit_action_types'>";
						echo "Action";
					echo "</a>";
					echo "<a id='menu-item4' href='index.php?func=edit_sample_types'>";
						echo "Sample";
					echo "</a>";
					echo "<a id='menu-item4' href='index.php?func=edit_container_types'>";
						echo "Container";
					echo "</a>";
				echo "</div>";
			echo "</li>";
		echo "</ul>";
		echo "<ul>";
			echo "<li id='heading5'>";
				echo "<a id='links5' href='#'>";
						echo "Scan";
					echo "</a>";
				echo "<div id='menu-content5'>";
					echo "<a id='menu-item5' href='index.php?func=scan_in'>";
						echo "Scan Container In";
					echo "</a>";
					echo "<a id='menu-item5' href='index.php?func=scan_out'>";
						echo "Scan Container Out";
					echo "</a>";
				echo "</div>";
			echo "</li>";
		echo "</ul>";
		echo "<ul>";
			echo "<li id='heading6'>";
				echo "<a id='links6' href='#'>";
						echo "Print Labels";
					echo "</a>";
				echo "<div id='menu-content6'>";
					echo "<a id='menu-item6' href='index.php?func=print_barcodes'>";
						echo "Print Barcode Labels";
					echo "</a>";
					echo "<a id='menu-item6' href='index.php?func=reprint_label'>";
						echo "Re-print a Label";
					echo "</a>";
				echo "</div>";
			echo "</li>";
		echo "</ul>";
	echo "</menu>";
	
	echo "</div>";
	echo "<form id='searchbox' action=''>";
    echo "<input id='search' type='text' size='40' placeholder='Search...'><div id='search_barcode'></div>";
	echo "</form>";
	$searches = array("Find Samples", "View container history","Find aliquots from sample","Customer Samples");
	echo "<div id='search_list' style='display:none;'>";
		echo "<div id='search_cancel'><img src='images/black-cancel.png' /></div>";
		$selection = new radio("","radio", "", "20", "" , "sampleSearch", "", "", $searches, "", "Verticle");
		echo "<span class='form_field'>".$selection->show_field()."</span><BR />";
		$field = new checkbox("Search additional information?", "checkbox", "", "40", "", "additional");
		echo "<span class='form_field'>".$field->show_field()."</span> Search additional information ?<BR /><BR/>";
		$button = new fields("submit Button", "submit", "blackbutton", 10, "Search","searchButton");
		echo $button->show_field();
	echo "</div>";
	?>
	<script>
	$(document).ready(function(){
		$('#home').click(function() {
			window.location.href = "index.php";
		});
		$('#links').click(function(){
			$(this).next('#menu-content').slideToggle();
			$(this).blur(function(){
				$("#menu-content").fadeOut("slow", 0);
			});
		});
		$('#links2').click(function(){
			$(this).next('#menu-content2').slideToggle();
			$(this).blur(function(){
				$("#menu-content2").fadeOut("slow", 0);
			});
		});
		$('#links3').click(function(){
			$(this).next('#menu-content3').slideToggle();
			$(this).blur(function(){
				$("#menu-content3").fadeOut("slow", 0);
			});
		});
		$('#links4').click(function(){
			$(this).next('#menu-content4').slideToggle();
			$(this).blur(function(){
				$("#menu-content4").fadeOut("slow", 0);
			});
		});
		$('#links5').click(function(){
			$(this).next('#menu-content5').slideToggle();
			$(this).blur(function(){
				$("#menu-content5").fadeOut("slow", 0);
			});
		});
		$('#links6').click(function(){
			$(this).next('#menu-content6').slideToggle();
			$(this).blur(function(){
				$("#menu-content6").fadeOut("slow", 0);
			});
		});
		$("#container").css("background-color", "#333");
		 $( "#search" ).click(function() {
				$('#search_list').show("slide", {
					direction: "up"
				}, 500);
			});
		$( "#search_cancel" ).click(function() {
			$('#search_list').hide("slide", {
				direction: "up"
			}, 500);
		});
		$("#searchButton").live("click", function () {
			var search_term = $("#search").val();
			var which_search = $("input:radio[name=sampleSearch][checked]").val();
			var additional = $("#additional").is(":checked");
			$('#search_list').hide("slide", {
				direction: "up"
			}, 500);
			$("#search").val("");
		});
		$("#search_barcode").live("click", function () {
			var barcode = $("#barcode").val();
			$("#search").val(barcode);
			$('#search_list').show("slide", {
					direction: "up"
				}, 500);			
		});
	});
	</script>
	<?php*/

}

function disp_tabs( $selected_tab ) {
	$tabnames = array(array("link"=>"tab1", "tabname"=>"Patient Details", "func"=>"add_patient"), array("link"=>"tab2", "tabname"=>"Appointments", "func"=>"show_appointments") );
	if(check_permissions("Add Users")) {
		array_push($tabnames, array("link"=>"tab7", "tabname"=>"User Management", "func"=>"user_management") );
	}
	if(check_permissions("Manage Lists")) {
		array_push($tabnames, array("link"=>"tab8", "tabname"=>"Lists", "func"=>"managelists") );
	}
	create_tabs( $tabnames,  $selected_tab);
}

function infoBar( $text ) {
	echo "<div class='infobar'>";
		echo "<div class='infoimg'>";
			echo "<div class='info'>".$text;
			echo "</div>";
		echo "</div>";
	echo "</div>";
}

function calcAge($dob) {
	$today = date('Y-m-d');
	$age = date("Y") - date("Y", strtotime($dob));
	if( date('m') < date("m", strtotime($dob)) ) {
		$age--;
	} 
	if( date('m') == date("m", strtotime($dob)) ) {
		if(date('d') < date("d", strtotime($dob)) ) {
			$age--;
		}
	}
	return($age);
}

function managelists() {
	if(!empty($_POST["listSelect"]) ) {
		$table 				   				= dl::select("sms_lists", "list_tablename = '".$_POST["listSelect"]."'" );
		$tableName 			   			= $table[0]["list_tablename"];
		$tableFieldId 		   				= $table[0]["list_field_id"];
		$tableFieldDescription 		= $table[0]["list_field_description"];
	}else{
		$tableName 			   			= "sms_notification_type";
		$tableFieldId 		  				= "nt_id";
		$tableFieldDescription 		= "nt_description";
	}
	$list = new editlist($tableName, $tableFieldId, $tableFieldDescription);
	$list->showtitle("del", "40"," Description","250", "searchHeader");
	$list->showlist("index.php?func=manage_lists");
	$list->showsubmit();
}

function editlists() {
	if( check_permissions("Manage Lists") ) {
		$list = new editlist( $_POST["hiddenTable"], $_POST["hiddenId"], $_POST["hiddenDescription"] );
		$list->addtolist();			
	}else{
		?>
		<script>
			alert('Invalid action attempted - you will now be logged out.');
			window.location.href = "index.php";
		</script>
		<?php
		die();
	}
}

function user_management() {
	if( check_permissions("Add Users") ) {
		$users= new user();
		if($_POST["manage_user"] == "Manage Users") {
			$users->manage_users( "index.php?func=updateUsers" );
		}elseif( $_POST["manage_types"] == "Manage User Types") {
			$users->manage_types( "index.php?func=updateTypes");
		}elseif( $_POST["manage_permission"] == "Manage Permissions") {
			$users->manage_permissions( "index.php?func=updatePermissions" );
		}else{
			$users->new_user("index.php?func=addUser");			
			$users->new_user_type("index.php?func=addType");	
			$users->new_permission("index.php?func=addPermission");
		}
	}else{
		?>
		<script>
			alert('Invalid action attempted - you will now be logged out.');
			window.location.href = "index.php";
		</script>
		<?php
		die();
	}
}

function validate_fieldName($index) {

?>
<script type="text/javascript">
	$(document).ready(function() {
		var func =  "validate_field";
		$("#<?php echo $index?>").keypress(function(event) {
			var key = String.fromCharCode(event.which);

			$.post(
				"confirm_update.php",
				{ func: func,
					fieldVal: $("#<?php echo $index?>").val()+key,
					key: key
				},
				function (data)
				{
					$('#field_confirm').html(data);
			});
		});
	})

</script>
<?php
}
function add_registration() {
	
	echo "<form>";
	echo "<data_entry>";
	echo "<div class='row'>";
		echo "<fieldset class='six columns'>";
		echo "<legend><div id='legend_colour'>New Registration</div></legend>";
		$customers =dl::select("customers");
		foreach($customers as $customer){
			$customer_list[] = $customer["c_name"];
		}
		//design the form
		echo "<ul>";
			echo "<li class='prepend field'>";
			echo "<span class='adjoined'><img src='library/images/date_picker.png'></span>";
			echo "<input id='contact_date' class='wide text input' type='text' placeholder='Contact Date'' />";
			echo "</li>";
			echo "<li class='default label'>Existing Customers</li>";
			echo "<li class='field'>";
				echo "<div class='picker'>";
					echo "<select id='customer'>";
					echo "<option value='#' selected disabled>Select an existing customer...</option>";
					foreach($customer_list as $cust) {
						echo "<option>$cust</option>";
					}
					echo "</select>";
				echo "</div>";
			echo "</li>";
			echo "<li class='field'>";
			echo "<input id='contact_name' class='wide text input' type='text' placeholder='Contact name'' />";
			echo "</li>";
			echo "<li class='field'>";
			echo "<input id='contact_email' class='wide text input' type='text' placeholder='Contact email'' />";
			echo "</li>";
			echo "<li class='field'>";
			echo "<input id='contact_phone' class='wide text input inputs' type='text' placeholder='Contact phone'' />";
			echo "</li>";
			echo "<li class='field'>";
			echo "<textarea id='required' class='input textarea' placeholder='Requirements' rows='2'></textarea>";
			echo "</li>";
			echo "<li class='prepend field'>";
			echo "<span class='adjoined'><img src='library/images/date_picker.png'></span>";
			echo "<input id='delivery_date' class='wide text input' type='text' placeholder='Expected delivery'' />";
			echo "</li>";
			echo "<li class='field'>";
			echo "<input id='contact_paymentRef' class='wide text input' type='text' placeholder='Payment Reference'' />";
			echo "</li>";
			echo "<li class='field'>";
			echo "<textarea id='contact_paymentDetails' class='input textarea' placeholder='Payment details' rows='2'></textarea>";
			echo "</li>";
		echo "</ul>";
		?>
			<script language="JavaScript">
							
			$(document).ready(function() {
				
				
				$("#contact_date").datepicker( {
				dateFormat: 		'dd/mm/yy',
				buttonImageOnly: 	true
				});
				$("#delivery_date").datepicker( {
				dateFormat: 		'dd/mm/yy',
				buttonImageOnly: 	true
				});
				
			});
			
		</script><?php
		$types = dl::select("sample_types");
		foreach($types as $type) {
			$sample_types[] = $type["st_type"]; 
		}
		$containers = dl::select("container_types");
		foreach($containers as $container) {
			$container_list[]= $container["ct_name"];
		}
		// the jQuery script checks for a click on the select graphic and then focuses to the field and the drop down box appears.
		?>
		<script type="text/javascript">
				$("#customer").change( function(event, ui) { 
					$('#contact_name').val("");
					$('#contact_email').val("");
					$('#contact_phone').val("");
				var func = "findCustomer";
				$.post(
					"ajax.php",
					{ func: func,
					customer: $("#customer").val()
					},
					function (data)
					{
					var json = $.parseJSON(data);
					$('#contact_name').val(json.ContactName);
					$('#contact_email').val(json.email);
					$('#contact_phone').val(json.Phone);
				});
		});
		</script>
		<?php
	
		echo "</fieldset>";

		echo "<fieldset class='six columns'>";
		echo "<legend><div id='legend_colour'>Sample Information</div></legend>";
		echo "<ul>";
			echo "<li class='field'>";
			echo "<input id='sample_no' class='wide text input' type='text' placeholder='Number of samples'' />";
			echo "</li>";
			echo "<li class='default label'>Sample Types</li>";
			echo "<li class='field'>";
				echo "<div class='picker'>";
					echo "<select id='sample_type'>";
					foreach($sample_types as $types) {
						echo "<option>$types</option>";
					}
					echo "</select>";
				echo "</div>";
			echo "</li>";
			echo "<li class='default label'>Containers</li>";
			echo "<li class='field'>";
				echo "<div class='picker'>";
					echo "<select id='sample_container'>";
					foreach($container_list as $containers) {
						echo "<option>$containers</option>";
					}
					echo "</select>";
				echo "</div>";
			echo "</li>";
			echo "<li class='field'>";
			echo "<label class='checkbox' for='boxes'>";
			echo "<input id='boxes' class='wide text input' type='checkbox'' />";
			echo "<span> </span> Stored in Boxes?";
			echo "</label>";
			echo "<input id='sample_boxsize' class='wide text input' type='text' placeholder='Box size'' />";
			echo "</li>";
			echo "<li class='default label'>Temperature</li>";
			echo "<li class='field'>";
				echo "<label class='radio' style='float: left;' for='sample_temperature1'>";
				echo "<input name='temperature' id='sample_temperature1' value='-20&degC' type='radio' />";
				echo "<span></span> -20&degC&nbsp";
				echo "</label>";
				echo "<label class='radio' style='float: left;' for='sample_temperature2'>";
				echo "<input name='temperature' id='sample_temperature2' value='-80&degC' type='radio' />";
				echo "<span></span> -80&degC&nbsp ";
				echo "</label>";
				echo "<label class='radio' style='float:left;' for='sample_temperature3'>";
				echo "<input name='temperature' id='sample_temperature3' value='-150&degC' type='radio' />";
				echo "<span></span> -150&degC ";
				echo "</label>";
			echo "</li>";
			echo "<li class='default label'>Samples Catalogued ?</li>";
			echo "<li class='field'>";
				echo "<label class='radio' style='float: left;' for='sample_catYes'>";
				echo "<input name='samples_catalogued' id='samples_catYes' value='Yes' type='radio' />";
				echo "<span></span> Yes&nbsp";
				echo "</label>";
				echo "<label class='radio' style='float: left;' for='sample_catNo'>";
				echo "<input name='samples_catalogued' id='samples_catNo' value='No' type='radio' />";
				echo "<span></span> No&nbsp ";
				echo "</label>";
			echo "</li>";
			echo "<li class='field'>";
			echo "<textarea id='sample_info' class='input textarea' placeholder='Sample Information' rows='2'></textarea>";
			echo "</li>";
			
		echo "</ul>";

		echo "<H3>Biobank Services</H3>";
		$services = dl::select("biobank_services_list", "", "bsl_id ASC");
		echo "<li class='field'>";
		foreach($services as $service) {
			echo "<label class='checkbox' style='float: left;' for='services".$service["bsl_id"]."'>";
			echo "<input id='services".$service["bsl_id"]."' class='wide text input' type='checkbox'' />";
			echo "<span> </span> ".$service["bsl_description"]."&nbsp";
			echo "</label>";
		}
		echo "</li>";
		
		echo "<BR /><BR /></fieldset>";
		echo "<div id='show_registration_message'></div>";
		echo "<div class='medium pretty primary btn icon-left entypo icon-install'><a id='newReg' href='#'>New Registration</a></div>";
		
	echo "</div>";
	?>
	<script>
		$(document).ready(function() {
			$("#newReg").click(function(event) {
				alert("posting");
				var func = "new_registration";
				var ids = [];
				var checked = [];
				<?php
				foreach($services as $service){
				?>
					$("#<?php echo "services".$service["bsl_id"]?>").each(function (){
						checked.push($(this).is(":checked"));
						ids.push("<?php echo $service["bsl_id"]?>");
					});
				<?php 
				}
				?>
				$.post(
					"ajax.php",
					{ func: func,
						contact_date: $("#contact_date").val(),
						contact_name: $("#contact_name").val(),
						contact_email: $("#contact_email").val(),
						contact_phone: $("#contact_phone").val(),
						customer: $("#customer").val(),
						required: $("#required").val(),
						delivery_date: $("#delivery_date").val(),
						contact_paymentRef: $("#contact_paymentRef").val(),
						contact_paymentDetails: $("#contact_paymentDetails").val(),
						sample_no: $("#sample_no").val(),
						sample_type: $("#sample_type").val(),
						sample_container: $("#sample_container").val(),
						boxes: $("#boxes").is(":checked"),
						sample_boxsize: $("#sample_boxsize").val(),
						sample_temperature: $("input:radio[name=temperature][checked]").val(),
						samples_catalogued: $("input:radio[name=samples_catalogued][checked]").val(),
						sample_info: $("#sample_info").val(),
						services: checked,
						ids: ids
					},
					function (data)
					{
						$('#show_registration_message').html(data);
						$('#show_registration_message').delay(200).fadeOut(2000);
						$('#show_registration_message').show();
						setTimeout(function() {
							window.location.href = "index.php?func=add_registration";
						}, 2000);
				});

			});
		})
	</script>
	<?php
	echo "<data_entry>";
	echo "</form>";
}


function new_container_template($option) {
	echo "<form>";
		echo "<data_entry>";
		echo "<div class='row'>";
			echo "<fieldset class='six columns'>";
			if($option == "new") {
			echo "<legend><div id='legend_colour'>New Container template</div></legend>";
				echo "<ul>";
					echo "<li class='field'>";
					echo "<input id='con_temp_name' name='con_temp_name' class='wide text input' type='text' placeholder='Template Name'' />";
					echo "</li>";
		}else{
			$templates = dl::select("container_templates");
			foreach($templates as $template) {
				$template_names[]= $template["ct_template_name"];
			}
			echo "<legend><div id='legend_colour'>Edit Container Template</div></legend>";
			echo "<li class='default label'>Template Name</li>";
			echo "<li class='field'>";
				echo "<div class='picker'>";
				echo "<select id='con_temp_name' name='con_temp_name'>";
				echo "<option value='#' selected disabled>Select a template...</option>";
				foreach($template_names as $t) {
					echo "<option>$t</option>";
				}
			echo "</select>";
			echo "</div>";
			echo "</li>";
			?>
			<script type="text/javascript">
					$("#con_temp_name").change( function(event, ui) { 
					var func = "getContainerTempValues";
					$.post(
						"ajax.php",
						{ func: func,
						con_temp_name: $("#con_temp_name").val()
						},
						function (data)
						{
							var json = $.parseJSON(data);
							var description = json.container_type;
							var rows = json.rows;
							var columns = json.columns;
							var row_type = json.row_type;
							var column_type = json.column_type;
							$('#container_type').val(description);
							$('#container_rows').val(rows);
							$('#container_columns').val(columns);
							$("#row_type").val(row_type);
							$("#column_type").val(column_type);
						});
					});
			</script>
			<?php
		}

					$containers = dl::select("container_types");
					foreach($containers as $container) {
						$container_names[]= $container["ct_name"];
					}
					echo "<li class='default label'>Container Type</li>";
					echo "<li class='field'>";
						echo "<div class='picker'>";
						echo "<select id='container_type' name='container_type'>";
					foreach($container_names as $con) {
						echo "<option>$con</option>";
					}
					echo "</select>";
					echo "</div>";
					echo "</li>";
					echo "<li class='field'>";
					echo "<input id='container_rows' name='container_rows' class='wide text input' type='text' placeholder='No. of Rows'' />";
					echo "</li>";
					echo "<li class='field'>";
					echo "<input id='container_columns' name='container_columns' class='wide text input' type='text' placeholder='No. of Columns'' />";
					echo "</li>";
					$list = array("","Alpha", "Numeric");
					echo "<li class='default label'>Row Type</li>";
					echo "<li class='field'>";
						echo "<div class='picker'>";
						echo "<select id='row_type' name='row_type'>";
						echo "<option value='#' disabled selected>Select the row type...</option>";
					foreach($list as $l) {
						echo "<option>$l</option>";
					}
					echo "</select>";
					echo "</div>";
					echo "</li>";
					echo "<li class='default label'>Column Type</li>";
					echo "<li class='field'>";
						echo "<div class='picker'>";
						echo "<select id='column_type' name='column_type'>";
						echo "<option value='#' disabled selected>Select the column type...</option>";
					foreach($list as $l) {
						echo "<option>$l</option>";
					}
					echo "</select>";
					echo "</div>";
					echo "</li>";
					echo "<div class='medium pretty primary icon-left btn icon-pencil' id='container_template'><a href='#'>Add Container Template</a></div>";
				echo "</ul>";
					echo "<div id='container_template_div'></div>";
			echo "</fieldset>";
		echo "</div>";
		echo "</data_entry>";
	echo "</form>";
		?>
		<script type="text/javascript">
		$("#container_template").click(function () {
			var func = "new_container_template";
			$.post(
				"ajax.php",
				{ func: func,
					tempName: $('#con_temp_name').val(),
					conType: $('#container_type').val(),
					conRows: $("#container_rows").val(),
					conCols: $("#container_columns").val(),
					conRowType: $('#row_type').val(),
					conColType: $('#column_type').val()
				},
				function (data)
				{
					$('#container_template_div').html(data);
					$('#con_temp_name').val("");
					$('#container_type').val("");
					$('#container_rows').val("");
					$('#container_columns').val("");
					$('#row_type').val("");
					$('#column_type').val("");
					$('#container_template_div').delay(200).fadeOut(2000);
					$('#container_template_div').show();
			});

		});
		</script>
		<?php
}

function new_container_type($option) {
	echo "<form>";
		echo "<data_entry>";
		echo "<div class='row'>";
			echo "<fieldset class='six columns'>";
		if($option == "new") {
			echo "<legend><div id='legend_colour'>New Container Type</div></legend>";
			echo "<li class='field'>";
			echo "<input id='con_type_name' name='con_type_name' class='wide text input' type='text' placeholder='Container Type Name'' />";
			echo "</li>";
		}else{
			$containers = dl::select("container_types", "", "ct_name ASC");
			foreach($containers as $container) {
				$container_names[]= $container["ct_name"];
			}
			echo "<legend><div id='legend_colour'>Edit Container Type</div></legend>";
			echo "<li class='default label'>Container Types</li>";
			echo "<li class='field'>";
				echo "<div class='picker'>";
				echo "<select id='con_type_name' name='con_type_name'>";
				echo "<option value='#' disabled selected>Select a container type...</option>";
				foreach($container_names as $c) {
					echo "<option>$c</option>";
				}
			echo "</select>";
			echo "</div><div id='delete_con_type' class='delete_this'></div>";
			echo "</li>";
			?>
			<script type="text/javascript">
					$("#con_type_name").change( function(event, ui) { 
					var func = "getContainerTypeValues";
					$.post(
						"ajax.php",
						{ func: func,
						con_type: $("#con_type_name").val()
						},
						function (data)
						{
							var json = $.parseJSON(data);
							var description = json.container_desc;
							var manufacturer = json.manufacturer;
							var allowed_containers = json.allowed_containers;
							$('#container_info').val(description);
							$('#con_type_manufacturer').val(manufacturer);
							$('#allowed_types').val(allowed_containers);
							$("#allowed_types").multiselect("refresh");
							$("#delete_con_type").show();
						});
					});
					
			</script>
			<?php
		}
		echo "<li class='field'>";
			echo "<textarea id='container_info' class='input textarea' placeholder='Container Description' rows='2'></textarea>";
		echo "</li>";
		echo "<li class='field'>";
		echo "<input id='con_type_manufacturer' name='con_type_manufacturer' class='wide text input' type='text' placeholder='Manufacturer'' />";
		echo "</li>";
		echo "<li class='default label'>Allowed Containers</li>";
		$type = dl::select("container_types","", "ct_name ASC");
		foreach($type as $t){
			$types[]= $t["ct_name"];
		}
		echo "<select class='multiselect' id='allowed_types' style='width:29em; height:1em;' name='allowed_types' multiple='multiple'>";
		foreach($types as $type){ 
			echo "<option>".$type."</option>";
		}
		echo "</select><br /><br />";
		?>
		<script>
		$(document).ready(function(){
			// choose either the full version
			$("#allowed_types").multiselect({
				selectedList: 3,
				hide: ['scale', 500]
			});
		});
		</script>

		<?php
		if($option == "new") {
			$buttonValue = "Add Container Type";
		}else{
			$buttonValue = "Edit Container Type";
		}
		echo "<div class='medium pretty primary icon-left btn icon-pencil' id='container_type'><a href='#'>$buttonValue</a></div>";
		echo "<div id='container_type_div'></div>";
	echo "</fieldset>";
	echo "</div>";
	echo "</data_entry>";
	echo "</form>";
	?>
		<script type="text/javascript">
				$("#container_type").click( function () {
					var func = "new_container_type";
					$.post(
						"ajax.php",
						{ func: func,
							option: '<?php echo $option ?>',
							typeName: $('#con_type_name').val(),
							conDescription: $('#container_info').val(),
							conManufacturer: $('#con_type_manufacturer').val(),
							allowedContainers: $('#allowed_types').val()
						},
						function (data)
						{
							$('#container_type_div').html(data);
							<?php if($option == "new") {?>
							$('#con_type_name').val("");
							<?php }else{?>
							$('#con_type_name').val("#");
							<?php } ?>
							$('#container_info').val("");
							$('#con_type_manufacturer').val("");
							$('#allowed_types').val("");
							$('#allowed_types').multiselect('refresh');
							$('#container_type_div').delay(200).fadeOut(2000);
							$('#container_type_div').show();
					});

				});
				$("#delete_con_type").click( function () {
					var func = "del_container_type";
					$.post(
						"ajax.php",
						{ func: func,
							typeName: $('#con_type_name').val(),
						},
						function (data)
						{
							$('#container_type_div').html(data);
							$('#con_type_name').val("#");
							$('#container_info').val("");
							$('#con_type_manufacturer').val("");
							$('#allowed_types').val("");
							$('#allowed_types').multiselect('refresh');
							$('#container_type_div').delay(200).fadeOut(2000);
							$('#container_type_div').show();
					});

				});
		</script>
		<?php
}

function new_container($function) {
	echo "<form>";
		echo "<data_entry>";
		echo "<div class='row'>";
			echo "<fieldset class='six columns'>";
				if($function == "new") {
					echo "<legend><div id='legend_colour'>New Container</div></legend>";
					echo "<li class='field'>";
					echo "<input id='con_name' name='con_name' class='wide text input' type='text' placeholder='Container Name'' />";
					echo "</li>";
				}else{
					echo "<li class='default label'>Container Name</li>";
					echo "<legend><div id='legend_colour'>Edit Containers</div></legend>";
					$container_names = dl::select("containers","", "c_container_name ASC");
					
					foreach($container_names as $cn) {
						$arrContainer[] = $cn["c_container_name"]; 
					}
					echo "<li class='field'>";
						echo "<div class='picker'>";
						echo "<select id='con_name' name='con_name'>";
						echo "<option value='#' disabled selected>Select a container</option>";
					foreach($arrContainer as $con) {
						echo "<option>$con</option>";
					}
					echo "</select>";
					echo "</div>";
					echo "</li>";
					?>
				<script type="text/javascript">

						$("#con_name").change( function(event, ui) { 
						var func = "getContainerValues";
						$.post(
							"ajax.php",
							{ func: func,
							container: $("#con_name").val()
							},
							function (data)
							{
								var json = $.parseJSON(data);
								var barcode = json.barcode;
								var template = json.template_name;
								var container = json.container;
								$('#template_type').val(template);
								$('#con_barcode').val(barcode);
							});
						});
				</script>
				<?php
				}
				$templates = dl::select("container_templates");
				foreach($templates as $template){
					$template_names[]= $template["ct_template_name"];
				}
				echo "<li class='default label'>Template Type</li>";
				echo "<li class='field'>";
					echo "<div class='picker'>";
					echo "<select id='template_type' name='template_type'>";
				foreach($template_names as $temps) {
					echo "<option>$temps</option>";
				}
				echo "</select>";
				echo "</div>";
				echo "</li>";
				echo "<li class='field append'>";
				echo "<input id='con_barcode' name='con_barcode' class='wide text input' type='text' placeholder='Container barcode'' />";
				echo "<span class='adjoined'><a href='#' id='getBarcode'>Get</a></span>";
				echo "</li>";
				if( $function == "new" ) {
					$buttonval = "Add New Container";
				}else{
					$buttonval = "Edit Container";
				}
				echo "<div class='medium pretty primary icon-left btn icon-pencil' id='container_new'><a href='#'>$buttonval</a></div>";
				echo "<div id='container_div'></div>";
			echo "</fieldset>";
		echo "</div>";
		echo "</data_entry>";
	echo "</form>";
	?>
	<script type="text/javascript">
		$("#getBarcode").click( function () {
			var barcode = $("#barcode").val();
			$("#con_barcode").val(barcode);	
		});
		$("#container_new").click( function () {
			var func = "new_container";
			$.post(
			"ajax.php",
			{ func: func,
				option: "<?php echo $function ?>",
				conName: $('#con_name').val(),
				conTemplate: $('#template_type').val(),
				conBarcode: $('#con_barcode').val()
			},
			function (data)
			{
				var json = $.parseJSON(data);
				var list = json.list;
				var message = json.message;
				$('#container_div').html(message);
				$("#con_name").val("");
				$('#template_type').val("");
				$('#con_barcode').val("");
				$('#container_div').delay(200).fadeOut(2000);
				$('#container_div').show();
			});
		});
	</script>
		<?php
}

function new_customer($option) {
	echo "<form>";
		echo "<data_entry>";
			echo "<div class='row'>";
				echo "<fieldset class='six columns'>";
					?>
					<script>
					globalValues = {};
					</script>
					<?php
					if($option == "new") {
						
						
						echo "<legend><div id='legend_colour'>New Customer</div></legend>";
						echo "<ul>";
						echo "<li class='field'>";
						echo "<input id='cust_name' name=cust_name' class='wide text input' type='text' placeholder='Customer name'' />";
						echo "</li>";
						
						?>
						<script>
						globalValues.customer_id = 0;
						</script>
						<?php
					}else{
						echo "<legend><div id='legend_colour'>Edit Customer</div></legend>";
						echo "<ul>";
						$customer_names = dl::select("customers");
						foreach($customer_names as $cn) {
							$arrCustomer[] = $cn["c_name"]; 
						}
						echo "<li class='default label'>Existing Customers</li>";
						echo "<li class='field'>";
							echo "<div class='picker'>";
							echo "<select id='cust_name' name='cust_name'>";
							echo "<option value='#' disabled selected>Select an existing customer...</option>";
						foreach($arrCustomer as $cust) {
							echo "<option>$cust</option>";
						}
						echo "</select>";
						echo "</div>";
						echo "</li>";
						?>
					<script type="text/javascript">
							$("#cust_name").change( function(event, ui) { 
							var func = "getCustomerValues";
							if($('#showContactDetails').is(':visible')) {
								$('#showContactDetails').hide();
							}
							$.post(
								"ajax.php",
								{ func: func,
								customer: $("#cust_name").val()
								},
								function (data)
								{	var json = $.parseJSON(data);
									globalValues.customer_id = json.customerId;
									var business = json.business;
									var registration = json.registration;
									var contacts = json.contacts;
									var html_show = "";
									var line="";
									if(jQuery.isEmptyObject(contacts) == false) {
										$.each(contacts, function(index, value) {
											var str = value;
											var pos = str.search(",");
											var type = str.substr(0,pos);
											var str2 = str.substr(pos+1,str.length);
											var pos = str2.search(",");
											var detail = str2.substr(0,pos);
											var id = str2.substr(pos+1, str2.length);
											html_show = "<list-content><div id='content-container'><div id='content-header'>"+type+"</div><div id='content' style='width:15em;'>"+detail+"</div><div id='content-del'><a href='#' id='button"+id+"' border='0'><img src='images/DeleteRed.png' /></a></div></div></list-content>";
											line = line.concat(html_show);
										});
										$('#showContactDetails').html(line);
										$.each(contacts, function(index, value) {
											var str = value;
											var pos = str.search(",");
											var type = str.substr(0,pos);
											var str2 = str.substr(pos+1,str.length);
											var pos = str2.search(",");
											var detail = str2.substr(0,pos);
											var id = str2.substr(pos+1, str2.length);
											$("#button"+id).click( function (){
												var func = "del_contact_details";
												$.post(
													"ajax.php",
													{ func: func,
														option: '<?php echo $option ?>',
														customer_id: globalValues.customer_id,
														conId: id
													},
													function (data)
													{
														$('#showContactDetails').html(data);
														$("#con_detail").val("");
														$('#contact_type').val("");
												});
											});
										});
										if($('#showContactDetails').is(':hidden')) {
											$('#showContactDetails').show("slide", {
												direction: "up"
											}, 500);
										}
									}
									
									$('#business').val(business);
									$('#cust_reg').val(registration);
									
									$("#con_detail").val("");
									$('#contact_type').val("");
								});
							});
					</script>
					<?php
					}
					echo "<li class='field'>";
					echo "<input id='business' name='business' class='wide text input' type='text' placeholder='Type of business'' />";
					echo "</li>";
					echo "<li class='field'>";
					echo "<input id='cust_reg' name='cust_reg' class='wide text input' type='text' placeholder='Registration No.'' />";
					echo "</li>";
					echo "<h3>Contact details</h3>";
					echo "<div id='showContactDetails'></div>";
					echo "<li class='default label'>Contact Type</li>";
					$contact_types = dl::select("contact_types");
					foreach($contact_types as $ct){
						$type_names[]= $ct["ct_type"];
					}
					echo "<li class='field'>";
						echo "<div class='picker'>";
						echo "<select id='contact_type' name='contact_type'>";
						foreach($type_names as $tn) {
							echo "<option>$tn</option>";
						}
					echo "</select>";
					echo "</div>";
					echo "</li>";
					// the jQuery script checks for a click on the select graphic and then focuses to the field and the drop down box appears.
					echo "<li class='field'>";
						echo "<textarea id='con_detail' class='input textarea' placeholder='Contact detail' rows='1'></textarea>";
					echo "</li>";
					echo "<div class='small pretty info icon-left btn icon-plus' id='add_details'><a href='#'>Add Details</a></div>";
					echo "<hr id='hr_line'>";
					echo "<div class='medium pretty primary icon-left btn icon-check' id='save_customer'><a href='#'>Save Customer</a></div>";
					echo "<div id='showContact_div'></div>";
					echo "</ul>";
				echo "</fieldset>";	
			echo "</div>";
		echo "</data_entry>";
	echo "</form>";
	?>
		<script type="text/javascript">
				$("#add_details").click( function (){
					var func = "new_contact_details";
					$.post(
						"ajax.php",
						{ func: func,
							option: '<?php $option ?>',
							customer_id: globalValues.customer_id,
							conType: $('#contact_type').val(),
							conDetail: $('#con_detail').val()
						},
						function (data)
						{
							if($('#showContactDetails').is(':hidden')) {
								$('#showContactDetails').show("slide", {
									direction: "up"
								}, 500);
							};
							$('#showContactDetails').html(data);
							$('#showContact_div').html("Details added/changed, 'Save Customer' will add the record.");
							$("#con_detail").val("");
							$('#contact_type').val("");
					});
				});
				$("#save_customer").click( function (){
					var func = "save_contact_details";
					$.post(
						"ajax.php",
						{ func: func,
							option: '<?php echo $option ?>',
							customer_id: globalValues.customer_id,
							conCust: $('#cust_name').val(),
							conBus: $('#business').val(),
							conReg: $('#cust_reg').val()
						},
						function (data)
						{
							$('#showContact_div').html(data);
							$("#cust_name").val("");
							$('#business').val("");
							$("#cust_reg").val("");
							$('#showContact_div').delay(200).fadeOut(2000);
							$('#showContact_div').show();
							if($('#showContactDetails').is(':visible')) {
								$('#showContactDetails').hide("slide", {
									direction: "up"
								}, 500);
								$('#showContactDetails').show();
							};
					});
				});
		</script>
	<?php
}

function accept_samples(){
	echo "<form>";
	echo "<data_entry>";
	echo "<div class='row'>";
	echo "<fieldset class='six columns'>";
		echo "<legend><div id='legend_colour'>Sample Information</div></legend>";
		echo "<ul>";
			echo "<li class='field append' id='getContainerBarcode' style='cursor: pointer;'>";
			echo "<input id='container_bc' name='container_bc' class='wide text input' type='text' placeholder='Container Barcode'' />";
			echo "<span class='adjoined'>Get</span>";
			echo "</li>";

			$sql = "select * from samples_list as sl 
				join customers as c on (sl.customer_id=c.c_id) where sl_status = 'Outstanding' order by sl_date_uploaded ASC
			";
			$samples_list = dl::getQuery($sql);
			if(!empty($samples_list)) {
				foreach($samples_list as $list) {
					$lists[]= $list["c_name"]." ".$list["sl_date_uploaded"];
				}
			}
			echo "<li class='field'>";
				echo "<div class='picker'>";
				echo "<select id='sample_listing' name='sample_listing'>";
				echo "<option value='#' disabled selected>Select a list to catalogue...</option>";
					foreach($lists as $list) {
						echo "<option>$list</option>";
					}
				echo "</select>";
			echo "</div>";
			echo "</li>";
			echo "<div id='show_samples'></div>";
			echo "<li class='field'>";
				echo "<label class='checkbox' for='uncat'>";
					echo "<input id='uncat' class='wide text input' type='checkbox'' />";
					echo "<span> </span> Save Uncatalogued ?";
				echo "</label>";
			echo "</li>";
			
			echo "(Select a samples list and it will be associated with the selected container.)<BR>";
			echo "<div class='medium pretty primary icon-left btn icon-check' id='save_samples'><a href='#'>Save Sample Locations</a></div>";
			
		echo "</ul>";
			echo "<div id='showContact_div'></div>";
	echo "</fieldset>";
		
		echo "<div style='width:50%; float:right;'>";
			echo "<div id='container_details'></div>";
		echo "</div>";
		echo "</div>";
	
	echo "<div id='dialog1' style='display: none; font-size:1em;'>";
		echo "Scan the sample container into the container location:<BR/><BR/>";
		echo "<ul>";
			echo "<li id='getBarcode' class='field append' style='cursor: pointer;'>";
			echo "<input id='stored_bc' name='stored_bc' class='wide text input' type='text' placeholder='Click Get to add the scanned barcode'' />";
			echo "<span class='adjoined'>Get</span>";
			echo "</li>";
		echo "</ul>";
		echo "<div id='sampleBarcode'></div>";
	echo "</div>";
	echo "<div id='dialog2' style='display: none; font-size:1em;'>";
		echo "Scan the barcode and select 'Remove' to remove the sample from the container location:<BR/><BR/>";
		echo "<ul>";
			echo "<li id='getRemBarcode' class='field append' style='cursor: pointer;'>";
			echo "<input id='rem_stored_bc' name='rem_stored_bc' class='wide text input' type='text' placeholder='Click Get to add the scanned barcode'' />";
			echo "<span class='adjoined'>Get</span>";
			echo "</li>";
			echo "<li class='field'>";
				echo "<textarea id='rem_note' class='input textarea' placeholder='Removal Note' rows='2'></textarea>";
			echo "</li>";
		echo "</ul>";
	echo "</div>";
	echo "</div>";
	echo "</data_entry>";
	echo "</form>";
	
	?>
	<script type="text/javascript">
	$(document).ready(function() {
		$("#sample_listing").change( function(event, ui) { 
			$.post(
				"ajax.php",
				{ func: "display_samples",
					sampleVal: $("#sample_listing").val()
				},
				function (data)
				{
					$('#show_samples').html(data);
					$("#show_samples").css({
						overflow: "scroll",
						height: "25em",
						width:	"30em"
					});
				}
			);
		});
		$("#getContainerBarcode").click( function () {
			var barcode = $("#barcode").val();
			$("#container_bc").val(barcode);	
			var func = "getContainerDetails";
			$.post(
				"ajax.php",
				{ func: func,
					conBarcode: barcode
				},
				function (data)
				{
					$('#container_details').html(data);
				});
		});
		$("#getBarcode").click( function () {
			var sBarcode = $("#barcode").val();
			$("#stored_bc").val(sBarcode);	
			var func = "checkSampleContainer";
			$.post(
				"ajax.php",
				{ func: func,
					conBarcode: $("#stored_bc").val()
				},
				function (data)
				{
					$('#sampleBarcode').html(data);
				});
		});
		$("#getRemBarcode").click( function () {
			var sBarcode = $("#barcode").val();
			$("#rem_stored_bc").val(sBarcode);	
			var func = "checkSampleContainer";
			$.post(
				"ajax.php",
				{ func: func,
					conBarcode: $("#stored_bc").val()
				},
				function (data)
				{
					$('#sampleBarcode').html(data);
				});
		});
		
		
		$("#save_samples").click(function() { 
			$.post(
				"ajax.php",
				{ func: "save_sample_locations",
					uncatalogued: $("#uncat").is(":checked"),
					container: $("#container_bc").val(),
					sampleVal: $("#sample_listing").val()
				},
				function (data)
				{
					$('#showContact_div').html(data);
				}
			);
		});
	});
	
	</script>
	<?php
}

function scan_container($action) {
	echo "<form>";
		echo "<div class='row'>";
			echo "<data_entry>";
				echo "<fieldset class='six columns'>";
					echo "<legend><div id='legend_colour'>Scan Containers</div></legend>";
						echo "<li class='default label'>Container 1</li>";
						echo "<li id='container_move' class='field append' style='cursor: pointer;'>";
						echo "<input id='getBC1' name='getBC1' class='wide text input' type='text' placeholder='Place this container'' />";
						echo "<span class='adjoined'>Get</span>";
						echo "</li>";
						echo "<div id='showContainer1'></div>";
						echo "<li class='default label'>Container 2</li>";
						echo "<li id='container_to' class='field append' style='cursor: pointer;'>";
						echo "<input id='getBC2' name='getBC2' class='wide text input' type='text' placeholder='Into this container'' />";
						echo "<span class='adjoined'>Get</span>";
						echo "</li>";
						echo "<div id='showContainer2'></div>";
						echo "<div class='medium pretty primary icon-left btn icon-pencil' id='store_con'><a href='#'>Store Container</a></div>";
						echo "<div id='containerMessage'></div>";
				echo "</fieldset>";
			echo "</data_entry>";
		echo "</div>";
	echo "</form>";
	?>
	<script>
	$(document).ready(function() {
		$("#container_move").click( function () {
			var sBarcode = $("#barcode").val();
			$("#getBC1").val(sBarcode);	
			var func = "checkMoveContainer";
			$.post(
				"ajax.php",
				{ func: func,
					conBarcode: sBarcode
				},
				function (data)
				{
					$('#showContainer1').html(data);
					$('#showContainer1').delay(200).fadeOut(2000);
					$('#showContainer1').show();
				});
		});
		$("#container_to").click( function () {
			var sBarcode = $("#barcode").val();
			$("#getBC2").val(sBarcode);	
			var func = "checkToContainer";
			$.post(
				"ajax.php",
				{ func: func,
					conBarcode: sBarcode
				},
				function (data)
				{
					$('#showContainer2').html(data);
					$('#showContainer2').delay(200).fadeOut(2000);
					$('#showContainer2').show();
				});
		});
		$("#store_con").click( function () {
			var container1 = $("#getBC1").val();
			var container2 = $("#getBC2").val();
			var func = "moveContainer";
			$.post(
				"ajax.php",
				{ func: func,
					container1: container1,
					container2: container2
				},
				function (data)
				{
					$('#showContainer2').html(data);
					$('#showContainer2').delay(5000).fadeOut(5000);
					$('#showContainer2').show();
					$("#container_move").val("");	
					$("#container_to").val("");
					
				});
		});
	})
	</script>
	<?php
}

function barcode_settings() {
	echo "<fieldset>";
		echo "<legend><div id='legend_colour'>Barcode Settings</div></legend>";
			$field = new fields("Barcode Prefix", "text", "greyInput", "15", "", "bc_prefix");
			echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
			echo "<span class='form_field'>".$field->show_field()."</span><BR>";

			$field = new fields("Barcode Number", "text", "greyInput", "25", "", "bc_number");
			echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
			echo "<span class='form_field'>".$field->show_field()."</span><BR>";
			
			$field = new fields("Barcode Suffix", "text", "greyInput", "15", "", "bc_suffix");
			echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
			echo "<span class='form_field'>".$field->show_field()."</span><BR>";
			
			$field = new fields("Settings Name", "text", "greyInput", "45", "", "settings_name");
			echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
			echo "<span class='form_field'>".$field->show_field()."</span><BR>";

			$button = new fields("submit Button", "submit", "bluebutton", 10, "Save Settings","save_settings");
			echo $button->show_field();
			echo "<div id='barcodeSettingsMessage'></div>";
	echo "</fieldset>";
	?>
	<script>
	$(document).ready(function() {
		$("#save_settings").live("click", function () {
			var func = "saveBarcodeSettings";
			$.post(
				"ajax.php",
				{ func: func,
					bcPrefix: $("#bc_prefix").val(),
					bcNumber: $("#bc_number").val(),
					bcSuffix: $("#bc_suffix").val(),
					bcName: $("#settings_name").val()
				},
				function (data)
				{
					$('#barcodeSettingsMessage').html(data);
					$('#barcodeSettingsMessage').delay(200).fadeOut(2000);
					$('#barcodeSettingsMessage').show();
				});
		});
	})
	</script>
	<?php
}

function printer_template() {
	echo "<fieldset>";
		echo "<legend><div id='legend_colour'>Create Print Template</div></legend>";
			$field = new fields("Position-x", "text", "greyInput", "5", "", "posx");
			echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
			echo "<span class='form_field'>".$field->show_field()."</span>";

			$field = new fields("Position-y", "text", "greyInput", "5", "", "posy");
			echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
			echo "<span class='form_field'>".$field->show_field()."</span><BR>";
			
			$field = new fields("Label Height (mm)", "text", "greyInput", "5", "", "l_height");
			echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
			echo "<span class='form_field'>".$field->show_field()."</span>";
			
			$field = new fields("Label Width (mm)", "text", "greyInput", "5", "", "l_width");
			echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
			echo "<span class='form_field'>".$field->show_field()."</span><BR>";
			
			$field = new fields("Barcode Height", "text", "greyInput", "5", "", "bc_height");
			echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
			echo "<span class='form_field'>".$field->show_field()."</span>";
			
			$field = new fields("Barcode Width", "text", "greyInput", "5", "", "bc_width");
			echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
			echo "<span class='form_field'>".$field->show_field()."</span><BR>";
			
			$types = dl::select("print_barcode_types");
			foreach($types as $type) {
				$type_names[]= $type["pbt_name"];
			}
			
			$field = new selection("Barcode Type", "text", "greyInput", "50", "", "bc_type", $type_names, "", "0");
			echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
			echo "<span class='form_field'>".$field->show_field()."</span><span class='greyInputSelect' id='bcTypeClick'><img class='gi-img' src='images/dropdown.png'></span><BR />";
			
			$settings = dl::select("print_barcode_settings");
			foreach($settings as $setting) {
				$setting_names[]= $setting["pbs_name"];
			}
			
			$field = new selection("Barcode Settings", "text", "greyInput", "50", "", "bc_settings", $setting_names, "", "0");
			echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
			echo "<span class='form_field'>".$field->show_field()."</span><span class='greyInputSelect' id='bcSettingsClick'><img class='gi-img' src='images/dropdown.png'></span><BR />";
			
			$field = new fields("Template Name", "text", "greyInput", "50", "", "print_template_name");
			echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
			echo "<span class='form_field'>".$field->show_field()."</span><BR>";
			
			$field = new checkbox("Readable Label?", "checkbox", "greyInput", "40", "", "readLabel", "");
			echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
			echo "<span class='form_field'>".$field->show_field()."</span> (A label shown under the barcode)<BR>";

			$button = new fields("submit Button", "submit", "bluebutton", 10, "Save Template","save_template");
			echo $button->show_field();
			echo "<div id='barcodeMessage'></div>";
	echo "</fieldset>";
	?>
	<script>
	$(document).ready(function() {
		$("#bcTypeClick").live("click", function () {
			$("#bc_type").focus();	
		});
		$("#bcSettingsClick").live("click", function () {
					$("#bc_settings").focus();	
				});
		$("#save_template").live("click", function () {
			var func = "saveBarcodeTemplate";
			$.post(
				"ajax.php",
				{ func: func,
					posx: $("#posx").val(),
					posy: $("#posy").val(),
					lHeight: $("#l_height").val(),
					lWidth: $("#l_width").val(),
					bcHeight: $("#bc_height").val(),
					bcWidth: $("#bc_width").val(),
					bcType: $("#bc_type").val(),
					bcSettings: $("#bc_settings").val(),
					bcTempName: $("#print_template_name").val(),
					readLabel: $("#readLabel").is(":checked")
				},
				function (data)
				{
					$('#barcodeMessage').html(data);
					/*$('#barcodeMessage').delay(200).fadeOut(2000);
					$('#barcodeMessage').show();*/
				});
		});
	})
	</script>
	<?php
}

function print_barcodes($option) {
	echo "<fieldset>";
		if($option == "re-print") {
			echo "<legend><div id='legend_colour'>Re-print labels</div></legend>";
		}else{
			echo "<legend><div id='legend_colour'>Print new labels</div></legend>";
		}
		
			$templates = dl::select("print_template");
			foreach($templates as $template) {
				$temp_names[]= $template["template_name"];
			}
			
			$field = new selection("Select Print Template", "text", "greyInput", "50", "", "print_template", $temp_names, "", "0");
			echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
			echo "<span class='form_field'>".$field->show_field()."</span><span class='greyInputSelect' id='templateClick'><img class='gi-img' src='images/dropdown.png'></span>";
			echo "<div id='show_selected'></div>";
			
			$field = new fields("How many to print?", "text", "greyInput", "5", "", "how_many");
			echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
			echo "<span class='form_field'>".$field->show_field()."</span><BR>";
			echo "<div id='showLastNumber'></div>";
			$button = new fields("submit Button", "submit", "bluebutton", 10, "Print Labels","print_labels");
			echo $button->show_field();
			echo "<div id='barcodeMessage'></div>";
	echo "</fieldset>";
	?>
	<script>
	$(document).ready(function() {
		$("#templateClick").live("click", function () {
			$("#print_template").focus();	
		});
		$("#print_template").on("autocompletechange", function(event, ui) { 
			var func = "showTemplateValues";
			var option = '<?php echo $option?>';
			$.post(
				"ajax.php",
				{ func: func,
				option: option,
				  selTemplate: $("#print_template").val()
				},
				function (data)
				{
					$('#show_selected').html(data);
				});
		});
		$("#how_many").on("input", function(event, ui) { 
			var func = "calculatePrints";
			$.post(
				"ajax.php",
				{ func: func,
				  barcodeNo: $("#bcNumber").val(),
				  noPrints: $("#how_many").val(),
				  selTemplate: $("#print_template").val()
				},
				function (data)
				{
					$('#showLastNumber').html(data);
				});
		});
		$("#print_labels").live("click", function () {
			var func = "updatePrintValue";
			$.post(
				"ajax.php",
				{ func: func,
				option: '<?php echo $option?>',
				bcnumber: $("#lastBC").val(),
				selTemplate: $("#print_template").val()
				},
				function (data)
				{
				$('#barcodeMessage').html(data);
			});
			window.open("pdf_print.php?barcode="+$("#bcNumber").val()+"&noPrints="+$("#how_many").val()+"&selTemplate="+$("#print_template").val());
		});
	});

	</script>
	<?php
}

function audit_report(){
	if( !check_permissions("View Audit") ) {
		die("Sorry but you do not have access to the Audit Reporting function.");				
	}
	echo "<form>";
		echo "<audit>";
			echo "<div class='row'>";
				echo "<div class='three columns'>";
					echo "<div id='filter'>";
						echo "<div id='filter-title'>Audit Report Filter</div>";
							echo "<li class='prepend field'>";
								echo "<span class='adjoined'><img src='library/images/date_picker.png'></span>";
								echo "<input id='from' class='wide text input' type='text' placeholder='Timestamp From'' />";
							echo "</li>";
							echo "<li class='prepend field'>";
								echo "<span class='adjoined'><img src='library/images/date_picker.png'></span>";
								echo "<input id='to' class='wide text input' type='text' placeholder='Timestamp To'' />";
							echo "</li>";
							?>
							<script language="JavaScript">
								$(document).ready(function() {
									$("#from").datepicker( {
									dateFormat: 		'dd/mm/yy',
									buttonImageOnly: 	true
									
									});
									$("#to").datepicker( {
									dateFormat: 		'dd/mm/yy',
									buttonImageOnly: 	true
									});
								});
							</script><?php

							$users = dl::select("user", "confirmed = 1");
							foreach($users as $u) {
								$user[] =  $u["user_name"];
							}
							echo "<li class='field'>";
								echo "<div class='picker'>";
								echo "<select id='user' name='user'>";
								echo "<option value='#' selected disabled>Select a user...</option>";
							foreach($user as $u) {
								echo "<option>$u</option>";
							}
							echo "</select>";
							echo "</div>";
							echo "</li>";
						$actions = dl::select("audit_actions");
						foreach($actions as $ac) {
							$action[] = $ac["aa_list"];
						}
						echo "<li class='field'>";
							echo "<div class='picker'>";
								echo "<select id='actions' name='actions'>";
								echo "<option value='#' selected disabled>Select an action...</option>";
								foreach($action as $a) {
									echo "<option>$a</option>";
								}
								echo "</select>";
							echo "</div>";
						echo "</li>";

						$sql = "select table_name, engine
						from information_schema.tables
						where table_type = 'BASE TABLE' and table_schema = 'nsample'";
						$tables = dl::getQuery($sql);
						foreach($tables as $t) {
							$table[] = $t["table_name"];
						}
						echo "<li class='field'>";
							echo "<div class='picker'>";
								echo "<select id='table_list' name='table_list'>";
								echo "<option value='#' selected disabled>Select a table...</option>";
								foreach($table as $t) {
									echo "<option>$t</option>";
								}
								echo "</select>";
							echo "</div>";
						echo "</li>";
						echo "<li class='field'>";
						echo "<input id='recId' name='recId' class='wide text input' type='text' placeholder='Record Id'' />";
						echo "</li>";
						echo "<div class='medium pretty primary icon-left btn icon-database' id='set_filter'><a href='#'>Set Filter</a></div> ";
						echo "<div class='medium pretty primary icon-left btn icon-erase' id='clear_filter'><a href='#'>Clear</a></div> ";
						echo "<div id='FilterMessage'></div>";
					echo "</div>";
				echo "</div>";
				echo "<div class='nine columns'>";
					echo "<div id='main_display'>";
						$actions = dl::select("audit_action");	
						audit_report_body($actions);
					echo "</div>";
				echo "</div>";
			echo "</div>";
		echo "</audit>";
	echo "</form>";
	?>
	<script>
	$(document).ready(function() {
		$("#clear_filter").click( function () {
				$("#from").val("");
				$("#to").val("");
				$("#user").val("#");
				$("#actions").val("#");
				$("#table_list").val("#");
				$("#recId").val("");
				$("#set_filter").click();
		});
		$("#set_filter").click( function () {
			var func = "audit_filter";
			$.post(
				"ajax.php",
				{ func: func,
				from: $("#from").val(),
				to: $("#to").val(),
				user: $("#user").val(),
				action: $("#actions").val(),
				record: $("#recId").val(),
				table: $("#table_list").val()
				},
				function (data)
				{
				$('#main_display').html(data);
			});
		});
	});
	</script>
	<?php 
}

function audit_report_body($actions) {
	$row_count 						= 0;
	$checkKey						= ""; // used to check that the returned detail is not repeated for each field in the record
	foreach($actions as $act) { // this checks if the field aa_id is not the same as the previous record and if not adds the record to $arr (array) 
		if($checkKey !== $act["aa_id"]) {
			$arr[]=$act;
			$checkKey = $act["aa_id"];
		}
	}
	$actions = $arr; //$actions has been cleansed of duplicate records and is now reassigned to continue the journey
	foreach( $actions as $action ) {
			$action_desc 			= dl::select("audit_actions", "aa_id = ". $action["audit_actions_id"]);
			$types_desc 			= dl::select("audit_action_types", "aat_id = ".$action_desc["audit_action_types_id"]);
			$typeDescription 		= $types_desc[0]["aat_list"];
			$actionDescription 	= $action_desc[0]["aa_list"];
			$response 				= getResponse($actionDescription);
			$id 							= dl::select("audit_identification", "ai_id = ".$action["audit_identification_id"]);
			$ts 							= dl::select("audit_timestamp", "at_id = ".$action["audit_timestamp_id"]);
			$identification 			= $id[0]["ai_username"]."</span> ]";
			$timestamp				= $ts[0]["at_timestamp"];
			$details						= dl::select("audit_details_actions", "audit_action_id = ".$action["aa_id"]);
			if($row_count++ 		== 0) {
				echo "<div id='row-even'>";
			}else{
				echo "<div id='row-odd'>";
				$row_count			=0;
			}
			echo "<div id='field-display'>".$timestamp."</div><div id='field-display'>".$typeDescription."</div><div id='field-display'>".$actionDescription."</div><div id='field-display'>".$response.$identification."</div><BR>";
			if(!empty($details)) {
				$detailItems = dl::select("audit_details", "ad_id = ".$details[0]["audit_details_id"]);
				echo "<div id='spacer'></div><div id='field-display'>Table affected:</div>";
				echo "<div id='spacer'></div><div id='field-display'>".$detailItems[0]["ad_tables"]." with values [ </div>";
				foreach($details as $detail) {
					$detailItems = dl::select("audit_details", "ad_id = ".$detail["audit_details_id"]);
					foreach($detailItems as $detailItem){
						echo "<div id='field-display'>".$detailItem["ad_key"]."::<span id='value-display'>".$detailItem["ad_value"]."</span></div>";
					}
				}
				echo "<div id='field-display'>[ Record_ID::<span id='value-display'>".$detailItems[0]["ad_record_id"]."</span> ]]</div><BR><BR>";
			}
			echo "</div>"; //end of row
		}	
}

function getResponse($action) {
	switch($action) {
		case "LOGIN":
		case "LOGOUT":
		case "FAILED LOGIN":
			$retVal = "User name : [ <span id='value-display'>";
			break;
		case "RECORD ADDED":
			$retVal = "Created By : User name: [ <span id='value-display'>";
			break;
		case "RECORD DELETED":
			$retVal = "Deleted By :  User name: [ <span id='value-display'>";
			break;
		case "RECORD UPDATED":
			$retVal = "Updated By :  User name: [ <span id='value-display'>";
			break;
		case "UPLOAD":
			$retVal = "File uploaded By :  User name: [ <span id='value-display'>";
			break;
	}
	return $retVal;
}





?>
