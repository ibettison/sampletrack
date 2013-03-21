<?php
	error_reporting("E_ALL & ~E_NOTICE");
	//$dl = new DataLayer();
	$database = 'nsample';
	$user = 'nsample';
	$password = 'jbwzc1bf';
	/* Specify the server and connection string attributes. */
	$serverName = "database.ncl.ac.uk";
	/* Connect using SQL Server Authentication. */
	$conn= dl::connect($serverName,$user,$password,$database);
?>