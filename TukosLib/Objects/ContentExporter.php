<?php
namespace TukosLib\Objects;

use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\HttpUtilities;
use Html2Text\Html2Text;
use TukosLib\TukosFramework as Tfk;

trait ContentExporter {
	protected $sendingOptions = ['appendtobody', 'asattachment'];
	protected $fileName;
	protected $htmlHeaderScript = 
		"<script>function subst() {" .
			"var vars={};var x=window.location.search.substring(1).split('&');" .
			"for (var i in x) {" .
				"var z=x[i].split('=',2);" .
				"vars[z[0]] = unescape(z[1]);}" .
				"var x=['frompage','topage','page','webpage','section','subsection','subsubsection'];" .
				"for (var i in x) {" .
					"var y = document.getElementsByClassName(x[i]);" .
					"for (var j=0; j<y.length; ++j) y[j].textContent = vars[x[i]];" .
		"}}</script>";
		//'<script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>' .
	protected $mathMLHeaderScript = <<<EOT
<script type="text/x-mathjax-config">
MathJax.Hub.Config({
	messageStyle: "none",
    }
);
</script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.1/MathJax.js?config=MML_CHTML" defer crossorigin="anonymous"></script>
EOT
	;
	protected $mathMLWkhtmlToPdfOptions = <<<EOT
  --run-script "MathJax.Hub.Config({NativeMML:{scale:200},CommonHTML:{scale:200}});MathJax.Hub.Queue(['Rerender',MathJax.Hub],function(){window.status='finished'})" --window-status finished --no-stop-slow-scripts 
EOT
	;
	protected $tukosHeader = '<title>${title}</title><link rel="stylesheet" href="${dojoBaseLocation}dijit/themes/claro/claro.css" media="screen">';
	protected $tukosFormsBodyScripts = '
<script>var dojoConfig ={
            baseUrl: "", isDebug: false, async: true, locale: "en-en",
            packages: [{"name": "dojo", "location": "${dojoBaseLocation}dojo"}, {"name": "dijit", "location": "${dojoBaseLocation}dijit"}, {"name": "dojox", "location": "${dojoBaseLocation}dojox"}, 
                      {"name": "tukos", "location": "${tukosBaseLocation}tukos"}]
        };
</script>
<script src="${dojoBaseLocation}dojo/dojo.js"></script>
<script>
    require(["tukos/expressions", "tukos/tukosForms", "tukos/PageManager", "dojo/parser", "dijit/Editor", "dijit/_editor/plugins/AlwaysShowToolbar"], function (expressions, tukosForms, Pmg, parser) {
        Pmg.initializeTukosForm(${tukosFormConfig});    
        tukos.onTdClick = function(td){
	    	expressions.onClick(td.children[0]);
	    };
	    tukos.onTdDblClick = function(td){
	    	expressions.onClick(td.children[0]);
	    }; 
	    tukos.onExpClick = function(expression){
	    	expressions.onClick(expression);
	    };
	    tukos.onExpBlur = function(expression){
	    	expressions.onBlur(expression);
	    };
	    parser.parse();
	});
</script>';

	function sendContent($query, $atts, $where = []){
		if ((empty($atts['from']) && empty($atts['fromwhere']) && empty($where)) || empty($atts['to'])){
			Feedback::add($this->tr('missingfromorto'));
			return [];
		}else{
			$mailArgs = ['tos' => explode(',', $atts['to']), 'subject' => $atts['subject']];
			if (!empty($atts['cc'])){
				$mailArgs['ccs'] = explode(',', $atts['cc']);
			}
			$mailArgs['body'] = (empty($atts['header']) ? '' : $atts['header']);
			$objectsStore =  Tfk::$registry->get('objectsStore');
			$accountInfo = $objectsStore->objectModel('mailaccounts')->getAccountInfo(['where' => empty($where) ? (empty($atts['fromwhere']) ? ['id' => $atts['from']] : $atts['fromwhere']) : $where, 'cols' => ['name', 'eaddress', 'username', 'password', 'smtpserverid', 'gmailtoken']]);
			$mailArgs['from'] = $accountInfo['name'] . ' <' . $accountInfo['eaddress'] . '>';
			$mailArgs['username'] = $accountInfo['username'];
			$mailArgs['password'] = $accountInfo['password'];
			$mailArgs['gmailtoken'] = json_decode($accountInfo['gmailtoken'], true);
			$mailArgs['googlecredentials'] = Tfk::$registry->get('tukosModel')->getOption('googlewebappcredentials');
			$smtpModel  = $objectsStore->objectModel('mailsmtps');
	
			if ($atts['sendas'] === 'appendtobody' || $atts['sendas'] === 'bodyandattachment'){
				$mailArgs['body'] .= $atts['content'];
			}
			if ($atts['sendas'] !== 'appendtobody'){
			    if($tmpFileName = $this->buildTargetFile($atts)){
			        $mailArgs['attachments'] = [$tmpFileName];
			    }else{
			        return [];
			    }
			}
			$smtpModel->send($accountInfo['smtpserverid'], $mailArgs, true);

			if ($atts['sendas'] !== 'appendtobody'){
				unlink($tmpFileName);
			}
			return [];
		}
	}
	function fileContent($query, $atts){
		$contentTypes = ['txt' => 'text/plain', 'html' => 'text/html', 'html2text' => 'text/plain', 'tukosform' => 'text/html', 'json' => 'plain/text', 'pdf' => 'application/pdf'];
		if (($tmpFileName = $this->buildTargetFile($atts))){
		    return HttpUtilities::downloadFile($tmpFileName, $contentTypes[$atts['formatas']], $query['downloadtoken']);
		}
	}
	function buildTargetFile($atts){
		$fileName = empty($atts['filename']) ? uniqId() : str_replace(' ', '_',$atts['filename']);
		$dirFileName = Tfk::$tukosTmpDir . $fileName;
		switch($atts['formatas']){
		    case 'txt':
		        return $this->buildFile($dirFileName .'.txt', implode('', Utl::getItems(['filecover', 'fileheader', 'content', 'filefooter'], $atts)));
		    case 'html':
		        return $this->buildHtmlFile($dirFileName . '.htm', implode('', Utl::getItems(['filecover', 'fileheader', 'content', 'filefooter'], $atts)));
		    case 'html2text':
		        return $this->buildHtml2TextFile($dirFileName .'.txt', implode('', Utl::getItems(['filecover', 'fileheader', 'content', 'filefooter'], $atts)));
		    case 'tukosform':
		        return $this->buildHtmlFile($dirFileName . '.htm', $atts['content'],
		          Utl::substitute($this->tukosHeader, ['title' => $fileName, 'dojoBaseLocation' => Tfk::$tukosFormsDojoBaseLocation]), '', null,
		          Utl::substitute($this->tukosFormsBodyScripts, [
		              'dojoBaseLocation' => Tfk::$tukosFormsDojoBaseLocation,
		              'tukosBaseLocation' => Tfk::$tukosFormsTukosBaseLocation,
		              'tukosFormConfig' => json_encode(['tukosFormsDomainName' => Tfk::$tukosFormsDomainName, 'messages' => Tfk::$registry->get('translatorsStore')->getTranslations(['sent'], $this->objectName)])
		          ]));
		    case 'json':
		        return $this->buildFile($dirFileName .'.json', json_encode(Utl::getItems(['filecover', 'fileheader', 'content', 'filefooter'], $atts)));
		    case 'pdf':
		        return $this->buildPdfFile($dirFileName, $atts, Utl::substitute($this->tukosHeader, ['title' => $fileName, 'dojoBaseLocation' => Tfk::$dojoBaseLocation]));
		    default:
		        Feedback::add($this->tr('unsupportedformatas'));
		        return false;
		}
	}
	function buildFile($fileName, $content){
	    $htmlHandle = fopen($fileName, "w");
	    fwrite($htmlHandle, $content);
	    fclose($htmlHandle);
	    return $fileName;
	}
	function buildHtmlFile($fileName, $content, $htmlHeader = '', $bodyAtts = '', $bodyDivAtts = null, $bodyScripts = ''){
	    $htmlHeader .= $this->hasMath($content) ? $this->mathMLHeaderScript : '';
	    return $this->buildFile($fileName, '<!DOCTYPE HTML><html ><head><meta charset="utf-8">' . $htmlHeader . '</head><body class="claro" ' . $bodyAtts . '>' .
	        (isset($bodyDivAtts) ? '<div ' . $bodyDivAtts . '>' : '') . $content . (isset($bodyDivAtts) ? '</div>' : '') . $bodyScripts . '</body></html>');
	}
	function buildHtml2TextFile($fileName, $content){
	    return $this->buildFile($fileName, Html2Text::convert($content));
	}
	function buildPdfFile($dirFileName, $atts, $htmlHeader){
	    $contentFileName =  $dirFileName . 'body.htm';
	    $this->buildHtmlFile($contentFileName, $atts['content'], $htmlHeader);
	    $htmlToPdfOptions =  ' ';
	    $bodyAtts = 'style="margin:0; padding: 0;" onload="subst()"';
	    $headerDivAtts = null;
	    if (!empty($atts['contentmargin'])){
	        $htmlToPdfOptions = ' --margin-top ' . $atts['contentmargin'] . ' ';
	        $offset = !empty($atts['marginoffset']) ? $atts['marginoffset'] : 30;
	        $coef = !empty($atts['margincoef']) ? $atts['margincoef'] : 1.3;
	        $headerDivOptions = 'style="height:' . intval($offset + $coef * ($atts['contentmargin'] - $offset)) . 'mm;overflow:hidden;"';
	    }
	    if ($atts['orientation'] === 'landscape'){
	        $htmlToPdfOptions .= ' -O landscape ';
	    }
	    if ($atts['smartshrinking'] === 'off'){
	        $htmlToPdfOptions .= ' --disable-smart-shrinking ';
	    }
	    if ($atts['zoom'] != 100){
	        $zoomValue = floatval($atts['zoom'])/100;
	        $htmlToPdfOptions .= ' --zoom ' . $zoomValue . ' ';
	    }
	    if (!empty($atts['fileheader'])){
	        $headerFileName = $dirFileName . 'header.htm';
	        $this->buildHtmlFile($headerFileName, $atts['fileheader'], $this->htmlHeaderScript, $bodyAtts, $headerDivAtts);
	        $htmlToPdfOptions .= ' --header-html ' . $headerFileName . ' ';
	    }
	    if (!empty($atts['filefooter'])){
	        $footerFileName = $dirFileName . 'footer.htm';
	        $this->buildHtmlFile($footerFileName, $atts['filefooter'], $this->htmlHeaderScript, $bodyAtts, '');
	        $htmlToPdfOptions .= ' --footer-html ' . $footerFileName . ' ';
	    }
	    if (!empty($atts['filecover'])){
	        $coverFileName = $dirFileName . 'cover.htm';
	        $this->buildHtmlFile($coverFileName, $atts['filecover']);
	        $htmlToPdfOptions .= $coverFileName . ' ';
	    }
	    $tmpPdfFileName = $dirFileName . '.pdf';
	    $streamsStore = Tfk::$registry->get('streamsStore');
	    if ($streamsStore->startStream('htmltopdf', Tfk::$htmlToPdfCommand.' '. $htmlToPdfOptions. ' ' . $contentFileName . ($this->hasMath($atts['content']) ? $this->mathMLWkhtmlToPdfOptions : '') . ' ' . $tmpPdfFileName, false)){
	        $streamsStore->waitOnStream('htmltopdf', false, 'forget');
	    }
	    unlink($contentFileName);
	    if (!empty($atts['fileheader'])){unlink($headerFileName);}
	    if (!empty($atts['filefooter'])){unlink($footerFileName);}
	    if (!empty($atts['filecover'])){unlink($coverFileName);}
	    return $tmpPdfFileName;
	}
	function hasMath($content){
	    return stripos($content, '<math') > -1;
	}
}
?>
