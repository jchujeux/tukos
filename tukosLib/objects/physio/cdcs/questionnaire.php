<?php
namespace TukosLib\Objects\Physio\Cdcs;

use TukosLib\Utils\Feedback;
use TukosLib\Objects\Questionnaire as ParentQuestionnaire;
use TukosLib\TukosFramework as Tfk;

trait Questionnaire {
	
	use ParentQuestionnaire;
	
	protected static $mapping = [
		0 => ['object' => 'physiocdcs', 'col' => 'questionnairetime', 'method' => 'dateTime'],
		1 => ['object' => 'physiopatients', 'col' => 'name', 'method' => 'capitalize'],
		2 => ['object' => 'physiopatients', 'col' => 'firstname', 'method' => 'capitalize'],
		3 => ['object' => 'physiopatients', 'col' => 'profession'],
		4 => ['object' => 'physiopatients', 'col' => 'telmobile', 'method' => 'phoneNumber'],
		6 => ['object' => 'physiopatients', 'col' => 'birthdate', 'method' => 'date'],
		7 => ['object' => 'physiopatients', 'col' => 'height', 'method' => 'height'],
		8 => ['object' => 'physiopatients', 'col' => 'weight', 'method' => 'weight'],
		9 => ['object' => 'physiocdcs', 'col' => 'clubyesorno', 'method' => 'yesOrNo'],
		10 => ['object' => 'physiocdcs', 'col' => 'specialty'],
		11 => ['object' => 'physiocdcs', 'col' => 'specialtysince'],
		12 => ['object' => 'physiocdcs', 'col' => 'trainingweek'],
		13 => ['object' => 'physiocdcs', 'col' => 'sportsgoal'],
		14 => ['object' => 'physiocdcs', 'col' => 'reason'],
		15 => ['object' => 'physiocdcs', 'col' => 'painstart'],
		16 => ['object' => 'physiocdcs', 'col' => 'painwhere'],
		17 => ['object' => 'physiocdcs', 'col' => 'painwhen'],
		18 => ['object' => 'physiocdcs', 'col' => 'painhow'],
		19 => ['object' => 'physiocdcs', 'col' => 'painevolution'],
		20 => ['object' => 'physiocdcs', 'col' => 'paindailyyesorno', 'method' => 'yesOrNo'],
		21 => ['object' => 'physiocdcs', 'col' => 'recentchanges'],
		22 => ['object' => 'physiocdcs', 'col' => 'orthosolesyesorno', 'method' => 'yesOrNo'],
		23 => ['object' => 'physiocdcs', 'col' => 'orthosolessince'],
		24 => ['object' => 'physiocdcs', 'col' => 'orthosoleseaseyesorno', 'method' => 'yesOrNo'],
		25 => ['object' => 'physiocdcs', 'col' => 'shoes'],
		26 => ['object' => 'physiocdcs', 'col' => 'antecedents'],
		27 => ['object' => 'physiocdcs', 'col' => 'exams'],
		28 => ['object' => 'physiopatients', 'col' => 'email'],
		29 => ['object' => 'physiocdcs', 'col' => 'clubname'],
		30 => ['object' => 'physiopatients', 'col' => 'sex', 'method' => 'sex'],
		31 => ['object' => 'physiocdcs', 'col' => 'breakfastyesorno', 'method' => 'yesOrNo'],
		32 => ['object' => 'physiocdcs', 'col' => 'vegetables'],
		33 => ['object' => 'physiocdcs', 'col' => 'fruits'],
		34 => ['object' => 'physiocdcs', 'col' => 'friedfat'],
		35 => ['object' => 'physiocdcs', 'col' => 'water'],
		36 => ['object' => 'physiocdcs', 'col' => 'alcool'],
		37 => ['object' => 'physiocdcs', 'col' => 'snack'],
		38 => ['object' => 'physiocdcs', 'col' => 'foodrace'],
	];
/*
	protected $yesOrNoOptions = ['yes', 'no'];
	
	public function storeNewQuestionnaires($values, $initialValue){// start from last row, and stop as soon as an existing CDC item is encountered
		$values = array_reverse($values, true);
		array_pop($values);
		foreach ($values as $row){
			$items = $this->clean($row);
			$cdcItem = $items['physiocdcs'];
			$existingCdcItem = $this->getOne(['where' => ['questionnairetime' => $cdcItem['questionnairetime']], 'cols' => ['id']]);
			if (!empty($existingCdcItem)){
				break;
			}else{
				if (empty($patientModel)){
					$objectsStore = Tfk::$registry->get('objectsStore');
					$patientModel = $objectsStore->objectModel('physiopatients');
				}
				$patientItem = $items['physiopatients'];
				$existingPatient = $possiblePatient = false;
				$foundPatients = $patientModel->getAll(['where' => ['name' => $patientItem['name'], 'firstname' => $patientItem['firstname']], 'cols' => ['id', 'birthdate']]);
				foreach ($foundPatients as $patient){
					if (empty($patient['birthdate']) || empty($patientItem['birthdate'])){
						if (empty($possiblePatient)){
							$possiblePatient = $patient;
						}
					}else if ($patient['birthdate'] === $patientItem['birthdate']){
						$existingPatient = $patient;
						break;
					}
				}
				$existingPatient = ($existingPatient !== false) ? $existingPatient : (($possiblePatient !== false) ? $possiblePatient : []);
				if (empty($existingPatient)){
					$insertedPatient = $patientModel->insert($patientItem, true);
					$insertedPatientIds[] = $patientId = $insertedPatient['id'];
				}else{
					$patientItem['id'] = $existingPatient['id'];
					$updatedPatient = $patientModel->updateOne($patientItem);
					if (!empty($updatedPatient)){
						$updatedPatientIds[] = $patientId = $updatedPatient['id'];
					}else{
						$didNotUpdatePatientIds[] = $patientId = $patientItem['id'];
					}
				}
				$cdcItem['parentid'] = $patientId;
				$newCdcItem = $this->insert(array_merge($initialValue, $cdcItem), true);
				$insertedCdcIds[] = $newCdcItem['id'];
			}
		}
		if (!empty($insertedPatientIds)){
			Feedback::add([$this->tr('insertedpatientids') => $insertedPatientIds]);
		}
		if (!empty($updatedPatientIds)){
			Feedback::add([$this->tr('updatedpatientids') => $updatedPatientIds]);
		}
		if (!empty($didNotUpdatePatientIds)){
			Feedback::add([$this->tr('didnotupdatepatientids') => $didNotUpdatePatientIds]);
		}
		if (!empty($insertedCdcIds)){
			Feedback::add([$this->tr('insertedcdcids') => $insertedCdcIds]);
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
					$items[$map['object']][$map['col']] = $method ? $this->$method($value) : $value;
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
*/
}
?>