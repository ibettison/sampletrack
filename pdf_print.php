<?php
//session_start();
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
include('connection.php');
include(CLASSPATH."barcode/php-barcode.php");
require("fpdf.php");
	
	$sql = "select * from print_template join print_barcode_settings on (barcode_settings_id=pbs_id)
	where template_name ='".$_GET["selTemplate"]."'";
	$selected = dl::getQuery($sql);
	//-------------------setup template ------------------------------//
	$x = $selected[0]["positionX"];
	$y = $selected[0]["positionY"];
	$bcHeight = $selected[0]["barcode_height"];
	$bcWidth = $selected[0]["barcode_width"];
	$labelHeight = $selected[0]["label_height"];
	$labelWidth = $selected[0]["label_width"];
	$labelText = $selected[0]["readable_label"];
	$types = dl::select("print_barcode_types", "pbt_id = ".$selected[0]["barcode_types_id"]);
	$barcodeType = $types[0]["pbt_name"];
	$prefix = $selected[0]["pbs_prefix"];
	$suffix = $selected[0]["pbs_suffix"];
	$angle= '0';
	$black = '000000';
	//------------------ end of Setup ---------------------------------//
	//echo $labelWidth, $labelHeight, $x, $y;
	$pdf = new FPDF('P', 'mm', array($labelWidth,$labelHeight));
	$strLen = strlen($_GET["barcode"]);
	$labels = $_GET["barcode"];
	$firstVal = $labels;	
	for($p=1; $p<=$_GET["noPrints"]; $p++){
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
		$Barcode = iconv('UTF-8', 'windows-1252', $Barcode);
		$data = Barcode::fpdf($pdf, $black, $x, $y, $angle, $barcodeType, array('code'=>$Barcode), $bcWidth, $bcHeight);
		$pdf->SetFont('Arial','B',10);
		  $pdf->SetTextColor(0, 0, 0);
		  if($labelText == 'true') {
			 $len = $pdf->GetStringWidth($data['hri']);
			Barcode::rotate(-$len / 2, ($data['height'] / 2) + $fontSize + 5, $angle, $xt, $yt);
			$pdf->Text($x + $xt, $y + $yt, $data['hri']);
		  }
	}
	  
	$pdf->Output();


?>