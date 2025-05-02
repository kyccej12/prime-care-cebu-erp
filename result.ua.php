<?php 
	
	session_start();
	require_once "handlers/_generics.php";
	
    $o = new _init;

    $a = $o->getArray("SELECT record_id AS id, LPAD(a.so_no,6,0) AS myso,DATE_FORMAT(b.so_date,'%m/%d/%Y') AS sodate, LPAD(b.patient_id,6,0) AS mypid,b.patient_name AS pname, YEAR(b.so_date) - YEAR(c.birthdate) AS age,IF(c.gender='M','Male','Female') AS gender, DATE_FORMAT(c.birthdate,'%m/%d/%Y') AS bday,e.patientstatus,b.physician,a.code,a.procedure,sampletype,serialno,DATE_FORMAT(extractdate,'%m/%d/%Y') AS exday,TIME_FORMAT(extractime,'%h:%i %p') AS etime,extractby,a.location FROM lab_samples a LEFT JOIN so_header b ON a.so_no = b.so_no AND a.branch = b.branch LEFT JOIN patient_info c ON b.patient_id = c.patient_id LEFT JOIN options_patientstat e ON b.patient_stat = e.id WHERE a.record_id = '$_REQUEST[lid]';");
    $b = $o->getArray("select * from lab_uaresult where so_no = '$a[myso]' and serialno = '$a[serialno]' and branch = '$_SESSION[branchid]';");
   
    /* SET DEFAULT VALUE */
    if(!$b['glucose']) { $b['glucose'] = 'NEGATIVE'; }
    if(!$b['protein']) { $b['protein'] = 'NEGATIVE'; }

    if($b['ph'] >= 7) { 
        $uratesDisabled = "disabled"; 
        $poDisabled = '';
    } else {
        $uratesDisabled = ''; 
        $poDisabled = "disabled";
    }


?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Prime Care Cebu, Inc.</title>
	<link rel="icon" type="image/x-icon" href="favicon.ico" />
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="ui-assets/texteditor/jquery-te-1.4.0.css" rel="stylesheet" type="text/css" />
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<script language="javascript" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script language="javascript" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script language="javascript" src="ui-assets/texteditor/jquery-te-1.4.0.min.js"></script>
	<script language="javascript" src="js/main.js?sid=<?php echo uniqid(); ?>"></script>
    <script>
        $(function() { 
            $("#ua_date").datepicker(); 
            
            var availableOptions = [
                "NEGATIVE",
                "POSITIVE",
                "TRACE"
            ];

            var availableOptions2 = [
                "POSITIVE",
                "NEGATIVE"
            ];

            var availableOptions3 = [
                "TRIPLE PHOSPHATE: RARE",
                "TRIPLE PHOSPHATE: FEW",
                "TRIPLE PHOSPHATE: MODERATE",
                "TRIPLE PHOSPHATE: ABUNDANT",
                "CALCIUM OXALATE: RARE",
                "CALCIUM OXALATE: FEW",
                "CALCIUM OXALATE: MODERATE",
                "CALCIUM OXALATE: ABUNDANT",
                "URIC ACID: RARE",
                "URIC ACID: FEW",
                "URIC ACID: MODERATE",
                "URIC ACID: ABUNDANT"
            ];

            var availableOptions4 = [
                "WBC CASTS: 0-1/LPF",
                "RBC CASTS: 0-1/LPF",
                "WBC IN CLUMPS: 0-1/HPF",
                "RBC IN CLUMPS: 0-1/KPF",
                "HYALINE: 0-1/HPF",
                "COARSE GRANULAR CAST: 0-1/LPF"
            ];

            var availableOptions5 = [
                "MODERATE",
                "FEW",
                "RARE",
                "ABUNDANT",
                "MANY"
            ];

            var avalilableOptions6 = [
                "NORMAL"
            ];

            $("#glucose, #protein, #leukocyte, #nitrite, #blood, #ketone , #bilirubin" ).autocomplete({
                 source: availableOptions,
                 minLength: 0
            }).focus(function() {
                $(this).data("uiAutocomplete").search($(this).val());
            });

            $("#crystals").autocomplete({
                 source: availableOptions3,
                 minLength: 0
            }).focus(function() {
                $(this).data("uiAutocomplete").search($(this).val());
            }); 

            $("#urobilinogen").autocomplete({
                 source: avalilableOptions6,
                 minLength: 0
            }).focus(function() {
                $(this).data("uiAutocomplete").search($(this).val());
            }); 

            $("#casts").autocomplete({
                 source: availableOptions4,
                 minLength: 0
            }).focus(function() {
                $(this).data("uiAutocomplete").search($(this).val());
            });

            $("#bacteria, #amorphous_urates, #amorphous_po4, #epith_hpf, #mucus_thread").autocomplete({
                source: availableOptions5,
                minLength: 0
            }).focus(function() {
                $(this).data("uiAutocomplete").search($(this).val());
            });
        
        });

        /* $(document).on('keydown', 'input[pattern]', function(e){
            var input = $(this);
            var oldVal = input.val();
            var regex = new RegExp(input.attr('pattern'), 'g');

            setTimeout(function(){
                var newVal = input.val();
                if(!regex.test(newVal)){
                input.val(oldVal); 
                }
            }, 1);
        
        }); */

        $(document).on('keypress', 'input', function(e) {
            if(e.keyCode == 13) {
                e.preventDefault();
                var inputs = $(this).closest('form').find(':input:visible');
                 inputs.eq( inputs.index(this)+ 1 ).focus();
            }
        });

        function checkPhValue(val) {
            var ph = parseFloat(val);

            if(ph >= 7) {
                $("#amorphous_urates").val('');
                $("#amorphous_urates").attr({ disabled: true });
                $("#amorphous_po4").attr({ disabled: false });
            } else {
                $("#amorphous_po4").val('');
                $("#amorphous_po4").attr({ disabled: true });
                $("#amorphous_urates").attr({ disabled: false });
            }


        }

    </script>
</head>
<body>
    <form name="frmUrinalysisReport" id="frmUrinalysisReport"> 
        <table width=100% cellpadding=0 cellspacing=0 valign=top>
         <tr>
             <td width=35% valign=top>
                <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>PATIENT & ORDER INFORMATION</td></tr></table>
                <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px; margin-bottom: 5px;">
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">SO #&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="ua_sono" id="ua_sono" value="<?php echo $a['myso']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">Service Order Date&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="ua_sodate" id="ua_sodate" value="<?php echo $a['sodate']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Patient ID&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ua_pid" id="ua_pid" value="<?php echo $a['mypid']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">Result Date&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="ua_date" id="ua_date" value="<?php if($rdate !='') { echo $rdate; } else { echo date('m/d/Y'); } ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Patient Name&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ua_pname" id="ua_pname" value="<?php echo $a['pname']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>

                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Gender&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ua_gender" id="ua_gender" value="<?php echo $a['gender']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Birthdate&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ua_birthdate" id="ua_birthdate" value="<?php echo $a['bday']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Age&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ua_age" id="ua_age" value="<?php echo $a['age']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Patient Status&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ua_patientstat" id="ua_patientstat" value="<?php echo $a['patientstatus']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Requesting Physician&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ua_physician" id="ua_physician" value="<?php echo $a['physician']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                </table>
                <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>SAMPLE DETAILS</td></tr></table>
                <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Test or Procedure&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ua_procedure" id="ua_procedure" value="<?php echo $a['procedure']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Procedure Code&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ua_code" id="ua_code" value="<?php echo $a['code']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Specimen Type&nbsp;:</td>
                        <td align=left>
                            <select class="gridInput" style="width:100%;" name="ua_spectype" id="ua_spectype">
                                <?php
                                    $iun = $o->dbquery("select id,sample_type from options_sampletype;");
                                    while(list($aa,$ab) = $iun->fetch_array()) {
                                        echo "<option value='$aa'";
                                        if($aa == $a['sampletype']) { echo "selected"; }
                                       echo ">$ab</option>";
                                    }
                                ?>
                            </select>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Sample Serial No.&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ua_serialno" id="ua_serialno" value="<?php echo $a['serialno']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Date Extracted&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ua_extractdate" id="ua_extractdate" value="<?php echo $a['exday']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Time Extracted&nbsp;:</td>
                        <td align=left>
                
                            <input type="text" class="gridInput" style="width:100%;" name="ua_extracttime" id="ua_extracttime" value="<?php echo $a['etime']; ?>" readonly>

                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Extracted By&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ua_extractby" id="ua_extractby" value="<?php echo $a['extractby']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Extraction Site&nbsp;:</td>
                        <td align=left>
                            <select class="gridInput" style="width:100%;" name="ua_location" id="ua_location">
                                <?php
                                    $iun = $o->dbquery("select id,location from lab_locations;");
                                    while(list($aa,$ab) = $iun->fetch_array()) {
                                        echo "<option value='$aa' ";
                                        if($aa == $a['location']) { echo "selected"; }
                                        echo ">$ab</option>";
                                    }
                                ?>
                            </select>
                        </td>				
                    </tr>
                </table>   
            </td>
            <td width=1%>&nbsp;</td>
            <td width=64% valign=top >
                 <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>RESULT DETAILS</td></tr></table>
                 <table width=100% cellpadding=0 cellspacing=3 class="td_content">
                    <tr>
                        <td align="left" colspan=3 class="bareBold" style="padding-left: 15px;"><b>MACROSCOPIC&nbsp;:</b></td>
                    </tr>
                    <tr>
                        <td align="left" width=25% class="bareBold" style="padding-left: 25px;">Color&nbsp;:</td>
                        <td align=left width=30%>
                            <select name="color" id="color" class="gridInput" style="width:100%;">
                                <option value="Clear" <?php if($b['color'] == 'Clear') { echo "selected"; } ?>>Clear</option>
                                <option value="Yellow" <?php if($b['color'] == 'Yellow') { echo "selected"; } ?>>Yellow</option>
                                <option value="Light Yellow" <?php if($b['color'] == 'Light Yellow') { echo "selected"; } ?>>Light Yellow</option>
                                <option value="Dark Yellow" <?php if($b['color'] == 'Dark Yellow') { echo "selected"; } ?>>Dark Yellow</option>
                                <option value="Amber" <?php if($b['color'] == 'Amber') { echo "selected"; } ?>>Amber</option>
                                <option value="Straw" <?php if($b['color'] == 'Straw') { echo "selected"; } ?>>Straw</option>
                                <option value="Dark Brown" <?php if($b['color'] == 'Dark Brown') { echo "selected"; } ?>>Dark Brown</option>
                                <option value="Bright Yellow" <?php if($b['color'] == 'Bright Yellow') { echo "selected"; } ?>>Bright Yellow</option>
                            </select>
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>
                    <tr>
                        <td align="left" width=25% class="bareBold" style="padding-left: 25px;">Appearance&nbsp;:</td>
                        <td align=left>
                            <select name="appearance" id="appearance" class="gridInput" style="width:100%;">
                                <option value="Clear" <?php if($b['appearance'] == 'Clear') { echo "selected"; } ?>>Clear</option>
                                <option value="Hazy" <?php if($b['appearance'] == 'Hazy') { echo "selected"; } ?>>Hazy</option>
                                <option value="Slightly Hazy" <?php if($b['appearance'] == 'Slightly Hazy') { echo "selected"; } ?>>Slightly Hazy</option>
                                <option value="Cloudy" <?php if($b['appearance'] == 'Cloudy') { echo "selected"; } ?>>Cloudy</option>
                                <option value="Slightly Cloudy" <?php if($b['appearance'] == 'Slightly Cloudy') { echo "selected"; } ?>>Slightly Cloudy</option>
                                <option value="Turbid" <?php if($b['appearance'] == 'Turbid') { echo "selected"; } ?>>Turbid</option>
                            </select>
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 25px;"></td>	
                    </tr>
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Leukocytes&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="leukocytes" id="leukocytes" value="<?php if($b['leukocytes'] == '') { echo "NEGATIVE"; } else { echo $b['leukocytes']; } ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Nitrite&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="nitrite" id="nitrite" value="<?php if($b['nitrite'] == '') { echo "NEGATIVE"; } else { echo $b['nitrite']; } ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Urobilinogen&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="urobilinogen" id="urobilinogen" value="<?php if($b['urobilinogen'] == '') { echo "NORMAL"; } else { echo $b['urobilinogen']; } ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Protein&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="protein" id="protein" value="<?php echo $b['protein']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>
                    <tr>
                        <td align="left" width=25% class="bareBold" style="padding-left: 25px;">pH&nbsp;:</td>
                        <td align=left>
                            <select name="ph" id="ph" class="gridInput" style="width:100%;" onchange="javascript: checkPhValue(this.value);">
                            <?php
                                for($phloop = 4.5; $phloop <= 8; $phloop+=0.5) {
                                    echo "<option value='".number_format($phloop,1)."' "; 
                                    if($b['ph'] == $phloop) { echo "selected"; }
                                    echo ">".number_format($phloop,1)."</option>";
                                }

                            ?>
                            </select>
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Blood&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="blood" id="blood" value="<?php if($b['blood'] == '') { echo "NEGATIVE"; } else { echo $b['blood']; } ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;" valign=top>Specific Gravity&nbsp;:</td>
                        <td align=left valign=top>
                            <select name="gravity" id="gravity" class="gridInput" style="width:100%;">
                            <?php
                                for($sgloop = 1.005; $sgloop <= 1.030; $sgloop+=0.005) {
                                    $valsg = number_format($sgloop, 3);

                                    echo "<option value='".$valsg."' "; 
                                    if($b['gravity'] == $valsg) { echo "selected"; }
                                    echo ">".$valsg."</option>";
                                }

                                /* echo "<option value = '1.030' ";
                                if($b['gravity'] == '1.030') { echo "selected"; }
                                echo ">1.030</option>"; */

                            ?>
                            </select>

                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;" valign=top></td>	
                    </tr>
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Ketone&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ketone" id="ketone" value="<?php if($b['ketone'] == '') { echo "NEGATIVE"; } else { echo $b['ketone']; } ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Bilirubin&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bilirubin" id="bilirubin" value="<?php if($b['bilirubin'] == '') { echo "NEGATIVE"; } else { echo $b['bilirubin']; } ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Glucose&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="glucose" id="glucose" value="<?php if($b['glucose'] == '') { echo "NEGATIVE"; } else { echo $b['glucose']; } ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>
                    
                    <tr>
                        <td align="left" colspan=3 class="bareBold" style="padding-left: 15px; padding-top: 20px;"><b>MICROSCOPIC&nbsp;:</b></td>
                    </tr>

                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">RBC/hpf&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="rbc_hpf" id="rbc_hpf" value="<?php echo $b['rbc_hpf']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>

                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">WBC/hpf&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="wbc_hpf" id="wbc_hpf" value="<?php echo $b['wbc_hpf']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 25px;"></td>	
                    </tr>

                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Epith./hpf&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="epith_hpf" id="epith_hpf" value="<?php echo $b['epith_hpf']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 25px;"></td>	
                    </tr>

                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Casts&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="casts" id="casts" value="<?php echo $b['casts']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 25px;"></td>	
                    </tr>

                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Mucus Threads&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="mucus_thread" id="mucus_thread" value="<?php echo $b['mucus_thread']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 25px;"></td>	
                    </tr>

                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Bacteria&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bacteria" id="bacteria" value="<?php echo $b['bacteria']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>

                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Crystals&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="crystals" id="crystals" value="<?php echo $b['crystals']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Amorphous (Urates)&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="amorphous_urates" id="amorphous_urates" value="<?php echo $b['amorphus_urates']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Amorphous (PO<sub>4</sub>)&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="amorphous_po4" id="amorphous_po4" value="<?php echo $b['amorphus_po4']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>
                    <tr>
                        <td align="left" width=25% class="bareBold" style="padding-left: 15px;" valign=top><b>Note&nbsp;:</b></td>
                        <td align=left width=75% colspan=3>
                            <input type="text" class="gridInput" style="width:90%; height: 50px; text-align: center;" name="remarks" id="remarks" value="<?php echo $b['remarks']; ?>">
                        </td>
                    </tr>
                    <tr>
                        <td align="left" width=25% class="bareBold" style="padding-left: 15px;" valign=top><b>Others&nbsp;:</b></td>
                        <td align=left width=75% colspan=3>
                            <input type="text" class="gridInput" style="width:90%; " name="others" id="others" value="<?php echo $b['others']; ?>">
                        </td>
                    </tr> 
                </table>
            </td>
        </tr>
    </table>              
</form>
</body>
</html>