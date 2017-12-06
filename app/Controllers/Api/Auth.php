<?php
/**
 * User: yongli
 * Date: 17/9/19
 * Time: 00:33
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers\Api;

use Api\MemberModel;
use App\Controllers\BaseApi;
use WifiAdmin\AuthListModel;

/**
 * Class Auth
 *
 * @package App\Controllers\Api
 */
class Auth extends BaseApi
{
    /**
     *
     * @var string
     */
    private $token = '';

    /**
     * mac地址
     *
     * @var string
     */
    private $mac = '';

    /**
     * IP地址
     *
     * @var string
     */
    private $ip = '';

    /**
     * 网关地址
     *
     * @var string
     */
    private $gw_id = '';

    /**
     * 首页
     */
    public function index()
    {
        if ($_REQUEST['ip']) {
            $this->ip = $_REQUEST['ip'];
        }
        if ($_REQUEST['gw_id']) {
            $this->gw_id = $_REQUEST['gw_id'];
        }
        $this->mac = '';
        if ($_REQUEST['mac']) {
            $this->mac = $_REQUEST['mac'];
        }
        if (!$_REQUEST['token']) {
            call_back(2, '', '无权限!');
        }
        $this->token = $_REQUEST['token'];
        $user        = MemberModel::select('*')->whereToken($this->token)->get()->toArray();
        if ($user) {
            MemberModel::whereToken($this->token)->update(['mac' => $this->mac]);
        }
        $result = AuthListModel::select('*')->whereToken($this->token)->get()->toArray();
        $result = $result ? $result[0] : [];
        if (!$result) {
            call_back(2, '', '无权限!');
        }
        //update time
        if (!$result['over_time']) {
            $data['mac']         = $this->mac;
            $data['login_ip']    = $this->ip;
            $data['pingcount']   = $result['pingcount'] + 1;
            $data['last_time']   = time(); //
            $data['update_time'] = time(); //
            $status              = AuthListModel::whereToken($this->token)->update($data);
            $status ? call_back(0) : call_back(2, '', '无权限!');
        } else {
            //limit
            $lf = $result['over_time'] - time();
            if ($lf < 0) {
                call_back(2, '', '无权限!');
            } else {
                $data ['mac']         = $this->mac;
                $data ['login_ip']    = $this->ip;
                $data ['ping_count']   = $result['ping_count'] + 1;
                $data ['last_time']   = time(); //
                $data ['update_time'] = time(); //
                AuthListModel::whereToken($this->token)->save($data);
                call_back(2, '', '无权限!');
            }
        }
    }
}