<?php

$hostname = str_replace('www.', '', strtolower($_SERVER['HTTP_HOST']));

switch ($hostname) {
	case "cleconeotek.de" :
		$template = "index-Deutsch.html";
		break;

	case "cleconeotek.cn" :
		$template = "index-chinese.html";
		break;

	case "cleconeotek.es" :
		$template = "index-spanish.html";
		break;

		case "cleconeotek.co.uk" :
			$template = "index-uk.html";
			break;

	default :
		$template = "index.html";
		break;
}

include $template;
