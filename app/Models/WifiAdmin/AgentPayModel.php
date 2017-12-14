<?php
/**
 * User: yongli
 * Date: 17/9/19
 * Time: 11:07
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace WifiAdmin;

use YP\Core\YP_Model;

class AgentPay extends YP_Model
{
    protected $table = 'wifi_agent_pay';

    /**
     * 获得代理信息
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getAgent()
    {
        return $this->hasOne('\Agent\AgentModel', 'id', 'aid');
    }
}