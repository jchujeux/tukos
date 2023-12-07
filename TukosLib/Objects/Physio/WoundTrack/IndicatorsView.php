<?php
namespace TukosLib\Objects\Physio\WoundTrack;
use TukosLib\Utils\Widgets;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

trait IndicatorsView {
    /*public function functionLabel ($funcName, $dayOrWeekOrMonth){
        return $this->tr($funcName) . '(' . $this->tr($dayOrWeekOrMonth) . ', 1)';
    }*/
    public function indicatorDescription($indicatorId, $id, $description, $minimum = 0, $maximum = 10, $tickinterval = 10, $ticklabel = '', $snapinterval = 1, $showvalue = 'yes'){
        return ['type' => 'horizontalLinearGauge', 'atts' => ['edit' => [
            'label' => $this->tr('TrackingIndicator') . " $id : " . $this->tr($description), 'style' => ['width' => 'auto', 'maxWidth' => '800px', 'margin' => '0 auto'], 
            'gaugeAtts' => ['indicatorColor' => 'black', 'height' => 30, 'minimum' => floatval($minimum), 'maximum' => $maximum, 'tickLabel' => $ticklabel, 'minorTicksEnabled' => false, 'majorTickInterval' => $tickinterval, 'snapInterval' => $snapinterval, 'showValue' => $showvalue === 'yes' ? true : false, 
                'gradient' => [0, '#B22222', 0.5, '#FF8C00', 1, '#7FFFD4'], 'style' => ['margin' => '0px 0px 0px 0px', 'height' => '50px'], 'useTooltip' => false]
        ]]];
    }
    public function gamePlansPreMerge($response, $customMode){
        $indicators =  $customMode === 'object'
            ? $this->user->getCustomView('physiogameplans', 'edit', 'tab', ['indicatorsConfig', 'indicators'])
            : $this->model->getCombinedCustomization(['id' => Utl::getItem('id', $response['data']['value'])], 'edit', 'tab', ['indicatorsConfig', 'indicators']);
            if (!empty($indicators)){
                $indicators = json_decode($indicators, true);
                foreach ($indicators as $indicator){
                    $indicatorId = 'indicator' . $indicator['id'];
                    $response['widgetsDescription'][$indicatorId] = Widgets::description($this->indicatorDescription($indicatorId, ...Utl::getItems(['id', 'description', 'minimum', 'maximum', 'tickinterval', 'ticklabel', 'snapinterval', 'showvalue'], $indicator)));
                    $response['postElts'][] = $indicatorId;
                    $response['dataLayout']['contents']['rowindicators']['widgets'][] = $indicatorId;
                    $indicatorIds[] = $indicatorId;
                }
                $response['trackingindicators'] = "return this.localActions.indicatorsExportAction(" . json_encode($indicatorIds) . ");";
            }
            return $response;
    }
    public function gameTracksIndicatorsPreMerge($response, $customMode){
        $model = Tfk::$registry->get('objectsStore')->objectModel('physiogameplans');
        $response['planColsToUpdate'] = ['parentid', 'extendedName', 'woundstartdate', 'treatmentstartdate', 'dateupdated', 'diagnostic', 'pathologyof', 'training', 'pain', 'exercises', 'biomechanics', 'comments', 'indicatorscache'];
        $response['planToTrack'] = ['parentid' => 'patientid', 'extendedName' =>  'name', 'comments' => 'notes'];
        $indicators =  $customMode === 'object'
            ? $this->user->getCustomView('physiogameplans', 'edit', 'tab', ['indicatorsConfig', 'indicators'])
            : $model->getCombinedCustomization(['id' => Utl::getItem('parentid', $response['data']['value'])], 'edit', 'tab', ['indicatorsConfig', 'indicators']);
            if (!empty($indicators)){
                $indicators = json_decode($indicators, true);
                foreach ($indicators as $indicator){
                    $description = Utl::getItems(['id', 'description', 'minimum', 'maximum', 'tickinterval', 'ticklabel', 'snapinterval', 'showvalue'], $indicator);
                    $indicatorId = 'planindicator' . $indicator['id'];
                    $response['widgetsDescription'][$indicatorId] = Widgets::description($this->indicatorDescription($indicatorId, ...$description));
                    $response['dataLayout']['contents']['row1']['contents']['col1']['contents']['row2']['contents']['row1']['contents']['row2']['widgets'][] = $indicatorId;
                    $response['planColsToUpdate'][] = 'indicator' . $indicator['id'];
                    $response['planToTrack']['indicator' . $indicator['id']] = $indicatorId;
                    $indicatorId = 'trackindicator' . $indicator['id'];
                    $indicatorDescription = Widgets::description($this->indicatorDescription($indicatorId, ...$description));
                    $trIndicatorId = $this->tr('trackingindicator') . " " . $indicator['id'];
                    $response['widgetsDescription']['records']['atts']['columns'][$indicatorId] = ['rowsFilters' => true, 'editOn' => 'click', 'field' => $indicatorId, 'label' => $trIndicatorId, 'title' => $trIndicatorId, 'editorArgs' => $indicatorDescription['atts'],
                        'editor' => $indicatorDescription['type'], 'widgetType' => $indicatorDescription['type']
                    ];
                    $response['widgetsDescription']['records']['atts']['nosendOnSave'][] = [$indicatorId];
                    $response['widgetsDescription']['records']['atts']['renderCallback'] = $this->indicatorsCacheRenderCallback();
                    $response['widgetsDescription']['records']['atts']['accordionAtts']['desktopRowLayout']['contents']['row3']['contents']['indicators']['widgets'][] = $indicatorId;
                }
            }
            return $response;
    }
    public function indicatorsCacheRenderCallback(){
        $translatedTrackIndicator = $this->tr('TrackingIndicator');
        return <<<EOT
if (column.field === 'indicatorscache'){
    const self = this, indicators = JSON.parse(node.innerHTML), translatedIndicators = {};
    utils.forEach(indicators, function(value, indicator){
        translatedIndicators['$translatedTrackIndicator' + ' ' + indicator.substring(14)] = value;
    });
    node.innerHTML = JSON.stringify(translatedIndicators);
}
EOT
        ;
    }
}
?>