<?php
	session_start();
	
	/* Added for Advance Search Function */
	if($_POST['sflag'] == 'Y') {
		if($_POST['stxt_description'] != '') { $b = "&idesc=$_POST[stxt_description]"; }
		if($_POST['stxt_dtf'] != '' && $_POST['stxt_dt2'] != '') {
			$c = "&dtf=".formatDate($_POST['stxt_dtf'])."&dt2=".formatDate($_POST['stxt_dt2']);
		} else {
			if($_POST['stxt_dtf'] != '') { $c = "&doc_date=".formatDate($_POST['stxt_dtf']); } 
			if($_POST['stxt_dt2'] != '') { $c = "&doc_date= '".formatDate($_POST['stxt_dt2']); }
		}
	}
	
?>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Medgruppe Polyclinics & Diagnostic Center, Inc.</title>
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="ui-assets/datatables/css/jquery.dataTables.css">
	<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
	<script>
		function viewCSO() {
			var table = $("#itemlist").DataTable();
			var arr = [];
		    $.each(table.rows('.selected').data(), function() { cso_no = this["cso_no"]; });
			
			if(!cso_no) {
				parent.sendErrorMessage("Please select record to view.");
			} else {
				parent.viewCSO(cso_no);
			}
		}

		$(document).ready(function() {
			$('#itemlist').dataTable({
				"ajax": {
					"url": "data/csolist.php",
					"method": "POST"
				},
				"scrollY":  "350px",
				"select":	'single',
				"pagingType": "full_numbers",
				"order": [[ 1, "desc" ]],
				"aoColumns": [
				  { mData: 'cso_no' },
				  { mData: 'csdate' },
				  { mData: 'duration' },
				  { mData: 'cso_type' },
				  { mData: 'cname' },
				  { mData: 'company' },
				  { mData: 'remarks' },
				  { mData: 'amount' },
				  { mData: 'status' },
				],
				"aoColumnDefs": [
					{ "className": "dt-body-center", "targets": [0,1,2,3,8] },
					{ "className": "dt-body-right", "targets": [7] }
				]
			});
		});
		
		function searchRecord() {
			$("#stxt_dtf").datepicker(); $("#stxt_dt2").datepicker();
			$("#searchDiv").dialog({
				title: "Search Record", 
				width: 400,
				resizable: false, 
				modal: true, 
				buttons: {
					"Search Record": function() {
						document.getElementById('frmSearch').submit();
					},
					"Close": function() { $(this).dialog("close"); }
				}
			});
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
<body bgcolor="#ffffff" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0" >

	<table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:2px;">
		<tr>
			<td>
				<button class="ui-button ui-widget ui-corner-all" onClick="parent.viewCSO('');">
					<span class="ui-icon ui-icon-plusthick"></span> Create Corporate Sales Order
				</button>
				<button class="ui-button ui-widget ui-corner-all" onClick="viewCSO();">
					<span class="ui-icon ui-icon-newwin"></span> Open Selected Corporate Sales Order
				</button>
				<button class="ui-button ui-widget ui-corner-all" onClick="parent.showCSO();">
					<span class="ui-icon ui-icon-refresh"></span> Reload List
				</button>
			</td>
		</tr>
	</table>
	<table id="itemlist" style="font-size:11px;">
		<thead>
			<tr>
				<th width=6%>CSO #</th>
				<th width=6%>DATE</th>
				<th width=12%>SCHEDULE</th>
				<th width=8%>CSO TYPE</th>
				<th width=15%>BILLED TO</th>
				<th width=15%>COMPANY</th>
				<th>REMARKS</th>
				<th width=10%>AMOUNT</th>
				<th width=10%>DOC STATUS</th>
			</tr>
		</thead>
	</table>
    <div id="searchDiv" style="display: none;">
		<form name = "frmSearch" id = "frmSearch" method = "POST" action = "phy.list.php">
			<input type = "hidden" name = "sflag" id = "sflag" value = "Y">
			<table width = "100%" cellpading = 0 cellspacing = 0>
				<tr>
					<td class="spandix-l">Date Covered :</td>
					<td ><input type="text" style="width:80%;" class="nInput" name="stxt_dtf" id="stxt_dtf"></td>
				</tr>
				<tr>
					<td class="spandix-l"></td>
					<td ><input type="text" style="width:80%;" class="nInput" name="stxt_dt2" id="stxt_dt2"></td>
				</tr>
				<tr>
					<td class="spandix-l">Item Description :</td>
					<td ><input type="text" style="width:80%;" class="nInput" name="stxt_description" id="stxt_description"></td>
				</tr>
			</table>
		</form>
	</div>
	</body>
</html>