<?php
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;
//use Zend\Console\Getopt;
use TukosLib\Utils\HtmlUtilities as HUtl;
use TukosLib\TukosFramework as Tfk;

class BuildBlogSiteMap {

    function __construct($parameters){ 
        $user         = Tfk::$registry->get('user');
        $store        = Tfk::$registry->get('store');
        $objectsStore = Tfk::$registry->get('objectsStore');
        try{
            $options = new \Zend_Console_Getopt([/*
                'class=s'      => 'this class name',
                'blogname-s'		=> 'blog or jch/blog',
            */]);
            switch($store->dbName){
                case 'tukosblog':
                        $blogUrl = 'https://tukos.site/' . 'blog';
                        $fullFileName = Tfk::$tukosPhpDir . 'tmp/sitemapblog.xml';
                        break;
                case 'jchblog': 
                        $blogUrl = 'https://tukos.site/' . 'jch/blog';
                        $fullFileName = Tfk::$tukosPhpDir . 'tmp/sitemapjchblog.xml';
                        break;
                default: 
                        Tfk::error_message('on', 'no site map defined for database: ' . $store);
                        return;
            }
            $blogModel = $objectsStore->objectModel('blog');
            $posts = $blogModel->getAll(['where' =>[['col' => 'permission', 'opr' => '<>', 'values' => 'PR'], ['col' => 'permission', 'opr' => '<>', 'values' => 'PL']],  'cols' => ['id', 'updated']]);
            foreach($posts as $post){
                $urls[] = ['tag' => 'url', 'content' => [['tag' => 'loc', 'content' => $blogUrl . '/post?id=' . $post['id']], ['tag' => 'lastmod', 'content' => substr($post['updated'], 0, 10)]]];
            }
            $siteMap = '<?xml version="1.0" encoding="UTF-8"?>' . HUTL::buildHtml(['tag' => 'urlset', 'atts' => 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"', 'content' => $urls]);
            
            file_put_contents($fullFileName, $siteMap);

            echo "<b>sitemap generated: " . $fullFileName;
        }catch(\Zend_Console_Getopt_Exception $e){
            Tfk::error_message('on', 'an exception occured while parsing command arguments : ', $e->getUsageMessage());
        }
    }
}
?>
