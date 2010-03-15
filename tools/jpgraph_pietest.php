<?php

$sys_path_to_jpgraph='/usr/share/jpgraph';

require_once($sys_path_to_jpgraph.'/jpgraph.php');
require_once($sys_path_to_jpgraph.'/jpgraph_pie.php');
require_once($sys_path_to_jpgraph.'/jpgraph_pie3d.php');

function util_unconvert_htmlspecialchars($string) {
        if (strlen($string) < 1) {
                return '';
        } else {
                //$trans = get_html_translation_table(HTMLENTITIES, ENT_QUOTES);
                $trans = get_html_translation_table(HTML_ENTITIES);
                $trans = array_flip ($trans);
                $str = strtr ($string, $trans);
                return $str;
        }
}

function report_pie_arr($labels, $vals, $format=1) {
        global $pie_labels,$pie_vals;
        //first get sum of all values
        for ($i=0; $i<count($vals); $i++) {
                $total += $vals[$i];
        }

        //now prune out vals where < 2%
	$rem=0;
	$pie_labels='';
	$pie_vals='';
        for ($i=0; $i<count($vals); $i++) {
                if (($vals[$i]/$total) < .02) {
                        $rem += $vals[$i];
                } else {
                        $pie_labels[]=utf8_decode(util_unconvert_htmlspecialchars($labels[$i]))." (". number_format($vals[$i],$format) .") ".number_format($vals[$i]/$total*100,1)."%%";
                        //$pie_vals[]=number_format($vals[$i],1);
                        $pie_vals[]=$vals[$i];
                }
        }
        if ($rem > 0) {
                $pie_labels[]=_('Other')." (". number_format($rem,$format) .") ";
                //$pie_vals[]=number_format($rem,1);
                $pie_vals[]=$rem;
        }

}



$vals = array(2236,1029,687,623,577,466,302,221,205,188,1009);
$labels=array("Jean","Paul","Pierre","Philippe","Aimé","Amédé","Noémi","Noël","Philibert","Alphonse","Autres");

// Create the graph. These two calls are always required
$graph  = new PieGraph(640, 480,"auto");
//$graph->SetMargin(50,10,35,50);
setlocale(LC_TIME, "fr_FR.UTF-8");
//setlocale(LC_TIME, "C");
setlocale(LC_ALL, "fr_FR.UTF-8");
$start=strtotime("12/28/2002");
$end=strtotime("6/30/2004");

//$graph->title->Set(_("Commits By User")." (".date('m/d/Y',$start) ."-". date('m/d/Y',$end) .")");
$graph->title->Set(utf8_decode(_("Commits By User")." (".strftime('%x',$start) ." - ". strftime('%x',$end) .")"));
$graph->subtitle->Set(fusionforge_get_config ('forge_name'));

// Create the tracker open plot
////report_pie_arr(util_result_column_to_array($res,0), util_result_column_to_array($res,1));
//$pie_vals=$vals;
//$pie_labels=$labels;
report_pie_arr($labels, $vals,0);

//print_r($pie_vals);
//print_r($pie_labels);

$p1  = new PiePlot3D($pie_vals);
$p1->ExplodeSlice (0);
$p1->SetLegends($pie_labels);
$graph->Add( $p1);

// Display the graph
$graph->Stroke();

?>
