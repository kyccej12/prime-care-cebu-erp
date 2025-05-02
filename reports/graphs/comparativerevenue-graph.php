<?php
	session_start();
	ini_set("max_execution_time",0);
	ini_set("memory_limit",-1);
	include("../../handlers/_generics.php");
	$con = new _init;
	
	$xdtf = date('Y-01-01');
	
	function separator1000_php($aVal) {
	    if($aVal!=0) { return 'P'.number_format($aVal); } else { return '0'; }
	}

	function barValueFormat($aLabel) {
	    if($aLabel != 0) { return 'P'.number_format($aLabel,2); }
	}

	function convertShort($n) {
		$n = (0+str_replace(",", "", $n));
		if (!is_numeric($n)) return false;
		
		if($n < 0) { $xn = $n * -1; } else { $xn = $n; }
		
		if ($xn > 1000000000000) $xn = round(($xn/1000000000000), 2).'T';
		elseif ($xn > 1000000000) $xn = round(($xn/1000000000), 2).'B';
		elseif ($xn > 1000000) $xn = round(($xn/1000000), 2).'M';
		elseif ($xn > 1000) $xn = round(($xn/1000), 2).'K';
		
		if($n < 0) {
			return '('.$xn.')';
		} else { return $xn; }

	}

	$data = array();
	$data2 = array();
	$avg = array();
	$lbl = array();

	for($i = 0; $i <= 11; $i++) {
		
		list($dtf,$dt2,$month) = $con->getArray("select date_add('$xdtf',INTERVAL $i MONTH),last_day(date_add('$xdtf',INTERVAL $i MONTH)), upper(date_format(date_add('$xdtf',INTERVAL $i MONTH),'%b'));");
		list($ly_dtf,$ly_dt2) = $con->getArray("select date_sub('$dtf',INTERVAL 1 YEAR),date_sub('$dt2',INTERVAL 1 YEAR);");
		
		list($amt) = $con->getArray("SELECT SUM(amount) AS amt FROM (SELECT IFNULL(d.sales_category,'UNCLASSIFIED') AS salescat, b.amount FROM so_header a LEFT JOIN so_details b ON a.so_no = b.so_no LEFT JOIN services_master c ON b.code = c.code LEFT JOIN options_salescategory d ON c.sales_cat = d.sales_id WHERE a.so_date BETWEEN '$dtf' AND '$dt2' AND a.status = 'Finalized' AND paid = 'Y' AND a.terms = '0' UNION ALL SELECT IFNULL(d.sales_category,'UNCLASSIFIED') AS salescat, b.amount FROM soa_header a LEFT JOIN soa_details b ON a.soa_no = b.soa_no LEFT JOIN services_master c ON b.code = c.code LEFT JOIN options_salescategory d ON c.sales_cat = d.sales_id WHERE a.soa_date BETWEEN '$dtf' AND '$dt2' AND a.status = 'Finalized') a;");
		list($amt2) = $con->getArray("SELECT SUM(amount) AS amt FROM (SELECT IFNULL(d.sales_category,'UNCLASSIFIED') AS salescat, b.amount FROM so_header a LEFT JOIN so_details b ON a.so_no = b.so_no LEFT JOIN services_master c ON b.code = c.code LEFT JOIN options_salescategory d ON c.sales_cat = d.sales_id WHERE a.so_date BETWEEN '$ly_dtf' AND '$ly_dt2' AND a.status = 'Finalized' AND paid = 'Y' AND a.terms = '0' UNION ALL SELECT IFNULL(d.sales_category,'UNCLASSIFIED') AS salescat, b.amount FROM soa_header a LEFT JOIN soa_details b ON a.soa_no = b.soa_no LEFT JOIN services_master c ON b.code = c.code LEFT JOIN options_salescategory d ON c.sales_cat = d.sales_id WHERE a.soa_date BETWEEN '$ly_dtf' AND '$ly_dt2' AND a.status = 'Finalized') a;");
		//echo "select sum(credit-debit) from acctg_gl a left join acctg_accounts b ON a.acct = b.acct_code where doc_date between '$ly_dtf' and '$ly_dtf' and b.acct_grp in ('9','10') AND a.branch = '$_SESSION[branchid]';";
	
		$data[$i] = $amt;
		$data2[$i] = $amt2;

		$lbl[$i] = $month;
		$amt = 0; $amt2 = 0;
	}
 
	require_once('../../lib/graph/jpgraph.php');
	require_once('../../lib/graph/jpgraph_bar.php');

	$graph = new Graph(800,250,'auto');
	$graph->SetScale("textlin");
	$theme_class=new UniversalTheme;
	$graph->SetTheme($theme_class);
	$graph->SetMargin(100,0,20,0);
	$graph->SetBox(false);
	$graph->ygrid->SetFill(false);
	$graph->xaxis->SetTickLabels($lbl);
	$graph->yaxis->HideLine(false);
	$graph->yaxis->HideTicks(false,false);
	$graph->yaxis->SetLabelFormatCallback('separator1000_php');
	
	$b1plot = new BarPlot($data);
	$b1plot->SetLegend("Current Period");
	$b2plot = new BarPlot($data2);
	$b2plot->SetLegend("Last Year");
	

	$b1plot->SetColor("white");
	$b1plot->SetFillColor("#11cccc");
	
	$b2plot->SetColor("white");
	$b2plot->SetFillColor("#1111cc");

	$gbplot = new GroupBarPlot(array($b1plot,$b2plot));
	$graph->Add($gbplot);
	
	/* $b1plot->value->Show();
	$b1plot->value->SetFont(FF_ARIAL,FS_NORMAL,7);
	$b1plot->value->SetColor("#4a4a4a");
	$b1plot->value->setFormatCallback('convert2Short');
	$b1plot->value->setAngle(90); */
	
	$b1plot->value->Show();
	$b1plot->value->SetFont(FF_ARIAL,FS_NORMAL,7);
	$b1plot->value->SetColor("#4a4a4a");
	$b1plot->value->setFormatCallback('convertShort');
	$b1plot->value->setAngle(90);

	$b2plot->value->Show();
	$b2plot->value->SetFont(FF_ARIAL,FS_NORMAL,7);
	$b2plot->value->SetColor("#4a4a4a");
	$b2plot->value->setFormatCallback('convertShort');
	$b2plot->value->setAngle(90);
	
	$graph->Stroke();

?>