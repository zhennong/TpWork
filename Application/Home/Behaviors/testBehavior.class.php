<?php
namespace Home\Behaviors;
    class testBehavior extends \Think\Behavior{
       
        public function run(&$param){
            
		
			
		
           // dump($id);
			//D('QuestionAsk')->where(array("id"=>47))->setInc('answer_number',1);
			//D('Ask')->execute(" update ".$this->getTableName()." set {$col} = ({$col} + '{$num}') where ".$this->pk." = '{$id}' ");
			//dump(D('QuestionAsk')->getLastsql());
			
			
			//updateCount();
			
        }
    }
	
	
	/*  public function updateCount($id,$col,$num = 1){
        $id = (int)$id;
        
        return $this->execute(" update ".$this->getTableName()." set {$col} = ({$col} + '{$num}') where ".$this->pk." = '{$id}' ");
    } */