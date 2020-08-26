<?php
namespace TukosLib\Objects\BusTrack\Categories;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;

class Model extends AbstractModel {
    
    protected $segmentOptions = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'MULTI'];
    protected $attributeTypeOptions = ['name', 'comments', 'segment'];
    
    function __construct($objectName, $translator=null){
        $colsDefinition =  [
            'vatfree'      => "VARCHAR(31) DEFAULT NULL",
            'vatrate' 		=> "DECIMAL (5, 4)",
            'applytocustomers' => "VARCHAR(7) DEFAULT NULL",
            'applytosuppliers' => "VARCHAR(7) DEFAULT NULL",
            'filterpriority' => "MEDIUMINT DEFAULT NULL",
            'criteria' => "longtext DEFAULT NULL"
        ];
        parent::__construct($objectName, $translator, 'bustrackcategories', ['parentid' => ['organizations']], ['criteria'], $colsDefinition, [], [], ['custom']);
        $this->defaultVatRates = [];
    }
    public function defaultVatRate($organization = ''){
        if (!isset($this->defaultVatRates[$organization])){
            $result =  $this->getOne(['where' => ['name' => 'other', 'parentid' => $organization], 'cols' => ['vatrate']]);
            if (empty($result)){
                Feedback::add($this->tr('othervatratenotfound'));
                $this->defaultVatRates[$organization] = 0.085;
            }else{
                $this->defaultVatRates[$organization] = $result['vatrate'];
            }
        }
        return $this->defaultVatRates[$organization];
    }
    public function _getCategories($organization){
        if (!isset($this->categories[$organization])){
            $items = $this->getAll(['where' => ['parentid' => $organization], 'cols' => ['id', 'name', 'vatfree', 'vatrate', 'applytocustomers', 'applytosuppliers', 'filterpriority', 'criteria'], 'orderBy' => ['filterpriority' =>  'ASC']]);
            $result = [];
            foreach ($items as $item){
                $id = Utl::getItem('id', $item);
                $result[$id] = $item;
            }
            $result[0] = ['name' => 'noncategorized', 'vatfree' => '', 'vatrate' => !empty($otherRate = Utl::getItem($this->tr('other'), $result)) ? $otherRate : $this->defaultVatRate(),
                'applytosuppliers' => false, 'applytocustomers' => false, 'filterpriority' => 0];
            $this->categories[$organization] = $result;
        }
        return $this->categories[$organization];
    }
    public function getCategories($organization, $customersOrSuppliers = ""){
        $categories = $this->_getCategories($organization);
        if ($customersOrSuppliers){
            return array_filter($categories, function($category) use ($customersOrSuppliers){
                return $category["applyto$customersOrSuppliers"];
            });
        }else{
            return $categories;
        }
    }
    public function vatFree($organization, $categoryId){
        return $this->_getCategories($organization)[$categoryId]['vatfree'];
    }
    public function vatRate($organization, $categoryId){
        return $this->_getCategories($organization)[$categoryId]['vatrate'];
    }
    public function name($organization, $categoryId){
        return $this->_getCategories($organization)[$categoryId]['name'];
    }
    public function tName($organization, $categoryId){
        return $this->tr($this->_getCategories($organization)[$categoryId]['name']);
    }
    public function criterias($organization, $categoryId){
        return json_decode(Utl::getItem('criteria', $this->getCategories($organization)[$categoryId], '[]'), true);
    }
    
}
?>