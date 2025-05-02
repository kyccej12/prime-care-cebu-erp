<?php
	session_start();

    //ini_set('display_errors','On');
    require_once "../handlers/initDB.php";

    class hl7 extends myDB {

        public $file;
        public $specimenID;
        public $newFileName;

        public function generateRandomString($length = 32) {
			return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
		}

        public function decodeDate($dateString) {
            return substr($dateString,0,4) . "-" . substr($dateString,4,2) . "-" . substr($dateString,6,2);
        }

        public function checkValidSerial($serialno) {

            $tail = substr($serialno,-1);
            if($tail == 'M') {
                list($isExist) = parent::getArray("select count(*) from pccmobile.lab_samples where serialno = '$serialno';");     
            } else {
                list($isExist) = parent::getArray("select count(*) from pccmain.lab_samples where serialno = '$serialno';");

            }

            if($isExist > 0) {
                return true;
            } else { 
                return false; 
            }
        }

        public function parseCBC($file) {

            $handle = fopen($file, "r");
            $read = file_get_contents($file); 
            $lines = explode("\r", $read);
            $traceno = $this->generateRandomString();
            $updateString = '';
            $i= 0;

            

            foreach($lines as $key => $value) {
                $cols[$i] = explode("|", $value);

                if($cols[$i][0] == 'MSH') { $date = $this->decodeDate($cols[$i][6]); }
                if($cols[$i][0] == "SPM") {
                    $specimenID = $cols[$i][2];
                    
                    if($this->checkValidSerial($specimenID)) {
                        $tail = substr($specimenID,-1);

                        if($tail == 'M') {
                            parent::dbquery("INSERT IGNORE INTO pccmobile.lab_cbcresult_temp (serialno,result_date) VALUES ('$specimenID','$date');");
                        }else {
                            parent::dbquery("INSERT IGNORE INTO pccmain.lab_cbcresult_temp (serialno,result_date) VALUES ('$specimenID','$date');");
                        }                    
                    }

                }
    
                /* RESULTS SEGMENT */
                if($cols[$i][0] == "OBX") {
                    // $identifier = trim($cols[$i][3],'^');
                   $tIdent = explode('^',$cols[$i][3]);
                    $identifier = $tIdent[1];

                    switch($identifier) {
                        case "WBC":
                            if($cols[$i][5] != '---') {
                                $updateString .= ",wbc = '".$cols[$i][5]."'";
                            }
                        break;
                        case "RBC":
                            if($cols[$i][5] != '---') {
                                $updateString .= ",rbc = '".$cols[$i][5]."'";
                            }
                        break;
                        case "HGB":
                            if($cols[$i][5] != '---') {
                                $updateString .= ",hemoglobin = '".$cols[$i][5]."'";
                            }
                        break;
                        case "HCT":
                            if($cols[$i][5] != '---') {
                                $updateString .= ",hematocrit = '".$cols[$i][5]."'";
                            }
                        break;
                        case "NEU%":
                            if($cols[$i][5] != '---') {
                                $updateString .= ",neutrophils = '".$cols[$i][5]."'";
                            }
                        break;
                        case "LYM%":
                            if($cols[$i][5] != '---') {
                                $updateString .= ",lymphocytes = '".$cols[$i][5]."'";
                            }
                        break;
                        case "MON%":
                            if($cols[$i][5] != '---') {
                                $updateString .= ",monocytes = '".$cols[$i][5]."'";
                            }
                        break;
                        case "EOS%":
                            if($cols[$i][5] != '---') {
                                $updateString .= ",eosinophils = '".$cols[$i][5]."'";
                            }
                        break;
                        case "BAS%":
                            if($cols[$i][5] != '---') {
                                $updateString .= ",basophils = '".$cols[$i][5]."'";
                            }
                        break;
                        case "PLT":
                            if($cols[$i][5] != '---') {
                                $updateString .= ",platelate = '".$cols[$i][5]."'";
                            }
                        break;
                        case "MCV":
                            if($cols[$i][5] != '---') {
                                $updateString .= ",mcv = '".$cols[$i][5]."'";
                            }
                        break;
                        case "MCH":
                            if($cols[$i][5] != '---') {
                                $updateString .= ",mch = '".$cols[$i][5]."'";
                            }
                        break;
                        case "MCHC":
                            if($cols[$i][5] != '---') {
                                $updateString .= ",mchc = '".$cols[$i][5]."'";
                            }
                        break;
                        /*
                        case "RDW-CV":
                            if($cols[$i][5] != '---') {
                                $updateString .= ",rdwcv = '".$cols[$i][5]."'";
                            }
                        break;
                        case "RDW-SD":
                            if($cols[$i][5] != '---') {
                                $updateString .= ",rdwsd = '".$cols[$i][5]."'";
                            }
                        break;
                        case "MPV":
                            if($cols[$i][5] != '---') {
                                $updateString .= ",mpv = '".$cols[$i][5]."'";
                            }
                        break;
                        case "PDW":
                            if($cols[$i][5] != '---') {
                                $updateString .= ",pdw = '".$cols[$i][5]."'";
                            }
                        break;
                        case "PCT":
                            if($cols[$i][5] != '---') {
                                $updateString .= ",pct = '".$cols[$i][5]."'";
                            }
                        break;
                        case "P-LCC":
                            if($cols[$i][5] != '---') {
                                $updateString .= ",plcc = '".$cols[$i][5]."'";
                            }
                        break;
                        case "P-LCR":
                            if($cols[$i][5] != '---') {
                                $updateString .= ",plcr = '".$cols[$i][5]."'";
                            }
                        break;
                        */
                    }
                }
                $i++;
            }
    
            fclose($handle);
            $newFileName = "CBC" . $specimenID . ".hl7";

            if($this->checkValidSerial($specimenID)) {

                $tail = substr($specimenID,-1);
                if($tail == 'M') {
                    $updateQuery = "UPDATE IGNORE pccmobile.lab_cbcresult_temp set parsed_on = now(), parsed_file = '$newFileName' $updateString WHERE serialno = '$specimenID';";
                } else {

                    $updateQuery = "UPDATE IGNORE pccmain.lab_cbcresult_temp set parsed_on = now(), parsed_file = '$newFileName' $updateString WHERE serialno = '$specimenID';";
                }

                //  if($_SESSION['userid'] == 1) {
                parent::dbquery($updateQuery);
                rename($file,"out/$newFileName");
             //}

            } else {
                rename($file,"stray/$newFileName");
            }
    
        }


        public function parseChem($file) {

            $handle = fopen($file, "r");
            $read = file_get_contents($file); 
            $lines = explode("\r", $read);
            $updateString = '';
            $i= 0;
    
            foreach($lines as $key => $value) {
                $cols[$i] = explode("|", $value);

                if($cols[$i][0] == 'MSH') { $date = $this->decodeDate($cols[$i][6]); }
    
                if($cols[$i][0] == "OBR") {
                    $specimenID = $cols[$i][2];
                
                    if($specimenID != '') {

                         parent::dbquery("INSERT IGNORE INTO lab_bloodchem_temp (serialno,result_date) VALUES ('" . $specimenID . "','$date');");
                    }
                }
    
                /* RESULTS SEGMENT */
                if($cols[$i][0] == "OBX") {
                    $identifier = trim($cols[$i][4],'^');
                    switch($identifier) {
                        case "ALB":
                            $updateString .= ",albumin = '" . ROUND($cols[$i][5],1) ."'";
                        break;
                        case "ALP":
                            $updateString .= ",alkaline = '" . ROUND($cols[$i][5],1) . "'";
                        break;
                        case "ALT":
                            $updateString .= ",sgpt = '" . ROUND($cols[$i][5],1) . "'";
                        break;
                        case "AMY":
                            $updateString .= ",amylase = '" . ROUND($cols[$i][5],1) . "'";
                        break;
                        case "AST":
                            $updateString .= ",sgot = '" . ROUND($cols[$i][5],1) . "'";
                        break;
                        case "CALCIUM":
                            $updateString .= ",calcium = '" . ROUND($cols[$i][5],1) . "'";
                        break;
                        case "CHO":
                            $updateString .= ",cholesterol = '" . ROUND($cols[$i][5],1) . "'";
                        break;
                        case "DBIL-F":
                            $updateString .= ",bilirubin_direct = '" . ROUND($cols[$i][5],1) . "'";
                        break;
                        case "HDL-C":
                            $updateString .= ",hdl = '" . ROUND($cols[$i][5],1) ."'";
                        break;
                        case "LDH":
                            $updateString .= ",ldh = '" . ROUND($cols[$i][5],1) . "'";
                        break;
                        case "LDL-C":
                            $updateString .= ",ldl = '" . ROUND($cols[$i][5],1) . "'";
                        break;
                        case "LPS":
                            $updateString .= ",lipase = '" . ROUND($cols[$i][5],1) . "'";
                        break;
                        case "TBIL-F":
                            $updateString .= ",bilirubin = '" . ROUND($cols[$i][5],1) . "'";
                        break;
                        case "TRIGLY":
                            $updateString .= ",triglycerides = '" . ROUND($cols[$i][5],1) . "'";
                        break;
                        case "URIC ACID":
                            $updateString .= ",uric = '" . ROUND($cols[$i][5],1) . "'";
                        break;
                        case "BUN":
                            $updateString .= ",bun = '" . ROUND($cols[$i][5],1) . "'";
                        break;
                        case "TPROTEIN":
                            $updateString .= ",protein = '" . ROUND($cols[$i][5],1) . "'";
                        break;
                        case "CREATININE":
                            $updateString .= ",creatinine = '" . ROUND($cols[$i][5],1) . "'";
                        break;
                        case "GLUC/FBS":
                            $updateString .= ",glucose = '" . ROUND($cols[$i][5],1) . "'";
                        break;
                        case "PROCAL":
                            $updateString .= ",procalcitonin = '" . ROUND($cols[$i][5],1) . "'";
                        break;

                    }
                }
                $i++;
            }
    
            fclose($handle);
            $newFileName = "CHM" . $specimenID . ".hl7";

            if($specimenID != '') {
                $updateQuery = "UPDATE IGNORE lab_bloodchem_temp set parsed_on = now(), parsed_file = '$newFileName' $updateString WHERE serialno = '$specimenID';";
                parent::dbquery($updateQuery);
                rename($file,"out/$newFileName");
            } else {
                rename($file,"stray/$newFileName");
            }
    
        }

        // public function parseSpChem($file) {

        //     $handle = fopen($file, "r");
        //     $read = file_get_contents($file); 
        //     $lines = explode("\r", $read);
        //     $updateString = '';
        //     $i= 0;
    
        //     foreach($lines as $key => $value) {
        //         $cols[$i] = explode("|", $value);

        //         if($cols[$i][0] == 'MSH') { $date = $this->decodeDate($cols[$i][6]); }
    
        //         if($cols[$i][0] == "OBR") {
                    
        //             $specimenID = $cols[$i][3];
                
        //             if($specimenID != '') {
                      
        //                 //echo $specimenID . "<br/>";

        //                 list($icount) = parent::getArray("SELECT COUNT(*) from lab_spchem_temp WHERE serialno = '" . $specimenID . "';");
        //                 if($icount == 0) {
        //                     parent::dbquery("INSERT IGNORE INTO lab_spchem_temp (serialno,result_date) VALUES ('" . $specimenID . "','$date');");
        //                 }
        //             }
        //         }
    
        //         /* RESULTS SEGMENT */
        //         if($cols[$i][0] == "OBX") {
        //             $identifier = trim($cols[$i][4],'^');
        //             switch($identifier) {
        //                 case "HbA1c":
        //                     $updateString .= ",hba1c = '" . ROUND($cols[$i][5],1) ."'";
        //                 break;
        //                 case "CRP":
        //                     $updateString .= ",crp = '". $cols[$i][5]. "'";
        //                 break;
        //                 case "Hs-CRP":
        //                     $updateString .= ",hscrp = '" . ROUND($cols[$i][5],1) . "'";
        //                 break;
        //                 case "cTnI":
        //                     $updateString .= ",tropi_qn = '".$cols[$i][5]."'";
        //                 break;
        //                 case "TSH":
        //                     $updateString .= ",tsh = '" . ROUND($cols[$i][5],1) . "'";
        //                 break;
        //                 case "FT3":
        //                     $updateString .= ",ft3 = '" . ROUND($cols[$i][5],1) . "'";
        //                 break;
        //                 case "FT4":
        //                     $updateString .= ",ft4 = '" . ROUND($cols[$i][5],1) . "'";
        //                 break;
        //                 case "T3":
        //                     $updateString .= ",t3 = '" . ROUND($cols[$i][5],1) . "'";
        //                 break;
        //                 case "T4":
        //                     $updateString .= ",t4 = '" . ROUND($cols[$i][5],1) ."'";
        //                 break;

        //             }
        //         }
        //         $i++;
        //     }
    
        //     fclose($handle);
        //     $newFileName = "SPCHM" . $specimenID . ".hl7";

        //     if($specimenID != '') {
        //         $updateQuery = "UPDATE IGNORE lab_spchem_temp set parsed_on = now(), parsed_file = '$newFileName' $updateString WHERE serialno = '$specimenID';";
                
        //         //echo $updateQuery . "<br/>";
                
        //         parent::dbquery($updateQuery);
        //         rename($file,"out/$newFileName");
        //     } else {
        //         rename($file,"stray/$newFileName");
        //     }
    
        // }

        // public function parseProthrombin($file) {
        //     $handle = fopen($file, "r");
        //     $read = file_get_contents($file); 
        //     $lines = explode("\r", $read);
        //     $i= 0; $specimenID = ''; $date = '';
    
        //     foreach($lines as $key => $value) {
        //         $cols[$i] = explode("|", $value);
        //         var_dump($cols[$i]);
               
        //         if($cols[$i][0] == 'MSH') { $specimenID = $cols[$i][7]; }
        //         if($cols[$i][0] == "OBR") { $date =  $this->decodeDate($cols[$i][7]); }
    
        //         /* RESULTS SEGMENT */
        //         if($cols[$i][0] == "OBX") {
        //            $test = $cols[$i][4];
        //            $value = $cols[$i][5];
        //            $unit = $cols[$i][6];
        //            $reference = $cols[$i][7];
                    
        //             parent::dbquery("insert ignore into lab_prothrombin (serialno,`date`,`test`,`result`,`unit`,`reference`) values ('$specimenID','$date','$test','$value','$unit','$reference');");

        //         }
        //         $i++;
        //     }
    
        //     fclose($handle);
        //     $newFileName = "PT" . $specimenID . ".hl7";

        //     if($specimenID != '') {
        //        rename($file,"out/$newFileName");
        //     } else {
        //         rename($file,"stray/$newFileName");
        //     }

        // }


    }

    $con = new hl7;

    $dir = 'in';
    $content = scandir($dir, 1);

    foreach($content as $hfile) {

        $tfile = explode(".",$hfile);

        if($tfile[1] == 'hl7') {

            $file = "in/$hfile";
            $handle = fopen($file, "r"); 
            $read = file_get_contents($file); 
            $lines = explode("\r", $read);
            $i= 0;

            foreach($lines as $key => $value) {
              
                $cols[$i] = explode("|", $value); 
                
                $segment = trim($cols[$i][0],"'");
                $source = trim($cols[$i][3],"'");

               // echo $source;

                if($segment == 'MSH') {

                    /* Close File */
                    fclose($handle);

                    switch($source) {
                        case "BK-200": //BIOBASE BK-200 MACHINE
                            $con->parseChem($file);
                        break;
                        // case "ECA^OCG10251001174^OCG-102": //PROTHROMBIN
                        //     $con->parseProthrombin($file);
                        // break;
                        // case "FA50": //Special Chemistry
                        //     $con->parseSpChem($file);
                        // break;
                        default: //DEFAULTS TO GENRUI HEMA ANALYZER
                            $con->parseCBC($file);
                        break;
                    }
                    
                }

                break;

            }

        }

    }
    

?>