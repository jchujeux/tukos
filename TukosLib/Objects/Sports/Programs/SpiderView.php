<?php
namespace TukosLib\Objects\Sports\Programs;
use TukosLib\Utils\Widgets;
use TukosLib\Utils\Utilities as Utl;

trait SpiderView {
    public function functionLabel ($funcName, $dayOrWeekOrMonth){
        return $this->tr($funcName) . '(' . $this->tr($dayOrWeekOrMonth) . ', 1)';
    }
    public function spiderDescription($chartId, $title){
        $kpiFunctions = ['SUM', 'EXPAVG', 'DAILYAVG', 'AVG', 'MIN', 'MAX', 'LAST', 'SESSION'];
        $kpiParameters = ['duration', 'intensity', 'stress', 'sport', 'mode', 'distance', 'elevationgain', 'sensations', 'perceivedeffort', 'perceivedmechload', 'mood', 'timemoving', 'trimpavghr', 'trimpavgpw', 'avghr', 'avgpw', 'hr95', 'trimphr', 'trimppw', 'avgcadence', 'mechload', 'h4time', 'h5time', 'sts', 'lts', 'tsb'];
        $kpiOptionsStore = array_merge(Utl::idsNamesStore($kpiFunctions, $this->tr, [false, 'uppercase', false, false, true]), Utl::idsNamesStore($kpiParameters, $this->tr, [true, 'ucfirst', false, false, false]));
        $kpiTranslations = array_merge(Utl::translations($kpiFunctions, $this->tr, 'uppercase'), Utl::translations($kpiParameters, $this->tr));
        return ['type' => 'chart', 'atts' => ['edit' => [
            'title' => $title, 'idProperty' => 'kpi',
            'style' => ['width' => 'auto'],
            'chartHeight' => '300px',
            'showTable' => 'no',
            'tableAtts' => ['dynamicColumns' => true/*, 'columns' => ['kpi' => ['label' => $this->tr('kpi'), 'field' => 'kpi', 'renderCell' => 'renderContent']]*/],
            'plots' =>  [
                'theSpider' => ['type' => 'Spider', 'labelOffset' => -20, 'divisions' => 4, 'precision' => 0, 'seriesFillAlpha' => 0.2, 'seriesWidth' => 2],
            ],
            'legend' => ['type' => 'SelectableLegend', 'options' => []],
            'axes' => ['dummy' => ['type' => 'Base']],
            'tooltip' => true,
            'tukosTooltip' => ['label' => '', 'onClickLink' => ['label' => $this->tr('help'), 'name' => 'kpisChartTukosTooltip']],
            'onWatchLocalAction' => [
                'kpisToInclude' => [$chartId => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->spiderChartChangeAction($chartId, 'kpisToInclude')]]],
                'sessionsSetsToInclude' => [$chartId => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->spiderChartChangeAction($chartId, 'sessionsSetsToInclude')]]],
                'hidden' => [$chartId => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->spiderChartChangeAction($chartId, 'hidden')]]]
            ],
            'customizableAtts' => [
                'kpisToInclude' => array_merge(Widgets::simpleDgrid(Widgets::complete(['label' => $this->tr('kpisToInclude'), 'style' => ['width' => '800px'], 'storeArgs' => ['idProperty' => 'idg'], 'colsDescription' => [
                    'rowId' => ['field' => 'rowId', 'label' => 'id', 'width' => 40, 'className' => 'dgrid-header-col'],
                    'kpiName' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('kpiName')], 'storeedit' => ['width' => 200]]), false),
                    'formula' => Widgets::description(Widgets::storeComboBox([
                        'edit' => ['label' => $this->tr('kpiformula'), 'style' => ['maxWidth' => '25em', 'width' => '25em'], 'translations' => $kpiTranslations, 'storeArgs' => ['data' => $kpiOptionsStore]], 
                        'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 400]]), false),
                    'axisMin' => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $this->tr('axisMin'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 60]]), false),
                    'axisMax' => Widgets::description(Widgets::numberTextBox(['edit' => ['label' => $this->tr('axisMax'), 'constraints' => ['pattern' =>  "0.######"]], 'storeedit' => ['width' => 60]]), false),
                    'kpiFilter' => Widgets::description(Widgets::tukosTextArea(['edit' => ['label' => $this->tr('kpifilter'), 'style' => ['width' => '15em']], 'storeedit' => ['width' => 200]]), false)
                ]])), ['att' => 'kpisToInclude', 'type' => 'SimpleDgridNoDnd', 'name' => $this->tr('kpisToInclude')]),
                'sessionsSetsToInclude' => array_merge(Widgets::simpleDgrid(Widgets::complete(['label' => $this->tr('sessionsSetsToInclude'), 'style' => ['width' => '1000px'], 'storeArgs' => ['idProperty' => 'idg'], 'colsDescription' => [
                    'rowId' => ['field' => 'rowId', 'label' => 'id', 'width' => 40, 'hidden' => true, 'className' => 'dgrid-header-col'],
                    'setName' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('sessionsSetName')], 'storeedit' => ['width' => 150]]), false),
                    'kpiDate' => Widgets::description(Widgets::storeComboBox([
                        'edit' => ['label' => $this->tr('kpiDate'), 'style' => ['width' => '100px'], 'translations' => ['TODAY' => $this->tr('today'), 'ENDOFLASTWEEK' => $this->tr('endoflastweek'), 'DISPLAYEDDATE' => $this->tr('displayeddate'), 'ENDOFDISPLAYEDWEEK' => $this->tr('endofdisplayedweek'),
                            'DATE' => $this->tr('date')], 'tukosTooltip' => ['label' => 'hello the world!', 'onClickLink' => ['label' => 'give me more ...', 'name' => 'displayedday', 'object' => 'sptprograms']],
                            'storeArgs' => ['data' => [['id' => 'today', 'name' => $this->tr('today'), 'tooltip' => $this->tr('todayKpiTooltip')],
                                ['id' => 'endoflastweek', 'name' => $this->tr('endoflastweek'), 'tooltip' => $this->tr('endoflastweekKpiTooltip')], ['id' => 'displayeddate', 'name' => $this->tr('displayeddate'), 'tooltip' => $this->tr('displayedDateKpiTooltip')],
                                ['id' => 'endofdisplayedweek', 'name' => $this->tr('endofdisplayedweek'), 'tooltip' => $this->tr('endOfdisplayedWeekKpiTooltip')], ['id' => 'yyyy-mm-dd', 'name' => $this->tr('yyyy-mm-dd'), 'tooltip' => $this->tr('yyyy-mm-ddKpiTooltip')]
                            ]]],
                        'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 150]]), false),
                    'durationOrSince' => Widgets::description(Widgets::storeComboBox([
                        'edit' => ['label' => $this->tr('durationOrSince'), 'style' => ['width' => '150px'], 'translations' => ['DURATION' => $this->tr('duration'), 'LASTSESSION' => $this->tr('lastsession'), 'DAY' => $this->tr('day'), 'WEEK' => $this->tr('week'), 'MONTH' => $this->tr('month')], 
                            'storeArgs' => ['data' => [['id' => 'day', 'name' => $this->functionLabel('duration', 'day'), 'tooltip' => $this->tr('durationOrSinceTooltip')], ['id' => 'week', 'name' => $this->functionLabel('duration', 'week'), 'tooltip' => $this->tr('durationOrSinceTooltip')],
                                ['id' => 'month', 'name' => $this->functionLabel('duration', 'month'), 'tooltip' => $this->tr('durationOrSinceTooltip')], ['id' => 'lastsession', 'name' => "{$this->tr('lastsession')}(0)", 'tooltip' => $this->tr('durationOrSinceTooltip')], 
                                ['id' => 'yyyy-mm-dd', 'name' => $this->tr('yyyy-mm-dd'), 'tooltip' => $this->tr('durationOrSinceTooltip')]
                        ]]],
                        'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 150]]), false),
                    'sessionsUntil' => Widgets::description(Widgets::storeComboBox([
                        'edit' => ['label' => $this->tr('sessionsUntil'), 'style' => ['width' => '150px'], 'translations' => ['TODAY' => $this->tr('today'), 'ENDOFLASTWEEK' => $this->tr('endoflastweek'), 'DISPLAYEDDATE' => $this->tr('displayeddate'), 'ENDOFDISPLAYEDWEEK' => $this->tr('endofdisplayedweek'),
                                'DATE' => $this->tr('date')],
                            'storeArgs' => ['data' => [['id' => 'today', 'name' => $this->tr('today'), 'tooltip' => $this->tr('todayKpiTooltip')],
                                ['id' => 'endoflastweek', 'name' => $this->tr('endoflastweek'), 'tooltip' => $this->tr('endoflastweekKpiTooltip')], ['id' => 'displayedday', 'name' => $this->tr('displayedday'), 'tooltip' => $this->tr('displayedDayKpiTooltip')],
                                ['id' => 'endofdisplayedweek', 'name' => $this->tr('endofdisplayedweek'), 'tooltip' => $this->tr('endOfdisplayedWeekKpiTooltip')],
                                ['id' => 'datetoday', 'name' => $this->functionLabel('date', 'today'), 'tooltip' => $this->tr('datetodayKpiTooltip')], ['id' => 'endoflastweek', 'name' => $this->functionLabel('date', 'endoflastweek'), 'tooltip' => $this->tr('endoflastweekKpiTooltip')],
                                ['id' => 'datedisplayedday', 'name' => $this->functionLabel('date', 'datedisplayedday'), 'tooltip' => $this->tr('datedisplayedDayKpiTooltip')],
                                ['id' => 'dateendofdisplayedweek', 'name' => $this->functionLabel('date', 'endofdisplayedweek'), 'tooltip' => $this->tr('dateendOfdisplayedWeekKpiTooltip')], ['id' => 'yyyy-mm-dd', 'name' => $this->tr('yyyy-mm-dd'), 'tooltip' => $this->tr('yyyy-mm-ddKpiTooltip cliquer <a href="https://tukos.site" target="_blank">ici</a>!')],
                            ]]],
                        // ['today', ['date, 'today'], 
                        'storeedit' => ['formatType' => 'translate', 'renderContentAction' => 'if (!this.formatOptions){this.formatOptions = {translations: this.editorArgs.translations};}', 'width' => 150]]), false),
                    'mode' => Widgets::description(Widgets::StoreSelect(['edit' => ['label' => $this->tr('mode'), 'style' => ['width' => '100px'], 'storeArgs' => ['data' => Utl::idsNamesStore($this->model->options('mode'), $this->tr)]],
                        'storeedit' => ['width' => 80]]), false),
                    'fillColor' => Widgets::description(Widgets::colorPickerTextBox(['edit' => ['label' => $this->tr('fillcolor')], 'storeedit' => ['width' => 80]]), false),
                    'sessionsfilter' => Widgets::description(Widgets::textBox(['edit' => ['label' => $this->tr('sessionsfilter')/*, 'title' => 'this is the title'*/, 'style' => ['width' => '200px']], 'storeedit' => [/*'width' => 400*/]]), false),
                    
                ]])), ['att' => 'sessionsSetsToInclude', 'type' => 'SimpleDgridNoDnd', 'name' => $this->tr('sessionsSetsToInclude')])
            ]
        ]]];
    }
    public function preMergeCustomizationAction($response, $customMode){
        $spiders =  $customMode === 'object'
            ? $this->user->getCustomView($this->objectName, 'edit', 'tab', ['programsConfig', 'spiders'])
            : $this->model->getCombinedCustomization(['id' => Utl::getItem('id', $response['data']['value'])], 'edit', 'tab', ['programsConfig', 'spiders']);
            if (!empty($spiders)){
                $spiders = json_decode($spiders, true);
                foreach ($spiders as $spider){
                    $name = $spider['name'];
                    $chartId = 'spider' . $spider['id'];
                    $response['widgetsDescription'][$chartId] = Widgets::description($this->spiderDescription($chartId, $name));
                    $response['dataLayout']['contents']['row2']['contents']['col1']['contents']['spidersrow']['widgets'][] = $chartId;
                }
            }
            //$response['dataLayout']['contents']['row2']['contents']['col1']['contents']['spidersrow']['widgets'] = array_column($spiders, 'name');
            return $response;
    }
    public function spiderChartChangeAction($chartId, $changedAtt){
        return <<<EOT
var form = sWidget.form;
form.resize();
if (!newValue || '$changedAtt' !== 'hidden'){
    form.kpiChartUtils.setChartValue('$chartId');
}
return true;
EOT
        ;
    }
}
?>