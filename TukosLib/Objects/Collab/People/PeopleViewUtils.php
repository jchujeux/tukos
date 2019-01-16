<?php
namespace TukosLib\Objects\Collab\People;


trait PeopleViewUtils {

	protected function setPeopleView(){
		
		$this->dataWidgets['parentid']['atts']['edit']['onChangeServerAction'] = [
			'inputWidgets' => ['parentid'],
			'urlArgs' => ['query' => ['params' => json_encode(['getOne' => 'getPeopleChanged'])]],
		];
		$this->dataWidgets['parentid']['atts']['edit']['onWatchLocalAction'] = ['value' => [
			'parentid' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' =>
				"var disabled = newValue ? true : false;\n sWidget.form.setWidgets({disabled: {" . substr(array_reduce($this->model->peopleCols, function($carry, $col){return $carry . $col . ': disabled,';}), 0, -1) . "}});  return true;"
		]]]];
	}
}

?>