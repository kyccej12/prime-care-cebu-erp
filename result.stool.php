<?php 
	
	session_start();
	require_once "handlers/_generics.php";
	
    $o = new _init;
    $a = $o->getArray("SELECT record_id AS id, LPAD(a.so_no,6,0) AS myso,DATE_FORMAT(b.so_date,'%m/%d/%Y') AS sodate, LPAD(b.patient_id,6,0) AS mypid,b.patient_name AS pname, YEAR(b.so_date) - YEAR(c.birthdate) AS age,IF(c.gender='M','Male','Female') AS gender, DATE_FORMAT(c.birthdate,'%m/%d/%Y') AS bday,e.patientstatus,b.physician,a.code,a.procedure,sampletype,serialno,DATE_FORMAT(extractdate,'%m/%d/%Y') AS exday,TIME_FORMAT(extractime,'%h:%i %p') AS etime,extractby,a.location FROM lab_samples a LEFT JOIN so_header b ON a.so_no = b.so_no AND a.branch = b.branch LEFT JOIN patient_info c ON b.patient_id = c.patient_id LEFT JOIN options_patientstat e ON b.patient_stat = e.id WHERE a.record_id = '$_REQUEST[lid]';");
    $b = $o->getArray("select * from lab_stoolexam where so_no = '$a[myso]' and branch = '$_SESSION[branchid]' and serialno = '$a[serialno]';");

    if($b['ova_parasites'] == '') { $b['ova_parasites'] = 'NO OVA & PARASITES SEEN'; }
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
            
            $("#stool_date").datepicker(); 
            var availableOptions = [
                "NEGATIVE",
                "TRACE"
            ];

            var availableOptions2 = [
                "POSITIVE",
                "NEGATIVE"
            ];

            var availableOptions3 = [
                "MODERATE",
                "FEW",
                "RARE",
                "ABUNDANT",
                "MANY"
            ];

            $("#blood, #mucus" ).autocomplete({
                 source: availableOptions, minLength: 0
            }).focus(function() {
                $(this).data("uiAutocomplete").search($(this).val());
            });

            $("#yeast_cells, #globules, #bacteria, #occult_blood").autocomplete({
                 source: availableOptions3,
                 minLength: 0
            }).focus(function() {
                $(this).data("uiAutocomplete").search($(this).val());
            });
        
        });

        $(document).on('keydown', 'input[pattern]', function(e){
            var input = $(this);
            var oldVal = input.val();
            var regex = new RegExp(input.attr('pattern'), 'g');

            setTimeout(function(){
                var newVal = input.val();
                if(!regex.test(newVal)){
                input.val(oldVal); 
                }
            }, 1);
        });

        $(document).on('keypress', 'input', function(e) {
            if(e.keyCode == 13) {
                e.preventDefault();
                var inputs = $(this).closest('form').find(':input:visible');
                inputs.eq( inputs.index(this)+ 1 ).focus();
            }
        });
    </script>
</head>
<body>
    <form name="frmStoolReport" id="frmStoolReport"> 
        <table width=100% cellpadding=0 cellspacing=0 valign=top>
         <tr>
             <td width=35% valign=top>
                <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>PATIENT & ORDER INFORMATION</td></tr></table>
                <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px; margin-bottom: 5px;">
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">SO #&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="stool_sono" id="stool_sono" value="<?php echo $a['myso']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">Service Order Date&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="stool_sodate" id="stool_sodate" value="<?php echo $a['sodate']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Patient ID&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="stool_pid" id="stool_pid" value="<?php echo $a['mypid']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">Result Date&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="stool_date" id="stool_date" value="<?php if($rdate !='') { echo $rdate; } else { echo date('m/d/Y'); } ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Patient Name&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="stool_pname" id="stool_pname" value="<?php echo $a['pname']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>

                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Gender&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="stool_gender" id="stool_gender" value="<?php echo $a['gender']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Birthdate&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="stool_birthdate" id="stool_birthdate" value="<?php echo $a['bday']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Age&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="stool_age" id="stool_age" value="<?php echo $a['age']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Patient Status&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="stool_patientstat" id="stool_patientstat" value="<?php echo $a['patientstatus']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Requesting Physician&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="stool_physician" id="stool_physician" value="<?php echo $a['physician']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                </table>
                <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>SAMPLE DETAILS</td></tr></table>
                <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Test or Procedure&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="stool_procedure" id="stool_procedure" value="<?php echo $a['procedure']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Procedure Code&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="stool_code" id="stool_code" value="<?php echo $a['code']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Specimen Type&nbsp;:</td>
                        <td align=left>
                            <select class="gridInput" style="width:100%;" name="stool_spectype" id="stool_spectype">
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
                            <input type="text" class="gridInput" style="width:100%;" name="stool_serialno" id="stool_serialno" value="<?php echo $a['serialno']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Date Extracted&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="stool_extractdate" id="stool_extractdate" value="<?php echo $a['exday']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Time Extracted&nbsp;:</td>
                        <td align=left>
                
                            <input type="text" class="gridInput" style="width:100%;" name="stool_extracttime" id="stool_extracttime" value="<?php echo $a['etime']; ?>" readonly>

                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Extracted By&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="stool_extractby" id="stool_extractby" value="<?php echo $a['extractby']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Site&nbsp;:</td>
                        <td align=left>
                            <select class="gridInput" style="width:100%;" name="stool_location" id="stool_location">
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
                                <option value="Brown" <?php if($b['color'] == 'Brown') { echo "selected"; } ?>>Brown</option>
                                <option value="Yellowish Brown" <?php if($b['color'] == 'Yellowish Brown') { echo "selected"; } ?>>Yellowish Brown</option>
                                <option value="Yellowish" <?php if($b['color'] == 'Yellowish') { echo "selected"; } ?>>Yellowish</option>
                                <option value="Reddish Brown" <?php if($b['color'] == 'Reddish Brown') { echo "selected"; } ?>>Reddish Brown</option>
                                <option value="Light Brown" <?php if($b['color'] == 'Light Brown') { echo "selected"; } ?>>Light Brown</option>
                                <option value="Yellowish" <?php if($b['color'] == 'Yellowish') { echo "selected"; } ?>>Yellowish</option>
                                <option value="Greenish Brown" <?php if($b['color'] == 'Greenish Brown') { echo "selected"; } ?>>Greenish Brown</option>
                                <option value="Dark Brown" <?php if($b['color'] == 'Dark Brown') { echo "selected"; } ?>>Dark Brown</option>
                                <option value="Brown Black" <?php if($b['color'] == 'Brown Black') { echo "selected"; } ?>>Brown Black</option>
                                <option value="Greenish" <?php if($b['color'] == 'Greenish') { echo "selected"; } ?>>Greenish</option>
                                <option value="Black" <?php if($b['color'] == 'Black') { echo "selected"; } ?>>Black</option>
                            </select>
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>
                    <tr>
                        <td align="left" width=25% class="bareBold" style="padding-left: 25px;">Consistency&nbsp;:</td>
                        <td align=left>
                            <select name="consistency" id="consistency" class="gridInput" style="width:100%;">
                                <option value="Formed" <?php if($b['consistency'] == 'Formed') { echo "selected"; } ?>>Formed</option>
                                <option value="Semi Formed" <?php if($b['appearance'] == 'Semi Formed') { echo "selected"; } ?>>Semi Formed</option>
                                <option value="Soft" <?php if($b['consistency'] == 'Soft') { echo "selected"; } ?>>Soft</option>
                                <option value="Watery" <?php if($b['consistency'] == 'Watery') { echo "selected"; } ?>>Watery</option>
                                <option value="Mucoid" <?php if($b['consistency'] == 'Mucoid') { echo "selected"; } ?>>Mucoid</option>
                                <option value="Mushy" <?php if($b['consistency'] == 'Mushy') { echo "selected"; } ?>>Mushy</option>
                                <option value="Loose" <?php if($b['consistency'] == 'Loose') { echo "selected"; } ?>>Loose</option>
                            </select>
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 25px;"></td>	
                    </tr>

                    <tr>
                        <td align="left" width=25% class="bareBold" style="padding-left: 25px;">Blood&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="blood" id="blood" value="<?php echo $b['blood']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>

                    <tr>
                        <td align="left" width=25% class="bareBold" style="padding-left: 25px;">Mucus&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="mucus" id="mucus" value="<?php echo $b['mucus']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>
      
                    <tr>
                        <td align="left" width=25% class="bareBold" style="padding-left: 25px;">Parasites&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="parasites" id="parasites" value="<?php echo $b['parasites']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>
                    
                    <tr>
                        <td align="left" colspan=3 class="bareBold" style="padding-left: 15px;"><b>MICROSCOPIC&nbsp;:</b></td>
                    </tr>

                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">RBC/hpf&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="rbc_hpf" id="rbc_hpf" value="<?php echo $b['rbc']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>

                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">WBC/hpf&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="wbc_hpf" id="wbc_hpf" value="<?php echo $b['wbc']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 25px;"></td>	
                    </tr>

                    <tr>
                        <td align="left" width=25% class="bareBold" style="padding-left: 25px;" valign=top>Ova & Parasites&nbsp;:</td>
                        <td align=left width=75% colspan=3>

                            <input type="text" name="ova_parasites" id="ova_parasites" style="width: 90%; height: 60px; text-align: center;" value="<?php echo $b['ova_parasites']; ?>">
                        </td>
                    </tr>

                    <tr>
                        <td align="left" colspan=3 class="bareBold" style="padding-left: 15px;"><b>Others&nbsp;:</b></td>
                    </tr>

                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Bacteria&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bacteria" id="bacteria" value="<?php echo $b['bacteria']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>

                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Fat Globules&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="globules" id="globules" value="<?php echo $b['globules']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>

                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Yeast Cells&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="yeast_cells" id="yeast_cells" value="<?php echo $b['yeast_cells']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>

                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 25px;">Occult Blood&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="occult_blood" id="occult_blood" value="<?php echo $b['occult_blood']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"></td>	
                    </tr>
                    <tr>
                        <td align="left" width=25% class="bareBold" style="padding-left: 15px; font-weight: bold;" valign=top>Remarks&nbsp;:</td>
                        <td align=left width=75% colspan=3>
                            <input type="text" name="remarks" id="remarks" style="width: 90%; height: 60px; text-align: center;" value="<?php echo $b['remarks']; ?>">
                        </td>
                    </tr>
                    <tr><td height=70>&nbsp;</td></tr>
                </table>
            </td>
        </tr>
    </table>              
</form>
</body>
</html>