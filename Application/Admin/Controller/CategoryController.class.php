<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 3/2/16
 * Time: 2:21 PM
 */

namespace Admin\Controller;


use Common\Tools;

class CategoryController extends AuthController
{
    public function cate_agricultural_medicine(){
        switch($_POST['operate']){
            case 'add_cate': //添加分类
                $Category = D('Category');
                if(!$data = $Category->create()){
                    echo $Category->getError();
                    exit();
                }
                if($_FILES){
                    $upload = new \Think\Upload();// 实例化上传类
                    $upload->maxSize   =     1024*1024*3 ;// 设置附件上传大小
                    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                    $upload->rootPath  =      APP_ROOT.C('UPLOADS'); // 设置附件上传根目录
                    $upload->savePath  =      'cat_img/'; // 设置附件上传（子）目录
                    $upload->autoSub = false;
                    // 上传文件
                    $info   =   $upload->upload();
                    if(!$info) {// 上传错误提示错误信息
                        echo $upload->getError();
                    }else{// 上传成功 获取上传文件信息
                        foreach($info as $file){
                            $data['cat_img'] = $file['savepath'].$file['savename'];
                        }
                    }
                }
                if($Category->data($data)->add()){
                    echo 1;
                }else{
                    echo 0;
                }
                break;

            case 'edit_cate': //编辑分类
                $Category = D('Category');
                if(!$data = $Category->create()){
                    echo $Category->getError();
                    exit();
                }
                if($_FILES){
                    Tools::_vp($_FILES);
                    $upload = new \Think\Upload();// 实例化上传类
                    $upload->maxSize   =     1024*1024*3 ;// 设置附件上传大小
                    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                    $upload->rootPath  =      APP_ROOT.C('UPLOADS'); // 设置附件上传根目录
                    $upload->savePath  =      'cat_img/'; // 设置附件上传（子）目录
                    $upload->autoSub = false;
                    // 上传文件
                    $info   =   $upload->upload();
                    if(!$info) {// 上传错误提示错误信息
                        echo $upload->getError();
                    }else{// 上传成功 获取上传文件信息
                        foreach($info as $file){
                            $data['cat_img'] = $file['savepath'].$file['savename'];
                        }
                    }
                }
                if($Category->save($data)){
                    echo 1;
                }else{
                    echo 1;
                }
                break;

            case 'delete_cate':
                if(M('Category')->where(['id'=>I("post.cate_id")])->delete()){
                    echo 1;
                }else{
                    echo 0;
                }
                break;

            default:
                $cates = M('Category')->select();
                $cate_tree = Tools::list2tree($cates);
                $x = M('Category')->where(['pid'=>0])->select();
                foreach($x as $k => $v){
                    if($y = M('Category')->where(['pid'=>$v['id']])->select()){
                        foreach($y as $key => $value){
                            $z[] = $value;
                        }
                    }
                }
                $p_cate = array_merge($x,$z);
                $this->assign(['cate_tree'=>$cate_tree,'p_cate'=>$p_cate]);
                $this->display();
                break;
        }
    }
}