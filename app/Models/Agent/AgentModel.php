<?php
/**
 * User: yongli
 * Date: 17/9/19
 * Time: 10:19
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace Agent;

use YP\Core\YP_Model;

/**
 * Class AgentModel
 *
 * @package Agent
 */
class AgentModel extends YP_Model
{
    protected $table = 'wifi_agent';

    /**
     * 代理等级
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getAgentLevel()
    {
        return $this->hasMany('\WifiAdmin\AgentLevelModel', 'id', 'level');
    }
}