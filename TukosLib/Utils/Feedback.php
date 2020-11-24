<?php

namespace TukosLib\Utils;

use TukosLib\TukosFramework as Tfk;

class Feedback {
    private static $feedback = []; 
    /*
     * Examples of use:
     *  Feedback::add('Everything went OK');
     *  Feedback::add(['Initial value: ', 1]);
     *  Feedback::add(['initial ids: ', [1, 2, 3, 4]]); 
     *  Feedback::add([['All tasks are closed'], ['You can relax now'], ['final ids: ', [10, 20, 30, 40]], 'Bye now!']);
     *  Feedback::get() returns 
     *     ['everything went OK', 'Initial value: 1', 'initial ids: 1, 2, 3, 4', 'All tasks are closed', 'You can relax now', 
     *      'final ids: 10, 20, 30, 40', Bye now']
     */
    public static function add($feedback){
        if (is_string($feedback)){
            self::$feedback[] = $feedback;
        }else if (is_array($feedback)){
            foreach ($feedback as $key => $feedbackItem){
                if (is_int($key)){
                    self::add($feedbackItem);
                }else{
                    if (is_array($feedbackItem)){
                        self::$feedback[] = $key . ': ' . implode(', ', $feedbackItem);
                    }else{
                        self::$feedback[] = $key . ': ' . $feedbackItem;
                    }
                }
            }
        }
    }
    
    public static function addErrorCode($code){
        self::add(Tfk::tr('ErrorCode') . ': ' . $code . '. ' . Tfk::tr('contactsupport'));
    }
    public static function get(){
        return self::$feedback;
    }

    public static function reset(){
        self::$feedback = [];
    }
}
?>
