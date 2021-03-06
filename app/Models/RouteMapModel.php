<?php
/**
 * User: yongli
 * Date: 17/9/19
 * Time: 10:24
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
use YP\Core\YP_Model;

/**
 * Class RouteMapModel
 *
 * @package Agent
 */
class RouteMapModel extends YP_Model
{
    protected $table = 'wifi_route_map';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getShop()
    {
        return $this->hasMany('\ShopMode', 'id', 'shop_id');
    }
}