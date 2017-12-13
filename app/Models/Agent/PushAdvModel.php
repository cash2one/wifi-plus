<?php
/**
 * User: yongli
 * Date: 17/12/5
 * Time: 18:13
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace Agent;

use YP\Core\YP_Model;

class PushAdvModel extends YP_Model
{
    protected $table = 'wifi_push_adv';

    /**
     * 获得代理
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getAgent()
    {
        return $this->hasOne('\Agent\AgentModel', 'id', 'aid');
    }
}
