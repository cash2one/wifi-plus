<?php
/**
 * User: yongli
 * Date: 17/9/19
 * Time: 00:30
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers\Agent;

use Agent\AgentModel;
use WifiAdmin\AgentPay;
use App\Controllers\BaseAgent;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Class Index
 *
 * @package App\Controllers\Agent
 */
class Index extends BaseAgent
{
    /**
     * 代理商用户ID
     *
     * @var
     */
    private $aid;

    protected $enumData = [
        'shoplevel' => [//消费水平
            ['key' => '低端', 'txt' => '低端'],
            ['key' => '工薪', 'txt' => '工薪'],
            ['key' => '小资', 'txt' => '小资'],
            ['key' => '中高档', 'txt' => '中高档'],
            ['key' => '高档', 'txt' => '高档'],
            ['key' => '奢华', 'txt' => '奢华'],
        ],
        'trades'    => [//行业类别
            ['key' => '餐饮', 'txt' => '餐饮'],
            ['key' => '酒店', 'txt' => '酒店'],
            ['key' => '咖啡厅', 'txt' => '咖啡厅'],
            ['key' => '足浴', 'txt' => '足浴'],
            ['key' => 'KTV', 'txt' => 'KTV'],
            ['key' => '购物商超', 'txt' => '购物商超'],
            ['key' => '酒店宾馆', 'txt' => '酒店宾馆'],
            ['key' => '休闲娱乐', 'txt' => '休闲娱乐'],
        ],
        'authmode'  => [//认证方式
            ['key' => '0', 'txt' => '注册认证', 'code' => 'reg'],
            ['key' => '1', 'txt' => '手机认证', 'code' => 'phone'],
            ['key' => '2', 'txt' => '无需认证', 'code' => 'allow'],
            // array('key'=>'3','txt'=>'微信密码认证','code'=>'wecha'),
            ['key' => '4', 'txt' => '微信关注认证', 'code' => 'wecha_follow'],
        ],
    ];

    /**
     * 获得用户报表
     */
    public function getUserChart()
    {
        $way = $this->request->getGet('mode');
        switch (strtolower($way)) {
            case 'today':
                $sql = ' select t,CONCAT(CURDATE()," ",t,"点") as show_date, COALESCE(total_count,0)  as total_count, COALESCE(regcount,0)  as reg_count ,COALESCE(phone_count,0) as phone_count from wifi_hours a left JOIN ';
                $sql .= '(select thour, count(id) as total_count , count(CASE when mode=0 then 1 else null end) as reg_count, count(CASE when mode=1 then 1 else null end) as phone_count from ';
                $sql .= '(select  FROM_UNIXTIME(tt.add_time,"%H") as thour,tt.id,tt.mode from wifi_member tt left join wifi_shop ss on tt.shop_id=ss.id where ss.pid=' . $this->aid;
                $sql .= ' and tt.add_date="' . date('Y-m-d') . '" and ( tt.mode=0 or tt.mode=1 ) ';
                $sql .= ' )a group by thour ) c ';
                $sql .= '  on a.t=c.thour ';
                break;

        }
        $result = DB::select($sql);
        $result = $result ?? [];
        call_back(0, $result);
        //        $db = D('Member');
        //        $rs = $db->query($sql);
        //        $this->ajaxReturn(json_encode($rs));
    }

    /**
     * 上网统计
     */
    public function getAuthRpt()
    {
        $way = $this->request->getGet('mode');
        switch (strtolower($way)) {
            case 'today':
                $sql = ' select t,CONCAT(CURDATE()," ",t,"点") as showdate, COALESCE(ct,0)  as ct ,COALESCE(ct_reg,0)  as ct_reg,COALESCE(ct_phone,0)  as ct_phone,COALESCE(ct_key,0)  as ct_key,COALESCE(ct_log,0)  as ct_log from wifi_hours a left JOIN ';
                $sql .= '( select thour ,count(*) as ct ,count(case when mode=0 then 1 else null end) as ct_reg,count(case when mode=1 then 1 else null end) as ct_phone,count(case when mode=2 then 1 else null end) as ct_key,count(case when mode=3 then 1 else null end) as ct_log from ';
                $sql .= '(select tt.shop_id,tt.mode,FROM_UNIXTIME(tt.login_time,"%H\") as thour,';
                $sql .= ' FROM_UNIXTIME(tt.login_time,"%Y-%m-%d") as d from wifi_auth_list tt left join wifi_shop ss on tt.shop_id=ss.id where ss.pid=' . $this->aid . ') a ';
                $sql .= ' where d="' . date('Y-m-d') . '" ';
                $sql .= ' group by thour ) ';
                $sql .= ' b on a.t=b.thour ';
                break;
        }
        $result = DB::select($sql);
        $result = $result ?? [];
        call_back(0, $result);
    }

    /**
     * 首页
     */
    public function index()
    {
        $nav['m'] = $this->controller();
        $nav['a'] = 'index';
        $this->assign('nav', $nav);
        $this->display();
    }

    /**
     * 商铺列表
     */
    public function shopList()
    {
        $build = \ShopModel::select([
            'id',
            'shop_name',
            'add_time',
            'linker',
            'phone',
            'account',
            'max_count',
            'link_flag'
        ])->wherePid($this->aid);
        $num   = $build->count();
        // 商铺数据
        $result = $build->skip(($this->page - 1) * $this->perPage)->take($this->perPage)->orderBy('id',
            'desc')->get()->toArray();
        // 获得分页配置
        $config = set_page_config($num, $this->url, 3, $this->perPage);
        // 实例化分页类
        $pagination = \Config\Services::pagination();
        // 初始化分页配置
        $pagination->initialize($config);
        $page = $pagination->create_links();
        $this->assign('page', $page);
        $this->assign('lists', $result);
        $nav['m'] = $this->controller();
        $nav['a'] = 'shop';
        $this->assign('nav', $nav);
        $this->display();

    }

    /**
     * 账户信息
     */
    public function account()
    {
        $result = AgentModel::select([
            'id',
            'name',
            'money',
            'linker',
            'phone',
            'level',
            'province',
            'city',
            'area'
        ])->with([
            'getShop' => function ($q) {
                $q->select('id', 'open_pay')->wherePid($this->uid);
            }
        ])->get()->toArray();
        $result = $result ? $result[0] : [];
        if (!$result) {
            call_back(2, '', '无效账户!');
        }
        $this->assign('info', $result);
        $nav['m'] = $this->controller;
        $nav['a'] = 'account';
        $this->assign('nav', $nav);
        $this->display();
    }

    /**
     * @return mixed
     */
    public function saveAccount()
    {
        $post   = $this->request->getPost();
        $status = AgentModel::whereId($this->aid)->update($post);
        $status ? call_back(0) : call_back(2, '', '操作失败!');
        //        $db          = D('Agent');
        //        $where['id'] = $this->aid;
        //        C('TOKEN_ON', false);
        //        if ($db->create($_POST, 2)) {
        //            if ($db->where($where)->save()) {
        //                $data['error'] = 0;
        //                $data['msg']   = "更新成功";
        //
        //                return $this->ajaxReturn($data);
        //            } else {
        //                $data['error'] = 1;
        //                $data['msg']   = $db->getError();
        //
        //                return $this->ajaxReturn($data);
        //            }
        //        } else {
        //            $data['error'] = 1;
        //            $data['msg']   = $db->getError();
        //
        //            return $this->ajaxReturn($data);
        //        }
    }

    /**
     * 商户编辑
     */
    public function shopEdit()
    {
        $id     = $this->request->getGet('id');
        $id     = $id ? intval($id) : 0;
        $result = \ShopModel::select('*')->wherePid($this->aid)->whereId($id)->get()->toArray();
        $result = $result ? $result[0] : [];
        if (!$result) {
            call_back(2, '', '参数不正确!');
        }
        $this->assign('shop', $result);
        $nav['m'] = $this->controller;
        $nav['a'] = 'shopList';
        $this->assign('nav', $nav);
        $this->assign('enumData', $this->enumData);
        $this->display();

    }

    /**
     * 添加
     */
    public function shopAdd()
    {
        $nav['m'] = $this->controller;
        $nav['a'] = 'shop';
        $this->assign('enumData', $this->enumData);
        $this->assign('nav', $nav);
        $this->display();

    }

    /**
     * 修改密码
     */
    public function pwd()
    {
        $this->display();
    }

    /**
     * @return mixed
     */
    public function doPwd()
    {
        $post = $this->request->getPost();
        if ($post) {
            if (!isset($post['password']) || !$post['password']) {
                call_back(2, '', '新密码不能为空');
            }
            if (!validate_pwd($post['password'])) {
                call_back(2, '', '密码由4-20个字符 ，数字，字母或下划线组成');
            }
            $result = AgentModel::select(['id', 'account', 'password'])->whereId($this->aid)->get()->toArray();
            $result = $result ? $result[0] : [];
            if (!$result) {
                call_back(2, '', '无效数据!');
            }
            if (md5($post['oldPwd']) != $result['password']) {
                call_back(2, '', '旧密码不正确');
            }
        }
        $post['update_time'] = time();
        $post['password']    = md5($_POST['password']);
        $status              = AgentModel::whereId($this->aid)->update($post);
        $status ? call_back(0) : call_back(2, '', '操作失败!');

    }

    /**
     * 编辑商户
     *
     * @return mixed
     */
    public function saveShop()
    {
        $post = $this->request->getPost();
        $info = \ShopModel::select('id')->whereId($post['id'])->wherePid($this->aid)->get()->toArray();
        $info = $info ? $info[0] : [];
        if (!$info) {
            call_back(2, '', '服务器忙，请稍候再试!');
        }
        $post['link_flag']   = 1;// 不受限制
        $post['update_time'] = time();
        $post['update_by']   = $this->aid;
        $status              = \ShopModel::whereId($post['id'])->wherePid($this->aid)->update($post);
        $status ? call_back(0) : call_back(2, '', '操作失败!');
        //        if (IS_AJAX) {
        //            $user         = D('Shop');
        //            $id           = I('post.id', '0', 'int');
        //            $where['id']  = $id;
        //            $where['pid'] = $this->aid;
        //            $info         = $user->where($where)->find();
        //            if (!$info) {
        //                //无此用户信息
        //                $data['error'] = 1;
        //                $data['msg']   = "服务器忙，请稍候再试";
        //
        //                return $this->ajaxReturn($data);
        //            }
        //            $_POST['linkflag'] = 1;//不限制
        //            if ($user->create($_POST, 2)) {
        //                if ($user->where($where)->save()) {
        //                    $data['error'] = 0;
        //                    $data['url']   = U('shoplist');
        //
        //                    return $this->ajaxReturn($data);
        //                } else {
        //                    $data['error'] = 1;
        //                    $data['msg']   = $user->getError();
        //
        //                    return $this->ajaxReturn($data);
        //                }
        //            } else {
        //                $data['error'] = 1;
        //                $data['msg']   = $user->getError();
        //
        //                return $this->ajaxReturn($data);
        //            }
        //        } else {
        //            $data['error'] = 1;
        //            $data['msg']   = "服务器忙，请稍候再试";
        //
        //            return $this->ajaxReturn($data);
        //        }
    }

    /**
     * 开户
     */
    public function openShop()
    {
        $post = $this->request->getPost();
        if (!$post) {
            call_back(2, '', '服务器忙，请稍候再试!');
        }
        $result = AgentModel::select(['id', 'money', 'level'])->with([
            'getAgentLevel' => function ($query) {
                $query->select(['id', 'open_pay']);
            }
        ])->whereId($this->aid)->get()->toArray();
        $result = $result ? $result[0] : [];
        if (!$result) {
            call_back(2, '', '服务器忙，请稍候再试');
        }
        $money = $result['money'] ? $result['money'] : 0;
        $pay   = $result['open_pay'] ? $result['open_pay'] : 0;
        if ($money < $pay) {
            call_back(2, '', '当前帐号余额不足，无法添加商户!');
        }
        $post['pid']         = $this->aid;
        $post['auth_mode']   = '#0#';
        $post['link_flag']   = 1;//不限制
        $post['max_count']   = getenv('OpenMaxCount');
        $post['create_time'] = time();
        $post['update_time'] = time();
        $post['create_by']   = $this->aid;
        $post['update_by']   = $this->aid;
        // 开启事务
        $build = \ShopModel::select();
        $build->getConnection()->beginTransaction();
        // 添加开户信息
        $id = \ShopModel::insertGetId($post);
        !$id ? call_back(2, '', '操作失败!') : '';
        $add['shop_id']     = $id;
        $add['sort_id']     = 0;
        $add['route_name']  = $post['shop_name'];
        $add['gw_id']       = $post['account'];
        $add['create_time'] = time();
        $add['update_time'] = time();
        $add['create_by']   = $this->aid;
        $add['update_by']   = $this->aid;
        // 添加路由
        $status1 = \RouteMapModel::insertGetId($add);
        // 扣款
        $status2 = AgentModel::whereId($this->aid)->update(['money' => $pay]);
        //添加消费记录
        $payData['aid']         = $this->aid;
        $payData['pay_money']   = $pay;
        $payData['old_money']   = $money;
        $payData['now_money']   = $money - $pay;
        $payData['do']          = 0;
        $payData['desc']        = '商户开户扣款';
        $payData['add_time']    = time();
        $payData['update_time'] = time();
        $payData['create_time'] = time();
        $payData['create_by']   = $this->aid;
        $payData['update_by']   = $this->aid;
        $status3                = AgentPay::insertGetId($payData);
        if ($id && $status1 && $status2 && $status3) {
            // 提交事务
            $build->getConnection()->commit();
            call_back(0);
        } else {
            // 回滚事务
            $build->getConnection()->rollBack();
            call_back(2, '', '操作失败!');
        }
        //            $user              = D('Shop');
        //            $now               = time();
        //            if ($user->create($_POST, 1)) {
        //                $aid = $user->add();
        //                if ($aid > 0) {
        //                    $rs['shopid']    = $aid;
        //                    $rs['sortid']    = 0;
        //                    $rs['routename'] = $_POST['shopname'];
        //                    $rs['gw_id']     = $_POST['account'];
        //                    M("Routemap")->data($rs)->add();
        //                    //扣款
        //                    $db->where($where)->setDec('money', $pay);
        //                    //添加消费记录
        //                    $paydata['aid']         = $this->aid;
        //                    $paydata['paymoney']    = $pay;
        //                    $paydata['oldmoney']    = $money;
        //                    $paydata['nowmoney']    = $money - $pay;
        //                    $paydata['do']          = 0;
        //                    $paydata['desc']        = '商户开户扣款';
        //                    $paydata['add_time']    = $now;
        //                    $paydata['update_time'] = $now;
        //                    D('Agentpay')->add($paydata);
        //                    $data['error'] = 0;
        //                    $data['url']   = U('shoplist');
        //
        //                    return $this->ajaxReturn($data);
        //                } else {
        //                    $data['error'] = 1;
        //                    $data['msg']   = $user->getDbError();
        //
        //                    return $this->ajaxReturn($data);
        //                }
        //            } else {
        //                $data['error'] = 1;
        //                $data['msg']   = $user->getError();
        //
        //                return $this->ajaxReturn($data);
        //            }
    }

    /**
     * 报表数据
     */
    public function report()
    {
        $build = AgentPay::select('')->whereAid($this->aid);
        $num   = $build->count();
        // 报表数据
        $result = $build->skip(($this->page - 1) * $this->perPage)->take($this->perPage)->orderBy('add_time',
            'desc')->get()->toArray();
        // 获得分页配置
        $config = set_page_config($num, $this->url, 3, $this->perPage);
        // 实例化分页类
        $pagination = \Config\Services::pagination();
        // 初始化分页配置
        $pagination->initialize($config);
        $page = $pagination->create_links();
        $this->assign('page', $page);
        $this->assign('lists', $result);
        $nav['m'] = $this->controller;
        $nav['a'] = $this->method;
        $this->assign('nav', $nav);
        $this->display();
    }

    /**
     *
     */
    public function routeList()
    {
        $get = $this->request->getGet();
        $id  = $get['id'] ? intval($get['id']) : 0;
        if (!$id) {
            call_back(2, '', '参数不正确');
        }
        $build = \RouteMapModel::select('*')->with([
            'getShop' => function ($query) {
                $query->select('id', 'shop_name')->wherePid($this->aid);
            }
        ])->whereShopId($id);
        $num   = $build->count();
        // 数据
        $result = $build->skip(($this->page - 1) * $this->perPage)->take($this->perPage)->orderBy('id',
            'desc')->get()->toArray();
        // 获得分页配置
        $config = set_page_config($num, $this->url, 3, $this->perPage);
        // 实例化分页类
        $pagination = \Config\Services::pagination();
        // 初始化分页配置
        $pagination->initialize($config);
        $page = $pagination->create_links();
        $this->assign('page', $page);
        $this->assign('lists', $result);
        $this->display();

    }

    /**
     * 编辑路由
     */
    public function editRoute()
    {
        $post = $this->request->getPost();
        if ($post) {
            $info = \RouteMapModel::select('id')->whereId($post['id'])->get()->toArray();
            if (!$info) {
                call_back(2, '', '没有此路由信息!');
            }
            $post['update_time'] = time();
            $status              = \RouteMapModel::whereId($post['id'])->update($post);
            $status ? call_back(0) : call_back(2, '', '操作失败!');
        } else {
            $get    = $this->request->getGet();
            $id     = $get['id'] ? intval($get['id']) : 0;
            $shopId = $get['shopId'] ? intval($get['shopId']) : 0;
            $info   = \RouteMapModel::select('*')->whereId($id)->whereShopId($shopId)->get()->toArray();
            $info   = $info ? $info[0] : [];
            if (!$info) {
                call_back(2, '', '参数不正确!');
            }
            $this->assign('info', $info);
            $this->display();
        }
    }

    /**
     * 删除路由
     *
     * @param $id
     * @param $shopId
     */
    public function delRoute($id, $shopId)
    {
        $info = \RouteMapModel::whereId($id)->whereShopId($shopId)->get()->toArray();
        if ($info) {
            call_back(2, '', '没有此路由信息!');
        }
        $status = \RouteMapModel::whereId($id)->whereShopId($shopId)->update(['is_delete' => 1]);
        $status ? call_back(0) : call_back(2, '', '删除失败!');

    }

    /**
     * 添加路由
     */
    public function addRoute()
    {
        $id = $this->request->getGet('id');
        if ($id) {
            call_back(2, '', '参数不正确!');
        }
        $info = \ShopModel::select(['id', 'shop_name'])->whereId($id)->get()->toArray();
        $info = $info ? $info[0] : [];
        $this->assign("shop", $info);
        $this->display();

    }

    /**
     * 添加
     */
    public function add()
    {
        $post                = $this->request->getPost();
        $post['create_time'] = time();
        $post['update_time'] = time();
        $post['create_by']   = $this->aid;
        $post['update_by']   = $this->aid;
        $status              = \RouteMapModel::insertGetId($post);
        if (!$status) {
            call_back(2, '', '操作失败!');
        }
        call_back(0);
    }
}