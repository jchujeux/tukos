<?php
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Objects\Directory;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class EliminateMfenced {
    
    function __construct($parameters){
        $user         = Tfk::$registry->get('user');
        $store        = Tfk::$registry->get('store');
        try{
            $options = new \Zend_Console_Getopt(
                ['app-s'		=> 'tukos application name (not needed in interactive mode)',
                    'db-s'		    => 'tukos application database name (not needed in interactive mode)',
                    'class=s'      => 'this class name',
                    'parentid-s'   => 'parent id (optional)',
                ]);
            $idsToConsider = $store->query("SELECT id from tukos WHERE comments REGEXP '<mfenced'")->fetchAll(\PDO::FETCH_COLUMN, 0);
            $head = '<head><meta http-equiv="Content-Type" content="text/html; charset=utf8"/>';
            foreach ($idsToConsider as $id){
                $comment = $store->query("SELECT comments from tukos WHERE id = $id")->fetchAll(\PDO::FETCH_COLUMN, 0)[0];
                $dom = @\DOMDocument::loadHTML("$head<body>$comment</body>");
                $mfencedNodes = $dom->getElementsByTagName('mfenced');
                while ($mfencedNodes->length > 0){
                    $mfencedNode = $mfencedNodes->item(0);
                    $mrow = $dom->createElement("mrow");
                    $mrow->appendChild($dom->createElement("mo", $mfencedNode->getAttribute('open')));
                    while ($mfencedNode->childNodes->length > 0){
                        $mrow->appendChild($mfencedNode->childNodes->item(0));
                    }
                    $mrow->appendChild($dom->createElement("mo", $mfencedNode->getAttribute('close')));
                    $mfencedNode->parentNode->replaceChild($mrow, $mfencedNode);
                }
                $transformedHTML = str_replace(['<body>', '</body>'], '', @$dom->saveHTML($dom->getElementsByTagName('body')->item(0)));
                $store->update(['comments' => $transformedHTML], ['where' => ['id' => $id], 'table' => 'tukos']);
            }
            $objValue  = ['name'            => 'EliminateMfenced',
                'parentid'        => $options->parentid    ? $options->parentid    : $user->id(),
                'datehealthcheck' => date('Y-m-d H:i:s'),
                'comments'        => empty($idsToConsider)
                    ? 'No comments field was found with an mfenced tag'
                    : 'the following ids had their mfenced tag eliminated: ' . implode(', ', $idsToConsider)
            ];
             echo $objValue['comments'];
        }catch(\Zend_Console_Getopt_Exception $e){
            Tfk::debug_mode('log', 'an exception occured while parsing command arguments in EliminateMfenced: ', $e->getUsageMessage());
        }
    }
}
?>
