<?php

	$con = mysqli_connect("localhost","root","","pccmain");
	if (mysqli_connect_errno()) {
	    printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}
	

	/* function dbquery($query) {
		global $con;
		return $con->query($query);
	}
	
	function getArray($query) {
		global $con;
		$rset = dbquery($query);
		return $rset->fetch_array();
	} */
	
	@mysqli_close($con);

?>