<?php
	session_start();
	include("handlers/_generics.php");
	$con = new _init;

	$res = $con->getArray("SELECT * from fa_master where fid = '$_GET[fid]';");

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="ui-assets/datatables/css/jquery.dataTables.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
	<script>
		var sPO = "";

		$(document).ready(function() {
			$('#details').DataTable({
				"scrollY":  "280",
				"select":	'single',
				"searching": false,
				"paging": false,
				"info": false,
				"aoColumnDefs": [
					{ className: "dt-body-center", "targets": [1,2,5,6]},
					{ className: "dt-body-right", "targets": [7]},
					{ "targets": [0], "visible": false }
				]
			});
		});
		

		function viewDetails() {
	
			var table = $("#details").DataTable();
			$.each(table.rows('.selected').data(), function() {
				doc_no = this[0];
				doc_type = this[2];
			});
		
			if(!doc_no) {
				parent.sendErrorMessage("- It appears you have not selected any transaction yet to view...");

			} else {
				

			}
		
		}

		function newLog() {
			$("#po_date").datepicker(); $("#date").datepicker();
			$("#itemEntry").dialog({
				title: "Maintenance Log Entry", 
				width: 480,
				modal: true,
				buttons: [
					{
						text: "Add this Entry",
						icons: { primary: "ui-icon-check" },
						click: function() {
							
							if(confirm("Are you sure you want to add this log entry?") == true) {
								$.post("src/sjerp.php", { 
									mod: "saveMlog", 
									asset_no: "<?php echo $res['asset_no']; ?>", 
									type: $("#type").val(),
									scope: $("#scope").val(),
									date: $("#date").val(),
									pby: $("#performed_by").val(),
									po_no: $("#po_no").val(),
									po_date: $("#po_date").val(),
									cost: $("#cost").val(),
									remarks: $("#remarks").val(),
									sid: Math.random() }, function() {
										alert("Log Entry Successfully Added!");
										parent.viewMlogs(<?php echo $_GET['fid']; ?>);
									}
								);
							}

						}
					}
				]
			});
		}

		function deleteLog() {
			var table = $("#details").DataTable();		
			var lid;
			$.each(table.rows('.selected').data(), function() {
				lid = this[0];
		
			});

			if(!lid) {
				parent.sendErrorMessage("Unable to continue. Please make sure you have selected a log entry to delete.");
			} else {
				if(confirm("Are you sure you want to delete this log entry?") == true) {
					$.post("src/sjerp.php", { mod: "deleteMLog", id: lid, sid: Math.random() }, function() { parent.viewMlogs(<?php echo $_GET['fid']; ?>); });

				}
			}

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
		.dataTables_filter input { width: 250px; }
	</style>
</head>
<body bgcolor="#FFFFFF" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0" >
	<table width=100% style="padding: 10px;" class="td_content">
		<tr>
			<td width=15% class="spandix-l">Asset No. :</td>
			<td align=left><?php echo $res['asset_no']; ?></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td width=15% class="spandix-l">Asset Description :</td>
			<td align=left><?php echo $res['asset_description'] ?></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td width=15% class="spandix-l">Serial No. # :</td>
			<td align=left><?php echo $res['serial_no']; ?></td>
		</tr>
		<tr>
			<td width=15% class="spandix-l">Date Acquired :</td>
			<td align=left><?php echo $res['date_acquired']; ?></td>
		</tr>
	</table>

	<table id="details">
		<thead>
			<tr>
				<th></th>
				<th width=10%>DATE</th>
				<th width=10%>TYPE</th>
				<th>ACTIVITY/SCOPE OF WORK</th>
				<th width=10%>PERFORMED BY</th>
				<th width=10%>PO #</th>
				<th width=10%>PO DATE</th>
				<th width=12%>MAINTENANCE COST</th>
			</tr>
		</thead>
		<tbody>
		<?php
			
			$query = $con->dbquery("SELECT record_id, date_format(date_performed,'%m/%d/%Y') as pd8, `type`, `details`, performed_by, po_no, if(po_date != '0000-00-00',date_format(po_date,'%m/%d/%Y'),'') pod8, cost from fa_mlogs where asset_no = '$res[asset_no]';");
			
			while($row = $query->fetch_array()) {
				switch($row['type']) {
					case "1":
						$type = "Routine Maintenance";
					break;
					case "2":
						$type = "Repair Job (In-house)";
					break;
					case "3":
						$type = "Repair Job (Outsourced)";
					break;
					case "4":
						$type = "Asset Re-assignment";
					break;

				}
				echo "<tr>
						<td>$row[record_id]</td>
						<td>$row[pd8]</td>
						<td>$type</td>
						<td>$row[details]</td>
						<td>$row[performed_by]</td>
						<td>$row[po_no]</td>
						<td>$row[pod8]</td>
						<td>".number_format($row['cost'],2)."</td>
				</tr>";


			}


		?>
		</tbody>
	</table>

    <table width=100% cellpadding=5 cellspacing=0 class="td_content">
		<tr>
			<td align=left>
				<button onClick="newLog();" class="buttonding"><img src="images/icons/add-2.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;New Maitenance Log</button>
				<button onClick="deleteLog();" class="buttonding"><img src="images/icons/delete.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;Delete Log Entry</button>
			</td>
		</tr>
	</table>
	
	<div id="itemEntry" style="display: none;">
		<table width=100% cellpadding=1>
			
			<tr>
				<td width=35% class="spandix-l">Type :</td>
				<td>
					<select class="gridInput" style="width: 90%;" name="type" id="type">
						<option value='1'>Routine Maintenance</option>
						<option value='2'>Repair Job (In-house)</option>
						<option value='3'>Repair Job (Outsourced)</option>
						<option value='4'>Asset Re-assignment</option>
					</select>
				</td>
			</tr>
			<tr>
				<td width=35% class="spandix-l" valign=top>Scope or Activity :</td>
				<td>
					<textarea name="scope" id="scope" style="width: 90%;" cols=3></textarea>
				</td>
			</tr>
			<tr>
				<td width=35% class="spandix-l">Date :</td>
				<td><input type="text" class="gridInput" style="width: 90%;" name="date" id="date" value="<?php echo date('m/d/Y'); ?>"></td>
			</tr>
			<tr>
				<td width=35% class="spandix-l">Performed By :</td>
				<td><input type="text" class="gridInput" style="width: 90%;" name="performed_by" id="performed_by"></td>
			</tr>
			<tr>
				<td width=35% class="spandix-l">PO # (if any):</td>
				<td><input type="text" class="gridInput" style="width: 90%;" name="po_no" id="po_no"></td>
			</tr>
			<tr>
				<td width=35% class="spandix-l">PO Date (if any):</td>
				<td><input type="text" class="gridInput" style="width: 90%;" name="po_date" id="po_date"></td>
			</tr>
			<tr>
				<td width=35% class="spandix-l">Maintenance Cost:</td>
				<td><input type="text" class="gridInput" style="width: 90%;" name="cost" id="cost" value="0.00"></td>
			</tr>
			<tr>
				<td width=35% class="spandix-l" valign=top>Memo/Remarks :</td>
				<td>
					<textarea name="remarks" id="remarks" style="width: 90%;" cols=3></textarea>
				</td>
			</tr>
		</table>
	</div>

	</body>
</html>