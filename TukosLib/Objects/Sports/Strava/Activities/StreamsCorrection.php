<?php
namespace TukosLib\Objects\Sports\Strava\Activities;

use TukosLib\Objects\Sports\KpisFormulaes as KF;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\ConversionUtilities as CUtl;
use TukosLib\TukosFramework as Tfk;

trait StreamsCorrection {

    public function addCorrectedStreams(&$activity, $forceCorrection = false){
        $massInMovement = 85;
        if (!empty($missingStreamCols = array_keys(array_diff_key(array_flip($this->streamCols), $activity)))){
            $missingStreams = array_filter($this->getOne(['where' => ['stravaid' => $activity['stravaid']], 'cols' => $missingStreamCols]));
            foreach($missingStreams as &$stream){
                $stream = json_decode($stream, true);
            }
            unset($stream);
        }else{
            $missingStreams = [];
        }
        if ($latlngstream = Utl::extractItem('latlngstream', $activity)){// if latlngstream is not present, addCorrectedStreams has already been applied
            $firstTimeCorrection = true;
            unset($missingStreams['latlngstream']);
            $streams = array_merge (array_intersect_key($activity, array_flip($this->streamCols)), $missingStreams);
            $streams['latitudestream'] = CUtl::degreesToRadians(array_column($latlngstream, 0));
            $streams['longitudestream'] = CUtl::degreesToRadians(array_column($latlngstream, 1));
        }else{
            $streams = array_intersect_key($activity, array_flip(array_merge($this->streamCols, ['latitudestream', 'longitudestream'])));
        }
        if (!empty($streams['latitudestream']) && ($forceCorrection || $firstTimeCorrection)){
            $this->latitude = $streams['latitudestream'][0];
            $this->longitude = $streams['longitudestream'][0];
            $streams = array_filter($streams);
            $streamsLength = count($streams['timestream']);
            $streamsToCorrect = $presentStreams = array_keys($streams);
            unset($streamsToCorrect[array_search('timestream', $streamsToCorrect)]); 
            $previousJumpKey = 0;
            $keyOffset = 0;
            $deltaPreviousDistance = 0.0;
            $activity['comments'] = '';
            for ($key = 1; $key < $streamsLength; $key++){
                $deltaDistance = CUtl::latlngRadiansToMeters($streams['latitudestream'][$key-1], $streams['longitudestream'][$key-1], $streams['latitudestream'][$key], $streams['longitudestream'][$key]);
                $deltaTime = $streams['timestream'][$key] - $streams['timestream'][$key-1];
                $deltaAltitude = $streams['altitudestream'][$key]-$streams['altitudestream'][$key-1];
                $powerJump = max(KF::estimatedpower_avg($deltaDistance/1000, 1, $deltaAltitude, $massInMovement, 0.0, 0.015, 0.2) + ($deltaDistance - $deltaPreviousDistance) * $massInMovement * $deltaDistance,0.0);
                if ($powerJump > 2500 || $deltaTime > 1){
                    $activity['comments'] .= 'Time: ' . $streams['timestream'][$key] . ' powerJump: ' . $powerJump . ' timestream index: ' . $key . ' deltaTime: ' . $deltaTime . ' deltaDistance: ' . $deltaDistance
                    . ' deltaPreviousDistance: ' . $deltaPreviousDistance . ' deltaAltitude: ' . $deltaAltitude . ' distance: ' . $streams['distancestream'][$key] . ' altitude: ' . $streams['altitudestream'][$key] . ' velocity: ' . $deltaDistance / $deltaTime . '<br>';
                }
                if($powerJump > 2500 && /*$deltaDistance / */$deltaTime > 1.0){
                    for ($backKey = $key - 2; $backKey > $previousJumpKey; $backKey--){
                        if($streams['altitudestream'][$backKey] !== $streams['altitudestream'][$backKey+1] || $streams['latitudestream'][$backKey] !== $streams['latitudestream'][$backKey+1] || 
                                $streams['longitudestream'][$backKey] !== $streams['longitudestream'][$backKey+1]){
                            break;
                        }
                    }
                    $backKey +=1; // $backKey is the first key from which we need to spread the jump, up to $key included
                    $duration = $streams['timestream'][$key] - $streams['timestream'][$backKey];
                    $activity['comments'] .= "=> applying correction (id duration > 1):  backKey:  $backKey; duration: $duration<br>" ;
                    if ($duration > 1){
                        forEach ($streamsToCorrect as $col){
                            $increment[$col] = ($streams[$col][$key] - $streams[$col][$backKey]) / $duration;
                        }
                        if (empty($streams['timestreamc'])){
                            foreach ($presentStreams as $col){
                                $streams[$col . 'c'][0] = $streams[$col][0];
                            }
                            $previousJumpKey = 1;
                        }
                        $correctedStreamsCount = count($streams['timestreamc']);
                        if (($countToPush = ($offsettedKey = $backKey + $keyOffset) - $correctedStreamsCount + 1) > 0){
                            foreach ($presentStreams as $col){
                                $colc = $col . 'c';
                                $streams[$colc] = array_merge($streams[$colc], array_slice($streams[$col], $previousJumpKey, $countToPush));
                            }
                        }
                        for ($forwardKey = $backKey+1; $forwardKey <= $key; $forwardKey++){
                            $offsettedKey = $forwardKey + $keyOffset;
                            for ($i = $streams['timestream'][$forwardKey-1] + 1; $i <= $streams['timestream'][$forwardKey]; $i++){
                                $streams['timestreamc'][$offsettedKey] = $streams['timestreamc'][$offsettedKey-1] + 1;
                                forEach ($streamsToCorrect as $col){
                                    $streams[$col . 'c'][$offsettedKey] = $streams[$col . 'c'][$offsettedKey-1] + $increment[$col];
                                }
                                $keyOffset += 1;
                                $offsettedKey = $forwardKey + $keyOffset;
                            }
                            $keyOffset += -1;
                        }
                        $previousJumpKey = $key;
                    }else if (!empty($streams['timestreamc'])){
                        $offsettedKey = $key + $keyOffset;
                        foreach ($presentStreams as $col){
                            $streams[$col . 'c'][$offsettedKey] = $streams[$col][$key];
                        }
                    }
                }else if (!empty($streams['timestreamc'])){
                    $offsettedKey = $key + $keyOffset;
                    foreach ($presentStreams as $col){
                        $streams[$col . 'c'][$offsettedKey] = $streams[$col][$key];
                    }
                }
                $deltaPreviousDistance = $deltaDistance;
            }
            if (empty($streams['timestreamc'])){
                foreach ($presentStreams as $col){
                    $streams[$col . 'c'] = null;
                }
            }else{
                $activity['timemovingc'] = count($streams['timestreamc']);
            }
            if ($keyOffset > 0){
                $activity['comments'] .= '=> Added records: ' . $keyOffset . '<br>';
            }
            $activity = array_merge($activity, $streams);
        }
    }
}
?>