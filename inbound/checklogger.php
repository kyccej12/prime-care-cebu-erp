<?php

    include("../handlers/initDB.php");

    $con = new myDB;

    function decodeDate($dateString) {
        return substr($dateString,0,4) . "-" . substr($dateString,4,2) . "-" . substr($dateString,6,2);
    }

    function generateRandomString($length = 32) {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }


    $dir = 'LOG';
    $content = scandir($dir, 1);

    foreach($content as $hfile) {

        $tfile = explode(".",$hfile);

        if($tfile[1] == 'log') {

            $file = "LOG/$hfile";
            $handle = fopen($file, "r");
            $read = file_get_contents($file); 
            $lines = explode("\r", $read);
            $traceno = generateRandomString();
            $updateString = '';
            $i = 0;

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
                
                if(substr($cols[$i][0],-1) == "R") {
                    $segmentValue = explode("^",$cols[$i][2]);
                
                    switch($segmentValue[3]) {
                        case "WBC":
                            $updateString .= ",wbc = '".$cols[$i][3]."'";
                        break;
                        case "RBC":
                            $updateString .= ",rbc = '".$cols[$i][3]."'";
                        break;
                        case "HGB":
                            $updateString .= ",hemoglobin = '".$cols[$i][3]."'";
                        break;
                        case "HCT":
                            $updateString .= ",hematocrit = '".$cols[$i][3]."'";
                        break;
                        case "NEU%":
                            $updateString .= ",neutrophils = '".$cols[$i][3]."'";
                        break;
                        case "LYM%":
                            $updateString .= ",lymphocytes = '".$cols[$i][3]."'";
                        break;
                        case "MON%":
                            $updateString .= ",monocytes = '".$cols[$i][3]."'";
                        break;
                        case "EOS%":
                            $updateString .= ",eosinophils = '".$cols[$i][3]."'";
                        break;
                        case "BAS%":
                            $updateString .= ",basophils = '".$cols[$i][3]."'";
                        break;
                        case "PLT":
                            $updateString .= ",platelate = '".$cols[$i][3]."'";
                        break;
                        case "MCV":
                            $updateString .= ",mcv = '".$cols[$i][3]."'";
                        break;
                        case "MCH":
                            $updateString .= ",mch = '".$cols[$i][3]."'";
                        break;
                        case "MCHC":
                            $updateString .= ",mchc = '".$cols[$i][3]."'";
                        break;
                        case "RDW-CV":
                            $updateString .= ",rdwcv = '".$cols[$i][3]."'";
                        break;
                        case "RDW-SD":
                            $updateString .= ",rdwsd = '".$cols[$i][3]."'";
                        break;
                        case "MPV":
                            $updateString .= ",mpv = '".$cols[$i][3]."'";
                        break;
                        case "PDW-CV":
                            $updateString .= ",pdwcv = '".$cols[$i][3]."'";
                        break;
                        case "PDW-SD":
                            $updateString .= ",pdwsd = '".$cols[$i][3]."'";
                        break;
                        case "PCT":
                            $updateString .= ",pct = '".$cols[$i][3]."'";
                        break;
                        case "P-LCC":
                            $updateString .= ",plcc = '".$cols[$i][3]."'";
                        break;
                        case "P-LCR":
                            $updateString .= ",plcr = '".$cols[$i][3]."'";
                        break;

                    }
                }
                $i++;
            }

            fclose($handle);
            $newFileName = "CBC" . strtoupper($traceno) . $specimenID . ".log";

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
    }

?>