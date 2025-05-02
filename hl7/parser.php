<?php
	$dir    = 'in';
    $content = scandir($dir, 1);

    foreach($content as $hfile) {

        $file = "in/$hfile";// Your Temp Uploaded file
		$handle = fopen($file, "r"); // Make all conditions to avoid errors
		$read = file_get_contents($file); //read
		$lines = explode("\r", $read);//get
		$i= 0;//initialize

        $specimenID = '';

        foreach($lines as $key => $value) {
            $cols[$i] = explode("|", $value);

			if($cols[$i][0] == MSH) {
                $specimenID = $cols[$i][12];
            }

            if($specimenID != '') {
                if($cols[$i][0] == 'OBX') {
                    

                }

            }

            $i++;
        }

    }
    

?>