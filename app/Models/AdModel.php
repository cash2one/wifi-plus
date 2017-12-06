<?php
/**
 * User: yongli
 * Date: 17/9/19
 * Time: 10:53
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
class AdModel extends \YP\Core\YP_Model
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