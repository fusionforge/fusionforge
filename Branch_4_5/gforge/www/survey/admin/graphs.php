<?php
/**
  *
  * General Graph showing module for Survey
  *
  * This script produces PNG image which shows graph of SCM metrics
  *
  *
  */

/**
 * Expecting data
 *  @ legend[] - array of legends
 *  @ value[]  - array of values
 *  @ type - grap type. We support pie and vertical bar graph (pie, vbar)
 *  @ width
 *  @ hight 
 */ 

require_once('pre.php');

// Check if we have jpgraph
if (!file_exists($sys_path_to_jpgraph.'/jpgraph.php')) {
    //# TODO: Need to show the message as a image file
    exit_error('Error', 'Package JPGraph not installed');
}

// Read jPGraph libraries. Make sure the $sys_path_to_jpgraph is correct in local.inc
require_once($sys_path_to_jpgraph.'/jpgraph.php');
require_once($sys_path_to_jpgraph.'/jpgraph_line.php');
require_once($sys_path_to_jpgraph.'/jpgraph_bar.php');
require_once($sys_path_to_jpgraph.'/jpgraph_pie.php');
require_once($sys_path_to_jpgraph.'/jpgraph_pie3d.php');

if ($type=='pie') {
    ShowPie($legend, $value);
} else {
    ShowHBar($legend, $value); 
}

/**
 * Show 3D Pie graph
 */ 
function ShowPie(&$legend, &$value) {
  
    $graph = new PieGraph(330,200,"auto");
    $graph->SetFrame(false);
    //$graph->title->Set("A simple 3D Pie plot");
    //$graph->title->SetFont(FF_FONT1,FS_BOLD);
    
    $p1 = new PiePlot3D($value);
    $p1->ExplodeSlice(1);
    $p1->SetCenter(0.45);
    $p1->SetLegends($legend);
    $graph->legend->SetPos(0.01,0.01,'right','top');

    $graph->Add($p1);
    $graph->Stroke();
}


/**
 * Show Horizontal Bar graph
 */ 
function ShowHBar(&$legend, &$value) {
    
    $height=50+count($value)*18;
    $width=500;
    
    // Set the basic parameters of the graph
    $graph = new Graph($width,$height,'auto');
    
    $graph->SetScale("textlin");
    $top = 30;
    $bottom = 20;
    $left = 100;
    $right = 50;
    $graph->Set90AndMargin($left,$right,$top,$bottom);
    $graph->xaxis->SetTickLabels($legend);
    $graph->SetFrame(false);

    // Label align for X-axis
    $graph->xaxis->SetLabelAlign('right','center','right');
    
    // Label align for Y-axis
    $graph->yaxis->SetLabelAlign('center','bottom');
    
    // Create a bar pot
    $bplot = new BarPlot($value);
    $bplot->SetFillColor("orange");
    $bplot->SetWidth(0.5);
    // We want to display the value of each bar at the top
    $graph->yaxis->scale->SetGrace(10);
    $graph->yaxis->SetLabelAlign('center','bottom');
    $graph->yaxis->SetLabelFormat('%d');
	
    $bplot->value->Show();
    $bplot->value->SetFormat('%.d votes');
    // Setup color for gradient fill style
    $bplot->SetFillGradient("navy","lightsteelblue",GRAD_MIDVER);
 
    $graph->Add($bplot);
    
    $graph->Stroke();
}
?>
