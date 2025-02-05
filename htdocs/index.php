<?php
	if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
		$uri = 'https://';
	} else {
		$uri = 'http://';
	}
	$uri .= $_SERVER['HTTP_HOST'];
	//header('Location: '.$uri.'/LearnHomework/Section_1/phpindex.php');
	header('Location: '.$uri.'/LearnHomework/Section_2/index.php');
	exit;
?>