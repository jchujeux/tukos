<?php
namespace TukosLib\Objects\Sports\Sessions;

use TukosLib\Objects\ViewUtils;

trait TemplatesViewMixin {

	public function filterWidgets(){
        return [
            'level1filter'    => ViewUtils::storeSelect('level1', $this, 'Level1', ['atts' => ['edit' => [
            	'onChangeLocalAction' => [
					'level2filter'  => ['value' => "return '';" ],
					'level3filter'  => ['value' => "return '';" ],
            		'exercisestemplates' => ['collection' => 
            			"var filters = newValue ? [['eq', 'level1', newValue]] : [];" .
            			"return tWidget.store.getRootCollection(filters);"
            		]
            	]]]]),
            'level2filter'    => ViewUtils::storeSelect('level2', $this, 'Level2', ['atts' => ['edit' => [
            	'dropdownFilters' => ['level1' => '@level1filter'],
            	'onChangeLocalAction' => [
					'level3filter'  => ['value' => "return '';" ],
            		'exercisestemplates' => ['collection' => 
            			"var level1 = tWidget.form.valueOf('level1'), filters = level1 ? [['eq', 'level1', newValue]] : [];" .
            			"if (newValue){filters.push(['eq', 'level2', newValue]);}" .
            			"return tWidget.store.getRootCollection(filters);"
            		]
            	]
            ]]]),
            'level3filter'    => ViewUtils::storeSelect('level3', $this, 'Level3', ['atts' => ['edit' => [
            	'dropdownFilters' => ['level1' => '@level1filter'],
            	'onChangeLocalAction' => [
            		'exercisestemplates' => ['collection' => 
            			"var level1 = tWidget.form.valueOf('level1'), filters = level1 ? [['eq', 'level1', newValue]] : [];" .
            			"var level2 = tWidget.form.valueOf('level2');" .
            			"if (level2){filters.push(['eq', 'level2', level2]);}" .
            			"if (newValue){filters.push(['eq', 'level3', newValue]);}" .
            			"return tWidget.store.getRootCollection(filters);"
            		]
            	]
            ]]]),
        ];
	}

	public function templatesSubObjects(){
		return [
			'warmuptemplates' => [
				'object' => 'sptsessionsstages',
				'atts' => ['title' => $this->tr('warmuptemplates'), 'storeType' => 'LazyMemoryTreeObjects',  'dndParams' => [ 'copyOnly' => true, 'selfAccept' => false]],
				'filters' => ['stagetype' => 'warmup'],
				 'allDescendants' => 'hasChildrenOnly',
			],
			'mainactivitytemplates' => [
				'object' => 'sptsessionsstages',
				'atts' => ['title' => $this->tr('mainactivitytemplates'),/* 'storeType' => 'LazyMemoryTreeObjects', */  'dndParams' => [ 'copyOnly' => true, 'selfAccept' => false]],
				'filters' => ['stagetype' => 'mainactivity'],
				'allDescendants' => true
			],
			'warmdowntemplates' => [
				'object' => 'sptsessionsstages',
				'atts' => ['title' => $this->tr('warmdowntemplates'), /*'storeType' => 'LazyMemoryTreeObjects',*/ 'dndParams' => [ 'copyOnly' => true, 'selfAccept' => false]],
				'filters' => ['stagetype' => 'warmdown'],
				 'allDescendants' => true, 
			],
			'varioustemplates' => [
				'object' => 'sptsessionsstages',
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
		return['grid' => ['level1filter', 'level2filter', 'level3filter'], 'get' => ['level1filter', 'level2filter', 'level3filter'], 'post' => ['level1filter', 'level2filter', 'level3filter']];
	}
}
?>

