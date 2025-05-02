<?php
	require_once '../handlers/_generics.php';
	$o = new _init;
	
?>
<html>
<head>
	<link href="../style/style.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="../ui-assets/jquery/jquery-1.12.3.js"></script>
	<script type="text/javascript" src="../ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script language="javascript">
		function importData() {
			if($("#cutoff").val() == "") {
				alert("Error: You must specify cut-off period/log file.");
			} else {
				$.post("hrd.checkrawexists.php", { mod: "checkBio", period: $("#cutoff").val() }, function(data) {
					if(data == "shubet") {
						if(confirm("Error: Cut-off period specified has already been imported into the system. Do you want to process raw logs again?") == true) {
							$.post("checkrawexists.php", { mod: "checkFinal", period: $("#cutoff").val() }, function(data) {
								if(data == "shubet") { 
									alert("Error: Unable to continue. It seems the cut-off period you specified has already been posted for payroll processing."); 
								} else {
									document.frmimportlogs.submit();
								}
							},"html");
						}
					} else {
						document.frmimportlogs.submit();
					}
				},"html");
			}
		}
		
		function populatePeriods(batch,selbox) {
			$.post("misc-data.php", { mod: "populatePeriods", batch: batch, sid: Math.random() }, function(htmlData) { document.getElementById(selbox).innerHTML = htmlData; },"html");
		}
	</script>
</head>
<body leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
	<form enctype="multipart/form-data" name="frmimportlogs" method=post action="importlogs.php" target="_blank">
		<input type="hidden" name="MAX_FILE_SIZE" value="2000000">
		<table width=90% align=center>
			<tr><td height=8></td></tr>
			<tr>
				<td width=35%><span class="spandix-l">Payroll Batch :</span></td>
				<td>
					<select name="batch" id="batch" style="width: 90%; font-size: 11px;" class="gridInput" onchange="javascript: populatePeriods(this.value,'cutoff');" />
						<option value="">Select Payroll Batch</option>
						<?php
							$_sq = $o->dbquery("select batch_id,batch_details from pccpayroll.pay_batch order by batch_id");
							while($batchrow = $_sq->fetch_array(MYSQLI_BOTH)) {
								echo "<option value='$batchrow[0]' title='$batchrow[1]'>Batch $batchrow[0]</option>";
							}
						?>
					</select>
				</td>
			</tr>
			<tr><td height=4></td></tr>
			<tr>
				<td width=35%><span class="spandix-l">Payroll Period :</span></td>
				<td>
					<select name="cutoff" id="cutoff" style="width: 90%; font-size: 11px;" class="gridInput" />
						
					</select>
				</td>
			</tr>
			<tr><td height=4></td></tr>
			<tr><td class=bareThin width=30% align=right style="padding-right: 15px;">File to Upload&nbsp;:</td>
				<td align=left><input type=file id="userfile" name="userfile" style="width:90%"></td>
			</tr>
			<tr><td height=2></td></tr>
		</table>
		<hr style="width:80%;" align=center>
		<table align=center>
			<tr><td height=8></td></tr>
			<tr><td></td>
				<td>
					<button class="buttonding" onclick="importData();"><img src="../images/icons/download.png" border=0 width="16" height="16" align=absmiddle>&nbsp;&nbsp;Import Raw DTR File</button>
				</td>
			</tr>
		</table>
	</form>
</body>
</html>
<?
