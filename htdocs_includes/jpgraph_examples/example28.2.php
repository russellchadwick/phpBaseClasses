<?php
	$GRAPH = array ('pie');
	include 'begin.php';
	$display->minimalHead ();

$data = array(40,60,21,33,12,33);

$graph = new PieGraph(150,150,"auto");
$graph->SetShadow();

$graph->title->Set("'pastel' Theme");
$graph->title->SetFont(FF_FONT1,FS_BOLD);

$p1 = new PiePlot($data);
$p1->SetTheme("pastel");
$p1->SetCenter(0.5,0.55);
$p1->value->Show(false);
$graph->Add($p1);
$graph->Stroke();

?>


