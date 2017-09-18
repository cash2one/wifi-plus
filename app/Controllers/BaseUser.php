<?php
/**
 * User: yongli
 * Date: 17/9/19
 * Time: 00:25
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers;

class BaseUser extends Base
{
    protected function _initialize()
    {
        parent::_initialize();
        $this->isLogin();
    }
    private function isLogin()
    {
        if(!session('uid'))
        {
            $this->redirect('index/index/log');
        }
    }
}