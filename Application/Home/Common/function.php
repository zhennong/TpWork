<<<<<<< HEAD
<?php

//加密函数
function think_encrypt($data, $key = '', $expire = 0) {
    $key  = md5(empty($key) ? 'admin' : $key);
    $data = base64_encode($data);
    $x    = 0;
    $len  = strlen($data);
    $l    = strlen($key);
    $char = '';
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }
    $str = sprintf('%010d', $expire ? $expire + time():0);
    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1)))%256);
    }
    return str_replace(array('+','/','='),array('-','_',''),base64_encode($str));
}
=======
<?php
>>>>>>> a49182571c15c2aed96f6c562b3c60c1dcb58b4d
