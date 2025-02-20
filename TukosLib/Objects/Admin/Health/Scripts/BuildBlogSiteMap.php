<?php
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\HtmlUtilities as HUtl;
use TukosLib\TukosFramework as Tfk;

class BuildBlogSiteMap {

    function __construct($parameters){ 
        $store        = Tfk::$registry->get('store');
        $objectsStore = Tfk::$registry->get('objectsStore');
        try{
            $options = new \Zend_Console_Getopt([
                'app-s'      => 'this class name',
                'db-s'      => 'database name',
                'class-s'      => 'this class name',
                'parentid-s'      => 'why ?',
                'rootUrl-s'		=> 'https://tukos.site or https://localhost, omit if interactive',
                'updateall-s' => 'if YES, the update field for all urls is set to today, else it is set to the update field value for each post'
            ]);
            switch($store->dbName){
                case 'tukosblog':
                    $blogUrl = $options->getOption('rootUrl') . '/blog';
                        $fullFileName = Tfk::$tukosPhpDir . 'tmp/sitemapblog.xml';
                        break;
                case 'jchblog':
                    $blogUrl = $options->getOption('rootUrl') . '/jch/blog';
                    $fullFileName = Tfk::$tukosPhpDir . 'tmp/sitemapjchblog.xml';
                    break;
                case 'tukos20':
                    $blogUrl = $options->getOption('rootUrl') . '/tukos20/blog';
                    $fullFileName = Tfk::$tukosPhpDir . 'tmp/sitemaptukos20blog.xml';
                    break;
                default: 
                        Tfk::error_message('on', 'no site map defined for database: ' . $store);
                        return;
            }
            $blogModel = $objectsStore->objectModel('blog');
            $updateAll = $options->getOption('updateall');
            $today = date('Y-m-d');
            $posts = $blogModel->getAll(['where' =>[['col' => 'permission', 'opr' => '<>', 'values' => 'PR'], ['col' => 'permission', 'opr' => '<>', 'values' => 'PL']],  'cols' => ['id', 'updated']]);
            foreach($posts as $post){
                $urls[] = ['tag' => 'url', 'content' => [['tag' => 'loc', 'content' => $blogUrl . '/post?id=' . $post['id']], ['tag' => 'lastmod', 'content' => $updateAll ? $today : substr($post['updated'], 0, 10)]]];
            }
            $siteMap = '<?xml version="1.0" encoding="UTF-8"?>' . HUTL::buildHtml(['tag' => 'urlset', 'atts' => 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"', 'content' => $urls]);
            
            file_put_contents($fullFileName, $siteMap);

            echo "<b>sitemap generated: " . $fullFileName;
        }catch(\Zend_Console_Getopt_Exception $e){
            Tfk::error_message('on', 'an exception occured while parsing command arguments : ', $e->getMessage() . '<br>' . $e->getUsageMessage());
        }
    }
}
?>
