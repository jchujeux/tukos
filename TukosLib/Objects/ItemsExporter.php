<?php
namespace TukosLib\Objects;

use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

trait ItemsExporter{

	public function exportItems($query, $atts){		
		return $this->downloadFile($this->buildFile($atts), 'plain/txt', $query['downloadtoken']);
	}

	public function buildFile($atts){
		$idsToExport = json_decode($atts['ids'], true);
		$visibleCols = json_decode($atts['visibleCols'], true);
		$fileName = Tfk::tukosTmpDir . uniqId() . '.txt';
		$colsToDownload = array_diff(isset($visibleCols['id']) ? $visibleCols : array_merge($visibleCols, ['id']), ['0', 'created', 'creator', 'updated', 'updator']);
		$items = $this->getAll(['where' => [['col' => 'id', 'opr' => 'IN', 'values' => $idsToExport]], 'cols' => $colsToDownload]);
		if (!empty($items)){
			$ids = array_flip(array_column($items, 'id'));
			$i = 1;
			foreach ($ids as &$id){
				$id = '*' . $i;
				$i += 1;
			}
			$presentIdCols = array_intersect($this->idCols, $colsToDownload);
			foreach($items as &$item){
				foreach ($presentIdCols as $col){
					$idColValue = $item[$col];
					if(empty($idColValue) || (empty($ids[$idColValue]) && $idColValue >= 10000)){
						unset($item[$col]);
					}else{
						$item[$col] = empty($ids[$idColValue]) ? $idColValue : $ids[$idColValue];
					}
				}
				$item['id'] = $ids[$item['id']];
			}
			unset($item);
			$fileHandle = fopen($fileName, "w");
			foreach($items as $item){
				fwrite($fileHandle, json_encode($item) . "\n");
			}
			fclose($fileHandle);
		}else{
			Feedback::add($this->tr('itemsnotfound'));
		}
		return $fileName;
	}

	public function downloadFile($fileName, $contentType, $downloadToken){
		if ($fileHandle = fopen($fileName, 'r')){
			header("Content-Type: " . $contentType);
			header("Content-length:" . filesize($fileName));
			header("Content-Disposition: attachment; filename=" . basename($fileName));
			header("Content-Description: PHP Generated Data");
			setcookie('downloadtoken', $downloadToken, 0, '/');		  
			while (!feof($fileHandle)){
				$buffer = fread($fileHandle, 2048);
				echo $buffer;
			}
			fclose($fileHandle);
			unlink($fileName);
			return false;
		}else{
			Feedback::add($this->tr('errorgeneratingfile'));
			return [];
		}
	}
}
?>