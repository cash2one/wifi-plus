<?php
/**
 * User: yongli
 * Date: 17/9/19
 * Time: 00:24
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers;

class BaseApi extends Base
{
    public $browser  = null;
    public $agent    = null;
    public $tmplname = "";

    public function __construct()
    {
        parent::__construct();
        $this->browser = getUserBrowser();
        $this->agent   = getAgent();
    }
}