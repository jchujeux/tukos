<?php
namespace TukosLib\Objects;

use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

trait ContentExporter {
	protected $sendingOptions = ['appendtobody', 'asattachment'];
	protected $fileName;
	public $htmlHeaderScript = 
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
	protected $tukosFormsHeader = '<title>${title}</title><link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/dojo/1.14.1/dijit/themes/claro/claro.css" media="screen">';
	protected $tukosFormsBodyScripts = '
<script>var dojoConfig ={
            baseUrl: "", isDebug: false, async: true, locale: "en-en",
            //],
            packages: [{"name": "tukos", "location": "http://localhost/tukos/tukosenv/src/tukos"}]
        };
</script>
<script src="https://ajax.googleapis.com/ajax/libs/dojo/1.14.1/dojo/dojo.js"></script>
<script>
    require(["tukos/expressions", "tukos/tukosForms", "tukos/PageManager", "dojo/parser", "dijit/Editor", "dijit/_editor/plugins/AlwaysShowToolbar"], function (expressions, tukosForms, Pmg, parser) {
				tukos = {Pmg: Pmg};
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

	function sendContent($query, $atts){
		if (empty($atts['from']) || empty($atts['to'])){
			Feedback::add($this->tr('missingfromorto'));
			return [];
		}else{
			$mailArgs = ['tos' => explode(',', $atts['to']), 'subject' => $atts['subject']];
			if (!empty($atts['cc'])){
				$mailArgs['ccs'] = explode(',', $atts['cc']);
			}
			$mailArgs['body'] = (empty($atts['header']) ? '' : $atts['header']);
			$objectsStore =  Tfk::$registry->get('objectsStore');
			$accountInfo = $objectsStore->objectModel('mailaccounts')->getAccountInfo(['where' => ['id' => $atts['from']], 'cols' => ['name', 'eaddress', 'username', 'password', 'privacy', 'smtpserverid']]);
			$mailArgs['from'] = $accountInfo['name'] . ' <' . $accountInfo['eaddress'] . '>';
			$mailArgs['username'] = $accountInfo['username'];
			$mailArgs['password'] = $accountInfo['password'];
			$smtpModel  = $objectsStore->objectModel('mailsmtps');
	
			if ($atts['sendas'] === 'appendtobody' || $atts['sendas'] === 'bodyandattachment'){
				$mailArgs['body'] .= $atts['content'];
			}
			if ($atts['sendas'] !== 'apppendtobdy'){
				$tmpFileName = $this->buildTargetFile($atts);
				$mailArgs['attachments'] = [$tmpFileName];
			}
			$smtpModel->send($accountInfo['smtpserverid'], $mailArgs, true);

			if ($atts['sendas'] !== 'appendtobody'){
				unlink($tmpFileName);
			}
			return [];
		}
	}
	function fileContent($query, $atts){
		$tmpFileName = $this->buildTargetFile($atts);
		if ($fileHandle = fopen($tmpFileName, 'r')){
			header("Content-Type: application/pdf");
			header("Content-length:" . filesize($tmpFileName));
			header("Content-Disposition: attachment; filename=" . basename($tmpFileName));
			header("Content-Description: PHP Generated Data");
			setcookie('downloadtoken', $query['downloadtoken'], 0, '/');		  
			while (!feof($fileHandle)){
				$buffer = fread($fileHandle, 2048);
				echo $buffer;
			}
			fclose($fileHandle);
			unlink($tmpFileName);
			return [];
		}else{
			Feedback::add($this->tr('errorgeneratingfile'));
			return [];
		}
	}
	function buildTargetFile($atts, $dir = Tfk::tukosTmpDir){
		$fileName = empty($atts['filename']) ? uniqId() : str_replace(' ', '_',$atts['filename']);
		$dirFileName = $dir . $fileName;
		if ($atts['formatas'] === 'html'){
		    $tmpHtmFileName = $dirFileName . ".htm";
		    $this->buildHtmlFile($tmpHtmFileName, implode('', Utl::getItems(['filecover', 'fileheader', 'content', 'filefooter'], $atts)), Utl::substitute($this->tukosFormsHeader, ['title' => $fileName]), '', null, $this->tukosFormsBodyScripts);
		    return $tmpHtmFileName;
		}else{
		    $contentFileName =  $dirFileName . 'body.htm';
		    $this->buildHtmlFile($contentFileName, $atts['content'], $this->tukosFormsHeader);
		    $htmlToPdfOptions = ' ';
		    //$headerDivHeight = null;
		    $bodyAtts = 'style="margin:0; padding: 0;" onload="subst()"';
		    $headerDivAtts = null;
		    if (!empty($atts['contentmargin'])){
		        $htmlToPdfOptions = '--margin-top ' . $atts['contentmargin'] . ' ';
		        $offset = !empty($atts['marginoffset']) ? $atts['marginoffset'] : 30;
		        $coef = !empty($atts['margincoef']) ? $atts['margincoef'] : 1.3;
		        //$headerDivHeight = intval($offset + $coef * ($atts['contentmargin'] - $offset));
		        $headerDivOptions = 'style="height:' . intval($offset + $coef * ($atts['contentmargin'] - $offset)) . 'mm;overflow:hidden;"';
		    }
		    if ($atts['orientation'] === 'landscape'){
		        $htmlToPdfOptions .= '-O landscape ';
		    }
		    if ($atts['smartshrinking'] === 'off'){
		        $htmlToPdfOptions .= '--disable-smart-shrinking ';
		    }
		    if ($atts['zoom'] != 100){
		        $zoomValue = floatval($atts['zoom'])/100;
		        $htmlToPdfOptions .= '--zoom ' . $zoomValue . ' ';
		    }
		    if (!empty($atts['fileheader'])){
		        $headerFileName = $dirFileName . 'header.htm';
		        //$this->buildHtmlFile($headerFileName, $atts['fileheader'], $this->htmlHeaderScript, $headerDivHeight);
		        $this->buildHtmlFile($headerFileName, $atts['fileheader'], $this->htmlHeaderScript, $bodyAtts, $headerDivAtts);
		        $htmlToPdfOptions .= '--header-html ' . $headerFileName . ' ';
		    }
		    if (!empty($atts['filefooter'])){
		        $footerFileName = $dirFileName . 'footer.htm';
		        //$this->buildHtmlFile($footerFileName, $atts['filefooter'], $this->htmlHeaderScript);
		        $this->buildHtmlFile($footerFileName, $atts['filefooter'], $this->htmlHeaderScript, $bodyAtts, '');
		        $htmlToPdfOptions .= '--footer-html ' . $footerFileName . ' ';
		    }
		    if (!empty($atts['filecover'])){
		        $coverFileName = $dirFileName . 'cover.htm';
		        //$this->buildHtmlFile($coverFileName, $atts['filecover']);
		        $this->buildHtmlFile($coverFileName, $atts['filecover']);
		        $htmlToPdfOptions .= $coverFileName . ' ';
		    }
		    $tmpPdfFileName = $dirFileName . '.pdf';
		    $streamsStore = Tfk::$registry->get('streamsStore');
		    if ($streamsStore->startStream('htmltopdf', Tfk::htmlToPdfCommand . $htmlToPdfOptions . $contentFileName . ' ' . $tmpPdfFileName, false)){
		        $streamsStore->waitOnStream('htmltopdf', false, 'forget');
		    }
		    unlink($contentFileName);
		    if (!empty($atts['fileheader'])){unlink($headerFileName);}
		    if (!empty($atts['filefooter'])){unlink($footerFileName);}
		    if (!empty($atts['filecover'])){unlink($coverFileName);}
		    return $tmpPdfFileName;
		}
	}
	function buildHtmlFile($fileName, $content, $htmlHeader = '', $bodyAtts = '', $bodyDivAtts = null, $bodyScripts = ''){
	//function buildHtmlFile($fileName, $content, $htmlHeaderScript = '', $headerDivHeight = null){
	    $htmlHandle = fopen($fileName, "w");
/*
		if (!empty($htmlHeaderScript)){
			$styleOptions = isset($headerDivHeight) ? 'style="height:' . $headerDivHeight . 'mm;overflow:hidden;"' : '';
			fwrite($htmlHandle, '<!DOCTYPE HTML><html ><meta charset="utf-8"><head><meta charset="utf-8">' . $htmlHeaderScript . '</head><body class="claro" style="margin:0; padding: 0;" onload="subst()"><div ' . $styleOptions . '>' . $content . '</div></body></html>');
		}else{
			fwrite($htmlHandle, '<!DOCTYPE HTML><html ><meta charset="utf-8"><head><meta charset="utf-8"></head><body class="claro">' . $content . '</body></html>');
		}
*/
		fwrite($htmlHandle, '<!DOCTYPE HTML><html ><head><meta charset="utf-8">' . $htmlHeader . '</head><body class="claro" ' . $bodyAtts . '>' . 
		                     (isset($bodyDivAtts) ? '<div ' . $bodyDivAtts . '>' : '') . $content . (isset($bodyDivAtts) ? '</div>' : '') . $bodyScripts . '</body></html>');
		fclose($htmlHandle);
	}
}
?>
