<?php
/**
 * User: yongli
 * Date: 17/12/8
 * Time: 13:43
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers\WifiAdmin;

use App\Controllers\BaseAdmin;
use WifiAdmin\AdminModel;
use  WifiRbac;

/**
 * 管理员登入管理
 * Class LoginAction
 *
 * @package App\Controllers\WifiAdmin
 */
class LoginAction extends BaseAdmin
{

    /**
     * 登录界面
     */
    public function index()
    {
        $this->display();
    }

    /**
     * 登录操作
     */
    public function doLogin()
    {
        $post     = $this->request->getPost();
        $username = $post['user_name'];
        $password = md5($post['password']);
        if (!$username || !$password) {
            call_back(2, '', '请输入帐号密码1');
        }
        // 支持使用绑定帐号登录
        $authInfo = WifiRbac::authenticate(['user' => $username, 'state' => 1]);
        !$authInfo ? call_back(2, '', '帐号不存在或者被禁用') : '';
        // 验证管理员登入信息
        if ($authInfo['password'] != $password) {
            call_back(2, '', '帐号密码不正确');
        }
        $_SESSION['auth_id']    = $authInfo['id'];
        $_SESSION['admin_id']   = $authInfo['id'];// 用户ID
        $_SESSION['admin_mame'] = $authInfo['user'];// 用户名
        $_SESSION['role_id']    = $authInfo['role'];// 角色ID
        if ($authInfo['user'] == 'admin') {
            $_SESSION['wifi_admin'] = true;
        }
        $data['last_login_time'] = time();
        $data['last_login_ip']   = get_client_ip();
        $data['update_time']     = time();
        $status                  = AdminModel::whereId($authInfo['id'])->update($data);
        $status ? call_back(0) : call_back(2, '', '登录失败!');

    }

    /**
     * 退出
     */
    public function loginOut()
    {
        $_SESSION = null;
        session_destroy();
        unset($_SESSION);
        call_back(0);
    }

}