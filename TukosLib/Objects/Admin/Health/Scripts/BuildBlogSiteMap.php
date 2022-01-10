<?php
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;
//use Zend\Console\Getopt;
use TukosLib\Utils\HtmlUtilities as HUtl;
use TukosLib\TukosFramework as Tfk;

class BuildBlogSiteMap {

    function __construct($parameters){ 
        $user         = Tfk::$registry->get('user');
        $objectsStore = Tfk::$registry->get('objectsStore');
        try{
            /*$options = new \Zend_Console_Getopt([
                'app-s'		=> 'tukos application name (mandatory if run from the command line, not needed in interactive mode)',
                'db-s'		    => 'tukos application database name (not needed in interactive mode)','class=s'      => 'this class name',
                'parentid-s'   => 'parent id (optional, default is user->id())',
            ]);*/
            $blogModel = $objectsStore->objectModel('blog');
            $posts = $blogModel->getAll(['where' => $user->filterPrivate([], 'blog'), 'cols' => ['id', 'updated']]);
            foreach($posts as $post){
                $urls[] = ['tag' => 'url', 'content' => [['tag' => 'loc', 'content' => 'https://tukos.site/blog/post?id=' . $post['id']], ['tag' => 'lastmod', 'content' => substr($post['updated'], 0, 10)]]];
            }
            $siteMap = '<?xml version="1.0" encoding="UTF-8"?>' . HUTL::buildHtml(['tag' => 'urlset', 'atts' => 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"', 'content' => $urls]);
            
            $fullFileName = Tfk::$tukosPhpDir . 'tmp/sitemap.xml';
            file_put_contents($fullFileName, $siteMap);

            echo "<b>sitemap generated: " . $fullFileName;
        }catch(\Zend_Console_Getopt_Exception $e){
            Tfk::error_message('on', 'an exception occured while parsing command arguments : ', $e->getUsageMessage());
        }
    }
}
?>
