<?php
	session_start();
	//ini_set("display_errors","On");
	ini_set("memory_limit","2056M");
	ini_set("max_execution_time",0);

	/* Importing Data File */
	$uploadDir = "uploads/";

	$fileName = $_FILES['userfile']['name'];
	$tmpName = $_FILES['userfile']['tmp_name'];
		
	/* CHANGE UNIQUE FILENAME TO PREVENT DUPLICATION */
	$ext = substr(strrchr($fileName, "."), 1);
	$randName = md5(rand() * time());
	$newFileName = $randName . "." . $ext;
	$filePath = $uploadDir . $newFileName;
	
	$result = move_uploaded_file($tmpName, $filePath);
	if (!$result) {
		echo "Error uploading file";
		exit;
	} else {
	

		require_once 'handlers/_generics.php';
		$con = new _init();
		
		$file = "uploads/$newFileName";// Your Temp Uploaded file
		$handle = fopen($file, "r"); // Make all conditions to avoid errors
		$read = file_get_contents($file); //read
		$lines = explode("\n", $read);//get
		$i= 0;//initialize

		$counter = 1;
		foreach($lines as $key => $value) {
			$cols[$i] = explode(",", $value);

            $lname = trim(strtoupper($cols[$i][0]));
            $fname = trim(strtoupper($cols[$i][1]));
			$mname = trim(strtoupper($cols[$i][2]));
			
			if($lname != '') {
			
				$lname = rtrim(str_replace("&NTILDE;","&Ntilde;",$lname));
				$mname = rtrim(str_replace("&NTILDE;","&Ntilde;",$mname));
				$fname = rtrim(str_replace("&NTILDE;","&Ntilde;",$fname));
				$suffix = $cols[$i][3];
				$dob = $cols[$i][4];
				$gender = $cols[$i][5];
				//$pob = trim($cols[$i][6],'"');
				$email = $cols[$i][7];
				$cell = $cols[$i][8];
				$cstat = $cols[$i][9];
				$eid = $cols[$i][10];
				$hmo = $cols[$i][11];

				//$tempDate = explode("/",$dob);
				//$dob = $tempDate[2] . "-" . $tempDate[0] . "-" . $tempDate[1];
				

			switch($cstat) {
					case "SINGLE": $stat = 1; break;
					case "MARRIED": $stat = 2; break;
					case "S": $stat = 1; break;
					case "M": $stat = 2; break;
					case "SE": $stat = 3; break;
					case "WIDOW": $stat = 4; break;
				}

				$batchDate = date('Y-m-d');
				$insertData = $con->dbquery("INSERT IGNORE patient_info (lname,fname,mname,suffix,gender,birthdate,birthplace,cstat,mobile_no,email_add,employer,emp_street,hmo_no,emp_idno,created_by,created_on,batch_upload,batch_upload_date) VALUES ('$lname','$fname','$mname','$suffix','$gender','$dob','$pob','$cstat','$cell','$email','".$con->escapeString(htmlentities($_REQUEST['employer']))."','".$con->escapeString(htmlentities($_REQUEST['eaddr']))."','$hmo','$eid','$_SESSION[userid]',now(),'Y','$batchDate');");
				if($insertData) {
					list($pid) = $con->getArray("select patient_id from patient_info where lname = \"$lname\" and mname = \"$mname\" and fname = \"$fname\";");
					
					if($pid == '') { $pid = "NOT INSERTED"; }
					
					echo "UPLOADING RECORD LINE NO. " . number_format($counter) . " &raquo; $lname, $fname, $mname ($pid) <br/>";
					$counter++;
				} else {
					echo "UNSUCCESSFUL UPLOAD &raquo; $lname, $fname, $mname <br/>";
				}
			}

		}

		//echo "Batch List Successfully Uploaded. Please close this window...";
		
	}
?>