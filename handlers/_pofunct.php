<?php
	
	require_once("_generics.php");	
	class myPO extends _init {
		
		function setHeaderControls($status,$po_no,$urights) {

			$headerControls = '';

			if($lock != 'Y') {
				switch($status) {
					case "Finalized":
						list($posted_by,$posted_on) = parent::getArray("select fullname as name, date_format(updated_on,'%m/%d/%Y %p') as date_posted from po_header a left join user_info b on a.updated_by=b.emp_id where a.po_no = '$po_no';");
						
						if($urights == "admin") {
							$headerControls = '
								<button type = "button" name = "setActive" class="ui-button ui-widget ui-corner-all" onClick="reopenPO();">
									<span class="ui-icon ui-icon-unlocked"></span> Set this Document to Active Status
								</button>
							';
						}

						$headerControls .= '
							<button type = "button" name = "setPrint" class="ui-button ui-widget ui-corner-all" onClick="javascript: printPO();">
								<span class="ui-icon ui-icon-print"></span> Print Purchase Order
							</button>
						';
					break;
					case "Cancelled":

						if($urights == "admin") {
							$headerControls .= '
								<button type = "button" name = "setRecycle" class="ui-button ui-widget ui-corner-all" onClick="javascript: reopenPO();">
									<span class="ui-icon ui-icon-document-b"></span> Recycle this Document
								</button>
							';	
						}	

					break;
					case "Active": default:

						$headerControls = '
								<button type = "button" class="ui-button ui-widget ui-corner-all" onClick="finalize();">
									<span class="ui-icon ui-icon-check"></span> Finalize Purchase Order
								</button>
								<button type = "button" class="ui-button ui-widget ui-corner-all" onClick="savePOHeader();">
									<span class="ui-icon ui-icon-disk"></span> Save Changes Made
								</button>
						';

						if($urights == "admin" && $dS != 1) {
							$headerControls .= '
								<button type = "button" name = "setRecycle" class="ui-button ui-widget ui-corner-all" onClick="javascript: cancelPO();">
									<span class="ui-icon ui-icon-cancel"></span> Cancel this Document
								</button>
							';		
						}

					break;
				}
			} else {
				$headerControls .= '
					<button type = "button" name = "setPrint" class="ui-button ui-widget ui-corner-all" onClick="javascript: printPO();">
						<span class="ui-icon ui-icon-print"></span> Print Purchase Order
					</button>
				';
			}
			echo $headerControls;
		}
		
		function setNavButtons($po_no,$bid) {	
			$nav = '';	
			list($fwd) = parent::getArray("select po_no from po_header where po_no > $po_no and branch = '$bid' limit 1;");
			list($prev) = parent::getArray("select po_no from po_header where po_no < $po_no and branch = '$bid' order by po_no desc limit 1;");
			list($last) = parent::getArray("select po_no from po_header where branch = '$bid' order by po_no desc limit 1;");
			list($first) = parent::getArray("select po_no from po_header where branch = '$bid' order by po_no asc limit 1;");
			if($prev)
				$nav = $nav . "<a href=# onclick=\"parent.viewPO('$prev');\"><img src='images/resultset_previous.png'  title='Previous Record' /></a>";
			if($fwd) 
				$nav = $nav . "<a href=# onclick=\"parent.viewPO('$fwd');\"><img src='images/resultset_next.png' 'title='Next Record' /></a>";
			echo "<a href=# onclick=\"parent.viewPO('$first');\"><img src='images/resultset_first.png' title='First Record' /><a>" . $nav . "<a href=# onclick=\"parent.viewPO('$last');\"><img src='images/resultset_last.png' title='Last Record' /></a>";
		}
		
		function updateHeaderAmt($po_no,$bid) {
			list($gross,$net,$discount) = parent::getArray("select sum(ROUND(qty*cost,2)) as gross, sum(ROUND(qty*(cost-discount),2)) as net, sum(ROUND(qty*discount,2)) as total_discount from po_details where po_no = '$po_no' and branch = '$bid';");
			parent::dbquery("update ignore po_header set amount = '0$gross', discount = '0$discount', net = '0$net' where po_no = '$po_no' and branch = '$bid';");
		}
		
	}



?>