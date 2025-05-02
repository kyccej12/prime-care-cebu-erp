<?php 
	
	session_start();
	require_once "handlers/_generics.php";
	
    $o = new _init;

    $a = $o->getArray("SELECT record_id AS id, LPAD(a.so_no,6,0) AS myso,DATE_FORMAT(b.so_date,'%m/%d/%Y') AS sodate, LPAD(b.patient_id,6,0) AS mypid,b.patient_name AS pname, FLOOR(DATEDIFF(b.so_date,c.birthdate)/364.25) AS age, c.gender as xgender, IF(c.gender='M','Male','Female') AS gender, DATE_FORMAT(c.birthdate,'%m/%d/%Y') AS bday,e.patientstatus,b.physician,a.code,a.procedure,sampletype,serialno,DATE_FORMAT(extractdate,'%m/%d/%Y') AS exday,TIME_FORMAT(extractime,'%h:%i %p') AS etime,extractby,a.location FROM lab_samples a LEFT JOIN so_header b ON a.so_no = b.so_no AND a.branch = b.branch LEFT JOIN patient_info c ON b.patient_id = c.patient_id LEFT JOIN options_patientstat e ON b.patient_stat = e.id WHERE a.record_id = '$_REQUEST[lid]';");
   $b = $o->getArray("select * from lab_bloodchem where so_no = '$a[myso]' and serialno = '$a[serialno]' and branch = '$_SESSION[branchid]';");
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
        $(function() { $("#bloodchem_date").datepicker(); });

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

        function computeTotalBilirubin() {
            
        
            
            var a = $("#bilirubin_direct").val();
            var b = $("#bilirubin_indirect").val();

            if(a != '' && b != '') {
                var c = parseFloat(a) + parseFloat(b);
                $("#bilirubin").val(c.toFixed(2));
            }

        }

        function computeTotalProtein() {
            var a = $("#albumin").val();
            var b = $("#globulin").val();

            if(a != '' && b != '') {
                var c = parseFloat(a) + parseFloat(b);
                $("#protein").val(c.toFixed(2));

                var agRatio = parseFloat(a) / parseFloat(b);
                $("#agratio").val(agRatio.toFixed(2));
            }

           


        }
    </script>
</head>
<body>
    <form name="frmBloodChemResult" id="frmBloodChemResult"> 
        <table width=100% cellpadding=0 cellspacing=0 valign=top>
         <tr>
             <td width=35% valign=top>
                <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>PATIENT & ORDER INFORMATION</td></tr></table>
                <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px; margin-bottom: 5px;">
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">SO #&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="bloodchem_sono" id="bloodchem_sono" value="<?php echo $a['myso']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">Service Order Date&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="bloodchem_sodate" id="bloodchem_sodate" value="<?php echo $a['sodate']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Patient ID&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bloodchem_pid" id="bloodchem_pid" value="<?php echo $a['mypid']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%"  class="bareBold" style="padding-left: 15px;">Result Date&nbsp;:</td>
                        <td align=left>
                            <input class="gridInput" style="width:100%;" type=text name="bloodchem_date" id="bloodchem_date" value="<?php if($rdate !='') { echo $rdate; } else { echo date('m/d/Y'); } ?>">
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Patient Name&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bloodchem_pname" id="bloodchem_pname" value="<?php echo $a['pname']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>

                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Gender&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bloodchem_gender" id="bloodchem_gender" value="<?php echo $a['gender']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Birthdate&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bloodchem_birthdate" id="bloodchem_birthdate" value="<?php echo $a['bday']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Age&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bloodchem_age" id="bloodchem_age" value="<?php echo $a['age']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Patient Status&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bloodchem_patientstat" id="bloodchem_patientstat" value="<?php echo $a['patientstatus']; ?>" readonly> 
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Requesting Physician&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bloodchem_physician" id="bloodchem_physician" value="<?php echo $a['physician']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                </table>
                <table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>SAMPLE DETAILS</td></tr></table>
                <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Test or Procedure&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bloodchem_procedure" id="bloodchem_procedure" value="<?php echo $a['procedure']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Procedure Code&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bloodchem_code" id="bloodchem_code" value="<?php echo $a['code']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Specimen Type&nbsp;:</td>
                        <td align=left>
                            <select class="gridInput" style="width:100%;" name="bloodchem_spectype" id="bloodchem_spectype">
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
                            <input type="text" class="gridInput" style="width:100%;" name="bloodchem_serialno" id="bloodchem_serialno" value="<?php echo $a['serialno']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Date Extracted&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bloodchem_extractdate" id="bloodchem_extractdate" value="<?php echo $a['exday']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Time Extracted&nbsp;:</td>
                        <td align=left>
                
                            <input type="text" class="gridInput" style="width:100%;" name="bloodchem_extracttime" id="bloodchem_extracttime" value="<?php echo $a['etime']; ?>" readonly>

                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Extracted By&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bloodchem_extractby" id="bloodchem_extractby" value="<?php echo $a['extractby']; ?>" readonly>
                        </td>				
                    </tr>
                    <tr><td height=3></td></tr>
                    <tr>
                        <td align="left" width="35%" class="bareBold" style="padding-left: 15px;">Phleb/Imaging Site&nbsp;:</td>
                        <td align=left>
                            <select class="gridInput" style="width:100%;" name="bloodchem_location" id="bloodchem_location">
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
                        <td align="left" width=25% class="bareBold" style="padding-left: 15px;">Glucose/FBS&nbsp;:</td>
                        <td align=left width=20%>
                            <input type="text" class="gridInput" style="width:100%;" name="glucose" id="glucose" value="<?php echo $b['glucose']; ?>" pattern="^\d*(\.\d{0,2})?$" >
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getAttribute('L009',$a['age'],$a['xgender']); ?></sup></td>	
                    </tr>
      
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 15px;" valign=top>Uric Acid&nbsp;:</td>
                        <td align=left valign=top>
                            <input type="text" class="gridInput" style="width:100%;" name="uric" id="uric" value="<?php echo $b['uric']; ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;" valign=top><?php echo $o->getAttribute('L004',$a['age'],$a['xgender']); ?></td>	
                    </tr>

                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 15px;">Blood Urea Nitrogen (BUN)&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bun" id="bun" value="<?php echo $b['bun']; ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getAttribute('L005',$a['age'],$a['xgender']); ?></td>	
                    </tr>

                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 15px;">Creatinine&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="creatinine" id="creatinine" value="<?php echo $b['creatinine']; ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getAttribute('L020',$a['age'],$a['xgender']); ?></td>	
                    </tr>

                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 15px;">Total Cholesterol&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="cholesterol" id="cholesterol" value="<?php echo $b['cholesterol']; ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getAttribute('L019',$a['age'],$a['xgender']); ?></td>	
                    </tr>
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 15px;">Triglycerides&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="triglycerides" id="triglycerides" value="<?php echo $b['triglycerides']; ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getAttribute('L032',$a['age'],$a['xgender']); ?></td>	
                    </tr>
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 15px;">HDL - Chol&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="hdl" id="hdl" value="<?php echo $b['hdl']; ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;">>40 mg/dL</td>	
                    </tr>                   
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 15px;">LDL - Chol&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="ldl" id="ldl" value="<?php echo $b['ldl']; ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;">0 - 130 mg/dL</td>	
                    </tr>   
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 15px;">VLDL&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="vldl" id="vldl" value="<?php echo $b['vldl']; ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;">0 - 40 mg/dL</td>	
                    </tr>        
                    <tr><td height=3>&nbsp;</td></tr>
                    <tr>
                        <td align="left" colspan=3 class="bareBold" style="padding-left: 15px;"><b>Enzymes&nbsp;:</b></td>
                    </tr>
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 35px;">SGOT/AST&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="sgot" id="sgot" value="<?php echo $b['sgot']; ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getAttribute('L028',$a['age'],$a['xgender']); ?></td>	
                    </tr>
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 35px;">SGPT/ALT&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="sgpt" id="sgpt" value="<?php echo $b['sgpt']; ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getAttribute('L029',$a['age'],$a['xgender']); ?></td>	
                    </tr>
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 35px;">Alkaline Phosphatase&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="alkaline" id="alkaline" value="<?php echo $b['alkaline']; ?>" pattern="^\d*(\.\d{0,2})?$">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getAttribute('L016',$a['age'],$a['xgender']); ?></td>	
                    </tr>

                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 15px; font-weight: bold;">Total Bilirubin&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bilirubin" id="bilirubin" value="<?php echo $b['bilirubin']; ?>" readonly>
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getAttribute('L109',$a['age'],$a['xgender']); ?></td>	
                    </tr>               
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 35px;">Direct Bilirubin&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bilirubin_direct" id="bilirubin_direct" value="<?php echo $b['bilirubin_direct']; ?>" pattern="^\d*(\.\d{0,2})?$" onblur = "javascript: computeTotalBilirubin();">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getAttribute('L003',$a['age'],$a['xgender']); ?></td>	
                    </tr>                  
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 35px;">Indirect Bilirubin&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="bilirubin_indirect" id="bilirubin_indirect" value="<?php echo $b['bilirubin_indirect']; ?>" pattern="^\d*(\.\d{0,2})?$" onblur = "javascript: computeTotalBilirubin();">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;">0.1 - 1.0 mg/dL</td>	
                    </tr> 
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 15px; font-weight: bold;">Total Protein&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="protein" id="protein" value="<?php echo $b['protein']; ?>" readonly>
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getAttribute('L027',$a['age'],$a['xgender']); ?></td>	
                    </tr>                
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 35px;">Albumin&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="albumin" id="albumin" value="<?php echo $b['albumin']; ?>" pattern="^\d*(\.\d{0,2})?$" onblur = "javascript: computeTotalProtein();">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getAttribute('L001',$a['age'],$a['xgender']); ?></td>	
                    </tr>
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 35px;">Globulin&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="globulin" id="globulin" value="<?php echo $b['globulin']; ?>" pattern="^\d*(\.\d{0,2})?$" onblur = "javascript: computeTotalProtein();">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getAttribute('L110',$a['age'],$a['xgender']); ?></td>	
                    </tr>
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 35px;">A/G Ratio&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="agratio" id="agratio" value="<?php echo $b['agratio']; ?>" readonly>
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;">1.1 - 1.8</td>	
                    </tr>
                    <tr><td height=3>&nbsp;</td></tr>            
                    <tr>
                        <td align="left" colspan=3 class="bareBold" style="padding-left: 15px;"><b>Electrolytes&nbsp;:</b></td>
                    </tr>                
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 35px;">Na&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="electrolytes_na" id="electrolytes_na" pattern="^\d*(\.\d{0,2})?$" value="<?php echo $b['electrolytes_na']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getAttribute('L030',$a['age'],$a['xgender']); ?></td>
                    </tr>
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 35px;">K&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="electrolytes_k" id="electrolytes_k" pattern="^\d*(\.\d{0,2})?$" value="<?php echo $b['electrolytes_k']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getAttribute('L026',$a['age'],$a['xgender']); ?></td>
                    </tr>    
                    <tr>
                        <td align="left" class="bareBold" style="padding-left: 35px;">CI&nbsp;:</td>
                        <td align=left>
                            <input type="text" class="gridInput" style="width:100%;" name="electrolytes_ci" id="electrolytes_ci" pattern="^\d*(\.\d{0,2})?$" value="<?php echo $b['electrolytes_ci']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getAttribute('L017',$a['age'],$a['xgender']); ?></td>
                    </tr> 
                    
                    <tr>
                        <td align="left" width=25% class="bareBold" style="padding-left: 15px;">Calcium&nbsp;:</td>
                        <td align=left width=20%>
                            <input type="text" class="gridInput" style="width:100%;" name="calcium" id="calcium" pattern="^\d*(\.\d{0,2})?$" value="<?php echo $b['calcium']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getAttribute('L006',$a['age'],$a['xgender']); ?></td>	
                    </tr>
                    <tr>
                        <td align="left" width=25% class="bareBold" style="padding-left: 15px;">Phosphorus&nbsp;:</td>
                        <td align=left width=20%>
                            <input type="text" class="gridInput" style="width:100%;" name="phosphorus" id="phosphorus" pattern="^\d*(\.\d{0,2})?$" value="<?php echo $b['phosphorus']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getAttribute('L025',$a['age'],$a['xgender']); ?></td>	
                    </tr>
                    <tr>
                        <td align="left" width=25% class="bareBold" style="padding-left: 15px;">GGT&nbsp;:</td>
                        <td align=left width=20%>
                            <input type="text" class="gridInput" style="width:100%;" name="ggt" id="ggt" pattern="^\d*(\.\d{0,2})?$" value="<?php echo $b['ggt']; ?>">
                        </td>
                        <td align="left" class="bareBold" style="padding-left: 15px;"><?php echo $o->getAttribute('L081',$a['age'],$a['xgender']); ?></td>	
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