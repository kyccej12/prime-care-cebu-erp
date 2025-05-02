<?php 
	
	session_start();
	require_once "handlers/_generics.php";
	
    $o = new _init;

    $a = $o->getArray("SELECT record_id AS id, LPAD(a.so_no,6,0) AS myso,DATE_FORMAT(b.so_date,'%m/%d/%Y') AS sodate, LPAD(b.patient_id,6,0) AS mypid,b.patient_name AS pname, YEAR(b.so_date) - YEAR(c.birthdate) AS age,IF(c.gender='M','Male','Female') AS gender, DATE_FORMAT(c.birthdate,'%m/%d/%Y') AS bday,e.patientstatus,b.physician,a.code,a.procedure,sampletype,serialno,DATE_FORMAT(extractdate,'%m/%d/%Y') AS exday,TIME_FORMAT(extractime,'%h:%i %p') AS etime,extractby,a.location, a.machine FROM lab_samples a LEFT JOIN so_header b ON a.so_no = b.so_no AND a.branch = b.branch LEFT JOIN patient_info c ON b.patient_id = c.patient_id LEFT JOIN options_patientstat e ON b.patient_stat = e.id WHERE a.record_id = '$_REQUEST[lid]';");
    //list($impression,$rdate) = $o->getArray("select impression,date_format(result_date,'%m/%d/%Y') from lab_descriptive where so_no = '$a[myso]' and branch = '$_SESSION[branchid]' and code = '$a[code]' and serialno = '$a[serialno]';");
    $b = $o->getArray("select * from lab_cbcresult where so_no = '$a[myso]' and serialno = '$a[serialno]';");

    // $mach = $o->getArray("select * from lab_cbc_defvalues where mach = '$a[machine]';");

    if(count($b) == 0) {
        $b = $o->getArray("select * from lab_cbcresult_temp where serialno = '$a[serialno]';");
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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
	<script language="javascript" src="js/main.js?sid=<?php echo uniqid(); ?>"></script>
    <script>
        $(function() { $("#cbc_date").datepicker(); });

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

        $('input.number').keyup(function (event) {
                // skip for arrow keys
                if (event.which >= 37 && event.which <= 40) return;
                // format number
                $(this).val(function (index, value) {
                    return value
                    .replace(/\D/g, "")
                    .replace(/\B(?=(\d{3})+(?!\d))/g, ",")
                    ;
                });
            });

        function separateMe(val) {

            valu = parseFloat(parent.stripComma(val));

            $("#platelate").val(parent.kSeparator(valu));
        }

        function changeMachine(val) {
            $.post("src/sjerp.php", { mod: "changeCbcMachine", so_no: $("#cbc_sono").val(), serialno: $("#cbc_serialno").val(), machine: val, sid: Math.random() }, function() {
                setTimeout(function(){ 
                },350);
            });
        }

    </script>
</head>
<body>
    <form name="frmCBCResult" id="frmCBCResult"> 
        <table width=100% cellpadding=0 cellspacing=0 valign=top>
         <tr>
             <td width=35% valign=top>
                <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>PATIENT & ORDER INFORMATION</td></tr></table>
                <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px; margin-bottom: 5px;">
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">SO #&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="cbc_sono" id="cbc_sono" value="<?php echo $a['myso']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">Service Order Date&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="cbc_sodate" id="cbc_sodate" value="<?php echo $a['sodate']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Patient ID&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="cbc_pid" id="cbc_pid" value="<?php echo $a['mypid']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">Result Date&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="cbc_date" id="cbc_date" value="<?php if($rdate !='') { echo $rdate; } else { echo date('m/d/Y'); } ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Patient Name&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="cbc_pname" id="cbc_pname" value="<?php echo $a['pname']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>

                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Gender&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="cbc_gender" id="cbc_gender" value="<?php echo $a['gender']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Birthdate&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="cbc_birthdate" id="cbc_birthdate" value="<?php echo $a['bday']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Age&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="cbc_age" id="cbc_age" value="<?php echo $a['age']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Patient Status&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="cbc_patientstat" id="cbc_patientstat" value="<?php echo $a['patientstatus']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Requesting Physician&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="cbc_physician" id="cbc_physician" value="<?php echo $a['physician']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                </table>
                <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>SAMPLE DETAILS</td></tr></table>
                <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Test or Procedure&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="cbc_procedure" id="cbc_procedure" value="<?php echo $a['procedure']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Procedure Code&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="cbc_code" id="cbc_code" value="<?php echo $a['code']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Specimen Type&nbsp;:</td>
                        <td align=left>
                            <select class="gridInput" style="width:100%;" name="cbc_spectype" id="cbc_spectype">
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
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Machine&nbsp;:</td>
                        <td align=left>
                            <select class="gridInput" style="width:100%;" name="cbc_machine" id="cbc_machine" onchange="javascript: changeMachine(this.value);">
                               <option value = 'YUMIZEN' <?php if($b['machine'] == 'YUMIZEN') { echo "selected"; } ?>>Yumizen</option>
                               <option value = 'STAC' <?php if($b['machine'] == 'STAC') { echo "selected"; } ?>>STAC</option>
                            </select>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Sample Serial No.&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="cbc_serialno" id="cbc_serialno" value="<?php echo $a['serialno']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Date Extracted&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="cbc_extractdate" id="cbc_extractdate" value="<?php echo $a['exday']; ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Time Extracted&nbsp;:</td>
                        <td align=left>
                
                            <input type="text" class="gridInput" style="width:100%;" name="cbc_extracttime" id="cbc_extracttime" value="<?php echo $a['etime']; ?>" readonly>

                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Extracted By&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="cbc_extractby" id="cbc_extractby" value="<?php echo $a['extractby']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Phleb/Imaging Site&nbsp;:</td>
                        <td align=left>
                            <select class="gridInput" style="width:100%;" name="cbc_location" id="cbc_location">
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
                        <td align="left" width=25% class="bareBold" style="padding-left: 15px;">WBC&nbsp;:</td>
                        <td align=left width=20%>
                            <input type="text" class="gridInput" style="width:100%;" name="wbc" id="wbc" pattern="^\d*(\.\d{0,2})?$" value="<?php echo $b['wbc']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getCBCAttribute2($a['age'],$a['gender'],"WBC",$a['machine']); ?></td>	
                    </tr>
      
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 15px;" valign=top>RBC&nbsp;:</td>
                        <td align=left valign=top>
                            <input type="text" class="gridInput" style="width:100%;" name="rbc" id="rbc" pattern="^\d*(\.\d{0,2})?$" value="<?php echo $b['rbc']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;" valign=top><?php echo $o->getCBCAttribute2($a['age'],$a['gender'],"RBC",$a['machine']); ?></td>	
                    </tr>

                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 15px;">Hemoglobin&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="hemoglobin" id="hemoglobin" pattern="^\d*(\.\d{0,2})?$" value="<?php echo $b['hemoglobin']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getCBCAttribute2($a['age'],$a['gender'],"HEMOGLOBIN",$a['machine']); ?></td>	
                    </tr>
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 15px;">Hematocrit&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="hematocrit" id="hematocrit" pattern="^\d*(\.\d{0,2})?$" value="<?php echo $b['hematocrit']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getCBCAttribute2($a['age'],$a['gender'],"HEMATOCRIT",$a['machine']); ?></td>	
                    </tr>
                    <tr>
                        <td align="left" width=25% class="bareBold" style="padding-left: 15px;">MCV&nbsp;:</td>
                        <td align=left width=20%>
                            <input type="text" class="gridInput" style="width:100%;" name="mcv" id="mcv" pattern="^\d*(\.\d{0,2})?$" value="<?php echo $b['mcv']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getCBCAttribute2($a['age'],$a['gender'],"MCV",$a['machine']); ?></td>
                    </tr>
                    <tr>
                        <td align="left" width=25% class="bareBold" style="padding-left: 15px;">MCH&nbsp;:</td>
                        <td align=left width=20%>
                            <input type="text" class="gridInput" style="width:100%;" name="mch" id="mch" pattern="^\d*(\.\d{0,2})?$" value="<?php echo $b['mch']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getCBCAttribute2($a['age'],$a['gender'],"MCH",$a['machine']); ?></td>
                    </tr>
                    <tr>
                        <td align="left" width=25% class="bareBold" style="padding-left: 15px;">MCHC&nbsp;:</td>
                        <td align=left width=20%>
                            <input type="text" class="gridInput" style="width:100%;" name="mchc" id="mchc" pattern="^\d*(\.\d{0,2})?$" value="<?php echo $b['mchc']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getCBCAttribute2($a['age'],$a['gender'],"MCHC",$a['machine']); ?></td>
                    </tr>
                    <tr><td height=5>&nbsp;</td></tr>
                    <tr>
                        <td align="left" colspan=3 class="bareBold" style="padding-left: 15px;"><b>Differential Count&nbsp;:</b></td>
                    </tr>
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 35px;">Neutrophils&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="neutrophils" id="neutrophils" pattern="^\d*(\.\d{0,2})?$" value="<?php echo $b['neutrophils']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getCBCAttribute2($a['age'],$a['gender'],"NEUTROPHILS",$a['machine']); ?></td>	
                    </tr>
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 35px;">Lymphocytes&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="lymphocytes" id="lymphocytes" pattern="^\d*(\.\d{0,2})?$" value="<?php echo $b['lymphocytes']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getCBCAttribute2($a['age'],$a['gender'],"LYMPHOCYTES",$a['machine']); ?></td>	
                    </tr>
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 35px;">Monocytes&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="monocytes" id="monocytes" pattern="^\d*(\.\d{0,2})?$" value="<?php echo $b['monocytes']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getCBCAttribute2($a['age'],$a['gender'],"MONOCYTES",$a['machine']); ?></td>	
                    </tr>
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 35px;">Eosinophils&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="eosinophils" id="eosinophils" pattern="^\d*(\.\d{0,2})?$" value="<?php echo $b['eosinophils']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getCBCAttribute2($a['age'],$a['gender'],"EOSINOPHILS",$a['machine']); ?></td>	
                    </tr>
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 35px;">Basophils&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="basophils" id="basophils" pattern="^\d*(\.\d{0,2})?$" value="<?php echo $b['basophils']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getCBCAttribute2($a['age'],$a['gender'],"BASOPHILS",$a['machine']); ?></td>	
                    </tr>
                    <tr>
                        <td align="left" width=25% class="bareBold" style="padding-left: 15px;">Platelate Count&nbsp;:</td>
                        <td align=left width=20%>
                            <input type="text" class="gridInput" style="width:100%;" name="platelate" class="number" id="platelate" pattern="^\d*(\.\d{0,2})?$" value="<?php if($b['platelate'] > 0) { echo number_format($b['platelate']); } ?>" onchange="javascript: separateMe(this.value);">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getCBCAttribute2($a['age'],$a['gender'],"PLATELATE",$a['machine']); ?></td>	
                    </tr>
                    <tr>
                        <td align="left" width=25% class="bareBold" style="padding-left: 15px;" valign=top>Remarks&nbsp;:</td>
                        <td align=left width=75% colspan=3>
                            <textarea name="remarks" id="remarks" style="width: 90%;" rows=3><?php echo $b['remarks']; ?></textarea>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>              
</form>
</body>
</html>