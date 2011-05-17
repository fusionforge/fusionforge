<?php
/**
 * FusionForge Survey HTML Facility
 * General Graph showing module for Survey
 *
 * Copyright 2010 (c) FusionForge Team
 * http://fusionforge.org/
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/*
 * This script produces PNG image which shows graph of SCM metrics
 */

/**
 * Expecting data
 *  @ legend[] - array of legends
 *  @ value[]  - array of values
 *  @ type - grap type. We support pie and vertical bar graph (pie, vbar)
 *  @ width
 *  @ hight 
 */ 

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';

// Check if we have jpgraph
if (!file_exists(forge_get_config('jpgraph_path').'/jpgraph.php')) {
    //# TODO: Need to show the message as a image file
    exit_error(_('Package JPGraph not installed'),'surveys');
}

// Read jPGraph libraries.
require_once forge_get_config('jpgraph_path').'/jpgraph.php';
require_once forge_get_config('jpgraph_path').'/jpgraph_line.php';
require_once forge_get_config('jpgraph_path').'/jpgraph_bar.php';
require_once forge_get_config('jpgraph_path').'/jpgraph_pie.php';
require_once forge_get_config('jpgraph_path').'/jpgraph_pie3d.php';

$type = getStringFromRequest('type');
$legend = getStringFromRequest('legend');
$value = getStringFromRequest('value');

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
