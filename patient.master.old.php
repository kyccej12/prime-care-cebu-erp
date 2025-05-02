<?php
	session_start();
	
	function getMod($def,$mod) {
		if($def == $mod) { echo "class=\"float2\""; }
	}
	
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Medgruppe Polyclinics & Diagnostic Center, Inc.</title>
<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
<link href="style/style.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="ui-assets/datatables/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
<script>

	function changeMod(mod) {
		document.changeModPage.mod.value = mod;
		document.changeModPage.submit();
	}

	function editRecord(){
		var table = $("#itemlist").DataTable();
		var arr = [];
	   	$.each(table.rows('.selected').data(), function() {
		   arr.push(this["pid"]);
	   	});
	  
		if(!arr[0]) {
			parent.sendErrorMessage("You haven't selected any record yet...");
		} else {
			parent.showPatientInfo(arr[0]);	
		}
	}
	
	$(document).ready(function() {
		var myTable = $('#itemlist').DataTable({
			"scrollY":  "340px",
			"select":	'single',
			"pagingType": "full_numbers",
			"bProcessing": true,
			"sAjaxSource": "data/patientlist.php",
			"order": [[2,"asc"],[3,"asc"],[4,"asc"]],
			"aoColumns": [
			  { mData: 'pid' },
			  { mData: 'patient' },
			  { mData: 'lname' },
			  { mData: 'fname' },
			  { mData: 'mname' },
			  { mData: 'gender' },
			  { mData: 'paddress' },
			  { mData: 'email_add' },
			  { mData: 'mobile_no' }
			],
			"aoColumnDefs": [
				{ className: "dt-body-center", "targets": [1,5,7,8]},
				{ "targets": [0], "visible": false }
			]
		});

		$('#itemlist tbody').on('dblclick', 'tr', function () {
				var data = myTable.row( this ).data();	
				parent.showPatientInfo(data['pid']);
			});

	});

	function refreshList() {
		$('#itemlist').DataTable().ajax.url("data/patientlist.php").load();
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
	<table width="100%" cellspacing="0" cellpadding="0" style="padding-left: 5px; margin-bottom:2px;">
		<tr>
			<td>
				<button class="ui-button ui-widget ui-corner-all" onClick="parent.addPatient('');">
					<img src="images/icons/adduser.png" width=14 height=14 align=absmiddle /> New Patient Record
				</button>
				<button class="ui-button ui-widget ui-corner-all" onClick="editRecord();">
					<span class="ui-icon ui-icon-newwin"></span> Open Selected Patient Information
				</button>
				<button class="ui-button ui-widget ui-corner-all" onClick="refreshList();">
					<span class="ui-icon ui-icon-refresh"></span> Reload List
				</button>
			</td>
		</tr>
	</table>
	<table id="itemlist" style="font-size:11px;">
		<thead>
			<tr>
				<th></th>
				<th width=10%>PATIENT ID</th>
				<th width=10%>LAST NAME</th>
				<th width=10%>FIRST NAME</th>
				<th width=10%>MIDDLE NAME</th>
				<th width=10%>GENDER</th>
				<th>ADDRESS</th>
				<th width=10%>EMAIL ADD</th>
				<th width=10%>MOBILE NO</th>
			</tr>
		</thead>
	</table>

 <form name="changeModPage" id="changeModPage" action="contact.master.php" method="GET" >
	<input type="hidden" name="mod" id="mod">
</form>
</body>
</html>
