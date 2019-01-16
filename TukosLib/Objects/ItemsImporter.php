<?php
namespace TukosLib\Objects;

use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

trait ItemsImporter{
	public function importItems($query, $atts){
		return $this->addUploadedItems($this->uploadItems());
	}

	public function addUploadedItems($items){
		if (is_array($items)){
			$itemsToPostProcess = [];
			foreach ($items as $item){
				if (is_array($item)){
					$presentIdCols = array_intersect($this->idCols, array_keys($item));
					$insertedId = $this->insert($item, true)['id'];
					$insertedIds[$item['id']] = $insertedId;
					$valuesToPostProcess = [];
					foreach ($presentIdCols as $col){
						if ($item[$col][0] === '*'){
							$valuesToPostProcess[$col] = $item[$col];
						}
					}
					if (!empty($valuesToPostProcess)){
						$itemsToPostProcess[] = ['id' => $insertedId, 'idColsValues' => $valuesToPostProcess];
					}
				}
			}
			foreach ($itemsToPostProcess as $toPostProcess){
				$idColsValues = $toPostProcess['idColsValues'];
				$newValue = ['id' => $toPostProcess['id']];
				foreach ($idColsValues as $idCol => $value){
					$newValue[$idCol] = $insertedIds[$value];
				}
				$this->updateOne($newValue);
			}
			return ['outcome' => 'success'];
		}else{
			Feedback::add($this->tr('baditeminimportedfile'));
			return ['outcome' => 'failure'];
		}
	}

	public function uploadItems(){
		$fileName = $_FILES['uploadedfile']['tmp_name'];
		$fileHandle = fopen($fileName, "rb");
		$isOk = true;
        while (($item = fgets($fileHandle)) && $isOk){
        	$newItem = json_decode($item, true);
        	if ($newItem === null){
        		$isOk = false;
        	}else{
        		$items[] = $newItem;
        	}
        }
        fclose($fileHandle);
        return $isOk ? $items : false;
	}
}
?>