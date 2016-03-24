<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 16-3-24
 * Time: 下午2:43
 */

namespace Admin\Controller;


class AskController extends AuthController
{
    public function perguntas()
    {
        $this->display();
    }
}