<?php
	//ini_set("display_errors", "on");
	include("handlers/initDB.php");
	$con = new myDB;
	$pid = $_GET['pid'];	
	
	$res = array();
	if($pid != '') {
		$res = $con->getArray("select *,date_format(birthdate,'%m/%d/%Y') as pbday, if(spouse_birthdate!='0000-00-00',date_format(spouse_birthdate,'%m/%d/%Y'),'') as sbday, if(hmo_expiry!='0000-00-00',date_format(hmo_expiry,'%m/%d/%Y'),'') as hexpiry from patient_info where patient_id = '$pid';");
	}

	session_start();
	if(isset($_POST['submit'])){
		//echo var_dump($_POST);
		$temp = explode(".",$_FILES["uploadedfile"]["name"]);
		$filename =  $temp[0] . "." . end($temp);

		$path = "household_pic/$filename";
		$imageFileType = pathinfo($path,PATHINFO_EXTENSION);


		// Check file size
		if ($_FILES["uploadedfile"]["size"] > 2000000) {
		    echo ">> Sorry, your file is too large.<br/>";
		    $error = 1;
		}

		// Allow certain file formats
		if($imageFileType != "JPG" && $imageFileType != "jpg" && $imageFileType != "JPEG" && $imageFileType != "jpeg" && $imageFileType != "png" && $imageFileType != "PNG") {
		    $error = 1;
		}else{
			 move_uploaded_file($_FILES["uploadedfile"]["tmp_name"],$path); 
			$con->dbquery("UPDATE citylights.household SET h_pic = '$filename' WHERE record_id = '$_POST[hh_rid]';");
		}
	}
	
	function getMod($def,$mod) { if($def == $mod) { return "class=\"float2\""; } }

?>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Medgruppe Polyclinics & Diagnostic Center, Inc.</title>
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="ui-assets/datatables/css/jquery.dataTables.css">
	<link rel="stylesheet" type="text/css" href="ui-assets/keytable/css/keyTable.jqueryui.css">
	<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/page.jumpToData().js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/keytable/js/dataTables.keyTable.min.js"></script>
<script>
	$(document).ready(function() {
		<?php if($error == 1) { echo "parent.sendErrorMessage(\"Invalid Image Format. Please have convert this file into JPEG or PNG format.\")"; } ?>
		 $("#p_bday").datepicker({changeMonth: true, changeYear: true, yearRange: "-90:+00"}); $("#s_bday").datepicker({changeMonth: true, changeYear: true,  yearRange: "-90:+00"});
		 $("#p_hmo_expiry").datepicker({changeMonth: true, changeYear: true});
		<?php 
			switch($_GET['mod']) {
				case "2":
					echo 'var myTable = $("#solist").dataTable({
						"scrollY":  "430",
						"select":	"single",
						"searching": false,
						"paging": false,
						"info": false,
						"bSort": false,
						"aoColumnDefs": [
							{ className: "dt-body-center", "targets": [0,1,2] },
							{ className: "dt-body-right", "targets": [4] }
						]
					});
					
					$("#solist tbody").on("dblclick", "tr", function () {
						var data = myTable.row( this ).data();	
						parent.viewSO(data[0]);
					});
					
					';
				break;
				case "3":
					echo "$('#ilist').dataTable({
						\"keys\": true,
						\"scrollY\":  \"200px\",
						\"select\":	\"single\",
						\"pagingType\": \"full_numbers\",
						\"bProcessing\": false,
						\"serverSide\": true,
						\"sAjaxSource\": \"data/vehicles.php?owner_id=".$ownerid."\",
						\"aoColumns\": [
						  { mData: 'plate_no' },
						  { mData: 'v_type' },
						  { mData: 'v_brand' },
						  { mData: 'v_model' },
						  { mData: 'v_parking' }
						],
						\"aoColumnDefs\": [
							{className: \"dt-body-center\", \"targets\": [4]},
							{ \"targets\": [0], \"visible\": true }
						]
					});";
				break;
			}
		?>

		$('#p_employer').autocomplete({
				source:'suggestCompany.php', 
				minLength:3,
				select: function(event,ui) {
					$("#e_street").val(ui.item.address);
					
				}
			});	


	});

	function saveCInfo(fid) {
		if(confirm("Are you sure you want to save changes made to this file?") == true) {
			var msg = "";
			
			if($("#p_lname").val() == '') { msg = msg + "- Patient's Last Name is empty!<br/>"; }
			if($("#p_fname").val() == '') { msg = msg + "- Patient's First Name is empty!<br/>"; }
			if($("#p_mname").val() == '') { msg = msg + "- Patient's Middle Name is empty!<br/>"; }
			if($("#p_bday").val() == '') { msg = msg + "- Patient's Birthdate is empty!<br/>"; }
			if($("#p_email").val() == '') { msg = msg + "- Patient's email address is empty. Please indicate <b>N/A</b> if the patient cannot provide any alternative email address. <br/>"; }
			if($("#p_hmo").val() != '') {
				if($("#p_hmo_no").val() == '') {
					msg = msg + "- For Patients with HMO/Insurance Provider, you must indicate patient's HMO Policy or ID No.";
				}
			}


			if(msg!="") {
				parent.sendErrorMessage(msg);
			} else {
				var url = $(document.frmPatientInfo).serialize();
				url = "mod=savePatientInfo&"+url;
				$.post("src/sjerp.php", url);
				alert("Record Successfuly Saved!")
				parent.showPatients();
				parent.closeDialog("#customerdetails");
			}
		}
	}
	
	function deleteCust(fid) {
		if(confirm("Are you sure you want to delete this record?") == true) {
		var url = $(document.frmPatientInfo).serialize();
		url = "mod=deletePatient&"+url;
			$.post("src/sjerp.php", url);
			alert("Record Successfuly Deleted!");
			parent.showPatients();
			parent.closeDialog("#customerdetails"); 
		}	
	}

	function getCities(pid,selbox) {
		$.post("src/sjerp.php", { mod: "getCities", pid: pid, sid: Math.random() }, function(data) {
			document.getElementById(selbox).innerHTML = data;
		},"html");
	}
	
	function getBrgy(city,selbox) {
		$.post("src/sjerp.php", { mod: "getBrgy", city: city, sid: Math.random() }, function(data) {
			document.getElementById(selbox).innerHTML = data;
		},"html");
	}
	
	function changeMod(mod) {
		document.changeModPage.mod.value = mod;
		document.changeModPage.submit();
	}

	
</script>
<style>
	.dataTables_wrapper {
		display: inline-block;
		font-size: 11px;
		width: 100%; 
	}
	
	table.dataTable tr.odd { background-color: #f5f5f5;  }
	table.dataTable tr.even { background-color: white; }
</style>
</head>
<body bgcolor="#ffffff" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
<form name="frmPatientInfo" id="frmPatientInfo">
	<input type="hidden" name="pid" id="pid" value="<?php echo $pid; ?>">
	<table width="100%" cellspacing="0" cellpadding="5" style="border-bottom: 1px solid black; margin-bottom: 5px;">
		<tr>
			<td align=left>
				<?php 
					if($_GET['mod'] == 1) { 
						echo '<a href="#" onClick="saveCInfo(\''. $pid . '\');" class="topClickers"><img src="images/icons/floppy.png" width=18 height=18 align=absmiddle />&nbsp;Save Changes Made</a>&nbsp;';
						if($pid != '') {
				    		echo '&nbsp;<a href="#" onClick="parent.printPatientInfo(\'' . $pid . '\');" class="topClickers"><img src="images/icons/print.png" width=18 height=18 align=absmiddle />&nbsp;Print Patient Information Slip</a>&nbsp;&nbsp;<a href="#" onClick="deleteCust(\'' . $pid . '\');" class="topClickers"><img src="images/icons/delete.png" width=18 height=18 align=absmiddle />&nbsp;Delete Record</a>';
						}
					}
				?>
			</td>
		</tr>
	</table>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" >
		<tr>
			<td width="30%" align=center class="spandix-l" rowspan=11>
				<img src="images/id/main.png" width=120 height=120 >
				<?php
					if($pid != '') { echo "<br/><br/><span>Patient ID: <b>" . str_pad($pid,6,'0',STR_PAD_LEFT). "</b></span>"; }

				?>
			</td>
			<td width="70%" align=left colspan=2>
				<table width=100% cellpadding=0 cellspacing=2>
					<tr>
						<td>
							<input type="text" id="p_lname" name="p_lname" class="gridInput" style="width: 100%;" value="<?php echo $res['lname']; ?>" />
							<br/><span style="font-size:7pt;color:grey;padding-left:1%;" >Last Name</span>
						</td>
						<td>
							<input type="text" id="p_fname" name="p_fname" class="gridInput" style="width: 100%;" value="<?php echo $res['fname']; ?>" />
							<br/><span style="font-size:7pt;color:grey;padding-left:1%;" >First Name</span>
						</td>
					
						<td>	
							<input type="text" id="p_mname" name="p_mname" class="gridInput" style="width: 100%;" value="<?php echo $res['mname']; ?>" />
							<br/><span style="font-size:7pt;color:grey;padding-left:1%;" >Full Middle Name</span>
						</td>
						<td width=10%>	
							<select id="p_suffix" name="p_suffix" class="gridInput" style="width: 100%;">
								<option value="">- NA -</option>
								<option value="JR" <?php if($res['suffix'] == 'JR') { echo "selected"; } ?>>JR</option>
								<option value="SR" <?php if($res['suffix'] == 'SR') { echo "selected"; } ?>>SR</option>
							</select>
							<br/><span style="font-size:7pt;color:grey;padding-left:1%;" >Suffix</span>
						</td>	
					</tr>
				</table>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width="30%" align=left>
				<select id="p_gender" name="p_gender" class="gridInput" style="width: 150px;">
					<option value="M" <?php if($res['gender'] == 'M') { echo "selected"; } ?>>Male</option>
					<option value="F" <?php if($res['gender'] == 'F') { echo "selected"; } ?>>Female</option>	
				</select>
				<br/><span style="font-size:7pt;color:grey;" >Gender</span>			
			</td>
			<td width="40%" align=center class="spandix-l" rowspan=9>
				<fieldset>
					<legend>Patient Records Location</legend>
					<table width=100%>
						<tr>
							<td width=30% class="spandix-l">Cabinet No.:</td>
							<td><input type="text" name="cabinet_no" id="cabinet_no" class="gridInput" style="width: 90%;" value="<?php echo $res['cabinet_no']; ?>"></td>
						</tr>
						<tr>
							<td width=30% class="spandix-l">Drawer No.:</td>
							<td><input type="text" name="drawer_no" id="drawer_no" class="gridInput" style="width: 90%;" value="<?php echo $res['drawer_no']; ?>"></td>
						</tr>
						<tr>
							<td width=30% class="spandix-l">Folder No.:</td>
							<td><input type="text" name="folder_no" id="folder_no" class="gridInput" style="width: 90%;" value="<?php echo $res['folder_no']; ?>"></td>
						</tr>
					</table>
				</fildset>
			</td>		
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width="30%" align=left>
				<input type="text" id="p_bday" name="p_bday" class="gridInput" style="width: 150px;" value="<?php echo $res['pbday']; ?>" />
				<br/><span style="font-size:7pt;color:grey;" >Birthdate</span>			
			</td>			
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width="30%" align=left>
				<select id="p_cstat" name="p_cstat" class="gridInput" style="width: 150px;">
					<?php
						$csQuery = $con->dbquery("select csid,civil_status from pccpayroll.options_civilstatus");
						while($csRow = $csQuery->fetch_array()) {
							echo "<option value='$csRow[0]' ";
							if($csRow[0] == $res['cstat']) { echo "selected"; }
							echo ">$csRow[1]</option>";
						}
					?>
				</select>
				<br/><span style="font-size:7pt;color:grey;">Civil Status</span>			
			</td>		
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width="30%" align=left>
				<input type="text" id="p_phic" name="p_phic" class="gridInput" style="width: 150px;" value="<?php echo $res['phic_no']; ?>" />
				<br/><span style="font-size:7pt;color:grey;">Philhealth ID No. (If Available)</span>			
			</td>		
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width="30%" align=left>
				<select id="p_pwd" name="p_pwd" class="gridInput" style="width: 150px;">
					<option value='N' <?php if($res['pwd'] == 'N') { echo "selected"; } ?>>- No -</option>
					<option value='Y' <?php if($res['pwd'] == 'Y') { echo "selected"; } ?>> - Yes -</option>
				</select>
				<br/><span style="font-size:7pt;color:grey;">Person With Disability</span>			
			</td>		
		</tr>
	</table>
	<table cellspacing=0 cellpadding=0 width=100% align=center style="margin-top: 20px;">
		<tr>
			<td style="padding: 0px 0px 1px 0px;">
				<div id="custmenu" align=left class="ddcolortabs">
					<ul class=float2>
						<li><a href="#" <?php echo getMod("1",$_GET['mod']); ?> onclick="javascript: changeMod(1);"><span id="tbbalance1">General Info</span></a></li>						
						<?php
							if($pid != '') {
								echo '<li><a href="#" ' . getMod("2",$_GET['mod']) . ' onclick="javascript: changeMod(2);"><span id="tbbalance2">Transaction History</span></a></li>
								<li><a href="#" ' . getMod("3",$_GET['mod']) . ' onclick="javascript: changeMod(3);"><span id="tbbalance5">Lab Results</span></a></li>';
							}
						?>	
					</ul>
				</div>
			</td>
		</tr>
	</table>
	<?php switch($_GET['mod']) {  case "1": default: ?>
	<table width="100%" cellpadding=0 cellspacing=1 class="td_content" style="padding:10px;" border=0>
	<tr>
			<td width=20% class="spandix-l" valign=top>Address:</td>
			<td width=80%>
				<table width=100% cellpadding=0 cellspacing=1>
					<tr>
						<td width=40%>
							<input type="text" id="p_street" name="p_street" class="gridInput" style="width: 100%" value="<?php echo $res['street']; ?>" />
							<br/><span style="font-size:7pt;color:grey;" >House #,Street,Village</span>
						</td>
						<td width=30%>
							<select id="p_brgy" name="p_brgy" class="gridInput" style="width: 99%;">
								<?php
									if($res['city'] != '') {
										$brgyQuery = $con->dbquery("select brgyCode, brgyDesc from options_brgy where citymunCode = '$res[city]' order by brgyDesc asc;");
										while($brgyRow = $brgyQuery->fetch_array()) {
											echo "<option value='$brgyRow[0]' "; if($brgyRow[0] == $res['brgy']) { echo "selected"; }
											echo ">$brgyRow[1]</option>";
										}
									}
								?>
							</select>
							<br/><span style="font-size:7pt;color:grey;" >Barangay</span>
						</td>
						<td>
							<select id="p_city" name="p_city" class="gridInput" style="width: 99%;" onchange = "getBrgy(this.value,'p_brgy');">
								<?php
									if($res['province'] != '') {
										$cityQuery = $con->dbquery("select citymunCode, citymunDesc from options_cities where provCode = '$res[province]' order by citymunDesc asc;");
										while($cityRow = $cityQuery->fetch_array()) {
											print "<option value='$cityRow[0]' "; if($cityRow[0] == $res['city']) { echo "selected"; }
											print ">$cityRow[1]</option>";
										}
									}
								?>
							</select>
							<br/><span style="font-size:7pt;color:grey;" >City or Municipality</span>
						</td>
					</tr>
					<tr>
						<td width=40%>
							<select id="p_province" name="p_province" class="gridInput" style="width: 100%;" onchange="getCities(this.value,'p_city');">
								<option value="">- Select Province -</option>
								<?php
									$provQuery = $con->dbquery("select provCode, provDesc from options_provinces order by provDesc asc;");
									while($provRow = $provQuery->fetch_array()) {
										print "<option value='$provRow[0]' "; if($provRow[0] == $res['province']) { echo "selected"; }
										print ">$provRow[1]</option>";
									}
								?>
							</select>
							<br/><span style="font-size:7pt;color:grey;" >Province</span>
						</td>
					<tr/>
				</table>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=20% class="spandix-l">Place of Birth  :</td>
			<td width=80%>
				<input type="text" id="p_birthplace" name="p_birthplace" class="gridInput" style="width: 40%" value="<?php echo $res['birthplace']; ?>" />
				<br/><span style="font-size:7pt;color:grey;" >(Municipality/City,Province)</span>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=20% class="spandix-l">Guardian's Full Name  :</td>
			<td width=80%>
				<input type="text" id="p_guardian" name="p_guardian" class="gridInput" style="width: 40%" value="<?php echo $res['guardian']; ?>" />
				<br/><span style="font-size:7pt;color:grey;" >For Patients accompanied by their guardians (eg. Infants, Toddlers, Senior Citizens, PWDs, etc.)</span>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=20% class="spandix-l">Mobile No. :</td>
			<td width=80%>
				<input type="text" id="p_mobileno" name="p_mobileno" class="gridInput" style="width: 40%" value="<?php echo $res['mobile_no']; ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=20% class="spandix-l">Telephone No. :</td>
			<td width=80%>
				<input type="text" id="p_telephone" name="p_telephone" class="gridInput" style="width: 40%" value="<?php echo $res['tel_no']; ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=20% class="spandix-l">Email Address :</td>
			<td width=80%>
				<input type="text" id="p_email" name="p_email" class="gridInput" style="width: 40%" value="<?php echo $res['email_add']; ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=20% class="spandix-l">Nationality :</td>
			<td width=80%>
				<select id="p_nation" name="p_nation" style="width: 40%;" class="gridInput" />
					<option value="66">Filipino</option>
					<?php
						$q0 = $con->dbquery("SELECT line_id,nation_desc FROM nationality order by nation_desc;");
						while($_0 = $q0->fetch_array()) {
							print "<option value='$_0[0]' "; if($_0[0] == $res['nationality']) { echo "selected"; }
							print ">$_0[1]</option>";
						}
					?>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=20% class="spandix-l" valign=top>Spouse (If Married):</td>
			<td width=80%>
				<table width=100% cellpadding=0 cellspacing=1>
					<tr>
						<td width=35%>
							<input type="text" id="s_lname" name="s_lname" class="gridInput" style="width: 95%" value="<?php echo $res['spouse_lname']; ?>" />
							<br/><span style="font-size:7pt;color:grey;" >Last Name</span>
						</td>
						<td width=30%>
							<input type="text" id="s_fname" name="s_fname" class="gridInput" style="width: 95%" value="<?php echo $res['spouse_fname']; ?>" />
							<br/><span style="font-size:7pt;color:grey;" >First Name</span>
						</td>
						<td>
							<input type="text" id="s_mname" name="s_mname" class="gridInput" style="width: 95%" value="<?php echo $res['spouse_mname']; ?>" />
							<br/><span style="font-size:7pt;color:grey;" >Full Middle Name</span>
						</td>
						<td width=10%>	
							<select id="s_suffix" name="s_suffix" class="gridInput" style="width: 100%;">
								<option value="">- NA -</option>
								<option value="JR" <?php if($res['s_suffix'] == 'JR') { echo "selected"; } ?>>JR</option>
								<option value="SR" <?php if($res['s_suffix'] == 'SR') { echo "selected"; } ?>>SR</option>
							</select>
							<br/><span style="font-size:7pt;color:grey;padding-left:1%;" >Suffix</span>
						</td>
					</tr>
					<tr>
					<td width=35%>
							<input type="text" id="s_bday" name="s_bday" class="gridInput" style="width: 60%" value="<?php echo $res['sbday']; ?>" />
							<br/><span style="font-size:7pt;color:grey;" >Spouse's Birthdate</span>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=20% class="spandix-l">Occupation :</td>
			<td width=80%>
				<input type="text" id="p_occupation" name="p_occupation" style="width: 40%;" class="gridInput" value = "<?php echo $res['occupation']; ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=20% class="spandix-l">Employer :</td>
			<td width=80%>
				<input type="text" id="p_employer" name="p_employer" style="width: 100%;" class="gridInput" value = "<?php echo $res['employer']; ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=20% class="spandix-l" valign=top>Employer's Address :</td>
			<td width=80%>
				<input type="text" id="e_street" name="e_street" class="gridInput" style="width: 100%" value="<?php echo $res['emp_street']; ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=20% class="spandix-l">Employee's ID No. :</td>
			<td width=80%>
				<input type="text" id="p_idno" name="p_idno" style="width: 40%;" class="gridInput" value = "<?php echo $res['emp_idno']; ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=20% class="spandix-l">HMO Provider :</td>
			<td width=80%>
				<input type="text" id="p_hmo" name="p_hmo" style="width: 40%;" class="gridInput" value = "<?php echo $res['hmo_provider']; ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=20% class="spandix-l">HMO ID No. :</td>
			<td width=80%>
				<input type="text" id="p_hmo_no" name="p_hmo_no" style="width: 40%;" class="gridInput" value = "<?php echo $res['hmo_no']; ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=20% class="spandix-l">HMO Card Expriy :</td>
			<td width=80%>
				<input type="text" id="p_hmo_expiry" name="p_hmo_expiry" style="width: 40%;" class="gridInput" value = "<?php echo $res['hexpiry']; ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
	</table>
	<?php break; 
		case "2":
		echo '<table id="solist" style="font-size:11px;">
		<thead>
			<tr>
				<th width=10%>SO #</th>
				<th width=12%>DATE</th>
				<th width=12%>OR #</th>
				<th>AVAILED SERVICE</th>
				<th width=15%>AMOUNT</th>
			</tr>
		</thead><tbody>';
		
		$soQuery = $con->dbquery("SELECT LPAD(a.so_no,6,'0') AS sono, DATE_FORMAT(so_date,'%m/%d/%Y') AS sodate, b.code, b.description, b.amount_due FROM so_header a LEFT JOIN so_details b ON a.so_no = b.so_no AND a.branch = b.branch WHERE a.patient_id = '$pid' AND a.status = 'Finalized' ORDER BY so_date asc;");
		while($soRow = $soQuery->fetch_array()) {

			list($orno) = $con->getArray("select distinct a.or_no from or_header a left join or_details b on a.doc_no = b.doc_no and a.branch = b.branch where b.so_no = '$soRow[sono]';");

			echo '<tr>
					<td><a href="#" class="text-decoration: none;" onclick="parent.viewSO(\''.$soRow['sono'].'\')">'.$soRow['sono'].'</a></td>
					<td>'.$soRow['sodate'].'</td>
					<td>'.$orno.'</td>
					<td>('.$soRow['code'].') '.$soRow['description'].'</td>
					<td>'.$soRow['amount_due'].'</td>
				 </tr>'; $orno = '';
		}
		echo '</tbody>';
	?>
	</table>
	<?php break; case "3": ?>
	<table id="ilist" style="font-size:11px;">
		<thead>
			<tr>
				<th width=20%>PLATE NO.</th>
				<th width=20%>CATEGORY</th>
				<th width=20%>MAKE/BRAND</th>
				<th width=20%>MODEL</th>
				<th width=20%>DESIGNATED PARKING</th>
			</tr>
		</thead>
	</table>
	<table>
	<tr>
		<td align=left colspan=2 style="padding-top:5px;">
			<a href="#" class="topClickers" onClick="javascript:newV();"><img src="images/icons/add-2.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Add Entry</a>&nbsp;&nbsp;
			<a href="#" class="topClickers" onClick="javascript:editV();"><img src="images/icons/tests256.png" width=16 height=16 border=0 align="absmiddle">&nbsp;View Entry</a>&nbsp;&nbsp;
			<a href="#" class="topClickers" onClick="javascript:deleteVehicle();"><img src="images/icons/delete.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Remove Entry</a>
		</td>
	</tr>
	</table>
	<?php break; } ?>
</form>

<form name="changeModPage" id="changeModPage" action="patient.details.php" method="GET" >
	<input type="hidden" name="pid" id="pid" value="<?php echo $_GET['pid']; ?>">
	<input type="hidden" name="mod" id="mod">
</form>
</body>
</html>