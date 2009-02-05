<?php

require_once('plugins/report/include/libs/jpgraph/jpgraph.php');
require_once('plugins/report/include/libs/jpgraph/jpgraph_radar.php');
require_once('plugins/report/include/libs/jpgraph/jpgraph_line.php');

class KiviatGraph {
	
	var $graph;
	
	function KiviatGraph($titles){		
		$this->graph = new RadarGraph(775,400,"auto");
        $this->graph->SetTitles($titles);
        $this->graph->img->SetAntiAliasing();
        $this->graph->HideTickMarks();
        $this->graph->axis->HideLabels();
        $this->graph->HideTickMarks();
        
        $this->graph->legend->SetLayout(LEGEND_HOR);
        $this->graph->legend->SetPos(0.5,.98,"center","bottom");
	}
	
    function addRadarPlot($data, $fillColor, $color, $legend){
        $radarPlot = new RadarPlot($data);
        $radarPlot->SetFillColor($fillColor);
        $radarPlot->SetColor($color);
        $radarPlot->setLegend($legend);
        $radarPlot->mark->SetFillColor($color);
        $radarPlot->mark->SetColor($color);
        $radarPlot->mark->SetSize(3);
        $radarPlot->mark->SetType(MARK_FILLEDCIRCLE);
        $this->graph->Add($radarPlot);
    }
    
    function printImage(){
    	$this->graph->Stroke();
    }
	
}

?>