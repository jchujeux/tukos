<?php
namespace TukosLib\Objects\Physio\WoundTrack\GameTracks;
use TukosLib\Utils\Widgets;
use TukosLib\Utils\Utilities as Utl;

trait TrendChartView {

    public function trendChartDescription($chartId, $title){
        $tr = $this->tr;
        $kpiFunctions = [/*'SUM', 'EXPAVG', 'DAILYAVG', 'AVG', 'MIN', 'MAX', 'LAST', 'SESSION'*/];
        $kpiParameters = ['recordtype', 'recorddate', 'globalsensation', 'environment', 'recovery', 'duration', 'distance', 'elevationgain', 'perceivedload', 'perceivedintensity', 'perceivedstress', 'mentaldifficulty'];
        return ['type' => 'dynamicChart', 'atts' => ['edit' => [
            'title' => $title, 
            'style' => ['width' => 'auto'],
            'chartHeight' => '300px',
            'showTable' => 'no',
            'tableAtts' => ['dynamicColumns' => true],
            'legend' => ['type' => 'SelectableLegend', 'options' => []],
            'tooltip' => true,
            'mouseZoomAndPan' => true,
            'onWatchLocalAction' => [
                'hidden' => [$chartId => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->trendChartChangeAction($chartId, 'hidden')]]],
                'axesToInclude' => [$chartId => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->trendChartChangeAction($chartId, 'axes')]]],
                'daytype' => [$chartId => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->trendChartChangeAction($chartId, 'daytype')]]],
                'plotsToInclude' => [$chartId => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->trendChartChangeAction($chartId, 'plots')]]],
                //'gridFilter' => [$chartId => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->trendChartChangeAction($chartId, 'gridFilter')]]],
                'kpisToInclude' => [$chartId => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->trendChartChangeAction($chartId, 'kpis')]]],
            ],
            'customizableAtts' => [
                'axes' => Utl::array_merge_recursive_replace(Widgets::simpleDgrid(Widgets::complete(['label' => $this->tr('Axes'), 'style' => ['width' => '800px'], 'storeArgs' => ['idProperty' => 'idg'], 
                    'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $this->tr('help'), 'name' => 'GameTracksTrendChartAxesTukosTooltip', 'object' => 'physiogametracks']],
                    'colsDescription' => [
                        'rowId' => ['field' => 'rowId', 'label' => 'id', 'width' => 40, 'className' => 'dgrid-header-col'],
                        'name' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('Name'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 60]]), false),
                        'title' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('Title')], 'storeedit' => ['width' => 100]]), false),
                        'titleOrientation' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => Utl::idsNamesStore(['axis', 'away'], $tr)], 'label' => $tr('titleorientation')], 'storeedit' => ['width' => 80]]), false),
                        'titleGap' => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $this->tr('Titlegap'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 60]]), false),
                        'vertical' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('vertical')], ['id' => false, 'name' => $tr('horizontal')]]], 'label' => $tr('axisorientation'),
                            'onChangeLocalAction' => ['titleOrientation' => ['value' => "return sWidget.valueOf('leftBottom') == 1 && !newValue ? 'away' : 'axis';"]]],  'storeedit' => ['width' => 80]]), false),
                        'leftBottom' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('leftbottom')], ['id' => false, 'name' => $tr('Righttop')]]], 'label' => $tr('Position'),
                            'onChangeLocalAction' => ['titleOrientation' => ['value' => "return newValue == 1 && !sWidget.valueOf('vertical') ? 'away' : 'axis';"]]]]), false),
                        'majorTicks' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 'label' => $tr('Majorticks')], 'storeedit' => ['width' => 60]]), false),
                        'majorTickStep' => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $this->tr('MajorTickStep'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 60]]), false),
                        'minorTicks' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 'label' => $tr('Minorticks')], 'storeedit' => ['width' => 60]]), false),
                        'tickslabel' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => Utl::idsNamesStore(['day', 'dateofday'], $tr)], 'label' => $tr('tickslabel')], 'storeedit' => ['width' => 80]]), false),
                        'dateoforigin' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => Utl::idsNamesStore(['woundstartdate', 'treatmentstartdate', 'firstrecorddate'], $tr)], 'label' => $tr('dateoforigin')], 'storeedit' => ['width' => 80]]), false),
                        'min' => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $this->tr('axisMin'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 60]]), false),
                        'max' => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $this->tr('axisMax'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 60]]), false),
                        'adjustmax' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 'label' => $tr('Adjustmax')], 'storeedit' => ['width' => 80]]), false),              
                ]])), ['att' => 'axesToInclude', 'type' => 'SimpleDgridNoDnd', 'name' => $this->tr('AxesToInclude'), 'atts' => ['initialRowValue' => ['vertical' => true, 'minorTicks' => false], 
                    'columns' => ['titleOrientation' => ['hidden' => true], 'titleGap' => ['hidden' => true], 'majorTicks' => ['hidden' => true], 'minorTicks' => ['hidden' => true]]]]),
                //'daytype' => ['att' => 'daytype', 'type' => 'StoreSelect', 'name' => $tr('daytype'), 'storeArgs' => ['data' => [['id' => 'dayoftreatment', 'name' => $tr('dayoftreatment')], ['id' =>  'dateofday', 'name' =>  $tr('dateofday')]]]],
                'plots' => Utl::array_merge_recursive_replace(Widgets::simpleDgrid(Widgets::complete([/*'label' => $this->tr('Axes'), */'style' => ['width' => '800px'], 'storeArgs' => ['idProperty' => 'idg'],
                    'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $this->tr('help'), 'name' => 'GameTracksTrendChartDiagramsTukosTooltip', 'object' => 'physiogametracks']],
                    'colsDescription' => [
                        'rowId' => ['field' => 'rowId', 'label' => 'id', 'width' => 40, 'className' => 'dgrid-header-col'],
                        'name' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('Name'), 'style' => ['width' => '5em']], 'storeedit' => ['minWidth' => 100]]), false),
                        'type' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => Utl::idsNamesStore(['Curves', 'ClusteredColumns'], $tr)], 'label' => $tr('plottype')], 'storeedit' => ['minWidth' => 60]]), false),
                        'hAxis' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('hAxis'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 60]]), false),
                        'vAxis' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('vAxis'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 60]]), false),
                        'lines' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 'label' => $tr('ShowLines')], 'storeedit' => ['width' => 60]]), false),
                        'areas' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 'label' => $tr('ShowAreas')], 'storeedit' => ['width' => 60]]), false),
                        'markers' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 'label' => $tr('ShowMarkers')], 'storeedit' => ['width' => 60]]), false),
                        'tension' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => '', 'name' => $tr('Brokenline')], ['id' => 'X', 'name' => $tr('Curved')]]], 'label' => $tr('Linetype')], 'storeedit' => ['width' => 60]]), false),
                        'interpolate' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 'label' => $tr('Interpolate')], 'storeedit' => ['minWidth' => 60]]), false),
                        'gap' => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $this->tr('Barsgap'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 60]]), false),
                ]])), ['att' => 'plotsToInclude', 'type' => 'SimpleDgridNoDnd', 'name' => $this->tr('plotsToInclude'), 'atts' => ['initialRowValue' => ['lines' => true, 'markers' => true]]]),
                //'gridFilter' => ['att' => 'gridFilter', 'type' => 'TextBox', 'name' => $tr('gridFilter')],
                'kpis' => Utl::array_merge_recursive_replace(Widgets::simpleDgrid(Widgets::complete(['label' => $this->tr('seriesToInclude'), 'style' => ['width' => '800px'], 'storeArgs' => ['idProperty' => 'idg'], 
                    'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $this->tr('help'), 'name' => 'GameTracksTrendChartKpisTukosTooltip', 'object' => 'physiogametracks']],
                    'colsDescription' => [
                        'rowId' => ['field' => 'rowId', 'label' => 'id', 'width' => 40, 'className' => 'dgrid-header-col'],
                        'name' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('Label')], 'storeedit' => ['width' => 150]]), false),
                        /*'kpi' => Widgets::description(Widgets::storeComboBox([
                            'edit' => ['label' => $this->tr('kpiformula'), 'style' => ['maxWidth' => '25em', 'width' => '25em'], 'translations' => $kpiTranslations, 'storeArgs' => ['data' => $kpiOptionsStore]],
                            'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 300]]), false),*/
    
                        'kpi' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $this->tr('Kpiformula'), 'style' => ['width' => '15em']], 'storeedit' => ['width' => 200]]), false),
                        'plot' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('Plot'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 100]]), false),
                        'tooltipunit' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('Tooltipunit'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 100]]), false),
                        'scalingfactor' => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $this->tr('Scalingfactor'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 60]]), false),
                        'absentiszero' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 'label' => $tr('Absentiszero')], 'storeedit' => ['width' => 80]]), false),
                        //'kpiFilter' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $this->tr('kpifilter'), 'style' => ['width' => '15em']], 'storeedit' => ['width' => 200]]), false)
                ]])), ['att' => 'kpisToInclude', 'type' => 'SimpleDgridNoDnd', 'name' => $this->tr('seriesToInclude')]),
            ]
        ]]];
    }
    public function trendChartPreMergeCustomizationAction($response, $customMode){
    $editConfig =  $customMode === 'object'
        ? $this->user->getCustomView($this->objectName, 'edit', 'tab', ['editConfig'])
        : $this->model->getCombinedCustomization(['id' => Utl::getItem('id', $response['data']['value'])], 'edit', 'tab', ['editConfig']);
        if (!empty($editConfig)){
            $trendChartsPerRow = Utl::getItem('trendchartsperrow', $editConfig);
            if ($trendChartsPerRow){
                $response['dataLayout']['contents']['row1']['contents']['col2']['contents']['rowtrendcharts']['tableAtts']['cols'] = $trendChartsPerRow;
            }
            $trendCharts = Utl::getItem('trendcharts', $editConfig);
            if ($trendCharts){
                $trendCharts = json_decode($trendCharts, true);
                foreach ($trendCharts as $trendChart){
                    $name = $trendChart['name'];
                    $chartId = 'trendchart' . $trendChart['id'];
                    $response['widgetsDescription'][$chartId] = Widgets::description($this->trendChartDescription($chartId, $name));
                    $response['dataLayout']['contents']['row1']['contents']['col2']['contents']['rowtrendcharts']['widgets'][] = $chartId;
                }
                $response['widgetsDescription']['records']['atts']['onWatchLocalAction'] = ['collection' => ['records' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' => $this->recordsWatchAction()]]]];
                $response['widgetsDescription']['records']['atts']['afterActions'] = [
                    'createNewRow' => $this->recordsRowWatchAction(),
                    'updateRow' => $this->recordsRowWatchAction(),
                    'deleteRow' => $this->recordsRowWatchAction(),
                    'deleteRows' => $this->recordsRowWatchAction(),
                ];
            }
        }
        return $response;
    }
    public function trendChartChangeAction($chartId, $changedAtt){
        return <<<EOT
var form = sWidget.form;
form.resize();
if (!newValue || '$changedAtt' !== 'hidden'){
    form.trendChartUtils.setChartValue('$chartId');
}
return true;
EOT
        ;
    }
    public function recordsWatchAction(){
        return <<<EOT
const form = sWidget.form;
if (form.editConfig && form.editConfig.trendcharts){
    if (form.trendChartUtils){
        let trendCharts = JSON.parse(form.editConfig.trendcharts);
        for (const trendChart of trendCharts){
            form.trendChartUtils.setChartValue('trendchart' + trendChart.id);
        }
    }else{    
        require(["tukos/objects/physio/woundTrack/gametracks/TrendChart"], function(TrendChart){
            form.trendChartUtils = new TrendChart({form: form, grid: sWidget, dateCol: 'recorddate'});
            /*let trendCharts = JSON.parse(form.editConfig.trendcharts);
            dojo.ready(function(){
                form.markIfChanged = form.watchOnChange = false;
                    for (const trendChart of trendCharts){
                        form.trendChartUtils.setChartValue('trendchart' + trendChart.id);
                    }
                wutils.markAsUnchanged(form.getWidget('trendchart' + trendChart.id));
                form.markIfChanged = form.watchOnChange = true;
            });*/
        });
    }
}
return true;
EOT
        ;
    }
    public function recordsRowWatchAction(){
        return <<<EOT
const form = this.form;
if (form.editConfig && form.editConfig.trendcharts){
    let trendCharts = JSON.parse(form.editConfig.trendcharts);
    for (const trendChart of trendCharts){
        form.trendChartUtils.setChartValue('trendchart' + trendChart.id);
    }
}
return true;
EOT
        ;
    }
}
?>