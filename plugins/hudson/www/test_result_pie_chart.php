<?php
require_once('env.inc.php');
require_once('pre.php');
require_once('preplugins.php');

require_once(forge_get_config('jpgraph_path').'/jpgraph.php');
require_once(forge_get_config('jpgraph_path').'/jpgraph_pie.php');

$request =& HTTPRequest::instance();
$pass_count = $request->get('p');
$fail_count = $request->get('f');
$skip_count = $request->get('s');
$total_count = $pass_count + $fail_count + $skip_count;

// graph size
$graph = new PieGraph(250,150);

// graph title
$graph->title-> Set(_("Test Results"));

// graph legend
$pass_legend = vsprintf(_("Pass (%s)"),  array($pass_count));
$fail_legend = vsprintf(_("Fail (%s)"),  array($fail_count));
$skip_legend = vsprintf(_("Skip (%s)"),  array($skip_count));

$array_legend = array($pass_legend, $fail_legend);
$array_value = array($pass_count, $fail_count);
$array_color = array('blue', 'red');
if ($skip_count != 0) {
    $array_legend[] = $skip_legend;
    $array_value[] = $skip_count;
    $array_color[] = 'black';
}

// Init pie chart with graph values
$pp  = new PiePlot($array_value);

// pie chart legend
$pp->SetLegends($array_legend);

// pie chart color values
// Pass is blue and Failed is red (Skip is black)
$pp->SetSliceColors($array_color);

// pie chart position
// the pie chart is a little bit on the left (0.35) and at the bottom (0.60)
$pp->SetCenter(0.35, 0.60);

$graph->Add($pp);

// display graph
$graph->Stroke();

?>
