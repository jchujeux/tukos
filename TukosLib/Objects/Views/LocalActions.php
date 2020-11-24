<?php
namespace TukosLib\Objects\Views;

trait LocalActions{
    function watchLocalActionString(){
        return <<<EOT
lang.setObject('customization.widgetsDescription.' + sWidget.pane.attachedWidget.widgetName + '.atts.dialogDescription.paneDescription.widgetsDescription.' + sWidget.widgetName + '.atts.value', 
    sWidget.get('serverValue') || sWidget.get('value'), sWidget.pane.form);
return true;
EOT;
    }
    function watchLocalActionTemplate($widgetName, $localActionString){
        return ['value' => [
            $widgetName  => ['localActionStatus' => [
                'action' => $localActionString
            ]],
            'update' => ['hidden' => ['action' => "return false;"]],
        ]];
        
    }
    function watchLocalAction($widgetName){
        return $this->watchLocalActionTemplate($widgetName, $this->watchLocalActionString());
    }
    function watchCheckboxLocalAction($widgetName){
        return ['checked' => [
                $widgetName  => ['localActionStatus' => ['action' => <<<EOT
lang.setObject('customization.widgetsDescription.' + sWidget.pane.attachedWidget.widgetName + '.atts.dialogDescription.paneDescription.widgetsDescription." . $widgetName . ".atts.checked', newValue, sWidget.pane.form);
return true
EOT
                ]],
                'update' => ['hidden' => ['action' => "return false;"]],
        ]];
    }
}
?>
