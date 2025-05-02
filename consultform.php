<?php	
	session_start();
	include("handlers/_generics.php");
	$mydb = new _init;
	
	if($_GET['mod'] == '') { $_GET['mod'] = 1; }


	$uid = $_SESSION['userid'];
	if(isset($_REQUEST['transid']) && $_REQUEST['pid'] != '') { 
		$res = $mydb->getArray("select *, LPAD(so_no,6,0) AS so, date_format(so_date,'%m/%d/%Y') as d8, date_format(birthdate,'%m/%d/%Y') as bday, date_format(last_visit,'%m/%d/%Y') as lvisit from consultation_form where trans_id = '$_REQUEST[transid]' and pid = '$_REQUEST[pid]';");
		$cSelected = "Y"; $id = $res['trans_id']; $status = $res['status']; $traceNo = $res['trace_no']; $sono = $res['so'];

	} else {  
		$traceNo = $mydb->generateRandomString();
	}

	function getMod($def,$mod) { 
		if($def == $mod) { return "class=\"float2\""; } 
	}

	$a = $mydb->getArray("SELECT *, DATE_FORMAT(birthdate,'%m/%d/%Y') AS bday FROM patient_info WHERE patient_id = '$_REQUEST[pid]';");

	/* Age */
	$today = date('Y-m-d');
	list($age) = $mydb->getArray("SELECT FLOOR(ROUND(DATEDIFF('$today','$a[birthdate]') / 364.25,2));");

	/* Address */
	list($brgy) = $mydb->getArray("SELECT brgyDesc FROM options_brgy WHERE brgyCode = '$a[brgy]';");
    list($ct) = $mydb->getArray("SELECT citymunDesc FROM options_cities WHERE cityMunCode = '$a[city]';");
    list($prov) = $mydb->getArray("SELECT provDesc FROM options_provinces WHERE provCode = '$a[province]';");

    if($a['street'] != '') { $myaddress.=$a['street'].", "; }
    if($brgy != "") { $myaddress .= $brgy.", "; }
    if($ct != "") { $myaddress .= $ct.", "; }
    if($prov != "")  { $myaddress .= $prov.", "; }
    $myaddress = substr($myaddress,0,-2);

	/* Civil Status */
	list($cstat) = $mydb->getArray("select civil_status from options_civilstatus where csid = '$a[cstat]';");


	function setSOClickers($status,$id,$urights) {
		global $mydb;
	
		switch($status) {
			case "Finalized":
				list($posted_by,$posted_on) = $mydb->getArray("select fullname as name, date_format(updated_on,'%m/%d/%Y %p') as date_posted from consultation_form a left join user_info b on a.updated_by = b.emp_id where a.trans_id = '$id';");
				if($urights == "admin") {
					$headerControls = '
						<button type = "button" name = "setActive" class="ui-button ui-widget ui-corner-all" onClick="reopen();">
							<span class="ui-icon ui-icon-unlocked"></span> Set this Document to Active Status
						</button>
						<button type = "button" class="ui-button ui-widget ui-corner-all" onClick="printMedCert();">
							<span class="ui-icon ui-icon-print"></span> Print Medical Certificate
						</button>
						<button type = "button" class="ui-button ui-widget ui-corner-all" onClick="printMedClearance();">
							<span class="ui-icon ui-icon-print"></span> Print Medical Clearance
						</button>
						<button type = "button" class="ui-button ui-widget ui-corner-all" onClick="printReferral();">
							<span class="ui-icon ui-icon-print"></span> Print Sickness Referral Slip
						</button>
					';
				}
			
			break;
			case "Cancelled":
				if($urights == "admin") {
					$headerControls .= '
						<button type = "button" name = "setRecycle" class="ui-button ui-widget ui-corner-all" onClick="javascript: reuse();">
							<span class="ui-icon ui-icon-document-b"></span> Recycle this Document
						</button>
					';
				}
			break;
			case "Active": default:

				$headerControls = '
						<button type = "button" class="ui-button ui-widget ui-corner-all" onClick="finalizeRecord();">
							<span class="ui-icon ui-icon-check"></span> Finalized Record
						</button>
						<button type = "button" class="ui-button ui-widget ui-corner-all" onClick="saveRecord();">
							<span class="ui-icon ui-icon-disk"></span> Save Changes
						</button>
						<button type = "button" class="ui-button ui-widget ui-corner-all" onClick="printMedCert();">
							<span class="ui-icon ui-icon-print"></span> Print Medical Certificate
						</button>
						<button type = "button" class="ui-button ui-widget ui-corner-all" onClick="printMedClearance();">
							<span class="ui-icon ui-icon-print"></span> Print Medical Clearance
						</button>
						<button type = "button" class="ui-button ui-widget ui-corner-all" onClick="printReferral();">
							<span class="ui-icon ui-icon-print"></span> Print Sickness Referral Slip
						</button>

				';
				if($urights == "admin" && $id != '' ) {
					$headerControls .= '
						<button type = "button" name = "setRecycle" class="ui-button ui-widget ui-corner-all" onClick="javascript: cancel();">
							<span class="ui-icon ui-icon-cancel"></span> Cancel this Form
						</button>
					';
					
				}
			break;
		}
	
		echo $headerControls;
	}
	
	function setSONavs($id) {
		global $mydb;
		list($fwd) = $mydb->getArray("select so_no from consultation_form where trans_id > '$id' limit 1;");
		list($prev) = $mydb->getArray("select so_no from consultation_form where trans_id < '$i'd order by so_no desc limit 1;");
		list($last) = $mydb->getArray("select so_no from consultation_form order by so_no desc limit 1;");
		list($first) = $mydb->getArray("select so_no from consultation_form order by so_no asc limit 1;");
		if($prev)
			$nav = $nav . "<a href=# onclick=\"parent.viewConsultForm('$prev');\"><img src='images/resultset_previous.png'  title='Previous Record' /></a>";
		if($fwd) 
			$nav = $nav . "<a href=# onclick=\"parent.viewConsultForm('$fwd');\"><img src='images/resultset_next.png' 'title='Next Record' /></a>";
		echo "<a href=# onclick=\"parent.viewConsultForm('$first');\"><img src='images/resultset_first.png' title='First Record' /><a>" . $nav . "<a href=# onclick=\"parent.viewConsultForm('$last');\"><img src='images/resultset_last.png' title='Last Record' /></a>";
	}


		
?>
<!doctype html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Medgruppe Polyclinics & Diagnostic Center, Inc.</title>
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="ui-assets/texteditor/jquery-te-1.4.0.css" rel="stylesheet" type="text/css" />
	<link href="ui-assets/datatables/css/jquery.dataTables.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script language="javascript" src="ui-assets/texteditor/jquery-te-1.4.0.min.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
	<script language="javascript" src="js/consultform.js?sid=<?php echo uniqid(); ?>"></script>

	<script>
	
		$(document).ready(function($) {

			$("#complaint").jqte();
			$("#diagnosis").jqte();
			$("#treatment").jqte();
			$("#recommendation").jqte();
			// $("#prescription").jqte();
			$("#history").jqte();

			$('#prescription1, #prescription2, #prescription3, #prescription4, #prescription5').autocomplete({
				source:"suggestPharmaItemsEMR.php", 
				minLength:3,
				select: function(event,ui) {
					$("#itemCode").val(ui.item.code);
					$("#itemUnit").val(ui.item.unit);
					$("#itemCost").val(ui.item.price);
				}
			});

		<?php
			if($_GET['mod'] == 7) {
				
					echo 'var myTable = $("#solist").dataTable({
						"scrollY":  "300",
						"select":	"single",
						"searching": false,
						"paging": false,
						"info": false,
						"bSort": false,
						"aoColumnDefs": [
							{ className: "dt-body-center", "targets": [0,1,2,4,5,6] }
						]
					});
	
					
					';
			}
		?>


			$("#last_visit").datepicker();

			$('#patient_id').autocomplete({
				source:'suggestPatient.php',
				minLength:3,
				select: function(event,ui) {
					$("#patient_name").val(ui.item.name);
					$("#patient_address").val(ui.item.addr);
					$("#dept").val(ui.item.dept);
					$("#company_id").val(ui.item.phic_no);
					$("#birthdate").val(ui.item.bday);
					$("#gender").val(ui.item.gender);
					$("#age").val(ui.item.age);
				}
			});


			$('#labrequest').dataTable({
				"ajax": {
					"url": "consult.datacontrol.php",
					"data": { trace_no: "<?php echo $traceNo; ?>", mod: "retrieveLabrequest", sid: Math.random() },
					"method": "POST"	
				},
				"scrollY":  "150",
				"select":	'single',
				"pagingType": "full_numbers",
				"bProcessing": true,
				"searching": false,
				"paging": false,
				"info": false,
				
				"aoColumns": [
					{ mData: 'id' },
					{ mData: 'code' },
					{ mData: 'description' },
					{ mData: 'unit' },
				],
				"aoColumnDefs": [
					{ className: "dt-body-center", "targets": [1]},
					{ "targets": [0], "visible": false }			
				]
			});


			$('#prescriptions').dataTable({
				"ajax": {
					"url": "src/sjerp.php",
					"data": { trace_no: "<?php echo $traceNo; ?>", mod: "retrievePrescriptions", sid: Math.random() },
					"method": "POST"	
				},
				"scrollY":  "150",
				"select":	'single',
				"pagingType": "full_numbers",
				"bProcessing": true,
				"searching": false,
				"paging": false,
				"info": false,
				
				"aoColumns": [
					{ mData: 'id' },
					{ mData: 'code' },
					{ mData: 'description' },
					{ mData: 'unit' },
				],
				"aoColumnDefs": [
					{ className: "dt-body-center", "targets": [2,3]},
					{ "targets": [0], "visible": false }
				]
			});

		
		});


		function redrawDataTable() {
			$('#labrequest').DataTable().ajax.url("consult.datacontrol.php?mod=retrieveConsultForm&trace_no=<?php echo $traceNo; ?>").load();
		}

		function changeMod(mod) {
			document.changeModPage.mod.value = mod;
			document.changeModPage.submit();
		}

		function calculateBMI() {
            var ht = parseFloat($("#height").val()) / 100;
            var wt = parseFloat($("#weight").val());
            

            if(ht>0 && wt>0) {
                var bmi = wt / (ht*ht);
                    bmi = bmi.toFixed(2);
                $("#bmi").val(bmi);
            }

        }


	</script>

	<style>
		.dataTables_wrapper {
			display: inline-block;
			font-size: 11px; 
			width: 100%; 
		}
		ul.ui-autocomplete {
			width: 400px;
		}
		table.dataTable tr.even { background-color: #f5f5f5;  }
		table.dataTable tr.odd { background-color: white; }
		.dataTables_filter input { width: 250px; }

		/* .jqte_editor, .jqte_source { height: 150px; max-height: 200px; }
		.jqte { width: 770px; } */
		.jqte,
		.jqte_editor {
			width: 100% !important;
			max-width: 99% !important;
			max-height: 200px;
			box-sizing: border-box;
		}
	</style>

</head>
<body leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
<div>
	<form name="frmEMR" id="frmEMR" onsubmit="return false;">
		<input type="hidden" name="cSelected" id="cSelected" value="<?php echo $cSelected; ?>">
		<input type=hidden name="trace_no" id="trace_no" value="<?php echo $traceNo; ?>">
		<input type=hidden name="so_no" id="so_no" value="<?php echo $sono; ?>">
		<input type=hidden name="pid" id="pid" value="<?php echo $_REQUEST['pid']; ?>">
		<input type=hidden name="trans_id" id="trans_id" value="<?php echo $id; ?>">
		<table width=100% cellpadding=0 cellspacing=0 border=0 align=center>
			<tr>
				<td class="upper_menus" align=left>
					<?php setSOClickers($status,$id,$_SESSION['utype']); ?>
				</td>

			</tr>
			<tr><td height=2></td></tr>
		</table>
		<table width=100% cellpadding=0 cellspacing=0 style="boder-collpase: collapse; margin-top: 15px;">
            <tr><td colspan=8 align=center><img src="images/doc-header.jpg" width=50% align=absmiddle /></td></tr>
			<tr><td height=4></td></tr>
			<tr>
				<td width=8% class="bebottom" >Last Name :</td>
				<td width=17% class="bebottom">	
					<input type="text" name="pe_lname" id="pe_lname" style="border: none; font-size: 11px; font-weight: bold;" value="<?php echo $a['lname']; ?>">
				</td>
				<td width=8% class="bebottom">First Name :</td>
				<td width=17% class="bebottom">	
					<input type="text" name="pe_fname" id="pe_fname" style="border: none; font-size: 11px; font-weight: bold;" value="<?php echo $a['fname']; ?>">
				</td>
				<td width=8% class="bebottom">Middle Name :</td>
				<td width=17% class="bebottom">	
					<input type="text" name="pe_mname" id="pe_mname" style="border: none; font-size: 11px; font-weight: bold;" value="<?php echo $a['mname']; ?>">
				</td>
				<td width=8% class="bebottom">Date :</td>
				<td width=17% class="bebottom">	
					<input type="text" name="pe_date" id="pe_date" style="border: none; font-size: 11px; font-weight: bold;" value="<?php echo date('m/d/Y'); ?>">
				</td>
			</tr>
            <tr>
				<td class="bebottom" >Address :</td>
				<td class="bebottom">	
					<input type="text" name="pe_address" id="pe_address" style="border: none; font-size: 11px;width: 98%; font-weight: bold;" value="<?php echo $myaddress; ?>">
				</td>
				<td class="bebottom">Age :</td>
				<td class="bebottom">	
                <input type="text" name="pe_age" id="pe_age" style="border: none; font-size: 11px;width: 98%; font-weight: bold;" value="<?php echo $age; ?>">
				</td>
				<td class="bebottom">Civil Status :</td>
				<td class="bebottom">	
					<input type="text" name="pe_cstatus" id="pe_cstatus" style="border: none; font-size: 11px; width: 98%; font-weight: bold;" value="<?php echo $cstat; ?>">
				</td>
				<td class="bebottom">Gender :</td>
				<td class="bebottom">	
					<input type="text" name="pe_gender" id="pe_gender" style="border: none; font-size: 11px; width: 98%; font-weight: bold;" value="<?php echo $a['gender']; ?>">
				</td>
			</tr>
            <tr>
				<td class="bebottom" >Place of Birth :</td>
				<td class="bebottom">	
					<input type="text" name="pe_pob" id="pe_pob" style="border: none; font-size: 11px; width: 98%; font-weight: bold;" value="<?php echo $a['birthplace']; ?>">
				</td>
				<td class="bebottom">Date of Birth :</td>
				<td class="bebottom">	
					<input type="text" name="pe_dob" id="pe_dob" style="border: none; font-size: 11px; width: 98%; font-weight: bold;" value="<?php echo $a['bday']; ?>">
				</td>
				<td class="bebottom"></td>
				<td class="bebottom" colspan=3>	
					<!--input type="text" name="pe_insurance" id="pe_insurance" style="border: none; font-size: 11px; width: 98%; font-weight: bold;"-->
				</td>
			</tr>
            <tr>
				<td class="bebottom" >Occupation :</td>
				<td class="bebottom">	
					<input type="text" name="pe_occ" id="pe_occ" style="border: none; font-size: 11px; width: 98%; font-weight: bold;" value="<?php echo $a['occupation']; ?>">
				</td>
				<td class="bebottom">Company :</td>
				<td class="bebottom">	
					<input type="text" name="pe_comp" id="pe_comp" style="border: none; font-size: 11px; width: 98%; font-weight: bold;" value="<?php echo $a['employer']; ?>">
				</td>
				<td class="bebottom">Tel/Mobile # :</td>
				<td class="bebottom" colspan=3>	
					<input type="text" name="pe_contact" id="pe_contact" style="border: none; font-size: 11px; width: 98%; font-weight: bold;" value="<?php echo $a['mobile_no']; ?>">
				</td>
			</tr>
            <tr><td style="padding-top:10px;"></td></tr>
		</table>
		<table border="0" cellpadding="0" cellspacing="1" width=100% class="td_content">
			<tr>
				<td width=50% valign=top>
					<table width=100% border="0" cellpadding="0" cellspacing="1">
						<tr>
							<td align="center" width="50%" class="bareBold" style="padding-left: 35px; font-weight: bold;">Last Clinic Visit :</td>
							<td align=left>
								<input class="gridInput" type="text" style="width:80%;" name="last_visit" id="last_visit" autocomplete="off" value="<?php echo $res['lvisit']; ?>">
							</td>
						</tr>
						<tr><td height=1></td></tr>
						<tr>
							<td align="center" width="50%" class="bareBold" style="padding-left: 35px; font-weight: bold;">Temperature (<sup>o</sup>C)&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:80%;" type=text name="temp" id="temp" value="<?php echo $res['temp']; ?>">
							</td>				
						</tr>
						<tr><td height=1></td></tr>
						<tr>
							<td align="center" width="50%" class="bareBold" style="padding-left: 35px; font-weight: bold;">Height (CM)&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:80%;" type=text name="height" id="height" value="<?php echo $res['height']; ?>" onchange="calculateBMI();">
							</td>				
						</tr>
						<tr><td height=1></td></tr>
						<tr>
							<td align="center" width="50%" class="bareBold" style="padding-left: 35px; font-weight: bold;">Respiratory Rate&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:80%;" type=text name="respiratory" id="respiratory" value="<?php echo $res['respiratory']; ?>">
							</td>				
						</tr>
						<tr><td height=1></td></tr>
						<tr>
							<td align="center" width="50%" class="bareBold" style="padding-left: 35px; font-weight: bold;">BMI&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:80%;" type=text name="bmi" id="bmi" value="<?php echo $res['bmi']; ?>">
							</td>				
						</tr>
					</table>
				</td>
				<td valign=top>
					<table border="0" cellpadding="0" cellspacing="1" width=100%>
						<tr>
							<td align="center" width="50%" class="bareBold" style="padding-left: 35px; font-weight: bold;">Attending Physician&nbsp;:</td>
							<td align=left>
								<input type="text" class="gridInput" style="width:80%;" name="physician" id="physician" value="<?php echo $res['physician']; ?>" >
							</td>				
						</tr>
						<tr><td height=1></td></tr>
						<tr>
							<td align="center" width="50%" class="bareBold" style="padding-left: 35px; font-weight: bold;">Weight (KGS)&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:80%;" type=text name="weight" id="weight" value="<?php echo $res['weight']; ?>" onchange="calculateBMI();">
							</td>				
						</tr>
						<tr><td height=1></td></tr>
						<tr>
							<td align="center" width="50%" class="bareBold" style="padding-left: 35px; font-weight: bold;">Pulse Rate&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:80%;" type=text name="pulse" id="pulse" value="<?php echo $res['pulse']; ?>">
							</td>				
						</tr>
						<tr><td height=1></td></tr>
						<tr>
							<td align="center" width="50%" class="bareBold" style="padding-left: 35px; font-weight: bold;">Blood Pressure (mm/HG)&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:80%;" type=text name="bp" id="bp" value="<?php echo $res['bp']; ?>">
							</td>				
						</tr>
						<tr><td height=1></td></tr>
						<tr>
							<td align="center" width="50%" class="bareBold" style="padding-left: 35px; font-weight: bold;">BMI CATEGORY&nbsp;:</td>
							<td align=left>
								<select name="bmi_category" id="bmi_category" class="gridInput" style="width:80%;">
									<option value="N/A" <?php if($res['bmi_category'] == 'N/A') { echo "selected"; } ?>>N/A</option>
									<option value="Underweight" <?php if($res['bmi_category'] == 'Underweight') { echo "selected"; } ?>>Underweight</option>
									<option value="Normal Weight" <?php if($res['bmi_category'] == 'Normal Weight') { echo "selected"; } ?>>Normal Weight</option>
									<option value="Overweight" <?php if($res['bmi_category'] == 'Overweight') { echo "selected"; } ?>>Overweight</option>
									<option value="Obese" <?php if($res['bmi_category'] == 'Obese') { echo "selected"; } ?>>Obese</option>
								</select>
							</td>				
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<table cellspacing=0 cellpadding=0 width=100% align=center style="margin-top: 5px;">
			<tr>
				<td style="padding: 0px 0px 1px 0px;">
					<div id="custmenu" align=left class="ddcolortabs">
						<ul class=float2>
							<li><a href="#" <?php echo getMod("1",$_GET['mod']); ?> onclick="javascript: changeMod(1);"><span id="tbbalance1">General Complaint</span></a></li>
							<?php if($_REQUEST['transid'] != '') { ?>
							<li><a href="#" <?php echo getMod("2",$_GET['mod']); ?> onclick="javascript: changeMod(2);"><span id="tbbalance1">Diagnosis</span></a></li>	
							<li><a href="#" <?php echo getMod("3",$_GET['mod']); ?> onclick="javascript: changeMod(3);"><span id="tbbalance1">Treatment Plan</span></a></li>
							<li><a href="#" <?php echo getMod("4",$_GET['mod']); ?> onclick="javascript: changeMod(4);"><span id="tbbalance1">Recommendation</span></a></li>
							<li><a href="#" <?php echo getMod("5",$_GET['mod']); ?> onclick="javascript: changeMod(5);"><span id="tbbalance1">Prescription</span></a></li>		
							<li><a href="#" <?php echo getMod("6",$_GET['mod']); ?> onclick="javascript: changeMod(6);"><span id="tbbalance1">Lab Request</span></a></li>		
							<li><a href="#" <?php echo getMod("7",$_GET['mod']); ?> onclick="javascript: changeMod(7);"><span id="tbbalance1">Lab Result</span></a></li>		
							<?php } ?>
						</ul>
					</div>
				</td>
			</tr>
		</table>
	<?php if($_GET['mod'] == '' || $_GET['mod'] == 1) { ?>
		<table width=100% cellpadding=0 cellspacing = 0 class="td_content">
			<tr>
				<td>

					<textarea style="width:100%;" name="complaint" id="complaint"><?php echo $res['complaints']; ?></textarea><br/>
					<?php if($status == 'Active' || $status == '') { ?>
						<a href="#" class="topClickers" onClick="javascript:saveComplaint();"><img src="images/icons/floppy.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Save Changes to General Complaint</a>
					<?php } ?>

				</td>
			</tr>
		</table>
	<?php } if($_REQUEST['transid'] != '') { if($_GET['mod'] == 2) { ?>
		<table width=100% cellpadding=0 cellspacing = 0 class="td_content">
			<tr>
				<td style="padding-right: 30px;">
					
					<textarea style="width:100%;" name="diagnosis" id="diagnosis"><?php echo $res['diagnosis']; ?></textarea><br/>
					<?php if($status == 'Active' || $status == '') { ?>
						<a href="#" class="topClickers" onClick="javascript:saveDiagnosis();"><img src="images/icons/floppy.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Save Changes to Diagnosis</a>
					<?php } ?>

				</td>
			</tr>
		</table>
	<?php } if($_GET['mod'] == 3) { ?>	
		<table width=100% cellpadding=0 cellspacing = 0 class="td_content">
			<tr>

				<td>
					<textarea style="width:100%;" rows=5 name="treatment" id="treatment"><?php echo $res['treatment']; ?></textarea><br/>
					<?php if($status == 'Active' || $status == '') { ?>
						<a href="#" class="topClickers" onClick="javascript:saveTreatment();"><img src="images/icons/floppy.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Save Changes to Treatment Plan</a>
					<?php } ?>
				</td>
			</tr>
		</table>
		<?php } if($_GET['mod'] == 4) { ?>	
			<table width=100% cellpadding=0 cellspacing = 0 class="td_content">
				<tr>

					<td width = 100%>
						<textarea style="width:100%;" rows=5 name="recommendation" id="recommendation"><?php echo $res['recommendation']; ?></textarea><br/>
						<?php if($status == 'Active' || $status == '') { ?>
							<a href="#" class="topClickers" onClick="javascript:saveRecommendation();"><img src="images/icons/floppy.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Save Changes to Recommendation</a>
						<?php } ?>
					</td>
				</tr>
			</table>
		<?php } if($_GET['mod'] == 5) { ?>	
			<table width=100% cellpadding=0 cellspacing = 0 class="td_content">
				<tr>
					<td width=20%>MEDICINE :</td>
					<td>*
						<input type=text style="width:70%;" name="prescription1" id="prescription1" value="<?php echo $res['prescription1']; ?>" onchange="parent.autoSavePrescription();">
					</td>
				</tr>
				<tr><td height=2></td></tr>
				<tr>
					<td width=20%>&nbsp;</td>
					<td>*
						<input type=text style="width:70%;" name="prescription2" id="prescription2" value="<?php echo $res['prescription2']; ?>" onchange="parent.autoSavePrescription();">
					</td>
				</tr>
				<tr><td height=2></td></tr>
				<tr>
					<td width=20%>&nbsp;</td>
					<td>*
						<input type=text style="width:70%;" name="prescription3" id="prescription3" value="<?php echo $res['prescription3']; ?>" onchange="parent.autoSavePrescription();">
					</td>
				</tr>
				<tr><td height=2></td></tr>
				<tr>
					<td width=20%>&nbsp;</td>
					<td>*
						<input type=text style="width:70%;" name="prescription4" id="prescription4" value="<?php echo $res['prescription4']; ?>" onchange="parent.autoSavePrescription();">
					</td>
				</tr>
				<tr><td height=2></td></tr>
				<tr>
					<td width=20%>&nbsp;</td>
					<td>*
						<input type=text style="width:70%;" name="prescription5" id="prescription5" value="<?php echo $res['prescription5']; ?>" onchange="parent.autoSavePrescription();">
					</td>
				</tr>
				<tr><td height=5></td></tr>
				<tr>
					<td width=20%>SIG:</td>
					<td>&nbsp;
						<textarea style="width:70%;" rows=5 name="sig" id="sig" onchange="parent.autoSavePrescription();"><?php echo $res['sig']; ?></textarea><br/>
					</td>
				</tr>
				</tr>
					<td colspan=5>
						<?php if($status == 'Active' || $status == '') { ?>
							<a href="#" class="topClickers" onClick="javascript:savePrescription();"><img src="images/icons/floppy.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Save Changes to Prescriptions</a>
							<a href="#" class="topClickers" onClick="javascript:printRX();"><img src="images/icons/print.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Print Prescription</a>
						<?php } ?>
	
					</td>
				</tr>
			</table>
		<?php } if($_GET['mod'] == 6) { ?>
			
			<table width=100% cellpadding=0 cellspacing = 0 id="labrequest" style="font-size:11px;">
				<thead>
					<tr>
						<th></th>
						<th width=10%>CODE</th>
						<th >DESCRIPTION</th>
						<th width=8%>UNIT</th>
					</tr>
				</thead>
			</table>
			<table width=100% cellpadding=0 cellspacing = 0>
				<tr>
					<td colspan=5 style="padding-top:5px;">
						<?php if($status == 'Active' || $status == '') { ?>
							<a href="#" class="topClickers" onClick="javascript:addItem();"><img src="images/icons/add-2.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Add Item</a>&nbsp;
							<a href="#" class="topClickers" onClick="javascript:deleteItem();"><img src="images/icons/delete.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Remove Selected Item</a>
							<a href="#" class="topClickers" onClick="javascript:printReq();"><img src="images/icons/print.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Print Lab Request</a>
						<?php } ?>
						
					</td>
				</tr>
			</table>
		<?php } if($_GET['mod'] == 7) { 
			
			echo '<table width=100% cellpadding=0 cellspacing = 0 id="solist" style="font-size:11px;">
				<thead>
					<tr>
						<th width=8%>SO #</th>
						<th width=8%>DATE</th>
						<th width=8%>CODE</th>
						<th>REQUESTED PROCEDURE</th>
						<th width=10%>TYPE</th>
						<th width=15%>SN#</th>
						<th width=15%>STATUS</th>
					</tr>
				</thead><tbody>';
				
				$soQuery = $mydb->dbquery("SELECT record_id AS id, LPAD(a.so_no,6,0) AS sono, DATE_FORMAT(b.so_date,'%m/%d/%Y') AS sodate, b.patient_name AS pname, YEAR(b.so_date) - YEAR(c.birthdate) AS age, IF(c.gender='M','Male','Female') AS gender,a.code,a.procedure,d.sample_type,serialno,DATE_FORMAT(CONCAT(extractdate,' ',extractime),'%m/%d/%Y %h:%i %p') AS tstamp,e.samplestatus AS `status`,a.status AS ostat FROM lab_samples a LEFT JOIN so_header b ON a.so_no = b.so_no AND a.branch = b.branch LEFT JOIN patient_info c ON b.patient_id = c.patient_id LEFT JOIN options_sampletype d ON a.sampletype = d.id LEFT JOIN options_samplestatus e ON a.status = e.id LEFT JOIN services_master f ON a.code = f.code WHERE c.patient_id = '$_REQUEST[pid]';");
				while($soRow = $soQuery->fetch_array()) {
					echo '<tr>
							<td><a href="#" class="text-decoration: none;" onclick="parent.printResult(\''.$soRow['code'].'\',\''.$soRow['sono'].'\',\''.$soRow['serialno'].'\')">'.$soRow['sono'].'</a></td>
							<td>'.$soRow['sodate'].'</td>
							<td>'.$soRow['code'].'</td>
							<td>'.$soRow['procedure'].'</td>
							<td>'.$soRow['sample_type'].'</td>
							<td>'.$soRow['serialno'].'</td>
							<td>'.$soRow['status'].'</td>
						</tr>'; 
				}
				echo '</tbody>';
			?>
			<?php }} ?>

	</form>
</div>

<form name="changeModPage" id="changeModPage" action="consultform.php" method="GET" >
	<input type="hidden" name="transid" id="transid" value="<?php echo $_REQUEST['transid']; ?>">
	<input type="hidden" name="pid" id="pid" value="<?php echo $_GET['pid']; ?>">
	<input type="hidden" name="mod" id="mod">
</form>
<div id="itemEntry" style="display: none; z-index: 100;">
	<form name="frmItemEntry" id="frmItemEntry">
		<input type="hidden" id="recordId" name="recordId">
		<table width="100%" cellspacing=2 cellpadding=0 >
			<tr>
				<td class="bareThin" align=left width=40%>Description :</td>
				<td align=left>
					<input type="text" name="itemDescription" id="itemDescription" class="inputSearch2" style="width: 80%; padding-left: 22px;">
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Item Code :</td>
				<td align=left>
					<input type="text" name="itemCode" id="itemCode" class="gridInput" style="width: 80%;" disabled>
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Unit :</td>
				<td align=left>
					<input type="text" name="itemUnit" id="itemUnit" class="gridInput" style="width: 80%;" disabled>
				</td>
			</tr>
		</table>
	</form>
</div>
</body>
</html>