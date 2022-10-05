<?php
namespace TukosLib\Objects;
use TukosLib\Utils\Widgets;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

trait ChartView {

    public function ChartDescription($chartId, $chartInfo, $kpiParameters, $dateOfOriginParameters = ['firstrecorddate'], $selectedDate = null){
        $tr = $this->tr;
        $kpiFunctions = ['SUM', 'EXPAVG', 'DAILYAVG', 'AVG', 'MIN', 'MAX', 'FIRST', 'LAST', 'ITEM'];
        $kpiTranslations = Utl::translations($kpiFunctions, $this->tr, 'uppercase');
        return ['type' => 'dynamicChart', 'atts' => ['edit' => [
            'title' => $chartInfo['name'], 
            'ignoreChanges' => true,
            'style' => ['width' => 'auto'],
            'chartHeight' => '300px',
            'showTable' => 'no',
            'colspan' => Utl::getItem('colspan', $chartInfo, 1, 1),
            'tableAtts' => ['dynamicColumns' => true],
            'legend' => ['type' => 'SelectableLegend', 'options' => []],
            'tooltip' => true,
            'mouseZoomAndPan' => $chartInfo['chartType'] === 'trend' ? true : false,
            'noMarkAsChanged' => true,
            'onWatchLocalAction' => [
                'hidden' => [$chartId => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->chartChangeAction($chartId, 'hidden')]]],
                'axesToInclude' => [$chartId => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->chartChangeAction($chartId, 'axes')]]],
                'daytype' => [$chartId => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->chartChangeAction($chartId, 'daytype')]]],
                'plotsToInclude' => [$chartId => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->chartChangeAction($chartId, 'plots')]]],
                //'gridFilter' => [$chartId => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->chartChangeAction($chartId, 'gridFilter')]]],
                'kpisToInclude' => [$chartId => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->chartChangeAction($chartId, 'kpis')]]],
                'itemsSetsToInclude' => [$chartId => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->chartChangeAction($chartId, 'itemsSetsToInclude')]]],
            ],
            'customizableAtts' => $chartInfo['chartType'] === 'trend' ? $this->trendChartCustomizableAtts($kpiTranslations, $dateOfOriginParameters) : $this->spiderChartCustomizableAtts($kpiTranslations, $selectedDate)
        ]]];
    }
    public function chartPreMergeCustomizationAction(&$response, &$chartLayoutRow, $customMode, $grid, $dateCol, $kpiParameters, $dateOfOriginParameters, $selectedDate = null){
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
                    $response['widgetsDescription'][$chartId] = Widgets::description($this->chartDescription($chartId, $chart, $kpiParameters, $dateOfOriginParameters, $selectedDate));
                    $chartLayoutRow['widgets'][] = $chartId;
                }
                $response['widgetsDescription'][$grid]['atts']['onWatchLocalAction'] = ['collection' => [$grid => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' => $this->gridWatchAction($dateCol, $selectedDate)]]]];
                $response['widgetsDescription'][$grid]['atts']['afterActions'] = [
                    'createNewRow' => $this->gridRowWatchAction(),
                    'updateRow' => $this->gridRowWatchAction(),
                    'deleteRow' => $this->gridRowWatchAction(),
                    'deleteRows' => $this->gridRowWatchAction(),
                    'expandRow' => Tfk::$registry->isMobile ? "this.form.localActions.accordionExpandAction(arguments[1][0].item.recordtype, arguments[1][0]);" : "this.form.localActions.desktopAccordionExpandAction(arguments[1][0].item.recordtype, arguments[1][0]);"
                ];
            }
        }
        return $response;
    }
    public function chartChangeAction($chartId, $changedAtt){
        return <<<EOT
var form = sWidget.form;
form.resize();
if (!newValue || '$changedAtt' !== 'hidden'){
    form.charts.setChartValue('$chartId');
}
return true;
EOT
        ;
    }
    public function gridWatchAction($dateCol, $selectedDate = null){
        return <<<EOT
const form = sWidget.form;
if (form.editConfig && form.editConfig.charts){
    const charts = JSON.parse(form.editConfig.charts);
    if (form.charts){
        form.charts.setChartsValue();     
    }else{    
        require(["tukos/charting/Charts"], function(Charts){
            form.charts = new Charts({form: form, grid: sWidget, dateCol: '$dateCol', selectedDate: '$selectedDate', charts: charts});
            dojo.ready(function(){
                form.markIfChanged = form.watchOnChange = false;
                form.charts.setChartsValue();
                setTimeout(function(){form.markIfChanged = form.watchOnChange = true;}, 0);//or else some of the edit form widget appear as changed
            });
        });
    }
}
return true;
EOT
        ;
    }
    public function gridRowWatchAction(){
        return <<<EOT
console.log('in gridRowWatchAction');
const form = this.form;
if (form.editConfig && form.editConfig.charts){
    form.charts.setChartsValue(JSON.parse(form.editConfig.charts));
}
return true;
EOT
        ;
    }
    public function plotColsToHide(){
        return <<<EOT
             {Curves: ['gap', 'vertical', 'values', 'label'], ClusteredColumns: ['lines', 'areas', 'markers', 'tension', 'interpolate', 'vertical', 'values', 'label'], Indicator: ['areas', 'markers', 'tension', 'interpolate', 'gap']}
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
    public function trendChartCustomizableAtts($kpiTranslations, $dateOfOriginParameters){
        $tr = $this->tr;
        return [
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
                'tickslabel' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => Utl::idsNamesStore(['daysinceorigin', 'dateofday', 'dayoftheyear', 'weeksinceorigin', 'dateofweek', 'weekoftheyear'], $tr)], 'label' => $tr('tickslabel')],
                    'storeedit' => ['width' => 80]]), false),
                'dateoforigin' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => Utl::idsNamesStore($dateOfOriginParameters, $tr)], 'label' => $tr('dateoforigin')], 'storeedit' => ['width' => 80]]), false),
                'min' => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $this->tr('axisMin'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 60]]), false),
                'max' => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $this->tr('axisMax'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 60]]), false),
                'adjustmax' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 'label' => $tr('Adjustmax')], 'storeedit' => ['width' => 80]]), false),
            ]])), ['att' => 'axesToInclude', 'type' => 'SimpleDgridNoDnd', 'name' => $this->tr('AxesToInclude'), 'atts' => ['initialRowValue' => ['vertical' => true, 'minorTicks' => false],
                'columns' => ['titleOrientation' => ['hidden' => true], 'titleGap' => ['hidden' => true], 'majorTicks' => ['hidden' => true], 'minorTicks' => ['hidden' => true]]]]),
            'plots' => Utl::array_merge_recursive_replace(Widgets::simpleDgrid(Widgets::complete([/*'label' => $this->tr('Axes'), */'style' => ['width' => '800px'], 'storeArgs' => ['idProperty' => 'idg'],
                'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $this->tr('help'), 'name' => 'GameTracksTrendChartDiagramsTukosTooltip', 'object' => 'physiogametracks']],
                'colsDescription' => [
                    'rowId' => ['field' => 'rowId', 'label' => 'id', 'width' => 40, 'className' => 'dgrid-header-col'],
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
                    'values' => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $this->tr('Indicatorvalue'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 60]]), false),
                    'label' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('Label'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 100]]), false),
                ]])), ['att' => 'plotsToInclude', 'type' => 'SimpleDgridNoDnd', 'name' => $this->tr('plotsToInclude'), 'atts' => ['initialRowValue' => ['lines' => true, 'markers' => true], 'onCellClickAction' => $this->onPlotRowIdClickAction()]]),
            //'gridFilter' => ['att' => 'gridFilter', 'type' => 'TextBox', 'name' => $tr('gridFilter')],
            'kpis' => Utl::array_merge_recursive_replace(Widgets::simpleDgrid(Widgets::complete(['label' => $this->tr('seriesToInclude'), 'style' => ['width' => '800px'], 'storeArgs' => ['idProperty' => 'idg'],
                'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $this->tr('help'), 'name' => 'GameTracksTrendChartKpisTukosTooltip', 'object' => 'physiogametracks']],
                'colsDescription' => [
                    'rowId' => ['field' => 'rowId', 'label' => 'id', 'width' => 40, 'className' => 'dgrid-header-col'],
                    'name' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('Label')], 'storeedit' => ['width' => 150]]), false),
                    'kpi' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $this->tr('Kpiformula'), 'style' => ['width' => '15em'], 'translations' => $kpiTranslations],
                        'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 200]]), false),
                    'plot' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('Plot'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 100]]), false),
                    'tooltipunit' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('Tooltipunit'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 100]]), false),
                    'scalingfactor' => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $this->tr('Scalingfactor'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 60]]), false),
                    'absentiszero' => Widgets::description(Widgets::storeSelect(['edit' => ['storeArgs' => ['data' => [['id'  => true, 'name' => $tr('yes')], ['id' => false, 'name' => $tr('no')]]], 'label' => $tr('Absentiszero')], 'storeedit' => ['width' => 80]]), false),
                    'kpiFilter' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $this->tr('itemsfilter'), 'style' => ['width' => '15em']], 'storeedit' => ['width' => 200]]), false)
                ]])), ['att' => 'kpisToInclude', 'type' => 'SimpleDgridNoDnd', 'name' => $this->tr('dataToInclude')])
        ];
    }
    public function spiderChartCustomizableAtts($kpiTranslations, $selectedDate){
        $itemsUntilTranslations = ['TODAY' => $this->tr('today'), 'ENDOFLASTWEEK' => $this->tr('endoflastweek'), 'DATE' => $this->tr('date')];
        $itemsUntilStoreArgsData = [['id' => 'today', 'name' => $this->tr('today'), 'tooltip' => $this->tr('todayKpiTooltip')], ['id' => 'endoflastweek', 'name' => $this->tr('endoflastweek'), 'tooltip' => $this->tr('endoflastweekKpiTooltip')],
            ['id' => 'datetoday', 'name' => $this->functionLabel('date', 'today'), 'tooltip' => $this->tr('datetodayKpiTooltip')], ['id' => 'endoflastweek', 'name' => $this->functionLabel('date', 'endoflastweek'), 'tooltip' => $this->tr('endoflastweekKpiTooltip')],
            ['id' => 'yyyy-mm-dd', 'name' => $this->tr('yyyy-mm-dd'), 'tooltip' => $this->tr('yyyy-mm-ddKpiTooltip cliquer <a href="https://tukos.site" target="_blank">ici</a>!')],
        ];
        if ($selectedDate){
            $itemsUntilTranslations = array_merge($itemsUntilTranslations, ['DISPLAYEDDATE' => $this->tr('displayeddate'), 'ENDOFDISPLAYEDWEEK' => $this->tr('endofdisplayedweek')]);
            $itemsUntilStoreArgsData = array_merge($itemsUntilStoreArgsData, [['id' => 'displayedday', 'name' => $this->tr('displayedday'), 'tooltip' => $this->tr('displayedDayKpiTooltip')],
                ['id' => 'endofdisplayedweek', 'name' => $this->tr('endofdisplayedweek'), 'tooltip' => $this->tr('endOfdisplayedWeekKpiTooltip')], 
                ['id' => 'datedisplayedday', 'name' => $this->functionLabel('date', 'datedisplayedday'), 'tooltip' => $this->tr('datedisplayedDayKpiTooltip')],
                ['id' => 'dateendofdisplayedweek', 'name' => $this->functionLabel('date', 'endofdisplayedweek'), 'tooltip' => $this->tr('dateendOfdisplayedWeekKpiTooltip')]
            ]);
        }
        return [
            'kpis' => Utl::array_merge_recursive_replace(Widgets::simpleDgrid(Widgets::complete(['label' => $this->tr('kpisToInclude'), 'style' => ['width' => '800px'], 'storeArgs' => ['idProperty' => 'idg'], 'colsDescription' => [
                'rowId' => ['field' => 'rowId', 'label' => 'id', 'width' => 40, 'className' => 'dgrid-header-col'],
                'name' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('Label')], 'storeedit' => ['width' => 200]]), false),
                'kpi' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $this->tr('Kpiformula'), 'style' => ['width' => '15em'], 'translations' => $kpiTranslations],
                    'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 200]]), false),
                'tooltipunit' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('Tooltipunit'), 'style' => ['width' => '5em']], 'storeedit' => ['width' => 100]]), false),
                'axisMin' => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $this->tr('axisMin'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 60]]), false),
                'axisMax' => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $this->tr('axisMax'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 60]]), false),
                'kpiFilter' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $this->tr('itemsfilter'), 'style' => ['width' => '15em']], 'storeedit' => ['width' => 200]]), false)
            ]])), ['att' => 'kpisToInclude', 'type' => 'SimpleDgridNoDnd', 'name' => $this->tr('dataToInclude')]),
            'itemsSetsToInclude' => Utl::array_merge_recursive_replace(Widgets::simpleDgrid(Widgets::complete(['label' => $this->tr('itemsSetsToInclude'), 'style' => ['width' => '1000px'], 'storeArgs' => ['idProperty' => 'idg'], 'colsDescription' => [
                'rowId' => ['field' => 'rowId', 'label' => 'id', 'width' => 40, 'hidden' => true, 'className' => 'dgrid-header-col'],
                'setName' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('Label')], 'storeedit' => ['width' => 150]]), false),
                /*'kpiDate' => Widgets::description(Widgets::storeComboBox([
                    'edit' => ['label' => $this->tr('kpiDate'), 'style' => ['width' => '100px'], 'translations' => ['TODAY' => $this->tr('today'), 'ENDOFLASTWEEK' => $this->tr('endoflastweek'), 'DISPLAYEDDATE' => $this->tr('displayeddate'), 'ENDOFDISPLAYEDWEEK' => $this->tr('endofdisplayedweek'),
                        'DATE' => $this->tr('date')], 'tukosTooltip' => ['label' => 'hello the world!', 'onClickLink' => ['label' => 'give me more ...', 'name' => 'displayedday', 'object' => 'sptprograms']],
                        'storeArgs' => ['data' => [['id' => 'today', 'name' => $this->tr('today'), 'tooltip' => $this->tr('todayKpiTooltip')],
                            ['id' => 'endoflastweek', 'name' => $this->tr('endoflastweek'), 'tooltip' => $this->tr('endoflastweekKpiTooltip')], ['id' => 'displayeddate', 'name' => $this->tr('displayeddate'), 'tooltip' => $this->tr('displayedDateKpiTooltip')],
                            ['id' => 'endofdisplayedweek', 'name' => $this->tr('endofdisplayedweek'), 'tooltip' => $this->tr('endOfdisplayedWeekKpiTooltip')], ['id' => 'yyyy-mm-dd', 'name' => $this->tr('yyyy-mm-dd'), 'tooltip' => $this->tr('yyyy-mm-ddKpiTooltip')]
                        ]]],
                    'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 150]]), false),*/
                'durationOrSince' => Widgets::description(Widgets::storeComboBox([
                    'edit' => ['label' => $this->tr('durationOrSince'), 'style' => ['width' => '150px'], 'translations' => ['DURATION' => $this->tr('duration'), 'LASTITEM' => $this->tr('lastitem'), 'DAY' => $this->tr('day'), 'WEEK' => $this->tr('week'), 'MONTH' => $this->tr('month')],
                        'storeArgs' => ['data' => [['id' => 'day', 'name' => $this->functionLabel('duration', 'day'), 'tooltip' => $this->tr('durationOrSinceTooltip')], ['id' => 'week', 'name' => $this->functionLabel('duration', 'week'), 'tooltip' => $this->tr('durationOrSinceTooltip')],
                            ['id' => 'month', 'name' => $this->functionLabel('duration', 'month'), 'tooltip' => $this->tr('durationOrSinceTooltip')], ['id' => 'lastitem', 'name' => "{$this->tr('lastitem')}(0)", 'tooltip' => $this->tr('durationOrSinceTooltip')],
                            ['id' => 'yyyy-mm-dd', 'name' => $this->tr('yyyy-mm-dd'), 'tooltip' => $this->tr('durationOrSinceTooltip')]
                            ]]],
                    'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 150]]), false),
                'itemsUntil' => Widgets::description(Widgets::storeComboBox([
                    'edit' => ['label' => $this->tr('itemsUntil'), 'style' => ['width' => '150px'], 'translations' => $itemsUntilTranslations, 'storeArgs' => ['data' => $itemsUntilStoreArgsData]],
                    'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 150]]), false),
                'fillColor' => Widgets::description(Widgets::colorPickerTextBox(['edit' => ['label' => $this->tr('fillcolor')], 'storeedit' => ['width' => 80]]), false),
                'itemsFilter' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('itemsfilter')/*, 'title' => 'this is the title'*/, 'style' => ['width' => '200px']], 'storeedit' => [/*'width' => 400*/]]), false),
                        
             ]])), ['att' => 'itemsSetsToInclude', 'type' => 'SimpleDgridNoDnd', 'name' => $this->tr('itemsSetsToInclude')])
        ];                          
    }
}
?>