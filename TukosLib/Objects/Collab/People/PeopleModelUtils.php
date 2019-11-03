<?php
namespace TukosLib\Objects\Collab\People;

use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

trait PeopleModelUtils {

	public function inGetOne($atts, $jsonColsPaths = [], $jsonNotFoundValue=null){
		$peopleCols = array_intersect($this->peopleCols, $atts['cols']);
		$atts['cols'] = array_diff($atts['cols'], $this->peopleCols);
		$result = parent::getOne($atts, $jsonColsPaths, $jsonNotFoundValue);
		if ($result['parentid'] && !empty($peopleCols)){
			$peopleModel = Tfk::$registry->get('objectsStore')->objectModel('people');
			return array_merge($result, $peopleModel->getOne(['where' => ['id' => $result['parentid']], 'cols' => $peopleCols]));
		}else{
			return $result;
		}
	}
	public function updateOne($newValues, $atts=[], $insertIfNoOld = false, $jsonFilter=false, $init = true){
		$newValues = $this->handlePeopleCols($newValues);
		parent::updateOne($newValues, $atts, $insertIfNoOld, $jsonFilter);
	}
	public function insert($values, $init = false, $jsonFilter = false, $reference = null){
		$values = $this->handlePeopleCols($values);
		return parent::insert($values, $init, $jsonFilter, $reference);
	}
	
	protected function handlePeopleCols($values){
		$peopleValues = array_intersect_key($values, array_flip($this->peopleCols));
		$values = array_diff($values, $peopleValues);
		if (empty($values['parentid']) && §empty($peopleValues)){
			$peopleModel = Tfk::$registry->get('objectsStore')->objectModel('people');
			$existingPeople = $peopleModel->getOne(['where' => array_filter($peopleValues), 'cols' => ['id']]);
			if (empty($existingPeople)){
				$values['parentid'] = $peopleModel->insert($peopleValues)['id'];
				Feedback::add($this->tr('newpeoplecreated'));
			}else{
				$values['parentid'] = $existingPeople['id'];
				Feedback::add($this->tr('peoplealreadyexists'));
			}
		}
		return $values;
	}
	
	public function getPeopleChanged($atts){
		$peopleModel = Tfk::$registry->get('objectsStore')->objectModel('people');
		return $peopleModel->getOne(['where' => ['id' => $atts['where']['parentid']], 'cols' => $this->peopleCols]);
	}
	
}

?>