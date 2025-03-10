<?php
namespace TukosLib\Web;

use TukosLib\Utils\Utilities as Utl;

class BlogPostStructuredData{
    public static function headerScript($title, $datePublished, $dateModified, $postAuthor){
        $template = <<<EOT
<script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "BlogPosting",
          "headline": "\${title}",
          "datePublished": "\${datePublished}",
          "dateModified": "\${dateModified}",
          "author": {
              "@type": "Person",
              "name": "\${postAuthor}"
            },
        "publisher":
          {
            "name": "Tukos",
            "url": "https://tukos.site/blog/post?id=10005"
          }
        }
        </script>

EOT
        ;
        return Utl::substitute($template, ['title' => $title, 'datePublished' => $datePublished, 'dateModified' => $dateModified, 'postAuthor' => $postAuthor]);
    }
}
?>