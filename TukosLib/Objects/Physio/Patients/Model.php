<?php
namespace TukosLib\Objects\Physio\Patients;

use TukosLib\Objects\Collab\People\Model as PeopleModel;
use TukosLib\Utils\Utilities as Utl;


class Model extends PeopleModel {

	protected $sexOptions = ['male', 'female'];
    protected $lateralityOptions = ['left', 'right'];
    protected $corpulenceOptions = ['thin', 'normal', 'fat', 'obese'];
    protected $morphotypeOptions = ['endomorph', 'mesomorph', 'ectomorph'];

    public function getOne ($atts, $jsonColsPaths = [], $jsonNotFoundValue=null, $absentColsFlag = 'forbid'){
        $computedCols = ['age' => ['birthdate'], 'imc' => ['height', 'weight']];
        Utl::adjustSourceCols($atts['cols'], $toAddComputedCols, $addedSourceCols, $computedCols);
        $item = parent::getOne($atts, $jsonColsPaths, $jsonNotFoundValue, $absentColsFlag);
        if (empty($toAddComputedCols)){
            return $item;
        }else{
            if (in_array('age', $toAddComputedCols)){
                if (!empty($item['birthdate'])){
                    $item['age'] = (new \DateTime($item['birthdate']))->diff(new \DateTime())->format('%Y');
                }else{
                    $item['age'] = '';
                }
            }
            if (in_array('imc', $toAddComputedCols) && !(empty($item['height']) || empty($item['weight']))){
                $height = floatval($item['height']);
                $weight = floatval($item['weight']);
                $item['imc'] = ($height > 0.0 && $weight > 0.0) ? round($weight / $height / $height) : '';
            }else{
                $item['imc'] = '';
            }
            return array_diff_key($item, array_flip($addedSourceCols));
        }
    }
}
?>
