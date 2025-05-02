<?php
	session_start();
?>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Medgruppe Polyclinics & Diagnostic Center, Inc.</title>
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="style/jquery.timepicker.css" rel="stylesheet" type="text/css" />
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="ui-assets/datatables/css/jquery.dataTables.css">
	<link rel="stylesheet" type="text/css" href="ui-assets/keytable/css/keyTable.jqueryui.css">
	<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery.timepicker.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/page.jumpToData().js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/keytable/js/dataTables.keyTable.min.js"></script>
	<script>

		$(document).ready(function() {
			var myTable = $('#itemlist').DataTable({
				"ajax": {
					"url": "data/consultlist.php",
					"method": "POST"
				},
				"scrollY":  "350px",
				"select":	'single',
				"pagingType": "full_numbers",
				"aoColumns": [
				  { mData: 'trans_id' } ,
				  { mData: 'prio' } ,
				  { mData: 'so' } ,
				  { mData: 'tdate' },
				  { mData: 'pname' },
				  { mData: 'age' },
				  { mData: 'gender' },
				  { mData: 'compname' },
				  { mData: 'procedure' },
				  { mData: 'clinic' },
				  { mData: 'status' },
				  { mData: 'pid' }
				],
				"aoColumnDefs": [
					{ className: "dt-body-center", "targets": [1,2,3,5,6,8,9,10] },
					{ "targets": [0,11], "visible": false }
				],
				"order": [[ 1, "desc" ]]
			});

			$('#itemlist tbody').on('dblclick', 'tr', function () {
				var data = myTable.row( this ).data();	
				parent.viewConsultForm(data['trans_id'],data['pid']);
			});
		});

		function viewRecord() {
			var table = $("#itemlist").DataTable();		
			var pid;
			var trans_id;
			$.each(table.rows('.selected').data(), function() {
				pid = this['pid'];
				trans_id = this['trans_id'];
			});
			
			if(trans_id == '' || trans_id == undefined) {
				parent.sendErrorMessage("Please select a record to continue....")
			} else {
			
				parent.viewConsultForm(trans_id,pid);
			}
		}


		function refresh() {
			$('#itemlist').DataTable().ajax.url("data/consultlist.php?sid="+Math.random()+"").load();
		}

		
	function assignClinic() {
		var table = $("#itemlist").DataTable();		
		var so_no;
		$.each(table.rows('.selected').data(), function() {
			so_no = this['so'];
			pid = this['pid'];
			trans_id = this['trans_id'];
		});

		if(!so_no) {
			parent.sendErrorMessage("Please select patient to assign in clinic....")
		} else {
			var dis = $("#clinicAssignment").dialog({
				title: "Assign Patient to Clinic",
				width: "420",
				modal: true,
				buttons: [
					{
						text: "Assign Patient to Selected Clinic",
						icons: { primary: "ui-icon-check" },
						click: function() {
							if(confirm("Are you sure you want to assign this patient to the selected clinic?") == true) {
								$.post("consult.datacontrol.php", { mod: "assignToClinic", pid: pid, so_no: so_no, transid: trans_id, clinic: $("#assClinic").val(), sid: Math.random() }, function (){
									refresh();
									dis.dialog("close");
								});
							}
						}
					},
					{
						text: "Close",
						icons: { primary: "ui-icon-closethick" },
						click: function() { $(this).dialog("close"); }
					}
				]

			})

		}

	}

	</script>
	<style>
		.dataTables_wrapper {
			display: inline-block;
			font-size: 11px; padding: 3px;
			width: 99%; 
		}
		
		table.dataTable tr.odd { background-color: #f5f5f5;  }
		table.dataTable tr.even { background-color: white; }
		.dataTables_filter input { width: 250px; }
	</style>
</head>
<body bgcolor="#ffffff" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0" >
	<table width=100% cellpadding=0 cellspacing=0 style="padding-left: 5px; margin-bottom: 2px;">
		<tr>
			<td align=left>
				<!-- <a href="#" onClick="parent.viewConsultForm('');" class="topClickers"><img src="images/icons/add-2.png" width=18 height=18 align=absmiddle />&nbsp;New Record</a>&nbsp; -->
				<button type="button" style="padding:7px;"><a href="#" onClick="viewRecord();" class="topClickers"><img src="images/icons/bill.png" width=18 height=18 align=absmiddle />&nbsp;Edit/Update Selected Record</a></button>
				<button type="button" style="padding:7px;"><a href="#" onClick="assignClinic();" class="topClickers"><img src="images/icons/doc-search.png" width=18 height=18 align=absmiddle />&nbsp;Assign Room</a></button>
				<button type="button" style="padding:7px;"><a href="#" onClick="refresh();" class="topClickers"><img src="images/icons/refresh.png" width=18 height=18 align=absmiddle />&nbsp;Refresh List</a></button>
			</td>
		</tr>
	</table>
	<table id="itemlist" style="font-size:11px;">
		<thead>
			<tr>
				<th></th>
				<th width=6%>PRIO #</th>
				<th width=6%>SO #</th>
				<th width=6%>SO DATE</th>
				<th width=20%>PATIENT NAME</th>
				<th width=5%>AGE</th>
				<th>GENDER</th>
				<th width=15%>COMPANY</th>
				<th>PROCEDURE</th>
				<th>ROOM #</th>
				<th width=10%>STATUS</th>
				<th></th>
			</tr>
		</thead>
	</table>
	<div id="clinicAssignment" style="display: none;">
		<table width=100% cellpadding=5>
			<tr>
				<td class="spandix-l" width=35%>Room Assignment :</td>
				<td>
					<select name="assClinic" id="assClinic" style="width: 100%; font-size: 11px;">
						<option value="1">Room 1</option>
						<option value="2">Room 2</option>
						<option value="3">Room 3</option>
						<option value="4">Room 4</option>
						<option value="5">Room 5</option>
						<option value="6">Room 6</option>
					</select>
				</td>
			</tr>
		</table>
	</div>
</body>
</html>