<?php
/**
 * User: yongli
 * Date: 17/9/19
 * Time: 00:26
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers;

use WifiAdmin\TreeNodeModel;

/**
 * Class BaseAdmin
 *
 * @package App\Controllers
 */
class BaseAdmin extends Base
{
    /**
     * 用户ID
     *
     * @var
     */
    public $userId;

    public function doLoadID($id)
    {
        $nav['m'] = $this->controller;
        $nav['a'] = $id;
        $this->assign('nownav', $nav);
    }

    /**
     * 初始化
     */
    public function initialization()
    {
        // 执行父级的_initialize方法
        parent::initialization();
        //判断权限
        if (C('USER_AUTH_ON') && !in_array(MODULE_NAME, explode(',', C('NOT_AUTH_MODULE')))) {
            if (!\WifiRbac::AccessDecision(GROUP_NAME)) {
                //检查认证识别号
                if (!$_SESSION [C('USER_AUTH_KEY')]) {
                    //跳转到认证网关
                    redirect(PHP_FILE . C('USER_AUTH_GATEWAY'));
                }
                // 没有权限 抛出错误
                if (C('RBAC_ERROR_PAGE')) {
                    // 定义权限错误页面
                    redirect(C('RBAC_ERROR_PAGE'));
                } else {
                    if (C('GUEST_AUTH_ON')) {
                        $this->assign('jumpUrl', PHP_FILE . C('USER_AUTH_GATEWAY'));
                    }
                    // 提示错误信息
                    $this->error(L('_VALID_ACCESS_'));

                }

            } else {
                $this->userId = session(C('USER_AUTH_KEY'));
                $this->loadMenu();
            }

        }

    }

    /**
     * 加载菜单
     */
    private function loadMenu()
    {
        $where['status'] = 1;
        $where['menu_flag'] = 1;
        $order['sort'] = 'asc';
        $order['id'] = 'asc';
        $nav = TreeNodeModel::select([
            'id',
            'title',
            'g',
            'm',
            'a',
            'ico',
            'single',
            'pid',
            'level'
        ])->whereStatus(1)->whereMenuFlag(1)->orderBy(['sort' => 'asc', 'id' => 'asc'])->get()->toArray();
        $this->assign('nav', $nav);
        if ($_SESSION['admin_mame'] == C('SPECIAL_USER')) {
            $result = TreeNodeModel::select('id')->get()->toArray();
            $access = array_column($result, 'id');
            $this->assign('navIds', $access);
        } else {
            $ids = \WifiRbac::getAccessIDList($_SESSION[C('USER_AUTH_KEY')]);
            $this->assign('navIds', $ids);
        }

    }
}