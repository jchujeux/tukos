<?php
namespace TukosLib\Objects\Sports\Workouts;

use TukosLib\Objects\ViewUtils;

trait TemplatesViewMixin {

    public static function levelFiltersActionString(){
        return <<<EOT
var filters = [];
['level1', 'level2', 'level3'].forEach(function(level){
    var filterValue = sWidget.valueOf(level);
    if(filterValue){
        filters.push(['eq', level, filterValue]);
    }
});
return tWidget.store.getRootCollection(filters);
EOT;
    }
    public static function levelFiltersAction(){
        return ['exercisestemplates' => ['collection' => self::levelFiltersActionString()]];
    }
    public function filterWidgets(){
        return [
            'level1'    => ViewUtils::objectSelect($this, 'Level1', 'sptexerciseslevels', ['atts' => ['edit' => ['dropdownFilters' => ['level' => 1], 'onChangeLocalAction' => self::levelFiltersAction()]]]),
            'level2'    => ViewUtils::objectSelect($this, 'Level2', 'sptexerciseslevels', ['atts' => ['edit' => ['dropdownFilters' => ['level' => 2], 'onChangeLocalAction' => self::levelFiltersAction()]]]),
            'level3'    => ViewUtils::objectSelect($this, 'Level3', 'sptexerciseslevels', ['atts' => ['edit' => ['dropdownFilters' => ['level' => 3], 'onChangeLocalAction' => self::levelFiltersAction()]]]),
        ];
	}
	public function templatesSubObjects(){
		return [
			'warmuptemplates' => [
				'object' => 'sptworkoutsstages',
				'atts' => ['title' => $this->tr('warmuptemplates'), 'storeType' => 'LazyMemoryTreeObjects',  'dndParams' => [ 'copyOnly' => true, 'selfAccept' => false]],
				'filters' => ['stagetype' => 'warmup'],
				 'allDescendants' => 'hasChildrenOnly',
			],
			'mainactivitytemplates' => [
				'object' => 'sptworkoutsstages',
				'atts' => ['title' => $this->tr('mainactivitytemplates'),/* 'storeType' => 'LazyMemoryTreeObjects', */  'dndParams' => [ 'copyOnly' => true, 'selfAccept' => false]],
				'filters' => ['stagetype' => 'mainactivity'],
				'allDescendants' => true
			],
			'warmdowntemplates' => [
				'object' => 'sptworkoutsstages',
				'atts' => ['title' => $this->tr('warmdowntemplates'), /*'storeType' => 'LazyMemoryTreeObjects',*/ 'dndParams' => [ 'copyOnly' => true, 'selfAccept' => false]],
				'filters' => ['stagetype' => 'warmdown'],
				 'allDescendants' => true, 
			],
			'varioustemplates' => [
				'object' => 'sptworkoutsstages',
				'atts' => ['title' => $this->tr('varioustemplates'), /*'storeType' => 'LazyMemoryTreeObjects',*/ 'dndParams' => [ 'copyOnly' => true, 'selfAccept' => false]],
				'filters' => ['stagetype' => 'various'],
				 'allDescendants' => true, 
			],
			'exercisestemplates' => [
				'object' => 'sptexercises',
				'atts' => ['title' => $this->tr('exercisestemplates'), 'style' => ['maxHeight' => '800px'], 'dndParams' => [ 'copyOnly' => true, 'selfAccept' => false]],
				'filters' => [],
				'allDescendants' => true,
        	],
		];
	}
	
	public function filterWidgetsExceptionCols(){
		return['grid' => ['level1', 'level2', 'level3'], 'get' => ['level1', 'level2', 'level3'], 'post' => ['level1', 'level2', 'level3']];
	}
}
?>

