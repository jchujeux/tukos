<?php
namespace TukosLib\Objects;

use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;

use TukosLib\TukosFramework as Tfk;

trait Questionnaire {
/* extended questionnaire needs to provide $mappping array as per example below:
	protected static $mapping = [
		0 => ['object' => 'physiocdcs', 'col' => 'questionnairetime', 'method' => 'dateTime'],
		1 => ['object' => 'physiopatients', 'col' => 'name', 'method' => 'capitalize'],
		2 => ['object' => 'physiopatients', 'col' => 'firstname', 'method' => 'capitalize'],
		...
	];
*/
	protected $yesOrNoOptions = ['yes', 'no'];
	
	public function storeNewQuestionnaires($values, $initialValue, $domainObjectName, $peopleObjectName){// start from last row, and stop as soon as an existing domain item is encountered
		$values = array_reverse($values, true);
		array_pop($values);
		foreach ($values as $row){
			$items = $this->clean($row);
			$domainItem = $items[$domainObjectName];
			$existingObjectItem = $this->getOne(['where' => ['questionnairetime' => $domainItem['questionnairetime']], 'cols' => ['id']]);
			if (!empty($existingObjectItem)){
				break;
			}else{
				if (empty($peopleModel)){
					$objectsStore = Tfk::$registry->get('objectsStore');
					$peopleModel = $objectsStore->objectModel($peopleObjectName);
				}
				$peopleItem = $items[$peopleObjectName];
				$existingPeople = $possiblePeople = false;
				$foundPeople = $peopleModel->getAll(['where' => ['name' => $peopleItem['name'], 'firstname' => $peopleItem['firstname']], 'cols' => ['id', 'birthdate']]);
				foreach ($foundPeople as $people){
					if (empty($people['birthdate']) || empty($peopleItem['birthdate'])){
						if (empty($possiblePeople)){
							$possiblePeople = $people;
						}
					}else if ($people['birthdate'] === $peopleItem['birthdate']){
						$existingPeople = $people;
						break;
					}
				}
				$existingPeople = ($existingPeople !== false) ? $existingPeople : (($possiblePeople !== false) ? $possiblePeople : []);
				if (empty($existingPeople)){
					$insertedPeople = $peopleModel->insert($peopleItem, true);
					$insertedPeopleIds[] = $peopleId = $insertedPeople['id'];
				}else{
					$peopleItem['id'] = $existingPeople['id'];
					$updatedPeople = $peopleModel->updateOne($peopleItem);
					if (!empty($updatedPeople)){
						$updatedPeopleIds[] = $peopleId = $updatedPeople['id'];
					}else{
						$didNotUpdatePeopleIds[] = $peopleId = $peopleItem['id'];
					}
				}
				$domainItem['parentid'] = $peopleId;
				$newDomainItem = $this->insert(array_merge($initialValue, $domainItem), true);
				$insertedDomainIds[] = $newDomainItem['id'];
			}
		}
		if (!empty($insertedPeopleIds)){
			Feedback::add([$this->tr('inserted' . $peopleObjectName . 'ids') => $insertedPeopleIds]);
		}
		if (!empty($updatedPeopleIds)){
			Feedback::add([$this->tr('updated' . $peopleObjectName . 'ids') => $updatedPeopleIds]);
		}
		if (!empty($didNotUpdatePeopleIds)){
			Feedback::add([$this->tr('didnotupdate' . $peopleObjectName . 'ids') => $didNotUpdatePeopleIds]);
		}
		if (!empty($insertedDomainIds)){
			Feedback::add([$this->tr('inserted' . $domainObjectName . 'ids') => $insertedDomainIds]);
		}else{
			Feedback::add($this->tr('nonewquestionnaireanswer'));
		}
	}
	
	public function clean($row){
		foreach($row as $col => &$value){
			$map = empty(self::$mapping[$col]) ? false : self::$mapping[$col];
			if ($map){
				$method = empty($map['method']) ? false : $map['method'];
				if (!empty($value)){
					if (isset($map['append']) && isset($items[$map['object']][$map['col']])){
						$items[$map['object']][$map['col']] .= Utl::utf8($map['append']) . ($method ? $this->$method($value) : $value);
					}else{
						$items[$map['object']][$map['col']] = (empty($map['append']) ? '' : Utl::utf8($map['append'])) . ($method ? $this->$method($value) : $value);
					}
				}
			}
		}
		return $items;
	}
	
	public function phoneNumber($value){
		return (empty($value) || array_search($value[0], ['0', '+', '(']) > -1) ? $value : '0' . $value;
	}
	public function dateTime($value){
		return date_format(date_create_from_format('d/m/Y H:i:s', $value), 'Y-m-d H:i:s');
	}
	public function date($value){
		return date_format(date_create_from_format('d/m/Y', $value), 'Y-m-d');
	}
	public function height($value){
		$height = floatVal(str_replace([',', 'm', 'M', ' '], ['.', '.', '.', ''], $value));
		return number_format($height > 100 ? $height / 100 : $height, 2, '.', '');
	}
	public function weight($value){
		return number_format(floatVal(str_replace([',', 'm', 'M', ' '], ['.', '.', '.', ''], $value)), 0, '.', '');
	}
	public function Capitalize($value){
		return mb_convert_case(trim($value), MB_CASE_TITLE);
	}
	public function yesOrNo($value){
		$convert = ['OUI' => 'yes', 'NON' => 'no'];
		return $convert[strtoupper(trim($value))];
	}
	public function sex($value){
		$convert = ['Masculin' => 'male', utf8_encode('Féminin') => 'female'];
		return $convert[$value];
	}
}
?>