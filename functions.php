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
		alert("boo");
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
				alert("returned");
				window.location.href = "index.php";
			});
	});
});
</script>
<?php
		echo "<div class='row'>";
			echo "<div class='twelve columns'>";	
				echo "<H1>Newcastle Biomedicine Biobank<BR>Sample Tracking System</H1>";
			echo "</div>";
		echo "</div>";
		echo "<div class='row'>";
		
		echo"The Newcastle Biomedicine Biobank Sample Tracking System login screen allows a registered user to login and manage samples, record new sample registration requests, process sample withdrawals,
				manage deliveries, create and re-freeze or process transportation of Aliquots. The system will record the complete lifecycle of the sample, recording where it came from, its location, 
				any Aliquot creations and their storage locations; recording all requests and deliveries against all samples. Finally the system will record publication, invention events regarding samples that have been stored in the 
				Biobank.</div>";
				echo "<div id='front-left-bottom'><img src='images/mobile-scanning.png' style='max-width:100%; height: auto;'></div>";
			echo "</div>";
			echo "<div id='front-right'>";
				echo "<div id='front-right-top'><img src='images/Freezers.png' style='max-width:100%; height: auto;'></div>";
				echo "<div id='front-right-bottom-right'><B>SCANNING</B><BR><BR>
				The SAMPLE TRACKING SYSTEM uses mobile technology to scan items into and out of the Newcastle Biobank. The system works in real time and stores the sample in containers. Containers can be anything, 
				from the room, the freezer, the shelf or the phial the sample resides in. All containers are linked so scanning a container identifies where it is and what it is automatically, you can even tell where it has been 
				and when it was moved. The system is centrally controlled by a built-in Audit system so every movement is monitored and can be traced back to date and time of the action and the individual performing the action.
				</div>";
				echo "<div class='front-right-bottom'><img src='images/parcels.png' style='max-width:100%; height: auto;'></div>";
			echo "</div>";
		echo "</div>"; // end of container
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
		echo "<div class='medium primary btn' id='m_registrations'><a href='#'>Manage Registrations</a></div>";
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
	echo "<div class='medium primary btn' id='m_lists'><a href='#'>Manage Lists</a></div>";
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
	
	}else{
		echo "<p>There are no recent transfers.</p>";
	}
}

function upload_excel() {
		echo "<fieldset>";
			echo "<legend><div id='legend_colour'>Upload Spreadsheet</div></legend>"; 
			echo "<p>Enter the file to upload and extract the sample information from. Make sure there are no empty columns before the headings start.</p>";
			echo "<form ENCTYPE='multipart/form-data' id='upload_form' method='post' action='index.php?func=uploadFile'>";
				$field = new fields("Select File", "file", "greyInput", "30", "", "frmFile");
				echo "<span class='form_prompt'>".$field->show_prompt()." </span>";
				echo "<span class='form_field'>".$field->show_field()."</span><BR />";
				$field = new fields("Excel Title Row", "text", "greyInput", "10", "", "excel_row");
				echo "<span class='form_prompt'>".$field->show_prompt()." </span>";
				echo "<span class='form_field'>".$field->show_field()."</span><BR />";
				$customers = dl::select("customers");
				foreach($customers as $c) {
					$custs[]= $c["c_name"];
				}
				$field = new selection("Customer", "text", "greyInput", "40", "", "customer_list", $custs, "", "0");
				echo "<span class='form_prompt'>".$field->show_prompt()." </span>";
				echo "<span class='form_field'>".$field->show_field()."</span><span class='greyInputSelect' id='custClick'><img class='gi-img' src='images/dropdown.png'></span><BR /><br />";
			$button = new fields("submit Button", "submit", "bluebutton", 10, "Upload File","submit");
			echo $button->show_field();
			echo "</form>";
		echo "</fieldset>";
		// the jQuery script checks for a click on the select graphic and then focuses to the field and the drop down box appears.
		?>
		<script type="text/javascript">
				$("#custClick").live("click", function () {
					$("#customer_list").focus();	
				});

		</script>
		<?php
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
						window.location.href = "index.php?func=read_excel&file=<?php echo "./documents/".$filename ?>&customer=<?php echo $_POST["customer_list"]?>&excel_row=<?php echo $_POST["excel_row"]?>";
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
	echo "<div id='sort-div''>";
	echo "<div id='sort-div-title'>SPREADSHEET</div>";
	echo "<ul id='sortable' class='connected' '>";
	echo "<li class='heading'>Match this...";
	foreach($rowVals as $row) {
		echo "<li class='ui-state-default'><span class='ui-icon ui-icon-arrowthick-2-n-s'></span>".$rowCount." ".$row."</li>";
		$rowCount++;
		if($rowCount == 11) {
			echo "</ul>";
			echo "<ul id='sortable2' class='connected' '>";
			echo "<li class='heading'>Additional information";
		}
		
	}
	echo "</ul><BR /><BR />";
	echo "</div>";

	echo "<div id='min-data'>";
		echo "<div id='min-data-title'>Minimum dataset</div>";
		echo "<ul id='min-data-ul'>";
			echo "<li class='heading'>With this...";
			echo "<li class='ui-state-default'> 0 CUSTOMER IDENTIFIER</li>";
			echo "<li class='ui-state-default'> 1 SAMPLE DESCRIPTION</li>";
			echo "<li class='ui-state-default'> 2 PATHOLOGY NUMBER</li>";
			echo "<li class='ui-state-default'> 3 DATE SAMPLE STORED</li>";
			echo "<li class='ui-state-default'> 4 SNOMED CODE</li>";
			echo "<li class='ui-state-default'> 5 SUBJECT GENDER</li>";
			echo "<li class='ui-state-default'> 6 SUBJECT AGE</li>";
			echo "<li class='ui-state-default'> 7 DISEASE STATE</li>";			
			echo "<li class='ui-state-default'> 8 SAMPLE STAGE</li>";
			echo "<li class='ui-state-default'> 9 STUDY NAME</li>";
			echo "<li class='ui-state-default'>10 ADULT OR PAEDIATRIC</li>";
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
	echo "<div id='button-div'>";
	echo "<div id='poof'></div>";
		echo "<ul id='sortable-deleted' class='connected'>";
		echo "</ul>";
		$button = new fields("submit Button", "submit", "bluebutton", 10, "Add Blank","blank");
		echo $button->show_field();
	
	$button = new fields("submit Button", "submit", "bluebutton", 10, "Process Spreadsheet","Process");
	echo $button->show_field();
	echo "<span id='showMatching'></span>";
	echo "</div>";
	echo "</div>";
	
		?>
	<script>
	$("#blank").click(function (e) {
		e.preventDefault();
		var $li = $("<li class='ui-state-default'/>").text("<SPACER>");
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
               <li><a href="index.php?func=add_registration">New Customer</a></li>
				<li><a href="index.php?func=upload_sheet">Container</a>
				<div class="dropdown">
					<ul>
							<li><a href="index.php?func=add_registration">New Container</a></li>
							<li><a href="index.php?func=upload_sheet">Container Template</a></li>
							<li><a href="index.php?func=accept_samples">Container Type</a></li>
							<li><a href="index.php?func=accept_samples">View transfer list</a></li>
							<li><a href="index.php?func=accept_samples">Find Creations</a></li>
					</ul>
				</div></li>
				<li><a href="index.php?func=accept_samples">New Request</a></li>
				<li><a href="index.php?func=accept_samples">View transfer list</a></li>
				<li><a href="index.php?func=accept_samples">Find Creations</a></li>
            </ul>
			
          </div>
        </li>
      </ul>
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
				alert("returned");
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
		echo "<fieldset>";
		echo "<legend><div id='legend_colour'>New Registration</div></legend>";
		$field = new dates("Contact Date", "text", "greyInput", "20", "", "contact_date", "contact_date");
		echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
		echo "<span class='form_field'>".$field->show_field()."</span><BR />";
		$customers =dl::select("customers");
		foreach($customers as $customer){
			$customer_list[] = $customer["c_name"];
		}
		$field = new selection("Existing Customer", "text", "greyInput", "40", "", "customer", $customer_list, "", "0");
		echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
		echo "<span class='form_field'>".$field->show_field()."</span><span class='greyInputSelect' id='custClick'><img class='gi-img' src='images/dropdown.png'></span><BR />";
		// the jQuery script checks for a click on the select graphic and then focuses to the field and the drop down box appears.
		?>
		<script type="text/javascript">
				$("#custClick").live("click", function () {
					$("#customer").focus();	
				});
				$("#customer").on("autocompletechange", function(event, ui) { 
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
		$field = new fields("Contact Name", "text", "greyInput", "40", "", "contact_name");
		echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
		echo "<span class='form_field'>".$field->show_field()."</span><BR>";
		$field = new fields("Contact Email", "text", "greyInput", "40", "", "contact_email");
		echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
		echo "<span class='form_field'>".$field->show_field()."</span><BR>";
		$field = new fields("Contact Phone No.", "text", "greyInput", "40", "", "contact_phone");
		echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
		echo "<span class='form_field'>".$field->show_field()."</span><BR>";
	
		$selection = new textArea("Requirements","text", "greyInput", "20", "" , "required", 40, 3);
		echo "<span class='form_prompt'>".$selection->show_prompt()."</span>";
		echo "<span class='form_field'>".$selection->show_field()."</span><BR />";
		$field = new dates("Expected Delivery", "text", "greyInput", "20", "", "delivery_date", "delivery_date");
		echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
		echo "<span class='form_field'>".$field->show_field()."</span><BR />";
		$field = new fields("Payment Reference.", "text", "greyInput", "40", "", "contact_paymentRef");
		echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
		echo "<span class='form_field'>".$field->show_field()."</span><BR>";
		$selection = new textArea("Payment Details","text", "greyInput", "20", "" , "contact_paymentDetails", 40, 3);
		echo "<span class='form_prompt'>".$selection->show_prompt()."</span>";
		echo "<span class='form_field'>".$selection->show_field()."</span><BR />";
		echo "</fieldset>";
		echo "<fieldset>";
		echo "<legend><div id='legend_colour'>Sample Information</div></legend>";

		$field = new fields("Number of Samples", "text", "greyInput", "10", "", "sample_no");
		echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
		echo "<span class='form_field'>".$field->show_field()."</span><BR>";
		$types = dl::select("sample_types");
		foreach($types as $type) {
			$sample_types[] = $type["st_type"]; 
		}
		$field = new selection("Sample Type", "text", "greyInput", "40", "", "sample_type", $sample_types, "", "0");
		echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
		echo "<span class='form_field'>".$field->show_field()."</span><span class='greyInputSelect' id='typeClick'><img class='gi-img' src='images/dropdown.png'></span><BR>";
		?>
		<script type="text/javascript">
				$("#typeClick").live("click", function () {
					$("#sample_type").focus();	
				});

		</script>
		<?php
		$containers = dl::select("container_types");
		foreach($containers as $container) {
			$container_list[]= $container["ct_name"];
		}
		$field = new selection("Containers", "text", "greyInput", "40", "", "sample_container", $container_list,"","0");
		echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
		echo "<span class='form_field'>".$field->show_field()."</span><span class='greyInputSelect' id='containerClick'><img class='gi-img' src='images/dropdown.png'></span><BR>";
		?>
		<script type="text/javascript">
				$("#containerClick").live("click", function () {
					$("#sample_container").focus();	
				});

		</script>
		<?php
		$field = new checkbox("Stored in Boxes?", "checkbox", "greyInput", "40", "", "boxes");
		echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
		echo "<span class='form_field'>".$field->show_field()."</span>";
		$field = new fields("Box Size", "text", "greyInput", "10", "", "sample_boxsize");
		echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
		echo "<span class='form_field'>".$field->show_field()."</span><BR>";
		$Temp = array("-20&degC", "-80&degC", "-150&degC");
		$selection = new radio("What Temperature?","radio", "greyInput", "20", "" , "sample_temperature", "", "", $Temp, "", "Horizontal");
		echo "<span class='form_prompt'>".$selection->show_prompt()."</span>";
		echo "<span class='form_field'>".$selection->show_field()."</span><BR />";
		$selection = new radio("Samples Catalogued?","radio", "greyInput", "20", "" , "samples_catalogued", "", "", array("Yes", "No"), "", "Horizontal");
		echo "<span class='form_prompt'>".$selection->show_prompt()."</span>";
		echo "<span class='form_field'>".$selection->show_field()."</span><BR />";
		$selection = new textArea("Sample info","text", "greyInput", "20", "" , "sample_info", 40, 3);
		echo "<span class='form_prompt'>".$selection->show_prompt()."</span>";
		echo "<span class='form_field'>".$selection->show_field()."</span><BR />";
		echo "<H3>Biobank Services</H3>";
		$services = dl::select("biobank_services_list", "", "bsl_id ASC");
		foreach($services as $service) {
			$field = new checkbox($service["bsl_description"], "checkbox", "greyInput", "40", "", "services".$service["bsl_id"]);
			echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
			echo "<span class='form_field'>".$field->show_field()."</span><BR>";
		}
		echo "<BR /><BR /></fieldset>";
		$button = new fields("submit Button", "submit", "bluebutton", 10, "New Registration","newReg");
		echo $button->show_field();
		echo "<div id='show_registration_message'></div>";
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
						sample_temperature: $("input:radio[name=sample_temperature][checked]").val(),
						samples_catalogued: $("input:radio[name=samples_catalogued][checked]").val(),
						sample_info: $("#sample_info").val(),
						services: checked,
						ids: ids
					},
					function (data)
					{
						$('#show_registration_message').html(data);
						$("#contact_date").val(""),
						$("#contact_name").val(""),
						$("#contact_email").val(""),
						$("#contact_phone").val(""),
						$("#customer").val(""),
						$("#required").val(""),
						$("#delivery_date").val(""),
						$("#contact_paymentRef").val(""),
						$("#contact_paymentDetails").val(""),
						$("#sample_no").val(""),
						$("#sample_type").val(""),
						$("#sample_container").val(""),
						$("#boxes").prop("checked", false),
						$("#sample_boxsize").val(""),
						$('input[name="sample_temperature"]').prop("checked",false),
						$('input[name="samples_catalogued"]').prop("checked",false),
						$("#sample_info").val(""),
						<?php
						$services = dl::select("biobank_services_list", "", "bsl_id ASC");
						foreach($services as $service){
						?>
							$("#<?php echo "services".$service["bsl_id"]?>").prop("checked", false);
						<?php 
						}
						?>
						$('#show_registration_message').delay(200).fadeOut(2000);
						$('#show_registration_message').show();
				});

			});
		})
	</script>
	<?php
}


function new_container_template () {
	echo "<fieldset>";
		echo "<legend><div id='legend_colour'>New Container template</div></legend>";
		$field = new fields("Template Name", "text", "greyInput", "40", "", "con_temp_name");
		echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
		echo "<span class='form_field'>".$field->show_field()."</span><BR>";
		$containers = dl::select("container_types");
		foreach($containers as $container) {
			$container_names[]= $container["ct_name"];
		}
		$field = new selection("Container Type", "text", "greyInput", "40", "", "container_type", $container_names, "", "0");
		echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
		echo "<span class='form_field'>".$field->show_field()."</span><span class='greyInputSelect' id='containerClick'><img class='gi-img' src='images/dropdown.png'></span><BR />";
		// the jQuery script checks for a click on the select graphic and then focuses to the field and the drop down box appears.
		?>
		<script type="text/javascript">
				$("#containerClick").live("click", function () {
					$("#container_type").focus();	
				});
		</script>
		<?php
		$field = new fields("No. Container Rows", "text", "greyInput", "10", "", "container_rows");
		echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
		echo "<span class='form_field'>".$field->show_field()."</span><BR>";
		$field = new fields("No. Container Columns", "text", "greyInput", "10", "", "container_columns");
		echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
		echo "<span class='form_field'>".$field->show_field()."</span><BR>";
		$list = array("Alpha", "Numeric");
		$field = new selection("Row Type", "text", "greyInput", "20", "", "row_type", $list, "", "0");
		echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
		echo "<span class='form_field'>".$field->show_field()."</span><span class='greyInputSelect' id='rowClick'><img class='gi-img' src='images/dropdown.png'></span><BR />";
		// the jQuery script checks for a click on the select graphic and then focuses to the field and the drop down box appears.
		?>
		<script type="text/javascript">
				$("#rowClick").live("click", function () {
					$("#row_type").focus();	
				});
		</script>
		<?php
		$field = new selection("Column Type", "text", "greyInput", "20", "", "column_type", $list, "", "0");
		echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
		echo "<span class='form_field'>".$field->show_field()."</span><span class='greyInputSelect' id='columnClick'><img class='gi-img' src='images/dropdown.png'></span><BR /><BR/>";
		// the jQuery script checks for a click on the select graphic and then focuses to the field and the drop down box appears.
		?>
		<script type="text/javascript">
				$("#columnClick").live("click", function () {
					$("#column_type").focus();	
				});
		</script>
		<?php
		$button = new fields("submit Button", "submit", "bluebutton", 10, "Add Container Template","container_template");
		echo $button->show_field();
		echo "<div id='container_template_div'></div>";
	echo "</fieldset>";
		?>
		<script type="text/javascript">
		$("#container_template").live("click", function () {
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
	echo "<fieldset>";
		if($option == "new") {
			echo "<legend><div id='legend_colour'>New Container Type</div></legend>";
			$field = new fields("Container Type Name", "text", "greyInput", "50", "", "con_type_name");
			echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
			echo "<span class='form_field'>".$field->show_field()."</span><BR>";
		}else{
			$containers = dl::select("container_types");
			foreach($containers as $container) {
				$container_names[]= $container["ct_name"];
			}
			echo "<legend><div id='legend_colour'>Edit Container Type</div></legend>";
			$field = new selection("Container Type Name", "text", "greyInput", "50", "", "con_type_name", $container_names, "", "0");
			echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
			echo "<span class='form_field'>".$field->show_field()."</span><span class='greyInputSelect' id='typeClick'><img class='gi-img' src='images/dropdown.png'></span><BR />";
			// the jQuery script checks for a click on the select graphic and then focuses to the field and the drop down box appears.
			?>
			<script type="text/javascript">
					$("#typeClick").live("click", function () {
						$("#con_type_name").focus();	
					});
					$("#con_type_name").on("autocompletechange", function(event, ui) { 
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
						});
					});
			</script>
			<?php
		}
		$selection = new textArea("Container Description","text", "greyInput", "20", "" , "container_info", 49, 3);
		echo "<span class='form_prompt'>".$selection->show_prompt()."</span>";
		echo "<span class='form_field'>".$selection->show_field()."</span><BR />";
		$field = new fields("Manufacturer", "text", "greyInput", "50", "", "con_type_manufacturer");
		echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
		echo "<span class='form_field'>".$field->show_field()."</span><BR>";
		$type = dl::select("container_types");
		foreach($type as $t){
			$types[]= $t["ct_name"];
		}
		echo "<span style='width:10em; display: inline-block; color: #333;'>Allowed Containers</span>";
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
		$button = new fields("submit Button", "submit", "bluebutton", 10, $buttonValue,"container_type");
		echo $button->show_field();
		echo "<div id='container_type_div'></div>";
	echo "</fieldset>";
	?>
		<script type="text/javascript">
				$("#container_type").live("click", function () {
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
							$('#con_type_name').val("");
							$('#container_info').val("");
							$('#con_type_manufacturer').val("");
							$('#allowed_types').val("");
							$('#container_type_div').delay(200).fadeOut(2000);
							$('#container_type_div').show();
					});

				});
		</script>
		<?php
}

function new_container($function) {
	echo "<fieldset>";
		if($function == "new") {
			echo "<legend><div id='legend_colour'>New Container</div></legend>";
			$field = new fields("Container Name", "text", "greyInput", "50", "", "con_name");
			echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
			echo "<span class='form_field'>".$field->show_field()."</span><BR>";
		}else{
			echo "<legend><div id='legend_colour'>Edit Containers</div></legend>";
			$container_names = dl::select("containers");
			foreach($container_names as $cn) {
				$arrContainer[] = $cn["c_container_name"]." - ". $cn["c_container_barcode"]; 
			}
			$field = new selection("Container Name", "text", "greyInput", "50", "", "con_name", $arrContainer, "", "0");
			echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
			echo "<span class='form_field'>".$field->show_field()."</span><span class='greyInputSelect' id='containerClick'><img class='gi-img' src='images/dropdown.png'></span><BR />";
			?>
		<script type="text/javascript">
				$("#containerClick").live("click", function () {
					$("#con_name").focus();	
				});
				$("#con_name").on("autocompletechange", function(event, ui) { 
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
						$('#con_name').val(container);
					});
				});
		</script>
		<?php
		}
		$templates = dl::select("container_templates");
		foreach($templates as $template){
			$template_names[]= $template["ct_template_name"];
		}
		$field = new selection("Template Type", "text", "greyInput", "50", "", "template_type", $template_names, "", "0");
		echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
		echo "<span class='form_field'>".$field->show_field()."</span><span class='greyInputSelect' id='templateClick'><img class='gi-img' src='images/dropdown.png'></span><BR />";
		// the jQuery script checks for a click on the select graphic and then focuses to the field and the drop down box appears.
		?>
		<script type="text/javascript">
				$("#templateClick").live("click", function () {
					$("#template_type").focus();	
				});
		</script>
		<?php
		$field = new fields("Container Barcode", "text", "greyInput", "50", "", "con_barcode");
		echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
		echo "<span class='form_field'>".$field->show_field()."</span><span class='bluebutton' id='getBarcode' style='float:none;'>Get</span><BR/>";
		if( $function == "new" ) {
			$buttonval = "Add New Container";
		}else{
			$buttonval = "Edit Container";
		}
		$button = new fields("submit Button", "submit", "bluebutton", 10, $buttonval,"container_new");
		echo $button->show_field();
		echo "<div id='container_div'></div>";
	echo "</fieldset>";
	?>
	<script type="text/javascript">
		$("#getBarcode").live("click", function () {
			var barcode = $("#barcode").val();
			$("#con_barcode").val(barcode);	
		});
		$("#container_new").live("click", function () {
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
					$("#con_name").autocomplete({
					source: list,
					minLength: 0 
				}).focus(function() {
					$("#con_name").autocomplete("search", "");
				});
			});

		});
	</script>
		<?php
}

function new_customer($option) {
		echo "<fieldset>";
		?>
		<script>
		globalValues = {};
		</script>
		<?php
		if($option == "new") {
			echo "<legend><div id='legend_colour'>New Customer</div></legend>";
			$field = new fields("Customer Name", "text", "greyInput", "50", "", "cust_name");
			echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
			echo "<span class='form_field'>".$field->show_field()."</span><BR>";
			?>
			<script>
			globalValues.customer_id = 0;
			</script>
			<?php
		}else{
			echo "<legend><div id='legend_colour'>Edit Customer</div></legend>";
			$customer_names = dl::select("customers");
			foreach($customer_names as $cn) {
				$arrCustomer[] = $cn["c_name"]; 
			}
			$field = new selection("Customer Name", "text", "greyInput", "50", "", "cust_name", $arrCustomer, "", "0");
			echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
			echo "<span class='form_field'>".$field->show_field()."</span><span class='greyInputSelect' id='customerClick'><img class='gi-img' src='images/dropdown.png'></span><BR />";
			?>
		<script type="text/javascript">
				
				$("#customerClick").live("click", function () {
					$("#cust_name").focus();	
				});
				$("#cust_name").on("autocompletechange", function(event, ui) { 
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
								html_show = "<list-content><div id='content-container'><div id='content-header'>"+type+"</div><div id='content' style='width:15em;'>"+detail+"</div><div id='content'><a href='#' id='button"+id+"' border='0'><img src='images/DeleteRed.png' /></a></div></div></list-content>";
								line = line.concat(html_show);
								$("#button"+id).live("click", function (){
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
							$('#showContactDetails').html(line);
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
		
		$field = new fields("Type of Business", "text", "greyInput", "50", "", "business");
		echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
		echo "<span class='form_field'>".$field->show_field()."</span><BR>";
		$field = new fields("Registration no.", "text", "greyInput", "50", "", "cust_reg");
		echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
		echo "<span class='form_field'>".$field->show_field()."</span><BR>";
		echo "<h3>Contact details</h3>";
		echo "<div id='showContactDetails'></div>";
		$contact_types = dl::select("contact_types");
		foreach($contact_types as $ct){
			$type_names[]= $ct["ct_type"];
		}
		$field = new selection("Contact Type", "text", "greyInput", "50", "", "contact_type", $type_names, "", "0");
		echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
		echo "<span class='form_field'>".$field->show_field()."</span><span class='greyInputSelect' id='contactClick'><img class='gi-img' src='images/dropdown.png'></span><BR />";
		// the jQuery script checks for a click on the select graphic and then focuses to the field and the drop down box appears.
	
		$field = new textArea("Contact Detail", "text", "greyInput", "50", "", "con_detail", 49, 3);
		echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
		echo "<span class='form_field'>".$field->show_field()."</span><br/>";
		$button = new fields("submit Button", "submit", "bluebutton", 10, "Add Details","add_details");
		echo $button->show_field();
		echo "<BR><BR><hr id='hr_line'><BR>";
		$button = new fields("submit Button", "submit", "bluebutton", 10, "Save Customer","save_customer");
		echo $button->show_field();
		echo "<div id='showContact_div'></div>";
	echo "</fieldset>";	
	?>
		<script type="text/javascript">
				$("#contactClick").live("click", function () {
					$("#contact_type").focus();	
				});
				$("#add_details").live("click", function (){
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
							$('#showContact_div').html("New Contact Details added, please save to add to the Current Customer.");
							$("#con_detail").val("");
							$('#contact_type').val("");
					});
				});
				$("#save_customer").live("click", function (){
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
	echo "<samples>";
	echo "<fieldset>";
		echo "<legend><div id='legend_colour'>Sample Information</div></legend>";
		echo "<div style='width: 50%; float:left;'>";
			$field = new fields("Container Barcode", "text", "greyInput", "45", "", "container_bc");
			echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
			echo "<span class='form_field'>".$field->show_field()."</span><span class='bluebutton' id='getContainerBarcode' style='float:none;'>Get</span><BR>";
			
			$sql = "select * from samples_list as sl 
				join customers as c on (sl.customer_id=c.c_id) where sl_status = 'Outstanding' order by sl_date_uploaded ASC
			";
			$samples_list = dl::getQuery($sql);
			if(!empty($samples_list)) {
				foreach($samples_list as $list) {
					$lists[]= $list["c_name"]." ".$list["sl_date_uploaded"];
				}
			}
			$field = new selection("Samples List", "text", "greyInput", "50", "", "sample_listing", $lists, "", "0");
			echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
			echo "<span class='form_field'>".$field->show_field()."</span><span class='greyInputSelect' id='samplesClick'><img class='gi-img' src='images/dropdown.png'></span><span class='bluebutton' id='showSamples' style='float:none; margin-left: 2.75em;'>View Samples</span><BR />";
			?>
			<script>
				$("#samplesClick").live("click", function () {
				$("#sample_listing").focus();	
				});
			</script>
			<?php
			echo "<div id='show_samples'></div>";
			echo "<BR><BR>";
			$field = new checkbox("Save Uncatalogued?", "checkbox", "greyInput", "40", "", "uncat", "");
			echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
			echo "<span class='form_field'>".$field->show_field()."</span><BR>(Select a samples list and it will be associated with the selected container.)<BR><BR>";
			$button = new fields("submit Button", "submit", "bluebutton", 10, "Save Sample Locations","save_samples");
			echo $button->show_field();
			
			
			echo "<div id='showContact_div'></div>";
		echo "</div>";
		echo "<div style='width:50%; float:right;'>";
			echo "<div id='container_details'></div>";
		echo "</div>";
	echo "</fieldset>";
	echo "<div id='dialog1' style='display: none; font-size:1em;'>";
		echo "Scan the sample container into the container location:<BR/><BR/>";
		$field = new fields("Barcode", "text", "greyInput", "20", "", "stored_bc");
		echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
		echo "<span class='form_field'>".$field->show_field()."</span><span class='bluebutton' id='getBarcode' style='float:none;'>Get</span>";
		echo "<div id='sampleBarcode'></div>";
	echo "</div>";
	echo "<div id='dialog2' style='display: none; font-size:1em;'>";
		echo "Scan the barcode and select 'Remove' to remove the sample from the container location:<BR/><BR/>";
		$field = new fields("Barcode", "text", "greyInput", "22", "", "rem_stored_bc");
		echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
		echo "<span class='form_field'>".$field->show_field()."</span><span class='bluebutton' id='getRemBarcode' style='float:none;'>Get</span>";
		$field = new textArea("Removal Note", "text", "greyInput", "50", "", "rem_note", 20, 3);
		echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
		echo "<span class='form_field'>".$field->show_field()."</span><br/>";
	echo "</div>";
	echo "</samples>";
	
	?>
	<script type="text/javascript">
	$(document).ready(function() {
		$("#getBarcode").live("click", function () {
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
		$("#getRemBarcode").live("click", function () {
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
		$("#getContainerBarcode").live("click", function () {
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
		$("#showSamples").click(function() { 
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
						height: "32em",
						width:	"42em"
					});
				}
			);
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
	echo "<fieldset>";
		echo "<legend><div id='legend_colour'>Scan Containers</div></legend>";
			$field = new fields("Place this Container", "text", "greyInput", "45", "", "container_move");
			echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
			echo "<span class='form_field'>".$field->show_field()."</span><span class='bluebutton' id='getBarcode1' style='float:none;'>Get</span><BR>";
			echo "<div id='showContainer1'></div>";
			$field = new fields("Into this Container", "text", "greyInput", "45", "", "container_to");
			echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
			echo "<span class='form_field'>".$field->show_field()."</span><span class='bluebutton' id='getBarcode2' style='float:none;'>Get</span><BR>";
			echo "<div id='showContainer2'></div>";
			$button = new fields("submit Button", "submit", "bluebutton", 10, "Store Container","store_con");
			echo $button->show_field();
			echo "<div id='containerMessage'></div>";
	echo "</fieldset>";
	?>
	<script>
	$(document).ready(function() {
		$("#getBarcode1").live("click", function () {
			var sBarcode = $("#barcode").val();
			$("#container_move").val(sBarcode);	
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
		$("#getBarcode2").live("click", function () {
			var sBarcode = $("#barcode").val();
			$("#container_to").val(sBarcode);	
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
		$("#store_con").live("click", function () {
			var container1 = $("#container_move").val();
			var container2 = $("#container_to").val();
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
	echo "<div id='filter'>";
	echo "<div id='filter-title'>Audit Report Filter</div>";
	$field = new dates("Timestamp From:", "text", "greyInput", "20", "", "from", "from");
	echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
	echo "<span class='form_field'>".$field->show_field()."</span><BR />";
	$field = new dates("Timestamp To:", "text", "greyInput", "20", "", "to", "to");
	echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
	echo "<span class='form_field'>".$field->show_field()."</span><BR />";
	$users = dl::select("user", "confirmed = 1");
	foreach($users as $u) {
		$user[] =  $u["user_name"];
	}
	$field = new selection("User", "text", "greyInput", "30", "", "user", $user, "", "0");
	echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
	echo "<span class='form_field'>".$field->show_field()."</span><span class='greyInputSelect' id='userClick'><img class='gi-img' src='images/dropdown.png'></span>";
	$actions = dl::select("audit_actions");
	foreach($actions as $ac) {
		$action[] = $ac["aa_list"];
	}
	$field = new selection("Actions", "text", "greyInput", "30", "", "actions", $action, "", "0");
	echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
	echo "<span class='form_field'>".$field->show_field()."</span><span class='greyInputSelect' id='actionsClick'><img class='gi-img' src='images/dropdown.png'></span>";
	$sql = "select table_name, engine
	from information_schema.tables
	where table_type = 'BASE TABLE' and table_schema = 'nsample'";
	$tables = dl::getQuery($sql);
	foreach($tables as $t) {
		$table[] = $t["table_name"];
	}
	$field = new selection("Tables", "text", "greyInput", "30", "", "table_list", $table, "", "0");
	echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
	echo "<span class='form_field'>".$field->show_field()."</span><span class='greyInputSelect' id='tablesClick'><img class='gi-img' src='images/dropdown.png'></span><BR>";
	$field = new fields("Record Id", "text", "greyInput", "20", "", "recId");
	echo "<span class='form_prompt'>".$field->show_prompt()."</span>";
	echo "<span class='form_field'>".$field->show_field()."</span><BR>";
	$button = new fields("submit Button", "submit", "bluebutton", 10, "Set Filter","set_filter");
	echo $button->show_field();
	$button = new fields("submit Button", "submit", "bluebutton", 10, "Clear","clear_filter");
	echo $button->show_field();
	echo "<div id='FilterMessage'></div>";
	echo "</div>";
	$actions = dl::select("audit_action");	
	echo "<div id='main_display'>";
	audit_report_body($actions);
	echo "</div>";
	?>
	<script>
	$(document).ready(function() {
		$("#userClick").live("click", function () {
			$("#user").focus();	
		});
		$("#actionsClick").live("click", function () {
			$("#actions").focus();	
		});
		$("#tablesClick").live("click", function () {
			$("#table_list").focus();	
		});
		$("#clear_filter").live("click", function () {
				$("#from").val("");
				$("#to").val("");
				$("#user").val("");
				$("#actions").val("");
				$("#table_list").val("");
				$("#recId").val("");
		});
		$("#set_filter").live("click", function () {
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
			echo "<div id='field-display'>".$timestamp."</div><div id='field-display'>".$typeDescription."</div><div id='field-display'>".$actionDescription."</div><div id='field-display'>".$response.$identification."</div><BR><BR>";
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
