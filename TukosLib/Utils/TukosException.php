<?php
namespace TukosLib\Utils;

class TukosException extends \Exception {
	var $more_info;

	public function __construct($message = "", $code = 0, $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}
?>
