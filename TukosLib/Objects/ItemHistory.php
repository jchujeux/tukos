<?php
/**
 * The history col stored an encoded array of the successive differences for an item, from in reverse chronological order:
 *  - $history[0] stores the differences between current item values and the values they had on previous save,
 *  - etc
 */
namespace TukosLib\Objects;

use ManuelLemos\CharTextDiff;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;



trait ItemHistory {


    protected function expandHistory($item, $atts){// rebuild the full successive previous item values from current value and the successive differences - adds $history[$i]['id'] starting at 1.
        $history = json_decode($item['history'], true);
        ItemsCache::cacheExtra($history, 'compressedhistory', $atts);
        $requiredJsonCols = array_intersect(array_keys($item), $this->jsonCols);
        $requiredTextCols = array_diff(array_intersect(array_keys($item), $this->textColumns), $requiredJsonCols);
        $currentTextColsValues = Utl::getItems($requiredTextCols, $item);
        $currentJsonColsValues = Utl::getItems($requiredJsonCols, $item);

        foreach ($history as $i => &$previousItem){
            if (is_array($previousItem)){
	        	$textCols = array_intersect(array_keys($previousItem), $requiredTextCols);
	            foreach ($textCols as $col){
	                $previousItem[$col] = CharTextDiff::Patch($currentTextColsValues[$col], $previousItem[$col]);
	                $currentTextColsValues[$col] = $previousItem[$col];
	            }
	            $jsonCols = array_intersect(array_keys($previousItem), $requiredJsonCols);
	            foreach ($jsonCols as $col){
	                if (is_string($currentJsonColsValues[$col])){
	                    $currentJsonColsValues[$col] = json_decode($currentJsonColsValues[$col], true);
	                }
	                $previousItem[$col] = Utl::array_merge_recursive_replace($currentJsonColsValues[$col], $previousItem[$col]);
	                $currentJsonColsValues[$col] = $previousItem[$col];
	                $previousItem[$col] = json_encode($previousItem[$col]);
	            }
	            $previousItem['id'] = $i+1;
            }else{
            	$previousItem = [];
            }
        }
        return $history;
    }
    
    public function subHistory($value, $wantedNonEmptyCols, $wantedAdditionalCols=['updated']){// retrieves the history for $wantedNonEmptyCols. $wantedAdditionalCols is added on every returned row
        $subHistory = [];
        if (!empty($value['history'])){
            $wantedNonEmptyKeys    = array_flip($wantedNonEmptyCols);
            $wantedAdditionalKeys = array_flip($wantedAdditionalCols);
            foreach ($value['history'] as $historicValue){
                $subHistoryItem = array_intersect_key(array_filter($historicValue), $wantedNonEmptyKeys);
                if (!empty($subHistoryItem)){
                    $subHistory[] = array_merge($subHistoryItem, array_intersect_key($historicValue, $wantedAdditionalKeys));
                }
            }
        }
        return $subHistory;
    }

    private function addHistory($oldHistory, $valuesToAdd){
        $newHistory = (empty($oldHistory) ? [$valuesToAdd] : array_merge([$valuesToAdd], $oldHistory));
        for ($i = 0, $size = count($newHistory); $i < $size; ++$i){
            $newHistory[$i]['id'] = $i + 1;
        }
        return $newHistory;
    }

    private function compressHistory($item, $id){
        $history = $item['history'];
        $historyToCompress = $history[0];// only the $history added by the last $addHistory needs to be compressed into the differences with $item
        $modifiedJsonCols = array_intersect(array_keys($historyToCompress), $this->jsonCols);
        $modifiedTextCols = array_diff(array_intersect(array_keys($historyToCompress), $this->textColumns), $modifiedJsonCols);
        $oldCompressedHistory = ItemsCache::getExtrafromCache('compressedhistory', $id);

        foreach ($modifiedTextCols as $col){
            $start = microtime(true);
            $historyToCompress[$col] = CharTextDiff::Diff($item[$col], ($historyToCompress[$col] === null ? '' : $historyToCompress[$col]));
            $end = microtime(true);
            $timeCharTextDiff = $end - $start;
            //Feedback::add('CharTextDiff duration: ' . $timeCharTextDiff);
        }
        foreach ($modifiedJsonCols as $col){
            $start = microtime(true);
            if (is_string($item[$col])){ 
                $item[$col] = json_decode($item[$col], true);
            }
            if (is_string($historyToCompress[$col])){
                $historyToCompress[$col] = json_decode($historyToCompress[$col], true);
            }
            // we need to add empty string for cols not in the previous history ($newNotInOld) so that on expandHistory they will be empty.
            if (empty($historyToCompress[$col])){
                $historyToCompress[$col] = array_map(function($item){$item = '';}, $item[$col]);
            }else{
                $oldToNew = Utl::array_diff_assoc_recursive($historyToCompress[$col], $item[$col]);
                $newToOld = Utl::array_diff_assoc_recursive($item[$col], $historyToCompress[$col]);
                $newNotInOld = Utl::array_diff_key_recursive($newToOld, $oldToNew);
                array_walk_recursive($newNotInOld, function(&$item){
                            $item = '';
                    }
                );
                $historyToCompress[$col] = Utl::array_merge_recursive_replace($oldToNew, $newNotInOld);
            }

            $end = microtime(true);
            //Feedback::add('json-diff_recursive duration: ' . $end - $start);
        }
        return json_encode((empty($oldCompressedHistory) ? [$historyToCompress] : array_merge([$historyToCompress], $oldCompressedHistory)));
    }
}
?>
