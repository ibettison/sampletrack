<?php
/*
	This class is used within the Sample Tracking system to add new and edit different types and adds data to different tables containing 3 fields:
		id
		name
		description
	The types created are then used as drop down lists for other tables
*/

class listTypes {
	public $suffix_name;
	public $table_name;

	function __construct( $table_name ) {
		$this->table_name 		= $table_name;
	}
	
	function set_suffix_name($name) {
		$this->suffix_name 		=	$name;
	}
	
	function get_suffix_name( ) {
		return $this->suffix_name;
	}
	
	function new_type($option) {
	echo "<form>";
	echo "<data_entry>";
	echo "<div class='row'>";
	echo "<div class='screen-wrapper'>";
	echo "<fieldset class='six columns'>";
		echo "<legend><div id='legend_colour'>".$this->suffix_name." Type</div></legend>";
		echo "<ul>";
			if($option == "edit") {
				$types = dl::select("$this->table_name");
				echo "<li class='field'>";
					echo "<div class='picker'>";
					echo "<select id='info_type_list' name='info_type_list'>";
					foreach($types as $type) {
						$t[]= $type["name"];
					}
					echo "<option value='#' selected disabled>Select the ".$this->suffix_name." Type...</option>";
					foreach($t as $ts) {
						echo "<option>$ts</option>";
					}
				echo "</select>";
				echo "</div>";
				echo "</li>";
			}
			echo "<li class='field'>";
				echo "<input id='info_type' name='info_type' class='wide text input' type='text' placeholder='".$this->suffix_name." Type' /><div id='content-del'></div>";
			echo "</li>";			
			echo "<li class='field'>";
				echo "<textarea id='info_desc' class='input textarea' placeholder='".$this->suffix_name." Description' rows='2'></textarea>";
			echo "</li>";
			echo "<div class='medium pretty primary icon-left btn icon-pencil' id='info_save'><a href='#'>Save ".$this->suffix_name." Type</a></div>";
		echo "</ul>";
		echo "<div id='show_div'></div>";
	echo "</fieldset>";
	echo "</div>";
	echo "</div>";
	echo "<data_entry>";
	echo "</form>";
	echo "<div id='dialog1' style='display:none'>Confirm that you want to delete the list item?</div>";
	?>
	<script>
		globalValues = {};
		$("#info_type_list").change( function(event, ui) { 
			/*This adds a new field to allow the change of the information type*/
			var func="show_info_details";
			$.post(
				"ajax.php",
				{ func: func,
					typeSelected: 	$("#info_type_list").val(),
					table:				'<?php echo $this->table_name?>'
				},
				function (data) {
					var json = $.parseJSON(data);
					/*reset variables*/
					/*$("#show_div").html(data);*/
					$('#info_type').val(json.infoType);
					$('#info_desc').val(json.infoDesc);
					globalValues.type 		= json.infoType;
					globalValues.desc 	= json.infoDesc;
					globalValues.info_id	= json.infoID;
					$("#content-del").html("<a href='#' border='0'><img src='images/DeleteRed.png' />");
				}
			);	
		});
		$("#content-del").click(function(){
			$("#dialog1").dialog({ 
				modal: true,
				height: 200,
				width: 300,
				autoOpen: true,
				title: 'Confirm deletion',
				buttons: [
				{
					id:		'btOk',
					text:		'OK',
					click:	function(){
						var func = 'delete_listId';										
						$.post(
						"ajax.php",
						{ func: func,
						  id: 			globalValues.info_id,
						  type: 		globalValues.type,
						  desc: 		globalValues.desc,
						  table:		'<?php echo $this->table_name?>'
						},
						function (data)
						{
							$("#show_div").html(data);
							$("#info_type_list").val("#");
							$("#info_type").val("");
							$('#info_desc').val("");
							$('#show_div').delay(200).fadeOut(2000);
							$('#show_div').show();
							<?php if($option 		== "edit") { ?>
								var str1 =  "index.php?func=edit_"
								var str2 = "<?php echo strtolower($this->suffix_name)?>_type";
								var redirect = str1.concat(str2);
								setTimeout(function(){
									window.location.href = redirect;
								},2000);						
							<?php }?>
						});
						$("#dialog1").dialog("destroy");
					}										
				},
				{
					id: 		'btCancel',
					text:		'Cancel',
					click:	function() {
						$("#dialog1").dialog("destroy");
					} 
				}]
			});
		});

		$("#info_save").click(function() {
			if(globalValues.info_id == "") {
				globalValues.info_id = 0;
			}
			var func = "save_info_details";
			$.post(
				"ajax.php",
				{ func: func,
					option:						'<?php echo $option ?>',
					table:						'<?php echo $this->table_name ?>',
					infoId: 						globalValues.info_id,
					infoType: 					$('#info_type').val(),
					infoDesc: 					$('#info_desc').val(),
				},
				function (data)
				{
					$('#show_div').html(data);
					$("#info_type_list").val("#");
					$("#info_type").val("");
					$('#info_desc').val("");
					$('#show_div').delay(200).fadeOut(2000);
					$('#show_div').show();
					<?php if($option 		== "edit") { ?>
						var str1 =  "index.php?func=edit_"
						var str2 = "<?php echo strtolower($this->suffix_name)?>_type";
						var redirect = str1.concat(str2);
						setTimeout(function(){
							window.location.href = redirect;
						},2000);						
					<?php }?>
			});
		});
	</script>
	<?php
}
}
?>