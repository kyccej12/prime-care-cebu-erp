<?php
	session_start();
	include('handlers/_generics.php');
	$con = new _init();
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Medgruppe Polyclinics & Diagnostic Center, Inc.</title>
<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
<link href="style/jquery.timepicker.css" rel="stylesheet" type="text/css" />
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
			"keys": true,
			"scrollY":  "300px",
			"select":	'single',
			"pagingType": "full_numbers",
			"bProcessing": true,
			"responsive": true,
			"scroller": true,
			"aoColumnDefs": [
			    { "className": "dt-body-center", "targets": [2,4,5]},
			    { "targets": [0], "visible": false }
            ]
		});

		$('#itemlist tbody').on('dblclick', 'tr', function () {
			var data = myTable.row( this ).data();	
			parent.xrayTemplateDetails(data[0]);
		});


		$('#itemlist tbody').on('dblclick', 'tr', function () {
			
		});

	});

	function viewRecord() {
		var table = $("#itemlist").DataTable();		
		var id;
		$.each(table.rows('.selected').data(), function() {
			id = this[0];
		});

		if(!id) {
			parent.sendErrorMessage("It appears that you haven't selected any record to view or update...");
		} else {
			parent.viewCareProviderProfile(id);
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
<body bgcolor="#ffffff" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
<div id = "main">
	<table width="100%" cellspacing="0" cellpadding="0" style="padding-left: 5px; margin-bottom: 2px;">
		<tr>
			<td>
				<button class="ui-button ui-widget ui-corner-all" onClick="parent.newCareProviderProfile();">
					<span class="ui-icon ui-icon-plusthick"></span> New Record
				</button>
				<button class="ui-button ui-widget ui-corner-all" onClick="viewRecord();">
					<span class="ui-icon ui-icon-plusthick"></span> View or Update Record
				</button>
				<button class="ui-button ui-widget ui-corner-all" onClick="parent.showMedConsultant();">
					<span class="ui-icon ui-icon-refresh"></span> Reload List
				</button>
			</td>
		</tr>
	</table>
	<table id="itemlist" style="font-size:11px;">
		<thead>
			<tr>
				<th></th>
				<th>Full Name</th>
				<th width=12%>Specialization</th>
				<th width=15%>File Created On</th>
				<th width=15%>Last Updated On</th>
				<th width=15%>Last Updated By</th>
				<th width=12%>File Status</th>
			</tr>
		</thead>
        <tbody>
        <?php
            $dQuery = $con->dbquery("SELECT id, a.fullname, specialization,DATE_FORMAT(a.created_on,'%m/%d/%Y %r') AS created, DATE_FORMAT(a.updated_on,'%m/%d/%Y %r') AS updated, b.fullname AS uby FROM options_doctors a LEFT JOIN user_info b ON a.updated_by = b.emp_id WHERE 1=1 ORDER BY a.fullname;");
            while($dRow = $dQuery->fetch_array()) {
                echo "<tr>
                        <td>$dRow[0]</td>
                        <td>$dRow[1]</td>
                        <td>$dRow[2]</td>
                        <td>$dRow[3]</td>
                        <td>$dRow[4]</td>
                        <td>$dRow[5]</td>
                        <td>$dRow[6]</td>
                    </tr>";    
            }
        ?>
        </tbody>
	</table>
</div>
</body>
</html>