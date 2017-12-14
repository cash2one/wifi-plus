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
 * Class ShopModel
 *
 * @package Agent
 */
class ShopModel extends YP_Model
{
    protected $table = 'wifi_shop';

    /**
     * 获得代理信息
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getAgent()
    {
        return $this->hasOne('\Agent\AgentModel', 'id', 'pid');
    }
}