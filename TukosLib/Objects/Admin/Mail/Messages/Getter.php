<?php
namespace TukosLib\Objects\Admin\Mail\Messages;

class Getter{
    
	public $id;
	public $date;
	public $subject;

	public $fromName;
	public $fromAddress;

	public $to = array();
	public $toString;
	public $cc = array();
	public $replyTo = array();

	public $textPlain;
	public $textHtml;
	/** @var IncomingMailAttachment[] */
	protected $attachments = array();
    
    function __construct($attachmentsDir = false){
        $this->attachmentsDir = $attachmentsDir;
        $this->serverEncoding = 'utf-8';
    }

	public function addAttachment($attachment) {
		$this->attachments[$attachment['id']] = $attachment;
	}

	/**
	 * @return IncomingMailAttachment[]
	 */
	public function getAttachments() {
		return $this->attachments;
	}

	/**
	 * Get array of internal HTML links placeholders
	 * @return array attachmentId => link placeholder
	 */
	public function getInternalLinksPlaceholders() {
		return preg_match_all('/=["\'](ci?d:(\w+))["\']/i', $this->textHtml, $matches) ? array_combine($matches[2], $matches[1]) : array();
	}

	public function replaceInternalLinks($baseUri) {
		$baseUri = rtrim($baseUri, '\\/') . '/';
		$fetchedHtml = $this->textHtml;
		foreach($this->getInternalLinksPlaceholders() as $attachmentId => $placeholder) {
			$fetchedHtml = str_replace($placeholder, $baseUri . basename($this->attachments[$attachmentId]->filePath), $fetchedHtml);
		}
		return $fetchedHtml;
	}

    public function get($mailboxStream, $cols, $sequence, $options = FT_UID){
        $result = [];
        $overview = @imap_fetch_overview($mailboxStream, $sequence, $options)[0];
        if (empty($overview)){
            return false;
        }else{
            foreach ($cols as $col){
                if (isset($overview->$col)){
                    $result[$col] = $overview->$col;
                }else{
                    $result[$col] = null;
                }
            }
            if (in_array('body', $cols)){
                $body   = $this->getBody($mailboxStream, $result['uid']);
                $result['body'] = (empty($body['textHtml']) ? $body['textPlain'] : $body['textHtml']);
            }
            if (in_array('attachments', $cols)){
                if (! empty($this->attachments)){
                    $result['attachments'] = $this->attachments;
                }
            }
            return $result;
        }
    }
    
    public function getHeaders($mailboxStream, $uid){

        $headersText = imap_fetchbody($mailboxStream, $uid, '0', FT_UID);
        return imap_rfc822_parse_headers($headersText);
    }
    
    public function getBody($mailboxStream, $uid){
        $this->body = ['textPlain' => '', 'textHtml' => ''];
        $this->attachments = [];
        $structure = @imap_fetchstructure($mailboxStream, $uid, FT_UID);
        if ($structure){
            if (empty($structure->parts)){
                $this->getpart($mailboxStream, $uid, $structure, 0);
            } else { 
                foreach ($structure->parts as $index => $part)
                    $this->getpart($mailboxStream, $uid, $part, $index+1);
            }
        }
        return $this->body;
    }

    public function getpart($mailboxStream, $uid, $partStructure, $partNum) {
        // $partNum = '1', '2', '2.1', '2.1.3', etc for multipart, 0 if simple
        //global $htmlmsg,$plainmsg,$charset,$attachments;
    
        // DECODE DATA
        $data = ($partNum) ? 
            @imap_fetchbody($mailboxStream, $uid, $partNum, FT_UID) : 
            @imap_body($mailboxStream, $uid, FT_UID);

		if($partStructure->encoding == 1) {
			$data = imap_utf8($data);
		}
		elseif($partStructure->encoding == 2) {
			$data = imap_binary($data);
		}
		elseif($partStructure->encoding == 3) {
			$data = imap_base64($data);
		}
		elseif($partStructure->encoding == 4) {
			$data = imap_qprint($data);
		}

		$params = array();
		if(!empty($partStructure->parameters)) {
			foreach($partStructure->parameters as $param) {
				$params[strtolower($param->attribute)] = $param->value;
			}
		}
		if(!empty($partStructure->dparameters)) {
			foreach($partStructure->dparameters as $param) {
				$paramName = strtolower(preg_match('~^(.*?)\*~', $param->attribute, $matches) ? $matches[1] : $param->attribute);
				if(isset($params[$paramName])) {
					$params[$paramName] .= $param->value;
				} else {
					$params[$paramName] = $param->value;
				}
			}
		}
		if(!empty($params['charset'])) {
			$data = iconv(strtoupper($params['charset']), $this->serverEncoding . '//IGNORE', $data);
		}

		// attachments
		$attachmentId = $partStructure->ifid ? trim($partStructure->id, " <>") : (isset($params['filename']) || isset($params['name']) ? mt_rand() . mt_rand() : null);
		if($attachmentId) {
			if(empty($params['filename']) && empty($params['name'])) {
				$fileName = $attachmentId . '.' . strtolower($partStructure->subtype);
			}
			else {
				$fileName = !empty($params['filename']) ? $params['filename'] : $params['name'];
				$fileName = $this->decodeMimeStr($fileName, $this->serverEncoding);
				$fileName = $this->decodeRFC2231($fileName, $this->serverEncoding);
			}
			/*$attachment = new IncomingMailAttachment();
			$attachment->id = $attachmentId;
			$attachment->name = $fileName;*/
			$attachment = ['id' => $attachmentId, 'name' => $fileName];
            if($this->attachmentsDir) {
				$replace = array(
					'/\s/' => '_',
					'/[^0-9a-zA-Z_\.]/' => '',
					'/_+/' => '_',
					'/(^_)|(_$)/' => '',
				);
				$fileSysName = preg_replace('~[\\\\/]~', '', $this->id . '_' . $attachmentId . '_' . preg_replace(array_keys($replace), $replace, $fileName));
				$attachment['filePath'] = $this->attachmentsDir . DIRECTORY_SEPARATOR . $fileSysName;
				file_put_contents($attachment['filePath'], $data);
			}
			$this->addAttachment($attachment);
		} else if($partStructure->type == 0 && $data) {
			if(strtolower($partStructure->subtype) == 'plain') {
				$this->body['textPlain'] .= $data;
			} else {
				$this->body['textHtml'] .= $data;
			}
		}
		elseif($partStructure->type == 2 && $data) {
			$this->body['textPlain'] .= trim($data);
		}
		if(!empty($partStructure->parts)) {
			foreach($partStructure->parts as $subPartNum => $subPartStructure) {
				if($partStructure->type == 2 && $partStructure->subtype == 'RFC822') {
					$this->getpart($mailboxStream, $uid, $subPartStructure, $partNum);
				}
				else {
					$this->getpart($mailboxStream, $uid, $subPartStructure, $partNum . '.' . ($subPartNum + 1));
				}
			}
		}
	}
	protected function decodeMimeStr($string, $charset = 'utf-8') {
		$newString = '';
		$elements = imap_mime_header_decode($string);
		for($i = 0; $i < count($elements); $i++) {
			if($elements[$i]->charset == 'default') {
				$elements[$i]->charset = 'iso-8859-1';
			}
			$newString .= iconv(strtoupper($elements[$i]->charset), $charset . '//IGNORE', $elements[$i]->text);
		}
		return $newString;
	}

	function isUrlEncoded($string) {
		$string = str_replace('%20', '+', $string);
		$decoded = urldecode($string);
		return $decoded != $string && urlencode($decoded) == $string;
	}

	protected function decodeRFC2231($string, $charset = 'utf-8') {
		if(preg_match("/^(.*?)'.*?'(.*?)$/", $string, $matches)) {
			$encoding = $matches[1];
			$data = $matches[2];
			if($this->isUrlEncoded($data)) {
				$string = iconv(strtoupper($encoding), $charset . '//IGNORE', urldecode($data));
			}
		}
		return $string;
	}


}
?>
