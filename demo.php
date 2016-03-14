<?php


function sendSms($content,$sendPhone,$stop = 0){
	$content = urlencode($content);
	$cnsmsUrl = 'http://api.cnsms.cn/?ac=send&uid=114294&pwd='.md5(626123).'&mobile='.$sendPhone.'&content='.$content;
	if ($stop!=0) {
		exit();
	}
	return file_get_contents($cnsmsUrl);
}

var_dump(sendSms(123456,15515783176));


