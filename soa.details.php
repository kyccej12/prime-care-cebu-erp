<?php	
	session_start();
	ini_set("max_execution_time",0);
	ini_set("memory_limit",-1);
	include("handlers/_generics.php");
	$mydb = new _init;
	
	$uid = $_SESSION['userid'];
	if(isset($_REQUEST['soa_no']) && $_REQUEST['soa_no'] != '') { 
		$res = $mydb->getArray("select *, lpad(customer_code,6,'0') as cid, lpad(soa_no,6,0) as soano, date_format(soa_date,'%m/%d/%Y') as d8, if(po_date != '0000-00-00',date_format(po_date,'%m/%d/%Y'),'') as pd8 from soa_header where soa_no = '$_REQUEST[soa_no]' and branch = '$_SESSION[branchid]';");
		$cSelected = "Y"; $soa_no = $res['soano']; $status = $res['status']; $traceNo = $res['trace_no'];
	} else {  
		$status = "Active"; $traceNo = $mydb->generateRandomString();
	}

	list($pax) = $mydb->getArray("select count(*) from soa_details where soa_no = '$_REQUEST[soa_no]';");
	list($source) = $mydb->getArray("select source from soa_details where soa_no = '$_REQUEST[soa_no]';");


	function setSOClickers($status,$soa_no,$uid,$dS,$urights) {
		global $mydb;
	
		switch($status) {
			case "Finalized":
				list($posted_by,$posted_on) = $mydb->getArray("select fullname as name, date_format(updated_on,'%m/%d/%Y %p') as date_posted from soa_header a left join user_info b on a.updated_by = b.emp_id where a.soa_no='$soa_no';");
				if($urights == "admin") {
					$headerControls = "<a href=\"#\" class=\"topClickers\" onclick=\"javascript: reopen();\"><img src='images/icons/edit.png' align=absmiddle width=16 height=16 />&nbsp;Set this Document to Active Status</a>&nbsp;";
				}
				$headerControls = $headerControls . "&nbsp;<a href=\"#\" class=\"topClickers\" onClick=\"javascript: export2Excel();\"><img src=\"images/icons/excel.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Export SOA to Excel</a>&nbsp;&nbsp;<a href=\"#\" class=\"topClickers\" onClick=\"javascript: print();\"><img src=\"images/icons/print.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Print Statement of Account</a>&nbsp;";
			break;
			case "Cancelled":
				if($urights == "admin") {
					$headerControls = $headerControls . "&nbsp;<a href=\"#\" class=\"topClickers\" onclick=\"javascript:reuse();\" style=\"padding: 5px;\"><img src=\"images/icons/refresh.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Recycle this Document</a>&nbsp;";	
				}
			break;
			case "Active": default:
				$headerControls = "<a href=\"#\" class=\"topClickers\" onClick=\"javascript:finalize();\"><img src=\"images/icons/ok.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Finalize SOA</a>&nbsp;&nbsp;<a href=\"#\" class=\"topClickers\" onclick=\"javascript:saveHeader();\"><img src=\"images/save.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Save Changes</a>&nbsp;&nbsp;&nbsp;<a href=\"#\" class=\"topClickers\" onclick=\"javascript:browseSO();\"><img src=\"images/icons/options.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Browse Unbilled S.O</a>&nbsp;<a href=\"#\" class=\"topClickers\" onclick=\"javascript:browseCSO();\"><img src=\"images/icons/options.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Browse Unbilled C.S.O</a>&nbsp;&nbsp;<a href=\"#\" class=\"topClickers\" onclick=\"javascript:browsePharmaSO();\"><img src=\"images/icons/options.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Browse Unbilled Pharmacy S.O</a>&nbsp;";
				if($urights == "admin" && $dS != 1) {
					$headerControls = $headerControls . "&nbsp;<a href=\"#\" class=\"topClickers\" onclick=\"javascript:cancelSOA();\"><img src=\"images/icons/cancel.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Cancel this Document</a>";
				}
			break;
		}
	
		echo $headerControls;
	}
	
	function setSONavs($soa_no) {
		global $mydb;
		list($fwd) = $mydb->getArray("select soa_no from soa_header where soa_no > $soa_no and branch = '$_SESSION[branchid]' limit 1;");
		list($prev) = $mydb->getArray("select soa_no from soa_header where soa_no < $soa_no and branch = '$_SESSION[branchid]' order by soa_no desc limit 1;");
		list($last) = $mydb->getArray("select soa_no from soa_header where branch = '$_SESSION[branchid]' order by soa_no desc limit 1;");
		list($first) = $mydb->getArray("select soa_no from soa_header where branch = '$_SESSION[branchid]' order by soa_no asc limit 1;");
		if($prev)
			$nav = $nav . "<a href=# onclick=\"parent.viewSOA('$prev');\"><img src='images/resultset_previous.png'  title='Previous Record' /></a>";
		if($fwd) 
			$nav = $nav . "<a href=# onclick=\"parent.viewSOA('$fwd');\"><img src='images/resultset_next.png' 'title='Next Record' /></a>";
		echo "<a href=# onclick=\"parent.viewSOA('$first');\"><img src='images/resultset_first.png' title='First Record' /><a>" . $nav . "<a href=# onclick=\"parent.viewSOA('$last');\"><img src='images/resultset_last.png' title='Last Record' /></a>";
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
	<script language="javascript" src="js/soa.js?sid=<?php echo uniqid(); ?>"></script>
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

			$('#referred_by').autocomplete({
				source:'suggestCompany.php', 
				minLength:3,
				/* select: function(event,ui) {
					$("#e_street").val(ui.item.address);
					
				} */
			});	

			$('#details').dataTable({
				"ajax": {
					"url": "soa.datacontrol.php",
					"data": { trace_no: "<?php echo $traceNo; ?>", mod: "retrieve", sid: Math.random() },
					"method": "POST"	
				},
				"scrollY":  "240",
				"select":	'single',
				"bProcessing": true,
				"searching": false,
				"paging": false,
				"info": false,
				"aoColumns": [
					{ mData: 'lid' },
					{ mData: 'sono' },
					{ mData: 'sdate' },
					{ mData: 'pname' },
					{ mData: 'code' },
					{ mData: 'description' },
					{ mData: 'unit' },
					{ mData: 'qty' },
					{ mData: 'price' },
					{ mData: 'amount' },
					{ mData: 'source' },
					{ mData: 'pid' },
					{ mData: 'xso' }
				],
				"aoColumnDefs": [
					{ "className": "dt-body-center", "targets": [1,2,4,6,7,8,9]},
					{ "targets": [0,10,11,12], "visible": false }
				]
			});
			
			<?php if($status == 'Finalized' || $status == 'Cancelled') {
				echo "$(\"#xform :input\").prop('disabled',true);";
			} else { ?>
				$("#soa_date").datepicker();
				$("#po_date").datepicker();
				$("#itemBirthdate").datepicker();
				$("#itemDate").datepicker();
			
			<?php } ?>
		});

		function redrawDataTable() {
			$('#details').DataTable().ajax.url("soa.datacontrol.php?mod=retrieve&trace_no=<?php echo $traceNo; ?>").load();
		}

		function export2Excel() {
			var soa_no = $("#soa_no").val();
			window.open("export/soa.php?soa_no="+soa_no+"&sid="+Math.random()+"","Statement of Account","location=1,status=1,scrollbars=1,width=640,height=720");
		}
	</script>

	<style>
		.dataTables_wrapper {
			display: inline-block;
			font-size: 11px; 
			width: 100%; 
		}
		
		table.dataTable tr.even { background-color: #f5f5f5;  }
		table.dataTable tr.odd { background-color: white; }
		.dataTables_filter input { width: 250px; }
	</style>

</head>
<body leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
<div>
	<form name="xform" id="xform">
		<input type="hidden" name="cSelected" id="cSelected" value="<?php echo $cSelected; ?>">
		<input type=hidden name="trace_no" id="trace_no" value="<?php echo $traceNo; ?>">
		<input type=hidden name="source_type" id="source_type" value="<?php echo $source; ?>">
		<table width=100% cellpadding=0 cellspacing=0 border=0 align=center>
			<tr>
				<td class="upper_menus" align=left>
					<?php setSOClickers($status,$soa_no,$uid,$dS,$_SESSION['utype']); ?>
				</td>
				<td width=30% align=right style='padding-right: 5px;'><?php if($soa_no != '') { setSONavs($soa_no); } ?></td>
			</tr>
			<tr><td height=2></td></tr>
		</table>

		<table border="0" cellpadding="0" cellspacing="1" width=100% class="td_content">
			<tr>
				<td width=60% valign=top>
					<table width=100% style="padding:0px 0px 0px 0px;">
						<tr><td height=2></td></tr>
						<tr>
							<td align="left" class="bareBold" style="padding-left: 35px;" valign=top>Bill To :</td>
							<td align=left>
								<table cellspacing=0 cellpadding=0 border=0 width=100%>
									<tr>
										<td width=25%><input type="text" id="customer_code" name="customer_code" value="<?php echo $res['cid']?>" class="inputSearch2" style="padding-left: 22px; width:98%;" onchange="javascript: saveHeader();"></td>
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
							<td align="left" width="25%" class="bareBold" style="padding-left: 35px;">Payment Terms&nbsp;:</td>
							<td align=left>
								<select class="gridInput" style="width:50%;" name="terms" id="terms" >
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
							<td align="left" class="bareBold" style="padding-left: 35px;">Referred By :</td>
							<td align=left>
								<input class="inputSearch2" type="text" name="referred_by" id="referred_by" autocomplete="off" value="<?php echo $res['referred_by']; ?>" style="width: 100%; padding-left: 22px;">
							</td>
						</tr>
						<tr>
							<td align="left" width="25%" class="bareBold" style="padding-left: 35px;">Revenue Center&nbsp;:</td>
							<td align=left>
								<select class="gridInput" style="width:50%;" name="revenue_center" id="revenue_center" >
									<option value='' <?php if($res['revenue_center'] == '') { echo "selected"; }?>>CENTER</option>
									<option value='081' <?php if($res['revenue_center'] == '081') { echo "selected"; }?>>AIRPORT</option>
									<option value='050' <?php if($res['revenue_center'] == '050') { echo "selected"; }?>>MOBILE</option>
									<option value='150' <?php if($res['revenue_center'] == '150') { echo "selected"; }?>>PPP (Province of Cebu)</option>
									<option value='190' <?php if($res['revenue_center'] == '190') { echo "selected"; }?>>CLINIC MANAGEMENT</option>
								</select>
							</td>				
						</tr>
					</table>
				</td>
				<td valign=top>
					<table border="0" cellpadding="0" cellspacing="1" width=100%>
						<tr><td height=2></td></tr>
						<tr>
							<td align="left" width="50%" class="bareBold" style="padding-left: 35px;">SOA No.&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:70%;" type=text name="soa_no" id="soa_no" value="<?php echo $soa_no; ?>" >
							</td>				
						</tr>
						<tr>
							<td align="left" width="50%" class="bareBold" style="padding-left: 35px;">Date&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:70%;" type=text name="soa_date" id="soa_date" value="<?php if(!$res['d8']) { echo date('m/d/Y'); } else { echo $res['d8']; }?>">
							</td>				
						</tr>
						<tr>
							<td align="left" width="50%" class="bareBold" style="padding-left: 35px;">PO No.&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:70%;" type=text name="po_no" id="po_no" value="<?php echo $res['po_no']; ?>" >
							</td>				
						</tr>
						<!-- <tr>
							<td align="left" width="50%" class="bareBold" style="padding-left: 35px;">PO Date&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:70%;" type=text name="po_date" id="po_date" value="<?php echo $res['pd8']; ?>" >
							</td>				
						</tr> -->
						<tr>
							<td align="left" width="50%" class="bareBold" style="padding-left: 35px;">Billing Statement No.&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:70%;" type=text name="stub_no" id="stub_no" value="<?php echo $res['stub_no']; ?>" >
							</td>				
						</tr>
						<tr>
							<td align="left" width="25%" class="bareBold" style="padding-left: 35px;">With VAT Presentation&nbsp;:</td>
							<td align=left>
								<select class="gridInput" style="width:50%;" name="vat" id="vat" >
								
									<option value='N' <?php if($res['vat'] == 'N') { echo "selected"; }?>>No</option>
									<option value='Y' <?php if($res['vat'] == 'Y') { echo "selected"; }?>>Yes</option>
								</select>
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
					<th width=8%>REF #</th>
					<th width=12%>DATE AVAILED</th>
					<th width=20%>PATIENT</th>
					<th width=8%>CODE</th>
					<th >PROCEDURE</th>
					<th width=8%>UNIT</th>
					<th width=8%>QTY</th>
					<th width=8%>PRICE</th>
					<th width=10%>AMT. DUE</th>
					<th></th>
					<th></th>
					<th></th>
				</tr>
			</thead>
		</table>

		<table width=100% class="td_content">
			<tr>
				<td width=50%>
					Transaction Remarks: <br/>
					<textarea rows=2 type="text" id="remarks" style="width:83%;" onchange='javascript: saveHeader();'><?php echo $res['remarks']; ?></textarea>
				</td>
				<td align=right width=50% valign=top>
					
					Transaction Total : &nbsp;&nbsp;<input style="width:200px;text-align:right;" type=text name="grandTotal" id="grandTotal" value="<?php echo number_format($res['amount'],2); ?>" readonly><br />
					No of Pax : &nbsp;&nbsp;<input style="width:200px;text-align:right;" type=text name="pax" id="pax" value="<?php echo $pax; ?>" readonly>		

				</td>
			</tr>
			<tr>
				<td align=left colspan=2 style="padding-top: 15px;">
					<?php if($status == 'Active' || $status == '') { ?>
						<a href="#" class="topClickers" onClick="javascript:addItem();"><img src="images/icons/add-2.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Add Item</a>&nbsp;&nbsp;
						<!--a href="#" class="topClickers" onClick="javascript:updateItem();"><img src="images/icons/discount-icon.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Apply Line Discount</a-->
						<a href="#" class="topClickers" onClick="javascript:deleteItem();"><img src="images/icons/delete.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Remove Selected Item</a>
					<?php } ?>
				</td>
			</tr>
		</table>	
	</form>
</div>
<div id="solist" style="display:none;"></div>
<div id="itemEntry" style="display: none; z-index: 100;">
	<form name="frmItemEntry" id="frmItemEntry">
		<input type="hidden" id="recordId" name="recordId">
		<table width="100%" cellspacing=2 cellpadding=0 >
			<tr>
				<td class="bareThin" align=left width=40%>Patient Name :</td>
				<td align=left>
					<input type="text" name="itemPname" id="itemPname" class="gridInput" style="width: 80%;">
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Date Availed :</td>
				<td align=left>
					<input type="text" name="itemAvailed" id="itemAvailed" class="gridInput" style="width: 80%;">
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Service Ref # :</td>
				<td align=left>
					<input type="text" name="itemReference" id="itemReference" class="gridInput" style="width: 80%;">
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Procedure Availed:</td>
				<td align=left>
					<input type="text" name="itemDescription" id="itemDescription" class="inputSearch2" style="width: 80%; padding-left: 22px;">
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Code :</td>
				<td align=left>
					<input type="text" name="itemCode" id="itemCode" class="gridInput" style="width: 80%;" disabled>
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Unit :</td>
				<td align=left>
					<input type="text" name="itemUnit" id="itemUnit" class="gridInput" style="width: 80%;" disabled>
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Quantity :</td>
				<td align=left>
					<input type="text" name="itemQty" id="itemQty" class="gridInput"style="width: 80%;" value=1 onchange="javascript: computeItemAmount(this.value);">
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Unit Price :</td>
				<td align=left>
					<input type="text" name="itemCost" id="itemCost" class="gridInput" style="width: 80%;" value='0.00' disabled>
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Amount :</td>
				<td align=left>
					<input type="text" name="itemAmount" id="itemAmount" class="gridInput" style="width: 80%;" value='0.00' disabled>
				</td>
			</tr>
		</table>
	</form>
</div>
</body>
</html>