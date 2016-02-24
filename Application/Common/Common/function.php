<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 2/23/16
 * Time: 12:19 PM
 */

/**
 * 获取where条件
 * @param $map
 * @param string $k
 * @return mixed
 */
function get_map($map,$k='id'){
    $x = [$k=>$map];
    if($map==null){
        $x = [];
    }
    if(is_array($map)){
        $x = [$k=>['in',$map]];
    }
    return $x;
}