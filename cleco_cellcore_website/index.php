<?php

$hostname = str_replace('www.', '', strtolower($_SERVER['HTTP_HOST']));

switch ($hostname) {
	case "clecocellcore.co.uk" :
		$template = "index-eu.php";
		break;

	default :
		$template = "index-us.php";
		break;
}

include $template;
?>
