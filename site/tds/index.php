<?php
	header('Location: https://' . str_replace('www.', '', $_SERVER['HTTP_HOST']) . 
		   '/tds/carnetderoute?notab=yes');
	exit;
?>
