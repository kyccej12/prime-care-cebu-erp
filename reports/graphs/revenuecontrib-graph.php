<?php // content="text/plain; charset=utf-8"
	
	session_start();
	ini_set("display_errors","Off");
	ini_set("max_execution_time",0);
	ini_set("memory_limit",-1);
	include("../../handlers/_generics.php");
	$con = new _init;

	require_once ('../../lib/graph/jpgraph.php');
	require_once ('../../lib/graph/jpgraph_pie.php');
	require_once ('../../lib/graph/jpgraph_pie3d.php');


	function labelMe($aLabel) {
		if($aLabel > 0) { return '%.1f%%'; }
	} 

	$data = array();
	$lbl = array();
	$legend = array();

	$i = 0;
	$a = $con->dbquery("SELECT salescat AS description, SUM(amount) AS amt FROM (SELECT IFNULL(d.sales_category,'UNCLASSIFIED') AS salescat, b.amount FROM so_header a LEFT JOIN so_details b ON a.so_no = b.so_no LEFT JOIN services_master c ON b.code = c.code LEFT JOIN options_salescategory d ON c.sales_cat = d.sales_id WHERE a.so_date BETWEEN '".$con->formatDate($_GET['dtf'])."' AND '".$con->formatDate($_GET['dt2'])."' AND a.status = 'Finalized' AND paid = 'Y' AND a.terms = '0' UNION ALL SELECT IFNULL(d.sales_category,'UNCLASSIFIED') AS salescat, b.amount FROM soa_header a LEFT JOIN soa_details b ON a.soa_no = b.soa_no LEFT JOIN services_master c ON b.code = c.code LEFT JOIN options_salescategory d ON c.sales_cat = d.sales_id WHERE a.soa_date BETWEEN '".$con->formatDate($_GET['dtf'])."' AND '".$con->formatDate($_GET['dt2'])."' AND a.status = 'Finalized') a GROUP BY salescat ORDER BY salescat;");
	while($b = $a->fetch_array()) {
		$data[$i] = $b[1];
		$lbl[$i] = "$b[0] (".$con->convert2Short($b[1]).")";
		//$legend[$i] = number_format($b[1],2);
		$i++;
	}

	$graph = new PieGraph(500,320);
	$graph->SetShadow();
	$graph->title->SetFont(FF_FONT1,FS_BOLD);
	 
	$p1 = new PiePlot($data);
	$p1->SetSize(0.25);
	$p1->SetCenter(0.80,0.50);
	$p1->SetGuideLines(true,false);
	$p1->SetGuideLinesAdjust(1);
	$p1->ExplodeAll(7);
	$p1->SetLabels($lbl);
	$p1->SetLabelPos(1);
	//$p1->SetLegends($legend);
	 
	$graph->Add($p1);
	$graph->Stroke();
 

?>