<?php
namespace TukosLib\Objects\Physio\PersoTrack\Treatments;

use TukosLib\TukosFramework as Tfk;

trait QSMChart {
    
    function qsmChartDescription(){
        $tr = $this->tr;
        $seriesDescription = function($col, $plot) use ($tr) {
            return ['value' => ['y' => $col/*, 'text' => 'day'*/, 'tooltip' => $col.'Tooltip'], 'options' => ['plot' => $plot, 'label' => $tr($col), 'legend' => $tr($col)]];
        };
        return [
            'type' => 'Chart',
            'atts' => ['edit' => [
                'title' => $tr('qsmchart'), 'idProperty' => 'day', 'kwArgs' => ['sort' => [['attribute' => 'dayoftreatment', 'descending' => false]]], 'style' => ['width' => Tfk::$registry->isMobile ? 'auto' : 'auto'], 'chartHeight' => '300px',
                'daytype' => 'dateofday',
                'chartAtts' => ['name' => 'qsmchart', 'type' => ['idp' => 'day', 'defaultidptype' => 'dateofday', 'idptypes' => [['id' => 'dayoftreatment', 'name' => $tr('dayoftreatment')], ['id' => 'dateofday', 'name' => $tr('dateofday')]], 
                    'cols' => ['painduring' => ['plot' => 'cluster', 'tCol' => $tr('painduring')], 'painafter' => ['plot' => 'cluster', 'tCol' => $tr('painduring')], 'painnextday' => ['plot' => 'cluster', 'tCol' => $tr('painnextday')],
                        'duration' => ['plot' => 'Lines', 'tCol' => $tr('duration'), 'legendUnit' => ' (mn)'], 'distance' => ['plot' => 'Lines', 'tCol' => $tr('distance'), 'legendUnit' => ' (km)', 'tooltipUnit' => ' km'], 
                        'elevationgain' =>  ['plot' => 'Lines', 'tCol' => $tr('elevationgain'), 'legendUnit' => ' (dam)', 'tooltipUnit' => ' m', 'scalingFactor' => 10], 'intensity' =>  ['plot' => 'Lines', 'tCol' => $tr('intensity')], 
                        'mechload' =>  ['plot' => 'Lines', 'tCol' => $tr('Tukos_Mechanical_Load'), 'scalingFactor' => 10.0]]]],
                'axes' => [
                    'x' => ['title' => $tr('dateofday'), 'titleOrientation' => 'away', 'titleGap' => 5, 'labelCol' => 'day', 'majorTicks' => true, 'majorTickStep' => 1, 'minorTicks' => false, 'titleFont' => 'normal normal normal 11pt Arial'],
                    'y1' => ['title' => $tr('pain'), 'vertical' => true, 'min' => 0, 'max' => 4, 'titleFont' => 'normal normal normal 11pt Arial'],
                    'y2' => ['title' => $tr('load'), 'vertical' => true, 'leftBottom' => false, /*'min' => 0, 'max' => 10, */'titleFont' => 'normal normal normal 11pt Arial', 'majorLabels' => false, 'minorLabels' => false],
                ],
                'plots' => [
                    'lines' => ['type' => 'Lines', 'hAxis' => 'x', 'vAxis' => 'y2', 'lines' => true, 'markers' => true, 'tension' => 'X', 'shadow' => ['dx' => 1, 'dy' => 1, 'width' => 2]],
                    'cluster' => ['type' => 'ClusteredColumns', 'vAxis' => 'y1', 'gap' => 3, 'styleFunc' => <<< EOT
(function(item){
    var colors = {0: 'blank', 1: 'lightgreen', 2: 'orange', 3: 'red', 4: 'red'};
    return  {fill: colors[item.y]};
})
EOT
                    ],
                    'day' => ['type' => 'Indicator', 'hAxis' => 'x', 'vAxis' => 'y2', 'stroke' => null, 'outline' => null, 'fill' => null, 'labels' => false, 'lineStroke' => ['color' => 'red', 'style' => 'shortDash', 'width' => 2]]
                ],
                'legend' => ['type' => 'SelectableLegend', 'options' => []],
                'series' => [
                    'painduring' => $seriesDescription('painduring', 'cluster'),
                    'painafter' => $seriesDescription('painafter', 'cluster'),
                    'painnextday' => $seriesDescription('painnextday', 'cluster'),
                    'duration' => $seriesDescription('duration', 'lines'),
                    'distance' => $seriesDescription('distance', 'lines'),
                    'elevationgain' => $seriesDescription('elevationgain', 'lines'),
                    'intensity' => $seriesDescription('intensity', 'lines'),
                    'mechload' => $seriesDescription('mechload', 'lines'),
                ],
                'tooltip' => true,
                'mouseZoomAndPan' => true,
                'onWatchLocalAction' => [
                    //'daytype' => ['qsmchart' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => '']]],
                    'colsToExclude' => ['qsmchart' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => "sWidget.set('value', sWidget.get('value')); return true;"]]],
                    //'hidden' => ['qsmchart' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => '']]]
                ],
                'customizableAtts' => [$tr('daytype') => ['att' => 'daytype', 'type' => 'StoreSelect', 'name' => $tr('daytype'), 'storeArgs' => ['data' => [['id' => 'dayoftreatment', 'name' => $tr('dayoftreatment')], ['id' =>  'dateofday', 'name' =>  $tr('dateofday')]]]]]
            ]]
        ];
    }
    function cellChartChangeLocalAction(){
        return <<<EOT
if (tWidget.column){
    var form = tWidget.column.grid.form;
    form.QSMChart.setChartValue(form, 'qsmchart');
}
return true;
EOT
        ;
    }
    protected function chartLocalAction(){
        return <<<EOT
var form = sWidget.form;
form.loadChartUtils.setChartValue(form, 'qsmchart');
return true;
EOT
        ;
    }
    protected function dateChangeChartLocalAction(){
        return <<<EOT
      tWidget.plots.day.values = dutils.difference(tWidget.form.valueOf('fromdate'), newValue, 'day') + 1;
	tWidget.chart.addPlot('day', tWidget.plots.day);
	try{
        tWidget.chart.render();
    }catch(err){
        console.log('Error rendering chart in localChartAction for widget: ' + tWidget.widgetName);
    }
	return true;
EOT;
    }
}
?>
