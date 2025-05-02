<?php
	session_start();
	include("handlers/_generics.php");
	$o = new _init;
?>
<html>
<head>
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
		var myTable = $('#itemlist').DataTable({
			"keys": true,
			"scrollY":  "310px",
			"select":	'single',
			"pageLength": 50,
			"pagingType": "full_numbers",
			"bProcessing": true,
			"responsive": true,
			"sAjaxSource": "data/samplelist.php",
			"scroller": true,
			"order": [[1, "desc"]],
			<?php if($_GET['code'] != 'undefined') { ?>
				"initComplete": function() {
					this.api().page.jumpToData("<?php echo $_GET['code']; ?>",1);
				},
			<?php } ?>
			"aoColumns": [
			  { mData: 'id' } ,
			  { mData: 'sono' } ,
			  { mData: 'sodate' },
			  { mData: 'pname' },
			  { mData: 'age' },
			  { mData: 'gender' },
			  { mData: 'code' }, 
			  { mData: 'procedure' },
			  { mData: 'sample_type' },
              { mData: 'serialno' },
			  { mData: 'tstamp' },
              { mData: 'status' },
			  { mData: 'ostat' }
			],
			"aoColumnDefs": [
			    { "className": "dt-body-center", "targets": [1,2,4,5,6,8,9,10]},
				{ "className": "dt-body-left", "targets": [7,11]},
			    { "targets": [0,12], "visible": false }
            ]
		});

		$('#itemlist tbody').on('dblclick', 'tr', function () {
			var data = myTable.row(this).data();
			var msg ='';

			if(data['ostat'] == '2') { msg = "It appears this sample has already been marked as \"Rejected\"!"; }
			if(data['ostat'] == '4') { msg = "It appears that a result is already available for the sample."; }

			if(msg != '') {
				parent.sendErrorMessage(msg);
			} else {
				parent.writeResult(data['id'],data['code']);
			}

		});

	});
	
	function refreshList() {
		$('#itemlist').DataTable().ajax.url("data/samplelist.php").load();
	}

	function rejectSample() {

		var table = $("#itemlist").DataTable();		
		var lid; var stat;
	   	$.each(table.rows('.selected').data(), function() {
		    lid = this["id"]; stat = this["ostat"]; 
	   	});

		if(!lid) {
			parent.sendErrorMessage("Please select record from the given list!");
		} else {

			var msg ='';

			if(stat == '2') { msg = "Sample has already been marked as \"Rejected\"!"; }
			/* if(stat == '3') { msg = "Result is already available for this procedure."; } */
			if(stat == '4') { msg = "Result is already available for this procedure."; }

			if(msg != '') {
				parent.sendErrorMessage(msg);
			} else {

				$.post("src/sjerp.php", {
					mod: "retrieveSample",
					lid: lid,
					sid: Math.random() },
					function(data) {

						$("#phleb_pname").val(data[0]);
						$("#phleb_procedure").val(data[1]);
						$("#phleb_code").val(data[2]);
						$("#phleb_spectype").val(data[3]);
						$("#phleb_serialno").val(data[4]);
						$("#phleb_location").val(data[5]);
						$("#phleb_date").val(data[6]);
						$("#phleb_hr").val(data[7]);
						$("#phleb_min").val(data[8]);
						$("#phleb_by").val(data[9]);

						var dis = $("#sampleDetails").dialog({
							title: "Sample Rejection",
							width: 540,
							resizeable: false,
							modal: true,
							buttons: [
								{
									text: "Reject Sample",
									icons: { primary: "ui-icon-check" },
									click: function() {
								
										if(confirm("Are you sure you want to mark this sample as Rejected?") == true) {
										
											$.post("src/sjerp.php", { 
												mod: "rejectSample",
												lid: lid,
												reason: $("#phleb_remarks").val(),
												sid: Math.random() }, 
												function() {
													alert("Sample successfully marked as \"REJECTED\"!");
													dis.dialog("close");
													refreshList();
												}
											);
										}	
									}
										
								},
								{
									text: "Close",
									icons: { primary: "ui-icon-closethick" },
									click: function() { $(this).dialog("close"); }
								}
							]
						});
					},"json"
				);
			}
		}
	}

	function writeResult() {
		var table = $("#itemlist").DataTable();		
		var lid; 
		var stat; 
		var code; 
		var serialno;
	   	$.each(table.rows('.selected').data(), function() {
		    lid = this["id"];
		    stat = this['ostat'];
			code = this['code'];
			serialno = this['serialno'];
	   	});

		if(!lid) {
			parent.sendErrorMessage("- It appears you have not selected any orders from the given list yet...");
		} else {
			var msg ='';

			if(stat == '2') { msg = "It appears this sample has already been marked as \"Rejected\"!"; }
			if(stat == '4') { msg = "It appears that a result is already available for the sample."; }


			if(msg != '') {
				parent.sendErrorMessage(msg);
			// } else {
			// 	$.post("src/sjerp.php", { mod: "checkMultipleChem", code: code, serialno: serialno, sid: Math.random() }, function(data) {
			// 		var scount = parseInt(data[0]);
			// 		if(scount > 1) {

			// 			var dis = $("#message").dialog({ 
			// 				title: "System Message",
			// 				width: "480",
			// 				modal: true,
			// 				resizeable: false,
			// 				buttons: [
			// 					{
			// 						icons: { primary: "ui-icon-copy" },
			// 						text: "Consolidate Report",
			// 						click: function() { 
			// 							if(confirm("Are you sure you want to conslidate multiple chemistry tests into one result sheet?") == true) {
											
			// 							}	
			// 						}
			// 					},
			// 					{
			// 						icons: { primary: "ui-icon-pencil" },
			// 						text: "Make Indiviual Report for this test",
			// 						click: function() { 
										
			// 						}	

			// 					}
			// 				]
			// 			});
					} else {
						parent.writeResult(lid,code);
					}
				// },"json");
			//}
		}

	}

	function printBarcode() {
		var table = $("#itemlist").DataTable();		
		var lid; var stat;
	   	$.each(table.rows('.selected').data(), function() {
		    serialno = this["serialno"];

	   	});

		if(!serialno) {
			parent.sendErrorMessage("- It appears you have not selected any record yet for barcode printing...");
		} else {
			var msg ='';

			if(msg != '') {
				parent.sendErrorMessage(msg);
			} else {
				parent.printBarcode(serialno);
			}
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
					<button class="ui-button ui-widget ui-corner-all" onClick="writeResult();">
						<span class="ui-icon ui-icon-pencil"></span> Write or Update Result
					</button>
					<button class="ui-button ui-widget ui-corner-all" onClick="printBarcode();">
						<span class="ui-icon ui-icon-print"></span> Print Barcode
					</button>
					<button class="ui-button ui-widget ui-corner-all" onClick="refreshList();">
						<span class="ui-icon ui-icon-refresh"></span> Reload List
					</button>
				</td>
			</tr>
		</table>
		<table class="cell-border" id="itemlist" style="font-size:11px;">
			<thead>
				<tr>
					<th></th>
					<th width=5%>SO #</th>
					<th width=8%>SO DATE</th>
					<th width=15%>PATIENT NAME</th>
					<th width=5%>AGE</th>
					<th width=7%>GENDER</th>
					<th width=7%>CODE</th>
					<th>REQUESTED PROCEDURE</th>
					<th width=8%>TYPE</th>
					<th width=8%>SN#</th>
					<th width=10%>TIMESTAMP</th>
					<th width=8%>STATUS</th>
					<th></th>
				</tr>
			</thead>
		</table>

</div>
<div id="sampleDetails" style="display: none;">
	<form name="frmReject" id="frmReject">
		<table width=100% cellspacing=0 cellpadding=0><tr><td width=100% class=gridHead align=center>SAMPLE DETAILS</td></tr></table>
        <table width=100% cellpadding=0 cellspacing=0 style="border: 1px solid #cdcdcd; padding: 10px;">
			<tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Patient&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="phleb_pname" id="phleb_pname">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
			<tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Required Procedure&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="phleb_procedure" id="phleb_procedure">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Procedure Code&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="phleb_code" id="phleb_code">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Specimen Type&nbsp;:</td>
                <td align=left>
                    <select class="gridInput" style="width:100%;" name="phleb_spectype" id="phleb_spectype">
                        <?php
                            $iun = $o->dbquery("select id,sample_type from options_sampletype;");
                            while(list($aa,$ab) = $iun->fetch_array()) {
                                echo "<option value='$aa'>$ab</option>";
                            }
                        ?>
                    </select>
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Sample Serial No.&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="phleb_serialno" id="phleb_serialno">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Date Extracted&nbsp;:</td>
                <td align=left>
                    <input type="text" class="gridInput" style="width:100%;" name="phleb_date" id="phleb_date" value="<?php echo date('m/d/Y'); ?>">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Time Extracted&nbsp;:</td>
                <td align=left>
        
                    <?php
                        $o->timify("phleb",$w="");
                    ?>

                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Extracted By&nbsp;:</td>
                <td align=left>
                    <input type="text" class="inputSearch2" style="width:100%;padding-left:22px;" name="phleb_by" id="phleb_by">
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;">Phleb/Imaging Site&nbsp;:</td>
                <td align=left>
                    <select class="gridInput" style="width:100%;" name="phleb_location" id="phleb_location">
                        <?php
                            $iun = $o->dbquery("select id,location from lab_locations;");
                            while(list($aa,$ab) = $iun->fetch_array()) {
                                echo "<option value='$aa'>$ab</option>";
                            }
                        ?>
                    </select>
                </td>				
            </tr>
            <tr><td height=3></td></tr>
            <tr>
                <td align="left" width="35%" class="bareBold" style="padding-right: 15px;" valign=top>Reason of Rejection&nbsp;:</td>
                <td align=left>
                    <textarea name="phleb_remarks" id="phleb_remarks" style="width:100%;" rows=3></textarea>
                </td>				
            </tr>
        </table>
	</form>
</div>
<div id="singleResult" style="display: none;"></div>
<div id="message" name="message" style="display: none;">
<p style="margin-left: 20px; text-align: justify;" id="message">It appears that this Laboratory Request is accompanied with other Chemistry test that maybe consolidated into one Report Sheet. Select the appropriate button action below to continue.</span></p>
</div>
</body>
</html>