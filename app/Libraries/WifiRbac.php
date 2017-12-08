<?php

/**
 * User: yongli
 * Date: 17/12/8
 * Time: 13:59
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
class WifiRbac
{
    /**
     * 认证方法
     *
     * @param        $map
     * @param string $model
     *
     * @return mixed
     */
    public static function authenticate($map, $model = '')
    {
        if (empty($model)) {
            $model = C('USER_AUTH_MODEL');
        }

        //使用给定的Map进行认证
        return M($model)->where($map)->find();
    }

    /**
     * 用于检测用户权限的方法,并保存到Session中
     *
     * @param null $authId
     */
    public static function saveAccessList($authId = null)
    {
        if (null === $authId) {
            $authId = $_SESSION[C('USER_AUTH_KEY')];
        }
        // 如果使用普通权限模式，保存当前用户的访问权限列表
        // 对管理员开发所有权限
        if (C('USER_AUTH_TYPE') != 2 && !$_SESSION[C('ADMIN_AUTH_KEY')]) {
            $_SESSION['_ACCESS_LIST'] = WifiRbac::getAccessList($authId);
        }

        return;
    }

    /**
     * 取得模块的所属记录访问权限列表 返回有权限的记录ID数组
     *
     * @param null   $authId
     * @param string $module
     *
     * @return array
     */
    public static function getRecordAccessList($authId = null, $module = '')
    {
        if (null === $authId) {
            $authId = $_SESSION[C('USER_AUTH_KEY')];
        }
        if (empty($module)) {
            $module = MODULE_NAME;
        }
        //获取权限访问列表
        $accessList = WifiRbac::getModuleAccessList($authId, $module);

        return $accessList;
    }

    /**
     * 检查当前操作是否需要认证
     *
     * @return bool
     */
    public static function checkAccess()
    {
        //如果项目要求认证，并且当前模块需要认证，则进行权限认证
        if (C('USER_AUTH_ON')) {
            $_module = [];
            $_action = [];
            if ("" != C('REQUIRE_AUTH_MODULE')) {
                //需要认证的模块
                $_module['yes'] = explode(',', strtoupper(C('REQUIRE_AUTH_MODULE')));
            } else {
                //无需认证的模块
                $_module['no'] = explode(',', strtoupper(C('NOT_AUTH_MODULE')));
            }
            //检查当前模块是否需要认证
            if ((!empty($_module['no']) && !in_array(strtoupper(MODULE_NAME),
                        $_module['no'])) || (!empty($_module['yes']) && in_array(strtoupper(MODULE_NAME),
                        $_module['yes']))
            ) {
                if ("" != C('REQUIRE_AUTH_ACTION')) {
                    //需要认证的操作
                    $_action['yes'] = explode(',', strtoupper(C('REQUIRE_AUTH_ACTION')));
                } else {
                    //无需认证的操作
                    $_action['no'] = explode(',', strtoupper(C('NOT_AUTH_ACTION')));
                }
                //检查当前操作是否需要认证
                if ((!empty($_action['no']) && !in_array(strtoupper(ACTION_NAME),
                            $_action['no'])) || (!empty($_action['yes']) && in_array(strtoupper(ACTION_NAME),
                            $_action['yes']))
                ) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * 登录检查
     *
     * @return bool
     */
    public static function checkLogin()
    {
        //检查当前操作是否需要认证
        if (WifiRbac::checkAccess()) {
            //检查认证识别号
            if (!$_SESSION[C('USER_AUTH_KEY')]) {
                if (C('GUEST_AUTH_ON')) {
                    // 开启游客授权访问
                    if (!isset($_SESSION['_ACCESS_LIST'])) // 保存游客权限
                    {
                        WifiRbac::saveAccessList(C('GUEST_AUTH_ID'));
                    }
                } else {
                    // 禁止游客访问跳转到认证网关
                    redirect(PHP_FILE . C('USER_AUTH_GATEWAY'));
                }
            }
        }

        return true;
    }

    /**
     * 权限认证的过滤器方法
     *
     * @param $appName
     *
     * @return bool
     */
    public static function AccessDecision($appName = GROUP_NAME)
    {
        //检查是否需要认证
        if (WifiRbac::checkAccess()) {
            //存在认证识别号，则进行进一步的访问决策
            $accessGuid = md5($appName . MODULE_NAME . ACTION_NAME);
            if (empty($_SESSION[C('ADMIN_AUTH_KEY')])) {
                if (C('USER_AUTH_TYPE') == 2) {
                    //加强验证和即时验证模式 更加安全 后台权限修改可以即时生效
                    //通过数据库进行访问检查
                    $accessList = WifiRbac::getAccessList($_SESSION[C('USER_AUTH_KEY')]);
                } else {
                    // 如果是管理员或者当前操作已经认证过，无需再次认证
                    if ($_SESSION[$accessGuid]) {
                        return true;
                    }
                    //登录验证模式，比较登录后保存的权限访问列表
                    $accessList = $_SESSION['_ACCESS_LIST'];
                }
                //判断是否为组件化模式，如果是，验证其全模块名
                $module = defined('P_MODULE_NAME') ? P_MODULE_NAME : MODULE_NAME;
                if (!in_array(strtoupper($appName) . '/' . strtoupper($module) . '/' . strtoupper(ACTION_NAME),
                    $accessList)
                ) {
                    $_SESSION[$accessGuid] = false;

                    return false;
                } else {
                    $_SESSION[$accessGuid] = true;
                }
            } else {
                //管理员无需认证
                return true;
            }
        }

        return true;
    }

    /**
     * 取得当前认证号的所有权限列表
     *
     * @param $authId 用户ID
     *
     * @return array
     */
    public static function getAccessList($authId)
    {
        // Db方式权限数据
        $db    = Db::getInstance(C('RBAC_DB_DSN'));
        $table = [
            'role'   => C('RBAC_ROLE_TABLE'),
            'user'   => C('RBAC_USER_TABLE'),
            'access' => C('RBAC_ACCESS_TABLE'),
            'node'   => C('RBAC_NODE_TABLE')
        ];
        $sql   = "select node.id,node.g,node.m,node.a from " . $table['role'] . " as role," . $table['user'] . " as user," . $table['access'] . " as access ," . $table['node'] . " as node " . "where user.user_id='{$authId}' and user.role_id=role.id and ( access.role_id=role.id  or (access.role_id=role.pid and role.pid!=0 ) ) and role.status=1 and access.node_id=node.id and node.status=1";
        $apps  = $db->query($sql);
        //log::write($sql);
        $access = [];
        foreach ($apps as $key => $app) {
            $access[] = strtoupper($app['g']) . '/' . strtoupper($app['m']) . '/' . strtoupper($app['a']);
        }

        return $access;
    }

    /**
     * 取得当前认证号的所有权限列表
     *
     * @param $authId 用户ID
     *
     * @return array
     */
    public static function getAccessIDList($authId)
    {
        // Db方式权限数据
        $db     = Db::getInstance(C('RBAC_DB_DSN'));
        $table  = [
            'role'   => C('RBAC_ROLE_TABLE'),
            'user'   => C('RBAC_USER_TABLE'),
            'access' => C('RBAC_ACCESS_TABLE'),
            'node'   => C('RBAC_NODE_TABLE')
        ];
        $sql    = "select node.id,node.g,node.m,node.a from " . $table['role'] . " as role," . $table['user'] . " as user," . $table['access'] . " as access ," . $table['node'] . " as node " . "where user.user_id='{$authId}' and user.role_id=role.id and ( access.role_id=role.id  or (access.role_id=role.pid and role.pid!=0 ) ) and role.status=1 and access.node_id=node.id and node.status=1";
        $apps   = $db->query($sql);
        $access = [];
        foreach ($apps as $key => $app) {
            $access[] = $app['id'];
        }

        return $access;
    }

    /**
     * 读取模块所属的记录访问权限
     *
     * @param $authId
     * @param $module
     *
     * @return array
     */
    public static function getModuleAccessList($authId, $module)
    {
        // Db方式
        $db     = Db::getInstance(C('RBAC_DB_DSN'));
        $table  = ['role' => C('RBAC_ROLE_TABLE'), 'user' => C('RBAC_USER_TABLE'), 'access' => C('RBAC_ACCESS_TABLE')];
        $sql    = "select access.node_id from " . $table['role'] . " as role," . $table['user'] . " as user," . $table['access'] . " as access " . "where user.user_id='{$authId}' and user.role_id=role.id and ( access.role_id=role.id  or (access.role_id=role.pid and role.pid!=0 ) ) and role.status=1 and  access.module='{$module}' and access.status=1";
        $rs     = $db->query($sql);
        $access = [];
        foreach ($rs as $node) {
            $access[] = $node['node_id'];
        }

        return $access;
    }
}