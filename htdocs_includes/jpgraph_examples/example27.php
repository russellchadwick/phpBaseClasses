<?php
	$GRAPH = array ('pie', 'pie3d');
	include 'begin.php';
	$display->minimalHead ();

$data = array(40,60,21,33);

$graph = new PieGraph(300,200,"auto");
$graph->SetShadow();

$graph->title->Set("A simple Pie plot");
$graph->title->SetFont(FF_FONT1,FS_BOLD);

$p1 = new PiePlot3D($data);
$p1->SetSize(0.5);
$p1->SetCenter(0.45);
$p1->SetLegends($gDateLocale->GetShortMonth());

$graph->Add($p1);
$graph->Stroke();

?>


