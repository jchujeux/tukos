<?php
namespace TukosLib\Objects;
use TukosLib\Utils\Widgets;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;


trait ChartView {

    static  $dateFormulaesToTranslate = ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY', 'DAY', 'WEEK', 'MONTH'];

    public function functionLabel ($funcName, $dayOrWeekOrMonth){
        return $this->tr($funcName) . '(' . $this->tr($dayOrWeekOrMonth) . ', 1)';
    }
    public function ChartDescription($chartId, $chartInfo, $dateWidgetNames = ['firstrecorddate'], $namesToTranslate, $missingKpisIndex, $selectedDateWidgetName = null){
        $kpiFunctions = ['TOFIXED', 'JSONPARSE', 'VECTOR', 'XY', 'SUM', 'EXPAVG', 'EXPINTENSITY', 'DAILYAVG', 'AVG', 'MIN', 'MAX', 'FIRST', 'LAST', 'ITEM', 'DATE', 'TIMETOSECONDS'];
        $translations = array_merge(Utl::translations($kpiFunctions, $this->tr, 'uppercasenoaccent'), Utl::translations($namesToTranslate, $this->tr, 'lowercase'), Utl::translations(self::$dateFormulaesToTranslate, $this->tr, 'lowercase'));
        $customizableAtts = $chartInfo['chartType'] . 'ChartCustomizableAtts';
        return ['type' => 'dynamicChart', 'atts' => ['edit' => [
            'title' => $chartInfo['name'], 
            'chartType' => $chartInfo['chartType'],
            'ignoreChanges' => true,
            'style' => ['width' => 'auto'],
            'chartHeight' => '300px',
            //'chartWidth' => '300px',
            'showTable' => 'no',
            'colspan' => Utl::getItem('colspan', $chartInfo, 1, 1),
            'tableAtts' => ['dynamicColumns' => true],
            'legend' => $chartInfo['chartType'] === 'pie' ? ['type' => 'Legend'] : ['type' => 'SelectableLegend', 'options' => []],
            'tooltip' => true,
            'mouseZoomAndPan' => $chartInfo['chartType'] === 'trend' ? true : false,
            'noMarkAsChanged' => true,
            'missingKpisIndex' => $missingKpisIndex,
            'onWatchLocalAction' => [
                'hidden' => [$chartId => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->chartChangeAction($chartId, 'hidden')]]],
                'axesToInclude' => [$chartId => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->chartChangeAction($chartId, 'axes')]]],
                'daytype' => [$chartId => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->chartChangeAction($chartId, 'daytype')]]],
                'plotsToInclude' => [$chartId => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->chartChangeAction($chartId, 'plots')]]],
                'title' => [$chartId => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->chartChangeAction($chartId, 'title')]]],
                'chartFilter' => [$chartId => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->chartChangeAction($chartId, 'chartFilter')]]],
                'kpisToInclude' => [$chartId => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->chartChangeAction($chartId, 'kpis')]]],
                'itemsSetsToInclude' => [$chartId => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->chartChangeAction($chartId, 'itemsSets')]]],
            ],
            'customizableAtts' => $this->$customizableAtts($translations, $dateWidgetNames, $selectedDateWidgetName)
        ]]];
    }
    public function chartPreMergeCustomizationAction(&$response, &$chartLayoutRow, $customMode, $grid, $dateCol, $dateWidgetNames, $namesToTranslate, $missingKpisIndex, $selectedDateWidgetName = null){
        if (!empty($response['widgetsDescription'])){
            $editConfig =  $customMode === 'object'
                ? $this->user->getCustomView($this->objectName, 'edit', 'tab', ['editConfig'])
                : $this->model->getCombinedCustomization(['id' => Utl::getItem('id', $response['data']['value'])], 'edit', 'tab', ['editConfig']);
            if (!empty($editConfig)){
                $chartsPerRow = Utl::getItem('chartsperrow', $editConfig);
                if ($chartsPerRow){
                    $chartLayoutRow['tableAtts']['cols'] = $chartsPerRow;
                }
                $charts = Utl::getItem('charts', $editConfig);
                if ($charts){
                    $charts = json_decode($charts, true);
                    foreach ($charts as $chart){
                        $chartId = 'chart' . $chart['id'];
                        $response['widgetsDescription'][$chartId] = Widgets::description($this->chartDescription($chartId, $chart, $dateWidgetNames, $namesToTranslate, $missingKpisIndex, $selectedDateWidgetName));
                        $chartLayoutRow['widgets'][] = $chartId;
                    }
                    $response['widgetsDescription'][$grid]['atts'] = Utl::array_merge_recursive_replace($response['widgetsDescription'][$grid]['atts'],
                        ['onWatchLocalAction' => ['collection' => [$grid => ['chartViewStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' => $this->gridWatchAction($dateCol, $selectedDateWidgetName)]]]]]);
                    $response['widgetsDescription'][$grid]['atts'] = Utl::array_merge_recursive_concat($response['widgetsDescription'][$grid]['atts'], ['afterActions' => [
                        'createNewRow' => $this->gridRowWatchAction(), 'updateRow' => $this->gridRowWatchAction(), 'deleteRow' => $this->gridRowWatchAction(), 'deleteRows' => $this->gridRowWatchAction()
                    ]]);
                }
            }
        }
        return $response;
    }
    public function chartChangeAction($chartId, $changedAtt){
        return <<<EOT
var form = sWidget.form;
//form.resize();
if (!newValue || '$changedAtt' !== 'hidden'){
    setTimeout(function(){//setTimeout needed so that chart render is called after the form has resized
        form.charts.setChartValue('$chartId');
    }, 0);
}
EOT
        ;
    }
    public function gridWatchAction($dateCol, $selectedDate = null){
        return <<<EOT
const form = sWidget.form;
if (form.editConfig && form.editConfig.charts){
    const chartsConfig = JSON.parse(form.editConfig.charts);
    if (form.charts){
        form.resize();
        form.charts.setChartsValue();     
		/*utils.waitUntil(
			function(){
				return form.markIfChanged;
			}, 
			function(){
                form.markIfChanged = form.watchOnChange = false;
                form.charts.setChartsValue();
                form.markIfChanged = form.watchOnChange = true;
			}, 
			100);*/
    }else{    
        require(["tukos/charting/Charts"], function(Charts){
            form.charts = new Charts({form: form, grid: sWidget, dateCol: '$dateCol', selectedDate: '$selectedDate', charts: chartsConfig});
        });
    }
}
EOT
        ;
    }
    public function gridRowWatchAction(){
        return <<<EOT
const form = this.form;
if (form.editConfig && form.editConfig.charts){
    form.charts.setChartsValue();
}
EOT
        ;
    }
    public function plotColsToHide(){
        return <<<EOT
             {Curves: ['gap', 'vertical', 'values', 'label'], Bubble: ['gap', 'vertical', 'values', 'label'], ClusteredColumns: ['lines', 'areas', 'markers', 'tension', 'interpolate', 'vertical', 'values', 'label'], Indicator: ['areas', 'markers', 'tension', 'interpolate', 'gap']}
EOT
        ;
    }
    public function plotColsToUnhide(){
        return <<<EOT
             ['lines', 'areas', 'markers', 'tension', 'interpolate', 'gap', 'vertical', 'values', 'label']
EOT
        ;
    }
    public function onPlotTypeChangeLocalAction(){
        return <<<EOT
const grid = sWidget.parent, newColumns = grid.columns;
{$this->plotColsToUnhide()}.forEach(function(colName){
    newColumns[colName].hidden = false;
});
if (newValue){
    const colsToHide = {$this->plotColsToHide()};
    colsToHide[newValue].forEach(function(colName){
        newColumns[colName].hidden = true;
    });
}
setTimeout(function(){
    grid.set('columns', newColumns);
    }, 100);
EOT
;
    }
    public function onPlotRowIdClickAction(){
        return <<<EOT
if (grid.clickedCell.column.field === 'rowId'){
    const newColumns = grid.columns, plotType = grid.clickedCell.row.data.type;
    {$this->plotColsToUnhide()}.forEach(function(colName){
        newColumns[colName].hidden = false;
    });
    if (plotType){
        const colsToHide = {$this->plotColsToHide()};
        colsToHide[plotType].forEach(function(colName){
            newColumns[colName].hidden = true;
        });
    }
    setTimeout(function(){
        grid.set('columns', newColumns);
        }, 100);
}
EOT
;
    }
    public function trendChartCustomizableAtts($translations, $dateWidgetNames, $selectedDateWidgetName){
        $tr = $this->tr;
        $dateFormulaesTranslations = Utl::translations(array_merge(self::$dateFormulaesToTranslate, $dateWidgetNames), $this->tr, 'lowercase');
        return [
            'title' => ['att' => 'title', 'type' => 'TextBox', 'name' => $tr('Title')],
            'chartFilter' => ['att' => 'chartFilter', 'type' => 'TextBox', 'name' => $tr('chartFilter')],
            'axes' => Utl::array_merge_recursive_replace(Widgets::simpleDgrid(Widgets::complete(['label' => $this->tr('Axes'), 'style' => ['maxWidth' => '1500px'], 'storeArgs' => ['idProperty' => 'idg'],
                'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $this->tr('help'), 'name' => 'TrendChartAxesTukosTooltip', 'object' => $this->objectName]],
                'colsDescription' => [
                    'rowId' => ['field' => 'rowId', 'label' => 'id', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
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
                    'tickslabel' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => Utl::idsNamesStore(['daysinceorigin', 'dateofday', 'dayoftheyear', 'weeksinceorigin', 'dateofweek', 'weekoftheyear'], $tr)], 'label' => $tr('tickslabel')],
                        'storeedit' => ['width' => 80]]), false),
                    'firstdate' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $this->tr('firstdate'), 'style' => ['width' => '15em'], 'translations' => $translations,
                        'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $this->tr('help'), 'name' => 'ChartDateFormulaesTukosTooltip', 'object' => $this->objectName]]],
                        'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 120]]), false),
                    'lastdate' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $this->tr('lastdate'), 'style' => ['width' => '15em'], 'translations' => $translations,
                        'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $this->tr('help'), 'name' => 'ChartDateFormulaesTukosTooltip', 'object' => $this->objectName]]],
                        'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 120]]), false),
                    'min' => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $this->tr('axisMin'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 60]]), false),
                    'max' => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $this->tr('axisMax'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 60]]), false),
                    'adjustmax' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 'label' => $tr('Adjustmax')], 'storeedit' => ['width' => 80]]), false),
                ]])), ['att' => 'axesToInclude', 'type' => 'SimpleDgridNoDnd', 'name' => $this->tr('AxesToInclude'), 'atts' => ['initialRowValue' => ['vertical' => true, 'minorTicks' => false],
                    'columns' => ['titleOrientation' => ['hidden' => true], 'titleGap' => ['hidden' => true], 'majorTicks' => ['hidden' => true], 'minorTicks' => ['hidden' => true]]]]),
            'plots' => Utl::array_merge_recursive_replace(Widgets::simpleDgrid(Widgets::complete(['storeArgs' => ['idProperty' => 'idg'], 'style' => ['width' => '1200px'],
                'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $this->tr('help'), 'name' => 'TrendChartDiagramsTukosTooltip', 'object' => $this->objectName]],
                'colsDescription' => [
                    'rowId' => ['field' => 'rowId', 'label' => 'id', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                    'name' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('Name'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 100]]), false),
                    'type' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => Utl::idsNamesStore(['Curves', 'ClusteredColumns', 'Indicator'], $tr)], 'label' => $tr('plottype'),
                        'onChangeLocalAction' => ['type' => ['localActionStatus' => $this->onPlotTypeChangeLocalAction()]]], 'storeedit' => ['minWidth' => 60]]), false),
                    'hAxis' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('hAxis'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 60]]), false),
                    'vAxis' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('vAxis'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 60]]), false),
                    'lines' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 'label' => $tr('ShowLines')], 'storeedit' => ['width' => 60]]), false),
                    'areas' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 'label' => $tr('ShowAreas')], 'storeedit' => ['width' => 60]]), false),
                    'markers' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 'label' => $tr('ShowMarkers')], 'storeedit' => ['width' => 60]]), false),
                    'tension' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => '', 'name' => $tr('Brokenline')], ['id' => 'X', 'name' => $tr('Curved')]]], 'label' => $tr('Linetype')], 'storeedit' => ['width' => 60]]), false),
                    'interpolate' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 'label' => $tr('Interpolate')], 'storeedit' => ['minWidth' => 60]]), false),
                    'gap' => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $this->tr('Barsgap'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 60]]), false),
                    'vertical' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('vertical')], ['id' => false, 'name' => $tr('horizontal')]]], 'label' => $tr('indicatororientation')],
                        'storeedit' => ['width' => 80]]), false),
                    'values' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $this->tr('Indicatorvalue'), 'translations' => $dateFormulaesTranslations], 'storeedit' => ['width' => 60]]), false),
                    'label' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('Label'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 100]]), false),
                ]])), ['att' => 'plotsToInclude', 'type' => 'SimpleDgridNoDnd', 'name' => $this->tr('plotsToInclude'), 'atts' => ['initialRowValue' => ['lines' => true, 'markers' => true], 'onCellClickAction' => $this->onPlotRowIdClickAction()]]),
            'kpis' => Utl::array_merge_recursive_replace(Widgets::simpleDgrid(Widgets::complete(['label' => $this->tr('seriesToInclude'), 'storeArgs' => ['idProperty' => 'idg'], 'style' => ['width' => '1200px'],
                'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $this->tr('help'), 'name' => 'TrendChartKpisTukosTooltip', 'object' => $this->objectName]],
                'colsDescription' => [
                    'rowId' => ['field' => 'rowId', 'label' => 'id', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                    'name' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('Name')], 'storeedit' => ['width' => 150]]), false),
                    'kpi' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $this->tr('Kpiformula'), 'style' => ['width' => '20em'], 'translations' => $translations],
                        'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 200]]), false),
                    'plot' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('Plot'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 100]]), false),
                    'displayformat' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => '', 'name' => $tr('none')], ['id' => 'secondsToHHMMSS', 'name' => $tr('secondsToHHMMSS')],
                        ['id' => 'minutesToHHMMSS', 'name' => $tr('minutesToHHMMSS')]]], 'label' => $tr('displayformat')], 'storeedit' => ['width' => 60]]), false),
                    'tooltipunit' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('Tooltipunit'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 100]]), false),
                    'scalingfactor' => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $this->tr('Scalingfactor'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 60]]), false),
                    'absentiszero' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 'label' => $tr('Absentiszero')], 'storeedit' => ['width' => 80]]), false),
                    //'fillColor' => Widgets::description(Widgets::colorPickerTextBox(['edit' => ['label' => $tr('fillcolor')], 'storeedit' => ['width' => 80]]), false),
                    'kpiFilter' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $this->tr('itemsfilter'), 'style' => ['width' => '15em'], 'translations' => $translations], 
                        'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 200]]), false)
                ]])), ['att' => 'kpisToInclude', 'type' => 'SimpleDgridNoDnd', 'name' => $this->tr('dataToInclude')])
        ];
    }
    public function spiderChartCustomizableAtts($translations, $dateWidgetNames,  $selectedDate){
        $tr = $this->tr;
        return [
            'title' => ['att' => 'title', 'type' => 'TextBox', 'name' => $tr('Title')],
            'chartFilter' => ['att' => 'chartFilter', 'type' => 'TextBox', 'name' => $tr('chartFilter')],
            'kpis' => Utl::array_merge_recursive_replace(Widgets::simpleDgrid(Widgets::complete(['label' => $tr('kpisToInclude'), 'storeArgs' => ['idProperty' => 'idg'], 'style' => ['width' => '1200px'],
                'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $tr('help'), 'name' => 'SpiderChartKpisTukosTooltip', 'object' => $this->objectName]],
                'colsDescription' => [
                    'rowId' => ['field' => 'rowId', 'label' => 'id', 'width' => 40, 'hidden' => true, 'className' => 'dgrid-header-col'],
                    'name' => Widgets::description(Widgets::textBox(['edit' => ['label' => $tr('Name')], 'storeedit' => ['width' => 200]]), false),
                    'kpi' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $tr('Kpiformula'), 'style' => ['width' => '20em'], 'translations' => $translations],
                        'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 200]]), false),
                    'displayformat' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => '', 'name' => $tr('none')], ['id' => 'secondsToHHMMSS', 'name' => $tr('secondsToHHMMSS')],
                        ['id' => 'minutesToHHMMSS', 'name' => $tr('minutesToHHMMSS')]]], 'label' => $tr('displayformat')], 'storeedit' => ['width' => 60]]), false),
                    'tooltipunit' => Widgets::description(Widgets::textBox(['edit' => ['label' => $tr('Tooltipunit'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 100]]), false),
                    'axisMin' => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $tr('axisMin'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 60]]), false),
                    'axisMax' => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $tr('axisMax'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 60]]), false),
                    'kpiFilter' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $tr('itemsfilter'), 'style' => ['width' => '15em'], 'translations' => $translations], 
                        'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 200]]), false)
                ]])), ['att' => 'kpisToInclude', 'type' => 'SimpleDgridNoDnd', 'name' => $tr('dataToInclude')]),
            'itemsSets' => Utl::array_merge_recursive_replace(Widgets::simpleDgrid(Widgets::complete(['label' => $tr('itemsSetsToInclude'), 'storeArgs' => ['idProperty' => 'idg'], 'style' => ['width' => '1200px'],
                'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $tr('help'), 'name' => 'SpiderChartItemsSetsTukosTooltip', 'object' => $this->objectName]],
                'colsDescription' => [
                'rowId' => ['field' => 'rowId', 'label' => 'id', 'width' => 40, 'hidden' => true, 'className' => 'dgrid-header-col'],
                'setName' => Widgets::description(Widgets::textBox(['edit' => ['label' => $tr('Label')], 'storeedit' => ['width' => 150]]), false),
                'firstdate' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $tr('firstdate'), 'style' => ['width' => '15em'], 'translations' => $translations,
                    'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $tr('help'), 'name' => 'ChartDateFormulaesTukosTooltip', 'object' => $this->objectName]]],
                    'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 120]]), false),
                'lastdate' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $tr('lastdate'), 'style' => ['width' => '15em'], 'translations' => $translations,
                    'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $tr('help'), 'name' => 'ChartDateFormulaesTukosTooltip', 'object' => $this->objectName]]],
                    'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 120]]), false),
                'kpidate' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $tr('kpidate'), 'style' => ['width' => '15em'], 'translations' => $translations,
                    'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $tr('help'), 'name' => 'ChartDateFormulaesTukosTooltip', 'object' => $this->objectName]]],
                    'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 150]]), false),
                'fillColor' => Widgets::description(Widgets::colorPickerTextBox(['edit' => ['label' => $tr('fillcolor')], 'storeedit' => ['width' => 80]]), false),
                'itemsFilter' => Widgets::description(Widgets::textBox(['edit' => ['label' => $tr('itemsfilter'), 'style' => ['width' => '200px'], 'translations' => $translations], 
                    'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', ]]), false),
                        
             ]])), ['att' => 'itemsSetsToInclude', 'type' => 'SimpleDgridNoDnd', 'name' => $tr('itemsSetsToInclude')])
        ];                          
    }
    public function pieChartCustomizableAtts($translations, $dateWidgetNames,  $selectedDate){
        $tr = $this->tr;
        return [
            'title' => ['att' => 'title', 'type' => 'TextBox', 'name' => $tr('Title')],
            'chartFilter' => ['att' => 'chartFilter', 'type' => 'TextBox', 'name' => $tr('chartFilter')],
            'kpis' => Utl::array_merge_recursive_replace(Widgets::simpleDgrid(Widgets::complete(['label' => $tr('kpisToInclude'), 'storeArgs' => ['idProperty' => 'idg'], 'style' => ['width' => '1200px'],
                'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $tr('help'), 'name' => 'pieChartKpisTukosTooltip', 'object' => $this->objectName]],
                'colsDescription' => [
                    'rowId' => ['field' => 'rowId', 'label' => 'id', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                    'name' => Widgets::description(Widgets::textBox(['edit' => ['label' => $tr('Name')], 'storeedit' => ['width' => 150]]), false),
                    'kpi' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $tr('Kpiformula'), 'style' => ['width' => '20em'], 'translations' => $translations],
                        'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 200]]), false),
                    'displayformat' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => '', 'name' => $tr('none')], ['id' => 'secondsToHHMMSS', 'name' => $tr('secondsToHHMMSS')],
                        ['id' => 'minutesToHHMMSS', 'name' => $tr('minutesToHHMMSS')]]], 'label' => $tr('displayformat')], 'storeedit' => ['width' => 60]]), false),
                    'tooltipunit' => Widgets::description(Widgets::textBox(['edit' => ['label' => $tr('Tooltipunit'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 100]]), false),
                    'firstdate' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $tr('firstdate'), 'style' => ['width' => '15em'], 'translations' => $translations,
                        'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $tr('help'), 'name' => 'ChartDateFormulaesTukosTooltip', 'object' => $this->objectName]]],
                        'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 100]]), false),
                    'lastdate' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $tr('lastdate'), 'style' => ['width' => '15em'], 'translations' => $translations,
                        'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $tr('help'), 'name' => 'ChartDateFormulaesTukosTooltip', 'object' => $this->objectName]]],
                        'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 100]]), false),
                    'kpidate' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $tr('kpidate'), 'style' => ['width' => '15em'], 'translations' => $translations,
                        'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $tr('help'), 'name' => 'ChartDateFormulaesTukosTooltip', 'object' => $this->objectName]]],
                        'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 100]]), false),
                    'fillColor' => Widgets::description(Widgets::colorPickerTextBox(['edit' => ['label' => $tr('fillcolor')], 'storeedit' => ['width' => 80]]), false),
                    'kpiFilter' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $tr('itemsfilter'), 'style' => ['width' => '15em'], 'translations' => $translations], 
                        'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 150]]), false)
                ]])), ['att' => 'kpisToInclude', 'type' => 'SimpleDgridNoDnd', 'name' => $tr('dataToInclude')])
        ];
    }
    public function repartitionChartCustomizableAtts($translations, $dateWidgetNames, $selectedDateWidgetName){
        $tr = $this->tr;
        $dateFormulaesTranslations = Utl::translations(array_merge(self::$dateFormulaesToTranslate, $dateWidgetNames), $this->tr, 'lowercase');
        return [
            'title' => ['att' => 'title', 'type' => 'TextBox', 'name' => $tr('Title')],
            'chartFilter' => ['att' => 'chartFilter', 'type' => 'TukosTextarea', 'name' => $tr('chartFilter'), 'atts' => ['translations' => $translations]],
            'axes' => Utl::array_merge_recursive_replace(Widgets::simpleDgrid(Widgets::complete(['label' => $this->tr('Axes'), 'storeArgs' => ['idProperty' => 'idg'], 'style' => ['width' => '1200px'],
                'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $this->tr('help'), 'name' => 'TrendChartAxesTukosTooltip', 'object' => $this->objectName]],
                'colsDescription' => [
                    'rowId' => ['field' => 'rowId', 'label' => 'id', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
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
                    'min' => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $this->tr('axisMin'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 60]]), false),
                    'max' => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $this->tr('axisMax'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 60]]), false),
                    'adjustmax' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 'label' => $tr('Adjustmax')], 'storeedit' => ['width' => 80]]), false),
                ]])), ['att' => 'axesToInclude', 'type' => 'SimpleDgridNoDnd', 'name' => $this->tr('AxesToInclude'), 'atts' => ['initialRowValue' => ['vertical' => true, 'minorTicks' => false],
                    'columns' => ['titleOrientation' => ['hidden' => true], 'titleGap' => ['hidden' => true], 'majorTicks' => ['hidden' => true], 'minorTicks' => ['hidden' => true]]]]),
            'plots' => Utl::array_merge_recursive_replace(Widgets::simpleDgrid(Widgets::complete(['storeArgs' => ['idProperty' => 'idg'], 'style' => ['width' => '1200px'],
                'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $this->tr('help'), 'name' => 'TrendChartDiagramsTukosTooltip', 'object' => $this->objectName]],
                'colsDescription' => [
                    'rowId' => ['field' => 'rowId', 'label' => 'id', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                    'name' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('Name'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 100]]), false),
                    'type' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => Utl::idsNamesStore(['Curves', 'ClusteredColumns', 'Indicator'], $tr)], 'label' => $tr('plottype'),
                        'onChangeLocalAction' => ['type' => ['localActionStatus' => $this->onPlotTypeChangeLocalAction()]]], 'storeedit' => ['minWidth' => 60]]), false),
                    'hAxis' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('hAxis'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 60]]), false),
                    'vAxis' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('vAxis'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 60]]), false),
                    'lines' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 'label' => $tr('ShowLines')], 'storeedit' => ['width' => 60]]), false),
                    'areas' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 'label' => $tr('ShowAreas')], 'storeedit' => ['width' => 60]]), false),
                    'markers' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 'label' => $tr('ShowMarkers')], 'storeedit' => ['width' => 60]]), false),
                    'tension' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => '', 'name' => $tr('Brokenline')], ['id' => 'X', 'name' => $tr('Curved')]]], 'label' => $tr('Linetype')], 'storeedit' => ['width' => 60]]), false),
                    'interpolate' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 'label' => $tr('Interpolate')], 'storeedit' => ['minWidth' => 60]]), false),
                    'gap' => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $this->tr('Barsgap'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 60]]), false),
                    'vertical' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('vertical')], ['id' => false, 'name' => $tr('horizontal')]]], 'label' => $tr('indicatororientation')],
                        'storeedit' => ['width' => 80]]), false),
                    'values' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $this->tr('Indicatorvalue'), 'translations' => $dateFormulaesTranslations], 'storeedit' => ['width' => 60]]), false),
                    'label' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('Label'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 100]]), false),
                ]])), ['att' => 'plotsToInclude', 'type' => 'SimpleDgridNoDnd', 'name' => $this->tr('plotsToInclude'), 'atts' => ['initialRowValue' => ['lines' => true, 'markers' => true], 'onCellClickAction' => $this->onPlotRowIdClickAction()]]),
            'kpis' => Utl::array_merge_recursive_replace(Widgets::simpleDgrid(Widgets::complete(['label' => $this->tr('seriesToInclude'), 'storeArgs' => ['idProperty' => 'idg'], 'style' => ['width' => '1200px'],
                'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $this->tr('help'), 'name' => 'TrendChartKpisTukosTooltip', 'object' => $this->objectName]],
                'colsDescription' => [
                    'rowId' => ['field' => 'rowId', 'label' => 'id', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                    'name' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('Nom')], 'storeedit' => ['width' => 70]]), false),
                    'category' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('CategoryLabel')], 'storeedit' => ['width' => 70]]), false),
                    'plot' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('Plot'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 80]]), false),
                    'kpi' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $this->tr('Kpiformula'), 'style' => ['width' => '20em'], 'translations' => $translations],
                        'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 200]]), false),
                    'firstdate' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $tr('firstdate'), 'style' => ['width' => '15em'], 'translations' => $translations,
                        'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $tr('help'), 'name' => 'ChartDateFormulaesTukosTooltip', 'object' => $this->objectName]]],
                        'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 100]]), false),
                    'lastdate' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $tr('lastdate'), 'style' => ['width' => '15em'], 'translations' => $translations,
                        'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $tr('help'), 'name' => 'ChartDateFormulaesTukosTooltip', 'object' => $this->objectName]]],
                        'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 100]]), false),
                    'kpidate' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $tr('kpidate'), 'style' => ['width' => '15em'], 'translations' => $translations,
                        'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $tr('help'), 'name' => 'ChartDateFormulaesTukosTooltip', 'object' => $this->objectName]]],
                        'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 100]]), false),
                    'displayformat' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => '', 'name' => $tr('none')], ['id' => 'secondsToHHMMSS', 'name' => $tr('secondsToHHMMSS')],
                        ['id' => 'minutesToHHMMSS', 'name' => $tr('minutesToHHMMSS')]]], 'label' => $tr('displayformat')], 'storeedit' => ['width' => 60]]), false),
                    'tooltipunit' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('Tooltipunit'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 70]]), false),
                    'scalingfactor' => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $this->tr('Scalingfactor'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 60]]), false),
                    'absentiszero' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 'label' => $tr('Absentiszero')], 'storeedit' => ['width' => 60]]), false),
                    'fillColor' => Widgets::description(Widgets::colorPickerTextBox(['edit' => ['label' => $tr('fillcolor')], 'storeedit' => ['width' => 80]]), false),
                    'itemsFilter' => Widgets::description(Widgets::tukosTextarea(['edit' => ['label' => $this->tr('itemsfilter'), 'style' => ['width' => '15em'], 'translations' => $translations],
                        'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 150]]), false)
                ]])), ['att' => 'kpisToInclude', 'type' => 'SimpleDgridNoDnd', 'name' => $this->tr('dataToInclude')])
        ];
    }
    public function xyChartCustomizableAtts($translations, $dateWidgetNames, $selectedDateWidgetName){
        $tr = $this->tr;
        $dateFormulaesTranslations = Utl::translations(array_merge(self::$dateFormulaesToTranslate, $dateWidgetNames), $this->tr, 'lowercase');
        return [
            'title' => ['att' => 'title', 'type' => 'TextBox', 'name' => $tr('Title')],
            'chartFilter' => ['att' => 'chartFilter', 'type' => 'TukosTextarea', 'name' => $tr('chartFilter'), 'atts' => ['translations' => $translations]],
            'axes' => Utl::array_merge_recursive_replace(Widgets::simpleDgrid(Widgets::complete(['label' => $this->tr('Axes'), 'storeArgs' => ['idProperty' => 'idg'], 'style' => ['width' => '1200px'],
                'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $this->tr('help'), 'name' => 'TrendChartAxesTukosTooltip', 'object' => $this->objectName]],
                'colsDescription' => [
                    'rowId' => ['field' => 'rowId', 'label' => 'id', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
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
                    'scaletype' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => Utl::idsNamesStore(['linear', 'logarithmic'], $tr)], 'label' => $tr('Scaletype')], 'storeedit' => ['width' => 80]]), false),
                    'min' => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $this->tr('axisMin'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 60]]), false),
                    'max' => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $this->tr('axisMax'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 60]]), false),
                    'adjustmax' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 'label' => $tr('Adjustmax')], 'storeedit' => ['width' => 80]]), false),
                ]])), ['att' => 'axesToInclude', 'type' => 'SimpleDgridNoDnd', 'name' => $this->tr('AxesToInclude'), 'atts' => ['initialRowValue' => ['vertical' => true, 'minorTicks' => false],
                    'columns' => ['titleOrientation' => ['hidden' => true], 'titleGap' => ['hidden' => true], 'majorTicks' => ['hidden' => true], 'minorTicks' => ['hidden' => true]]]]),
            'plots' => Utl::array_merge_recursive_replace(Widgets::simpleDgrid(Widgets::complete(['storeArgs' => ['idProperty' => 'idg'], 'style' => ['width' => '1500px'],
                'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $this->tr('help'), 'name' => 'TrendChartDiagramsTukosTooltip', 'object' => $this->objectName]],
                'colsDescription' => [
                    'rowId' => ['field' => 'rowId', 'label' => 'id', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                    'name' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('Name'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 100]]), false),
                    'type' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => Utl::idsNamesStore(['Curves', 'Bubble', 'ClusteredColumns', 'Indicator'], $tr)], 'label' => $tr('plottype'),
                        'onChangeLocalAction' => ['type' => ['localActionStatus' => $this->onPlotTypeChangeLocalAction()]]], 'storeedit' => ['minWidth' => 60]]), false),
                    'hAxis' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('hAxis'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 60]]), false),
                    'vAxis' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('vAxis'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 60]]), false),
                    'lines' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 'label' => $tr('ShowLines')], 'storeedit' => ['width' => 60]]), false),
                    'areas' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 'label' => $tr('ShowAreas')], 'storeedit' => ['width' => 60]]), false),
                    'markers' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 'label' => $tr('ShowMarkers')], 'storeedit' => ['width' => 60]]), false),
                    'markersProgressColor' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 
                        'label' => $tr('MarkersProgressColor')], 'storeedit' => ['width' => 60]]), false),
                    'tension' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => '', 'name' => $tr('Brokenline')], ['id' => 'X', 'name' => $tr('Curved')]]], 'label' => $tr('Linetype')], 'storeedit' => ['width' => 60]]), false),
                    'interpolate' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 'label' => $tr('Interpolate')], 'storeedit' => ['minWidth' => 60]]), false),
                    'gap' => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $this->tr('Barsgap'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 60]]), false),
                    'vertical' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('vertical')], ['id' => false, 'name' => $tr('horizontal')]]], 'label' => $tr('indicatororientation')],
                        'storeedit' => ['width' => 80]]), false),
                    'values' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $this->tr('Indicatorvalue'), 'translations' => $dateFormulaesTranslations], 'storeedit' => ['width' => 60]]), false),
                    'label' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('Label'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 100]]), false),
                ]])), ['att' => 'plotsToInclude', 'type' => 'SimpleDgridNoDnd', 'name' => $this->tr('plotsToInclude'), 'atts' => ['initialRowValue' => ['lines' => true, 'markers' => true], 'onCellClickAction' => $this->onPlotRowIdClickAction()]]),
            'kpis' => Utl::array_merge_recursive_replace(Widgets::simpleDgrid(Widgets::complete(['label' => $this->tr('seriesToInclude'), 'storeArgs' => ['idProperty' => 'idg'], 'style' => ['width' => '1500px'],
                'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $this->tr('help'), 'name' => 'TrendChartKpisTukosTooltip', 'object' => $this->objectName]],
                'colsDescription' => [
                    'rowId' => ['field' => 'rowId', 'label' => 'id', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                    'name' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('Name')], 'storeedit' => ['width' => 80]]), false),
                    'plot' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('Plot'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 80]]), false),
                    'kpi' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $this->tr('Kpiformula'), 'style' => ['width' => '20em'], 'translations' => $translations],
                        'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 200]]), false),
                    'firstdate' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $tr('firstdate'), 'style' => ['width' => '15em'], 'translations' => $translations,
                        'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $tr('help'), 'name' => 'ChartDateFormulaesTukosTooltip', 'object' => $this->objectName]]],
                        'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 100]]), false),
                    'lastdate' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $tr('lastdate'), 'style' => ['width' => '15em'], 'translations' => $translations,
                        'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $tr('help'), 'name' => 'ChartDateFormulaesTukosTooltip', 'object' => $this->objectName]]],
                        'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 100]]), false),
                    'kpidate' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $tr('kpidate'), 'style' => ['width' => '15em'], 'translations' => $translations,
                        'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $tr('help'), 'name' => 'ChartDateFormulaesTukosTooltip', 'object' => $this->objectName]]],
                        'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 100]]), false),
                    'displayformat' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => '', 'name' => $tr('none')], ['id' => 'secondsToHHMMSS', 'name' => $tr('secondsToHHMMSS')],
                        ['id' => 'minutesToHHMMSS', 'name' => $tr('minutesToHHMMSS')]]], 'label' => $tr('displayformat')], 'storeedit' => ['width' => 60]]), false),
                    'tooltipunit' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('Tooltipunit'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 70]]), false),
                    'scalingfactor' => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $this->tr('Scalingfactor'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 60]]), false),
                    'absentiszero' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 'label' => $tr('Absentiszero')], 'storeedit' => ['width' => 60]]), false),
                    'fillColor' => Widgets::description(Widgets::colorPickerTextBox(['edit' => ['label' => $tr('fillcolor')], 'storeedit' => ['width' => 80]]), false),
                    'itemsFilter' => Widgets::description(Widgets::tukosTextarea(['edit' => ['label' => $this->tr('itemsfilter'), 'style' => ['width' => '15em'], 'translations' => $translations],
                        'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 150]]), false)
                ]])), ['att' => 'kpisToInclude', 'type' => 'SimpleDgridNoDnd', 'name' => $this->tr('dataToInclude')])
        ];
    }
}
?>