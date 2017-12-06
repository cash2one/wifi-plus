<?php
/**
 * User: yongli
 * Date: 17/12/6
 * Time: 09:50
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace Agent;

use YP\Core\YP_Model;

class AdModel extends YP_Model
{
    protected $table = 'wifi_ad';

    /**
     * 获得广告的商铺信息
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getShop()
    {
        return $this->hasOne('\ShopModel', 'id', 'uid');
    }
}
