<?php
/**
 * User: yongli
 * Date: 17/9/19
 * Time: 00:35
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers\Api;

use App\Controllers\BaseApi;

/**
 * Class Index
 *
 * @package App\Controllers\Api
 */
class Index extends BaseApi
{
    public function index(){
        echo "欢迎使用";
    }

}