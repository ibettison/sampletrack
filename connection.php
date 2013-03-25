<?php
	error_reporting("E_ALL & ~E_NOTICE");
	$database = 'nsample';
	$user = 'nsample';
	$password = 'jbwzc1bf';
	/* Specify the server and connection string attributes. */
	$serverName = "crf88.ncl.ac.uk";
	/* Connect using SQL Server Authentication. */
	$conn= dl::connect($serverName,$user,$password,$database);
?>