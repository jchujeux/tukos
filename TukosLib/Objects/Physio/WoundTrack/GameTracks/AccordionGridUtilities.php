<?php
namespace TukosLib\Objects\Physio\WoundTrack\GameTracks;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\DateTimeUtilities as DUtl;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class AccordionGridUtilities {
    
    public static function translationSets(){
        return ['sports'];
    }
    public static function desktopRowLayout($tr){
        return [
            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
            'contents' => [
                'row1' => [
                    'tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'style' => ['border' => '1px solid grey'], 'orientation' => 'vert', 'id' => 'recordFiltersInfoPane', 'labelWidth' => 50],
                    'widgets' => ['rowId', 'recordtype', 'recorddate']
                ],
                'row2' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'widgetWidths' => ['50%', '50%'], 'widgetCellStyle' => ['verticalAlign' => 'top', 'padding' => '0px'], 
                        'style' => ['padding' => '0px'/*, 'tableLayout' => 'fixed', 'width' => '100%'*/], 'id' => 'activityPane'],
                    'contents' => [
                        'col1' => [
                            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'style' => ['padding' => '0px', 'width' => '50%'], 
                                'labelCellStyle' => ['border' => '1px solid grey', 'padding' => '0px']],
                            'contents' => [
                                'row1' => [
                                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'label' => $tr('Mechanicalload'),
                                        'style' => ['border' => '1px solid grey', 'padding' => '0px'], 'widgetCellStyle' => ['verticalAlign' => 'top', 'padding' => '0px']],
                                    'contents' => [
                                        'row1' => [
                                            'tableAtts' => ['cols' => 4, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'style' => ['padding' => '0px'], 'widgetCellStyle' => ['padding' => '0px'], 'id' => 'runningPane', 'labelWidth' => '80'],
                                            'widgets' => ['duration', 'distance', 'elevationgain'/*, 'elevationloss'*/, 'perceivedload']
                                        ],
                                        'row2' => [
                                            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'labelCellStyle' => ['fontWeight' => 'normal', 'fontStyle' => 'italic'], 'style' => ['padding' => '0px'], 'widgetCellStyle' => ['padding' => '0px'], 'id' => 'intensityPane'],
                                            'widgets' => ['perceivedintensity', 'intensitydetails']
                                        ],
                                        'row3' => [
                                            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'labelCellStyle' => ['fontWeight' => 'normal', 'fontStyle' => 'italic'], 'style' => ['padding' => '0px'], 'widgetCellStyle' => ['padding' => '0px']],
                                            'widgets' => ['activitydetails', 'perceivedstress', 'stressdetails']
                                        ]
                                    ]
                                ],
                                'row3' => [
                                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'orientation' => 'vert', 'label' => $tr('mentalconstraints'), 'style' => ['border' => '1px solid grey', 'padding' => '0px']],
                                    'contents' => [
                                        'row4' => [
                                            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'labelCellStyle' => ['fontWeight' => 'normal', 'fontStyle' => 'italic'], 'style' => ['padding' => '0px'], 'widgetCellStyle' => ['padding' => '0px']],
                                            'widgets' => ['mentaldifficulty', 'mentaldifficultydetails']
                                        ]
                                    ],
                                ]
                            ]
                        ],
                        'col2' => [
                            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'style' => ['padding' => '0px', 'width' => '50%'],
                                'widgetCellStyle' => ['verticalAlign' => 'top', 'padding' => '0px'], 'labelCellStyle' => ['border' => '1px solid grey']],
                            'contents' => [
                                'row1' => [
                                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'label' => $tr('biopsysocialconstraints'),
                                        'style' => ['border' => '1px solid grey', 'padding' => '0px'], 'labelCellStyle' => ['fontWeight' => 'normal', 'fontStyle' => 'italic']],
                                    'widgets' => ['globalsensation', 'globalsensationdetails', 'environment', 'environmentdetails', 'recovery', 'recoverydetails']
                                ]
                            ]
                        ],
                    ]
                ],
                'row3' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'orientation' => 'vert', 'widgetCellStyle' => ['verticalAlign' => 'top', 'padding' => '0px'], 'style' => ['border' => '1px solid grey', 'padding' => '0px'], 'id' => 'noteIndicatorsPane'],
                    'contents' => [
                        'col1' => [
                            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                            'widgets' => ['notecomments']
                        ],
                        'indicators' => [
                            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'style' => ['tableLayout' => 'fixed']],
                            'widgets' => [],
                        ],
                    ]
                    
                ],
            ]
        ];
    }
}
?>
