<?php
namespace TukosLib\Objects\Views;

trait LocalActions{

    function watchLocalAction($att){
        return ['value' => [
                $att  => ['localActionStatus' => [
                        'action' => "lang.setObject('customization.widgetsDescription.' + sWidget.pane.attachedWidget.widgetName + '.atts.dialogDescription.paneDescription.widgetsDescription." . $att . ".atts.value', newValue, sWidget.pane.form);return true;",
                ]],
                'update' => ['hidden' => [
                        'action' => "return false;"
                ]],
        ]];
    }
    function watchCheckboxLocalAction($att){
        return ['checked' => [
                $att  => ['localActionStatus' => [
                        'action' => "lang.setObject('customization.widgetsDescription.' + sWidget.pane.attachedWidget.widgetName + '.atts.dialogDescription.paneDescription.widgetsDescription." . $att . ".atts.checked', newValue, sWidget.pane.form);return true;",
                ]],
                'update' => ['hidden' => ['action' => "return false;"]],
        ]];
    }
}
?>
