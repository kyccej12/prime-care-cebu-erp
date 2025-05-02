<?php	
	session_start();
	include("handlers/_generics.php");
	$mydb = new _init;
	
	$uid = $_SESSION['userid'];
	if(isset($_REQUEST['cso_no']) && $_REQUEST['cso_no'] != '') { 
		$res = $mydb->getArray("select *, lpad(cso_no,6,0) as csono, date_format(cso_date,'%m/%d/%Y') as d8, if(customer_code!=0,lpad(customer_code,6,'0'),'') as cid, if(`from`!='0000-00-00',date_format(`from`,'%m/%d/%Y'),'') as dfrom, if(`until`!='0000-00-00',date_format(`until`,'%m/%d/%Y'),'') as duntil, if(`po_date`!='0000-00-00',date_format(`po_date`,'%m/%d/%Y'),'') as pod8 from cso_header where cso_no = '$_REQUEST[cso_no]' and branch = '$_SESSION[branchid]';");
		$cSelected = "Y"; $terms = $res['terms']; $cso_no = $res['csono']; $status = $res['status']; $traceNo = $res['trace_no']; 
	} else {  
	
		$status = "Active"; $terms = 0; $traceNo = $mydb->generateRandomString();
	}

	function setSOClickers($status,$cso_no,$terms,$urights) {
		global $mydb;
	
		switch($status) {
			case "Finalized":
				list($posted_by,$posted_on) = $mydb->getArray("select fullname as name, date_format(updated_on,'%m/%d/%Y %p') as date_posted from cso_header a left join user_info b on a.updated_by = b.emp_id where a.cso_no='$cso_no';");
				if($urights == "admin") {
					$headerControls = '
						<button type = "button" name = "setActive" class="ui-button ui-widget ui-corner-all" onClick="reopen();">
							<span class="ui-icon ui-icon-unlocked"></span> Set this Document to Active Status
						</button>
					';
				}

				/* if($terms != 0) {
					
					$headerControls .= '
						<button type = "button" name = "setVerify" class="ui-button ui-widget ui-corner-all" onClick="verify();">
							<span class="ui-icon ui-icon-check"></span> Mark as Verified
						</button>
					';
				} */

				$headerControls .= '
					<button type = "button" name = "setPrint" class="ui-button ui-widget ui-corner-all" onClick="javascript: printCSO();">
						<span class="ui-icon ui-icon-print"></span> Print Corporate Sales Order
					</button>
					<button type = "button" name = "setPrintBarcode" class="ui-button ui-widget ui-corner-all" onClick="javascript: printBarcode();">
						<img src="images/icons/serials.png" width=15 height=15 align=absmiddle /></span> Print Barcode
					</button>
					<button type = "button" name = "setPrintBarcode" class="ui-button ui-widget ui-corner-all" onClick="javascript: printCheckList();">
						<img src="images/icons/customer-report-icon.png" width=15 height=15 align=absmiddle /></span> Print Patient Attendance Sheet
					</button>
				';
			break;

			case "Cancelled":
				if($urights == "admin") {
					$headerControls .= '
						<button type = "button" name = "setRecycle" class="ui-button ui-widget ui-corner-all" onClick="javascript: reuse();">
							<span class="ui-icon ui-icon-refresh"></span> Recycle this Document
						</button>
					';
				}
			break;
			case "Active": default:

				$headerControls = '
						<button type = "button" class="ui-button ui-widget ui-corner-all" onClick="finalize();">
							<span class="ui-icon ui-icon-check"></span> Finalize & Print Corporate Sales Order
						</button>
						<button type = "button" class="ui-button ui-widget ui-corner-all" onClick="saveHeader();">
							<span class="ui-icon ui-icon-disk"></span> Save Changes Made
						</button>

				';
				if($urights == "admin" && $cso_no != '') {
					$headerControls .= '
						<button type = "button" name = "setRecycle" class="ui-button ui-widget ui-corner-all" onClick="javascript: cancel();">
							<span class="ui-icon ui-icon-cancel"></span> Cancel this Document
						</button>
					';
					
				}
			break;
		}
	
		echo $headerControls;
	}
	
	function setSONavs($cso_no) {
		global $mydb;
		list($fwd) = $mydb->getArray("select cso_no from cso_header where cso_no > $cso_no and branch = '$_SESSION[branchid]' limit 1;");
		list($prev) = $mydb->getArray("select cso_no from cso_header where cso_no < $cso_no and branch = '$_SESSION[branchid]' order by cso_no desc limit 1;");
		list($last) = $mydb->getArray("select cso_no from cso_header where branch = '$_SESSION[branchid]' order by cso_no desc limit 1;");
		list($first) = $mydb->getArray("select cso_no from cso_header where branch = '$_SESSION[branchid]' order by cso_no asc limit 1;");
		if($prev)
			$nav = $nav . "<a href=# onclick=\"parent.viewSO('$prev');\"><img src='images/resultset_previous.png'  title='Previous Record' /></a>";
		if($fwd) 
			$nav = $nav . "<a href=# onclick=\"parent.viewSO('$fwd');\"><img src='images/resultset_next.png' 'title='Next Record' /></a>";
		echo "<a href=# onclick=\"parent.viewSO('$first');\"><img src='images/resultset_first.png' title='First Record' /><a>" . $nav . "<a href=# onclick=\"parent.viewSO('$last');\"><img src='images/resultset_last.png' title='Last Record' /></a>";
	}

		
?>
<!doctype html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Medgruppe Polyclinics & Diagnostic Center, Inc.</title>
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="ui-assets/datatables/css/jquery.dataTables.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
	<script language="javascript" src="js/cso.js?sid=<?php echo uniqid(); ?>"></script>
	<script>
	
		$(document).ready(function($) {

			$('#customer_code').autocomplete({
				source:'suggestContacts.php', 
				minLength:3,
				select: function(event,ui) {
					$("#customer_name").val(decodeURIComponent(ui.item.cname));
					$("#customer_address").val(decodeURIComponent(ui.item.addr));
					$("#terms").val(ui.item.terms);
				}
			});

			$('#company').autocomplete({
				source:'suggestCompany.php', 
				minLength:3,
				select: function(event,ui) {
					$("#location").val(ui.item.address);
				} 
			});	

			$('#details').dataTable({
				"ajax": {
					"url": "cso.datacontrol.php",
					"data": { trace_no: "<?php echo $traceNo; ?>", mod: "retrieve", sid: Math.random() },
					"method": "POST"	
				},
				"scrollY":  "160",
				"select":	'single',
				"pagingType": "full_numbers",
				"bProcessing": true,
				"searching": false,
				"paging": false,
				"info": false,
				
				"aoColumns": [
					{ mData: 'id' },
					{ mData: 'pid' },
					{ mData: 'pname' },
					{ mData: 'gender' },
					{ mData: 'birthdate' },
					{ mData: 'code' },
					{ mData: 'description' },
					{ mData: 'amount', render: $.fn.dataTable.render.number(',', '.', 2, '') },
				],
				"aoColumnDefs": [
					{ className: "dt-body-center", "targets": [1,3,4,5]},
					{ className: "dt-body-right", "targets": [7]},
					{ "targets": [0], "visible": false }
				]
			});
			
			<?php if($status == 'Finalized' || $status == 'Cancelled') {
				echo "$(\"#xform :input:not([name=setActive], [name=setPrint], [name=setPrintBarcode], [name=setRecycle], [name=setVerify])\").prop('disabled',true);";
			} else { ?>
				$("#cso_date").datepicker();
				$("#from").datepicker();
				$("#until").datepicker();
				$("#po_date").datepicker();
			<?php } ?>
		});

		$(document).on('keydown', 'input[pattern]', function(e){
            var input = $(this);
            var oldVal = input.val();
            var regex = new RegExp(input.attr('pattern'), 'g');

            setTimeout(function(){
                var newVal = input.val();
                if(!regex.test(newVal)){
                input.val(oldVal); 
                }
            }, 1);
        });

		function redrawDataTable() {
			$('#details').DataTable().ajax.url("cso.datacontrol.php?mod=retrieve&trace_no=<?php echo $traceNo; ?>").load();
		}

		function printQSlip(priorityno) {
			var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/qslip.php?priority="+priorityno+"&sid="+Math.random()+"'></iframe>";
			$("#queueingslip").html(txtHTML);
			$("#queueingslip").dialog({ title: "Queueing Slip", width: 400, height: 520, resizable: false, modal: true });
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
		
	</style>

</head>
<body leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
<div>
	<form name="xform" id="xform" onsubmit="return false;">
		<input type="hidden" name="cSelected" id="cSelected" value="<?php echo $cSelected; ?>">
		<input type=hidden name="trace_no" id="trace_no" value="<?php echo $traceNo; ?>">
		<table width=100% cellpadding=0 cellspacing=0 border=0 align=center>
			<tr>
				<td class="upper_menus" align=left>
					<?php setSOClickers($status,$cso_no,$terms,$_SESSION['utype']); ?>
				</td>
				<td width=30% align=right style='padding-right: 5px;'><?php if($cso_no != '') { setSONavs($cso_no); } ?></td>
			</tr>
			<tr><td height=2></td></tr>
		</table>

		<table border="0" cellpadding="0" cellspacing="1" width=100% class="td_content">
			<tr>
				<td width=60% valign=top>
					<table width=100% style="padding:0px 0px 0px 0px;">
						<tr><td height=2></td></tr>
						<tr>
							<td align="left" class="bareBold" style="padding-left: 35px;" valign=top>Billed To :</td>
							<td align=left>
								<table cellspacing=0 cellpadding=0 border=0 width=100%>
									<tr>
										<td width=25%><input type="text" id="customer_code" name="customer_code" value="<?php echo $res['cid']?>" class="inputSearch2" style="padding-left: 22px; width:98%;"></td>
										<td width=75% align=right colspan=2><input class="gridInput" type="text" name="customer_name" id="customer_name" autocomplete="off" value="<?php echo $res['customer_name']; ?>" style="width: 100%;" readonly></td>
									</tr>
									<tr>
										<td style="font-size: 9px; color: gray; padding-left: 5px;">Customer ID</td><td colspan=2 style="font-size: 9px; padding-left: 5px; color: gray;">Customer Name</td>
									</tr>
									<tr>
										<td width=100% colspan=2><input class="gridInput" type="text" id="customer_address" name="customer_address" value="<?php echo $res['customer_address']?>" style="width: 100%;" readonly></td>
									</tr>
									<tr>
										<td colspan=2 style="font-size: 9px; color: gray; padding-left: 5px;" colspan=2 >Billing Address</td>
									</tr>
								</table>
							</td>				
						</tr>
						<tr>
							<td align="left" class="bareBold" style="padding-left: 35px;">Company :</td>
							<td align=left>
								<input class="inputSearch2" type="text" name="company" id="company" autocomplete="off" value="<?php echo $res['company']; ?>" style="width: 100%; padding-left: 22px;">
							</td>
						</tr>
						<tr>
							<td align="left" class="bareBold" style="padding-left: 35px;">Mobile Site Address :</td>
							<td align=left>
								<input class="gridInput" type="text" name="location" id="location" autocomplete="off" value="<?php echo $res['location']; ?>" style="width: 100%;">
							</td>
						</tr>
						<tr>
							<td align="left" class="bareBold" style="padding-left: 35px;">Schedule Date :</td>
							<td align=left>
								<input class="gridInput" type="text" name="from" id="from" autocomplete="off" value="<?php echo $res['dfrom']; ?>" style="width: 120px;"> To <input class="gridInput" type="text" name="until" id="until" autocomplete="off" value="<?php echo $res['duntil']; ?>" style="width: 120px;">
							</td>
						</tr>
						
					</table>
				</td>
				<td valign=top>
					<table border="0" cellpadding="0" cellspacing="1" width=100%>
						<tr><td height=2></td></tr>
						<tr>
							<td align="left" width="50%" class="bareBold" style="padding-left: 35px;">Corporate Service Order No.&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:85%;" type=text name="cso_no" id="cso_no" value="<?php echo $cso_no; ?>" readonly>
							</td>				
						</tr>
						<tr>
							<td align="left" width="50%" class="bareBold" style="padding-left: 35px;">Transaction Date&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:85%;" type=text name="cso_date" id="cso_date" value="<?php if(!$res['d8']) { echo date('m/d/Y'); } else { echo $res['d8']; }?>">
							</td>				
						</tr>
						<tr>
							<td align="left" class="bareBold" style="padding-left: 35px;">PO No. :</td>
							<td align=left>
								<input class="gridInput" type="text" name="po_no" id="po_no" autocomplete="off" value="<?php echo $res['po_no']; ?>" style="width: 85%;"> 
							</td>
						</tr>
						<tr>
							<td align="left" class="bareBold" style="padding-left: 35px;">PO Date :</td>
							<td align=left>
								<input class="gridInput" type="text" name="po_date" id="po_date" autocomplete="off" value="<?php echo $res['pod8']; ?>" style="width: 85%;"> 
							</td>
						</tr>
						<tr>
							<td align="left" width="25%" class="bareBold" style="padding-left: 35px;">Credit Terms&nbsp;:</td>
							<td align=left>
								<select class="gridInput" style="width:85%;" name="terms" id="terms" >
									<?php
										$srQuery = $mydb->dbquery("select terms_id, description from options_terms");
										while($srRow = $srQuery->fetch_array()) {
											echo "<option value='$srRow[0]' ";
											if($res['terms'] == $srRow[0]) { echo "selected"; }
											echo ">$srRow[1]</option>";
										}
									?>
								</select>
							</td>				
						</tr>
						<tr>
							<td align="left" width="50%" class="bareBold" style="padding-left: 35px;">CSO Type&nbsp;:</td>
							<td align=left>
								<select class="gridInput" style="width:85%;" name="cso_type" id="cso_type" >
									<option value='Mobile' <?php if($res['cso_type'] == 'Mobile') { echo "selected"; } ?>>Mobile</option>
									<option value='Walkin' <?php if($res['cso_type'] == 'Walkin') { echo "selected"; } ?>>Walkin</option>
								</select>
							</td>				
						</tr>
						<tr>
							<td align="left" width="50%" class="bareBold" style="padding-left: 35px;">Company's Representative&nbsp;:</td>
							<td align=left>
								<input type="text" class="gridInput" style="width:85%;" name="contact_person" id="contact_person" value="<?php echo $res['contact_person']; ?>" >
							</td>				
						</tr>
						<tr>
							<td align="left" width="50%" class="bareBold" style="padding-left: 55px; font-style: italic;">Contact No.&nbsp;:</td>
							<td align=left>
								<input type="text" class="gridInput" style="width:85%;" name="contact_no" id="contact_no" value="<?php echo $res['contact_no']; ?>" >
							</td>				
						</tr>
						<tr>
							<td align="left" width="50%" class="bareBold" style="padding-left: 55px; font-style: italic;">Email Address&nbsp;:</td>
							<td align=left>
								<input type="text" class="gridInput" style="width:85%;" name="contact_email" id="contact_email" value="<?php echo $res['email_add']; ?>" >
							</td>				
						</tr>
									
					</table>
				</td>
			</tr>
		</table>

		<table id="details">
			<thead>
				<tr>
					<th></th>
					<th width=10%>PID</th>
					<th >PATIENT</th>
					<th width=8%>SEX</th>
					<th width=12%>BIRTHDATE</th>
					<th width=8%>CODE</th>
					<th width=20%>DESCRIPTION</th>
					<th width=10%>AMOUNT</th>
				</tr>
			</thead>
		</table>

		<table width=100% class="td_content">
			<tr>
				<td width=50%>
					Transaction Remarks: <br/>
					<textarea rows=4 type="text" id="remarks" style="width:100%;" onchange='javascript: saveSOHeader();'><?php echo $res['remarks']; ?></textarea>
				</td>
				<td align=right width=50% valign=top>
					Transaction Amount : &nbsp;&nbsp;<input style="width:150px;text-align:right;" type=text name="gross" id="gross" value="<?php echo number_format($res['amount'],2); ?>" readonly><br/>			
				</td>
			</tr>
			<tr>
				<td align=left colspan=2 style="padding-top: 15px;">
					<?php if($status == 'Active' || $status == '') { ?>
						<a href="#" class="topClickers" onClick="javascript:addItem();"><img src="images/icons/add-2.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Add Item</a>&nbsp;
						<a href="#" class="topClickers" onClick="javascript:updateItem();"><img src="images/icons/edit.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Update Selected Entry</a>&nbsp;
						<a href="#" class="topClickers" onClick="javascript:deleteItem();"><img src="images/icons/delete.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Remove Selected Entry</a>
					<?php } ?>
				</td>
			</tr>
		</table>	
	</form>
</div>
<div id="itemEntry" style="display: none; z-index: 100;">
	<form name="frmItemEntry" id="frmItemEntry">
		<input type="hidden" id="recordId" name="recordId">
		<table width="100%" cellspacing=2 cellpadding=0 >
			<tr>
				<td class="bareThin" align=left width=40%>Patient ID :</td>
				<td align=left>
					<input type="text" name="itemPid" id="itemPid" class="inputSearch2" style="width: 80%; padding-left: 22px;">
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Patient Name :</td>
				<td align=left>
					<input type="text" name="itemPatient" id="itemPatient" class="gridInput" style="width: 80%;" readonly>
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Description :</td>
				<td align=left>
					<input type="text" name="itemDescription" id="itemDescription" class="inputSearch2" style="width: 80%; padding-left: 22px;">
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Item Code :</td>
				<td align=left>
					<input type="text" name="itemCode" id="itemCode" class="gridInput" style="width: 80%;" readonly>
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Unit Price :</td>
				<td align=left>
					<input type="text" name="itemPrice" id="itemPrice" class="gridInput" style="width: 80%;" value='0.00' readonly>
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>With LOA :</td>
				<td align=left>
					<select name="itemLOA" id="itemLOA" class="gridInput" style="width: 80%;">
						<option value='N'>- No -</option>
						<option value='Y'>- Yes -</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>HMO Card No. :</td>
				<td align=left>
					<input type="text" name="itemHMO" id="itemHMO" class="gridInput" style="width: 80%;" readonly>
				</td>
			</tr>
		</table>
	</form>
</div>
<div id="discounter" style="display: none;">
	<table width="100%" cellpadding=2 cellspacing=2>
		<tr>
			<td class="spandix-l" width=35%>Discount Type: </td>
			<td>
				<select name="discountType" id="discountType" class="gridInput" style="width: 80%;" onchange="javascript: selectDiscount(this.value);">
					<option value=''>- Select Discount Type -</option>
					<option value='E'>Employee's Discount</option>
					<option value='D'>Employee's Dependent</option>
					<option value='R'>Employee's Friends & Relatives</option>
					<option value='B'>Top Management Discretionary</option>
					<option value='O'>Other Discounts</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>Discount Percentage:</td>
			<td>
				<input type="text" name="discountPercent" id="discountPercent" class="gridInput" style="width: 80%; font-size: 11px;" pattern="^\d*(\.\d{0,2})?$" value="0" readonly>
			</td>
		</tr>
	</table>
</div>
<div id="queueingslip" style="display: none;"></div>
</body>
</html>