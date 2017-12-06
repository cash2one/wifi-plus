<?php
/**
 * User: yongli
 * Date: 17/9/19
 * Time: 00:37
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers\Api;

use App\Controllers\BaseApi;

class Ping extends BaseApi
{
    /**
     *
     * @var null
     */
    private $gw_id = null;

    /**
     *
     */
    public function index()
    {
        echo "Pong";
        if (isset($_REQUEST["gw_id"])) {
            $this->gw_id = $_REQUEST['gw_id'];
        }
        if (!empty($this->gw_id)) {
            //寻找网关ID
            $info = \RouteMapModel::select('*')->whereGwId($this->gw_id)->get()->toArray();
            $info = $info ? $info[0] : [];
            if ($info) {
                //更新心跳包
                $time                        = time();
                $save['last_heartbeat_time'] = $time;
                $save['user_agent']          = getAgent();
                $save['sys_up_time']          = $_GET['sys_up_time'];
                $save['sys_memfree']         = $_GET['sys_memfree'];
                $save['sys_load']            = $_GET['sys_load'];
                $save['wifidog_up_time']      = $_GET['wifidog_up_time'];
                \RouteMapModel::whereGwId($this->gw_id)->save($save);
            }
        }
    }
}