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
    /**
     * 当前页码
     *
     * @var int
     */
    public $page    = 1;

    /**
     * 每页显示
     *
     * @var int
     */
    public $perPage = 10;

    /**
     *
     */
    protected function _initialize()
    {
        parent::_initialize();
        $this->isLogin();
    }
    
}