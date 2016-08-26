<?php
namespace  Home\Controller;
use Common\Controller\CommonController;

class TestController extends CommonController {
	
	
	
	public  function test(){
		
		$id=47;
		
		\Think\Hook::listen('test',$id);
		
		//tag('test',$id);
	}
	 
	 
	 
	 
	 
	 
	
	
}





;?>