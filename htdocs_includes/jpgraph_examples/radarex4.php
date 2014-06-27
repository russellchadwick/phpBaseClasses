<?php
	$GRAPH = array ('radar');
	include 'begin.php';
	$display->minimalHead ();
	
// Some data to plot
$data = array(55,80,26,31,95);
	
// Create the graph and the plot
$graph = new RadarGraph(250,200,"auto");

// Create the titles for the axis
$titles = $gDateLocale->GetShortMonth();
$graph->SetTitles($titles);

// Add grid lines
$graph->grid->Show();
$graph->grid->SetLineStyle('dotted');

$plot = new RadarPlot($data);
$plot->SetFillColor('lightblue');

// Add the plot and display the graph
$graph->Add($plot);
$graph->Stroke();
?>