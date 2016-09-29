<?php
namespace Home\Controller;
use Common\Controller\CommonController;
use Common\Tools;
use Home\Api\ApiAppKnow;


class DetailsController extends CommonController {

    /**
     * 通过Catid Askid获取问答详情
     */
    public function index(){
        $api = new ApiAppKnow();
        $catid = I('get.cat_id');                  //获取分类ID
        $askid = I('get.ask_id');                  //获取问题ID

        if(!empty($catid) && !empty($askid)){
            //获取栏目数量
            $category = $api->getCategoryList($catid,null,null,null,null,null);

            //获取问答信息
            $x = $api->getAskInfo($askid);
            foreach ($x as $k => $v) {
                $x[$k]['addtime_date'] = $api->format_date($v['addtime']);
                for ($i = 0; $i < 6; $i++) {
                    if ($v['thumb' . $i]) {
                        $x[$k]['ask_images'][] = $v['thumb' . $i];
                        $x[$k]['image_count'] = $i + 1;
                    }
                }
                $x[$k]['area'] = Tools::arr2str($api->getAreaFullNameFromAreaID($v['areaid']),'');
            }
            $ask_data = $x;

            //问答列表
            $x = $api->getAskArcAnswers($askid,null,null,null);
            foreach ($x as $k => $v) {
                $x[$k]['addtime_date'] = $api->format_date($v['addtime']);
                $x[$k]['area'] = Tools::arr2str($api->getAreaFullNameFromAreaID($v['areaid']),'');
                $x[$k]['content'] = $api->eachKeyWord($v['content']); //获取关键词加链接
            }
            foreach ($x as $k=>$v){
                $y[] = $v;
            }
            $answer_data = $y;
            $this->assign(['category'=>$category[0],'ask_data'=>$ask_data[0],'answer_data'=>$answer_data]);
            $this->display();
        }else{
            echo "参数错误";
        }
    }
}