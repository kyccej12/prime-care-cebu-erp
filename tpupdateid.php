<?php

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

            $lname = strtoupper($cols[$i][0]);
            $fname = strtoupper($cols[$i][1]);
			$mname = strtoupper($cols[$i][2]);
			
			if($lname != '') {
			
				$lname = str_replace("&NTILDE;","&Ntilde;",$lname);
				$mname = str_replace("&NTILDE;","&Ntilde;",$mname);
				$fname = str_replace("&NTILDE;","&Ntilde;",$fname);
				$suffix = $cols[$i][3];
				$dob = $cols[$i][4];
				$gender = $cols[$i][5];
				$pob = $cols[$i][7];
				$email = $cols[$i][8];
				$cell = $cols[$i][9];
				$cstat = $cols[$i][10];
				$eid = $cols[$i][11];
				$hmo = $cols[$i][12];
                $idno = $cols[$i][13];


	
                list($pid) = $con->getArray("select patient_id from patient_info where lname = \"$lname\" and mname = \"$mname\" and fname = \"$fname\";");
                
                if($pid != '') {
                
                    echo "$counter ID NO &raquo; $lname, $fname, $mname ($pid) <br/>";
                    $counter++;
                    
                    echo "UPDATE IGNORE patient_info set emp_idno = '$eid' where patient_id = '$pid';<br/><br/>";
                    $con->dbquery("UPDATE IGNORE patient_info set emp_idno = '$eid' where patient_id = '$pid';");
                }

			}

		}
		
	}
?>