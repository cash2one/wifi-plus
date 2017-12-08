<?php
/**
 * User: yongli
 * Date: 17/12/8
 * Time: 13:53
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers\WifiAdmin;

use App\Controllers\BaseAdmin;

/**
 * 商户控制器
 * Class ShopAction
 *
 * @package App\Controllers\WifiAdmin
 */
class Shop extends BaseAdmin
{
    /**
     * 映射字典
     *
     * @var array
     */
    public $enumData = [
        'shop_level' => [//消费水平
            ['key' => '低端', 'txt' => '低端'],
            ['key' => '工薪', 'txt' => '工薪'],
            ['key' => '小资', 'txt' => '小资'],
            ['key' => '中高档', 'txt' => '中高档'],
            ['key' => '高档', 'txt' => '高档'],
            ['key' => '奢华', 'txt' => '奢华'],
        ],
        'trades'     => [//行业类别
            ['key' => '餐饮', 'txt' => '餐饮'],
            ['key' => '酒店', 'txt' => '酒店'],
            ['key' => '咖啡厅', 'txt' => '咖啡厅'],
            ['key' => '足浴', 'txt' => '足浴'],
            ['key' => 'KTV', 'txt' => 'KTV'],
            ['key' => '购物商超', 'txt' => '购物商超'],
            ['key' => '酒店宾馆', 'txt' => '酒店宾馆'],
            ['key' => '休闲娱乐', 'txt' => '休闲娱乐'],
        ],
        'auth_mode'  => [//认证方式
            ['key' => '0', 'txt' => '注册认证', 'code' => 'reg'],
            ['key' => '1', 'txt' => '手机认证', 'code' => 'phone'],
            ['key' => '2', 'txt' => '无需认证', 'code' => 'allow'],
            //['key' => '3', 'txt' => '微信密码认证', 'code' => 'wecha'],
            ['key' => '4', 'txt' => '微信关注认证', 'code' => 'wecha_follow'],
        ],
    ];

    /**
     * 商户列表
     */
    public function index()
    {
        $this->doLoadID(300);
        // 引用AdminPage页码类
        import('@.ORG.AdminPage');
        // 实例化一个操作shop表的对象
        $db = D('Shop');
        // 判断是否有POST数据提交，查询
        if (isset($_POST) && !empty($_POST)) {
            // 商户名称
            if (isset($_POST['sname']) && $_POST['sname'] != "") {
                $map['sname'] = $_POST['sname'];
                $where .= " and a.shopname like '%%%s%%'";
            }
            // 登录账号
            if (isset($_POST['slogin']) && $_POST['slogin'] != "") {
                $map['slogin'] = $_POST['slogin'];
                $where .= " and a.account like '%%%s%%'";
            }
            // 联系电话
            if (isset($_POST['phone']) && $_POST['phone'] != "") {
                $map['phone'] = $_POST['phone'];
                $where .= " and a.phone like '%%%s%%'";
            }
            // 代理商
            if (isset($_POST['agent']) && $_POST['agent'] != "") {
                $map['agent'] = $_POST['agent'];
                $where .= " and b.name like '%%%s%%'";
            }
            $_GET['p'] = 0;
        } else {
            // 商户名称
            if (isset($_GET['sname']) && $_GET['sname'] != "") {
                $map['sname'] = $_GET['sname'];
                $where .= " and a.shopname like '%%%s%%'";

            }
            // 登录账号
            if (isset($_GET['slogin']) && $_GET['slogin'] != "") {
                $map['slogin'] = $_GET['slogin'];
                $where .= " and a.account like '%%%s%%'";
            }
            // 联系电话
            if (isset($_GET['phone']) && $$_GET['phone'] != "") {
                $map['phone'] = $_GET['phone'];
                $where .= " and a.phone like '%%%s%%'";
            }
            // 代理商
            if (isset($_GET['agent']) && $_GET['agent'] != "") {
                $map['agent'] = $_GET['agent'];
                $where .= " and b.name like '%%%s%%'";
            }
        }
        // 统计商户数量
        $sqlcount = " select count(*) as ct from " . C('DB_PREFIX') . "shop a left join " . C('DB_PREFIX') . "agent b on a.pid=b.id ";
        if (!empty($where)) {
            $sqlcount .= " where true " . $where;
        }
        $rs    = $db->query($sqlcount, $map);
        $count = $rs[0]['ct'];
        // 实例化一个页码对象
        $page = new AdminPage($count, C('ADMINPAGE'));
        foreach ($map as $k => $v) {
            //赋值给Page";
            $page->parameter .= " $k=" . urlencode($v) . "&";
        }
        // 获得所有的商户数据
        $sql = " select a.id,a.shopname,a.add_time,a.linker,a.phone,a.account,a.maxcount,a.linkflag,b.name as agname from " . C('DB_PREFIX') . "shop a left join " . C('DB_PREFIX') . "agent b on a.pid=b.id ";
        if (!empty($where)) {
            $sql .= " where true " . $where;
        }
        $sql .= " order by a.add_time desc limit " . $page->firstRow . ',' . $page->listRows . " ";
        $result = $db->query($sql, $map);
        // 分配页码
        $this->assign('page', $page->show());
        // 分配商户数据
        $this->assign('lists', $result);
        $this->display();
    }

    /**
     * 添加商户
     *
     * @return mixed
     */
    public function addShop()
    {
        $this->doLoadID(300);
        $post = $this->request->getPost();
        // 判断是否有POST数据提交
        if ($post) {
            // 顶级代理商户
            $post['pid'] = 0;
            // 默认注册认证
            $post['auth_mode'] = '#0#';
            // 对密码加密
            $post['password']    = md5($post['password']);
            $post['create_time'] = time();
            $post['update_time'] = time();
            $post['create_by']   = $this->uid;
            $post['update_by']   = $this->uid;
            $status              = \ShopModel::insertGetId($post);
            $status ? call_back(0) : call_back(2, '', '操作失败!');
        } else {
            // 分配商户等级数据
            $this->assign('enumData', $this->enumData);
            $this->display();
        }
    }

    /**
     * 编辑商户信息
     *
     * @return mixed
     */
    public function editShop()
    {
        $this->doLoadID(300);
        $post = $this->request->getPost();
        // 判断是否有POST数据提交
        if ($post) {
            $info = \ShopModel::select('*')->whereId($post['id'])->get()->toArray();
            $info = $info ? $info[0] : [];

            //无此用户信息
            if (!$info) {
                call_back(2, '', '无此用户信息!');
            }
            // 商户信息更新时间
            $post['update_time'] = time();
            $status = \ShopModel::whereId($post['id'])->update($post);
            $status ? call_back(0) : call_back(2, '', '操作失败!');
        } else {
            // 没有POST数据提交
            // 获得要编辑的商户id
            $id          = $this->request->getGet('id');
            $info = \ShopModel::whereId($id)->get()->toArray();
            $info = $info ? $info[0] : [];
            if (!$info) {
                call_back(2, '', '参数不正确!');
            }
            // 分配商户信息
            $this->assign('shop', $info);
            //$enumData分配商户等级
            $this->assign('enumData', $this->enumData);
            $this->display();
        }
    }

}