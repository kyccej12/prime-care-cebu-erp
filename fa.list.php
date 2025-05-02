<html>
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

	$(document).ready(function() {

		var myTable = $('#itemlist').DataTable({
			"scrollY":  "370",
			"select":	'single',
			"pageLength": 50,
			"searching": false,
			"paging": false,
			"info": false,
			"bSort": false,
			"aoColumnDefs": [
				{ className: "dt-body-center", "targets": [1,4,7,8] },
				{ className: "dt-body-right", "targets": [6] },
				{ "targets": [0], "visible": false }
			]
		});

		$('#itemlist tbody').on('dblclick', 'tr', function () {
			var data = myTable.row( this ).data();	
			parent.viewFA(data[0]);
		});


	});



	function viewAsset() {

		var table = $("#itemlist").DataTable();		
		var aid;
		$.each(table.rows('.selected').data(), function() {
			aid = this[0];
		});
		if(aid == "") {
			parent.sendErrorMessage("Unable to retrieve record. Please select a record from the list, and once highlighted, press  \"<b><i>View Asset Information</i></b>\" button again...");
		} else {
			parent.viewFA(aid);
		}
	}
	
	function viewLogs() {

		var table = $("#itemlist").DataTable();		
		var aid;
		$.each(table.rows('.selected').data(), function() {
			aid = this[0];
		});
		if(aid == "") {
			parent.sendErrorMessage("Unable to continue. Please select a record from the list first and click the <b>Maintenance Logs</b> button again.");
		} else {
			parent.viewMlogs(aid);
		}
	}

	function searchRecord() {

		var stxt = document.getElementById("stxt").value;

		if(stxt != '') {
			document.getElementById("searchText").value = stxt;
			document.frmSearch.submit();
		} else {
			parent.sendErrorMessage("Unable to continue as you have specified and empty search string...");
		}

	}

	function handle(e){
        if(e.keyCode === 13){
			searchRecord();
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
<body bgcolor="#ffffff" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0" >
	<table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:2px;">
		<tr>
			<td width=50%>
				
			</td>
			<td align=right>
				<input name="stxt" id="stxt" type="text" class="gridInput" style="width: 240px; height: 24px;" value="<?php echo $_REQUEST['searchText']; ?>" placeholder="Search Record" onkeypress="handle(event)">
				<button class="ui-button ui-widget ui-corner-all" onClick="javascript: searchRecord();">
					<span class="ui-icon ui-icon-search"></span> Search Record
				</button>
			</td>
		</tr>
	</table>
	<table class="cell-border" id="itemlist" style="font-size:11px;">
		<thead>
			<tr>
				<th></th>
				<th width=8%>ASSET #</th>
				<th width=8%>SERIAL #</th>
				<th>DESCRIPTION</th>
				<th width=10%>CATEGORY</th>
				<th width=15%>VENDOR</th>
				<th width=10%>ASSET COST</th>
				
				<th width=12%>ACQUIRED ON</th>
				<th width=8%>STATUS</th>
				<th width=10%>ASSIGNED TO</th>
			</tr>
		</thead>

		<?php
			require_once("handlers/initDB.php");
			$con = new myDB;

			if($_REQUEST['searchText'] != '') {
				$stxt = $_REQUEST['searchText'];
				$searchString = " and  (a.asset_no = '$stxt' || a.serial_no like '%$stxt$' || a.asset_description like '%$stxt%' || a.vendor like '%$stxt%' || a.assigned_to like '%$stxt%') ";

			}

			$txt = "SELECT fid, asset_no, serial_no, asset_description, b.category, a.vendor, format(a.cost,2) as cost, if(date_acquired='0000-00-00','',date_format(date_acquired,'%m/%d/%Y')) as acqdate, `status`, assigned_to FROM fa_master a LEFT JOIN fa_category b on a.category=b.id where 1=1 $searchString order by asset_no;";
			$query = $con->dbquery($txt);
			$i = 0;
			while($row = $query->fetch_array()) {
				echo "<tr>
						<td>$row[fid]</td>
						<td>$row[asset_no]</td>
						<td>$row[serial_no]</td>
						<td>$row[asset_description]</td>
						<td>$row[category]</td>
						<td>$row[vendor]</td>
						<td>$row[cost]</td>
						<td>$row[acqdate]</td>
						<td>$row[status]</td>
						<td>$row[assigned_to]</td>
				</tr>";
				$i++;
			}

		?>


	</table>

	<table width="100%"  cellspacing="0" cellpadding="0" style="padding-left: 5px; background-color: #f7f7f7">
		<tr><td height=8></td></tr>
		<tr>
			<td>
				<button onClick="parent.viewFA('');" class="buttonding"><img src="images/icons/add.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;New Record</b></button>
				<button onClick="viewAsset();" class="buttonding"><img src="images/icons/fasset.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;View Asset Information</b></button>
				<button type=button class="buttonding" stylye="height: 25px;" onclick="javascript: viewLogs();" ><img src="images/icons/settings.png" width=18 height=18 align=absmiddle />&nbsp;Maintenance Logs</button>
				<button onClick="parent.showFA();" class="buttonding"><img src="images/icons/refresh.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;Refresh List</b></button>
			</td>
		</tr>
	</table>

	<form name="frmSearch" id="frmSearch" method = "POST" action = "fa.list.php">
		<input type="hidden" name="searchText" id="searchText">
	</form>

</body>
</html>