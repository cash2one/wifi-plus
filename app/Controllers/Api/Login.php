<?php
/**
 * User: yongli
 * Date: 17/9/19
 * Time: 00:36
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers\Api;

use Agent\AdCountModel;
use Api\MemberModel;
use App\Controllers\BaseApi;
use WifiAdmin\AuthListModel;

/**
 * Class Login
 *
 * @package App\Controllers\Api
 */
class Login extends BaseApi
{
    /**
     *
     *
     * @var null
     */
    private $gw_address = null;

    /**
     *
     * @var null
     */
    private $gw_port = null;

    /**
     *
     * @var null
     */
    private $gw_id = null;

    /**
     *
     * @var null
     */
    private $url = null;

    /**
     * 首页
     */
    public function index()
    {
        if (isset($_REQUEST['gw_address'])) {
            $this->gw_address = $_REQUEST['gw_address'];
            cookie('gw_address', $_REQUEST['gw_address']);
        }
        if (isset($_REQUEST['gw_port'])) {
            $this->gw_port = $_REQUEST['gw_port'];
            cookie('gw_port', $_REQUEST['gw_port']);
        }
        if (isset($_REQUEST['gw_id'])) {
            $this->gw_id = $_REQUEST['gw_id'];
            cookie('gw_id', $_REQUEST['gw_id']);
        }
        $mac = '';//海蜘蛛的没有mac,其实可以用网关的来代替
        if (isset($_REQUEST['mac'])) {
            cookie('mac', $_REQUEST['mac']);
            $mac = $_REQUEST['mac'];
        }
        if (isset($_REQUEST['url'])) {
            $this->url = $_REQUEST['url'];
            cookie('gw_url', $_REQUEST['url']);
            $this->wLog($this->url, $this->gw_id, $mac);//日志
        }
        $nowDate = date('Y-m-d', time());//当前日期
        //没有网关ID
        if (!$this->gw_id) {
            echo '参数不正确';
            die;
        }
        //检测是否为iphone
        $agent = $this->agent;
        $pos2  = strstr($agent, "iPhone OS");
        if ($pos2) {
            $this->assign('is_iphone', true);
        } else {
            $this->assign('is_iphone', false);
        }
        //检测是否为iphone
        //寻找网关ID
        $info = \RouteMapModel::select('*')->with([
            'getShop' => function ($query) {
                $query->select([
                    'wx',
                    'shop_name',
                    'notice',
                    'logo',
                    'ad_show_time',
                    'address',
                    'phone',
                    'auth_mode',
                    'max_count',
                    'link_flag',
                    'sh',
                    'eh',
                    'pid',
                    'count_flag',
                    'count_max',
                    'tpl_path',
                    'hotspot_name',
                    'hotspot_pass',
                    'wx_url',
                    'code_img'
                ]);
            }
        ])->whereGwId($this->gw_id)->get()->toArray();
        $info = $info ? $info[0] : [];
        if (!$info) {
            echo '参数不正确!';
            die;
        }
        $arr       = parse_url($this->url);
        $arr_query = $this->convertUrlQuery($arr['query']);
        $token     = $arr_query['token'];
        $num       = strlen($arr_query['token']);
        if ($num >= 0) {
            $mInfo = MemberModel::select('*')->whereToken($token)->get()->toArray();
            $mInfo = $mInfo ? $mInfo[0] : [];
            // 认证成功后，跳转页面
            if ($mInfo && (time() - $mInfo['add_time']) / 60 < 5) {
                // wifiDog认证地址
                $jump = 'http://' . $_REQUEST['gw_address'] . ':' . $_REQUEST['gw_port'] . '/wifidog/auth?token=' . $token;
                if (!cookie('gw_port')) {
                    $jump = cookie('gw_address') . '?username=' . $info['hotspot_name'] . '&password=' . $info['hotspot_pass'];
                }
                cookie('token', $token);
                if ($info['link_flag'] == 0) {
                    $count = MemberModel::select('id')->whereState(1)->where('start_date', '>',
                        time())->where('end_date', '<', time())->whereAid(0)->whereShopId($info['shop_id'])->count();
                    if ($count > $info['max_count']) {
                        $show = 0;
                    }
                }
                cookie('shopid', $info['shop_id']);
                $where['uid']    = $info['shop_id'];
                $where['ad_pos'] = 1;
                $ad              = \AdModel::select([
                    'id',
                    'ad_thumb',
                    'mode'
                ])->whereUid($info['shop_id'])->whereAdPos(1)->orderBy('ad_sort desc')->skip(0)->take(5)->get()->toArray();
                $this->assign('ad', $ad);
                $this->assign('shopInfo', $info);
                $this->assign('show', $show);
                $this->assign('jump', $jump);
                // 认证成功页面
                $this->display('ok');
                exit;
            }
        }
        if ($info['logo']) {
            $info['logo'] = $this->downloadUrl($info['logo']);
        }
        $tplKey = $info['tpl_path'];
        $show   = 1;
        $limit  = $info['link_flag'];
        if ($limit == 0) {
            $where['shop_id'] = $info['shop_id'];
            $count            = MemberModel::select('id')->whereShopId($info['shop_id'])->count();
            if ($count > $info['max_count']) {
                $show = 0;
            }
        }
        cookie('shopid', $info['shop_id']);
        $authMode        = $info['auth_mode'];
        $where['uid']    = $info['shop_id'];
        $where['ad_pos'] = 0;
        $ad              = \AdModel::select([
            'id',
            'ad_thumb',
            'mode'
        ])->whereUid($info['shop_id'])->whereAdPos(0)->orderBy('ad_sort desc')->skip(0)->take(5)->get()->toArray();
        $ids             = '';
        $tmPad           = [];
        foreach ($ad as $k => $v) {
            $v['ad_thumb'] = $this->downloadUrl($v['ad_thumb']);
            $ids .= $v['id'] . ",";
            $tmPad[] = $v;
        }
        $hour = (int)date('H');
        // 判断是否在允许上网时段
        if ($info['sh'] && $info['eh']) {
            $sh                = (int)$info['sh'];
            $eh                = (int)$info['eh'];
            $auth['open_sh']   = $sh;
            $auth['open_eh']   = $eh;
            $auth['open_flag'] = true;//设置时段
            if ($hour >= $sh && $hour <= $eh) {
                $auth['open'] = true;

            } else {
                $auth['open'] = false;
            }
        } else {
            $auth['open']      = true;
            $auth['open_flag'] = false;//未设置
        }
        if (!$authMode) {
            $auth['reg'] = 1;
        } else {
            $tmp = explode('#', $authMode);
            foreach ($tmp as $v) {
                if ($v != '#' && $v != '') {
                    $arr[] = $v;
                }
            }
            foreach ($arr as $v) {
                $temp = explode('=', $v);
                if (count($temp) > 1 && $temp[0] == '3') {
                    $auth['wx'] = 1;
                } else if (count($temp) > 1 && $temp[0] == '4') {
                    $auth['wx_f'] = 1;
                } else {
                    if ($v == '0') {
                        $auth['reg'] = 1;
                    }
                    if ($v == '4') {
                        $auth['wx_f'] = 1;
                    }
                    if ($v == '1') {
                        $auth['phone'] = 1;
                    }
                    if ($v == '2') {
                        $auth['allow'] = 1;
                    }
                }
            }
        }
        $auth['over_max'] = 0;
        // 判断是否启用认证限制
        if ($info['count_flag'] > 0) {
            $maxCount   = $info['count_max'];
            $auth_count = AuthListModel::select('id')->whereMac($mac)->whereShopId($info['shop_id'])->whereAddDate($nowDate)->count();
            if (($maxCount - $auth_count) <= 0) {
                //echo "超过啦";
                $auth['over_max'] = 1;
            } else {
                $auth['over_max'] = 0;
            }
        }
        $this->assign('ad', $tmPad);
        $this->assign('adId', $ids);
        $this->assign('show', $show);
        $this->assign("authMode", $auth);
        $this->assign("shopInfo", $info);
        if (!$tplKey || strtolower($tplKey) == 'default') {
            $this->display();
        } else {
            $this->display('index$' . $tplKey);
        }

    }

    /**
     * 统计广告
     */
    public function countAd()
    {
        $gid = cookie('gw_id');
        $sid = cookie('shopid');
        if (!$gid || !$sid) {
            exit;
        }
        $ids   = $this->request->getPost('ids');
        $idArr = explode(',', $ids);
        // 统计展示
        $build = AdCountModel::select();
        // 开启事务
        $build->getConnection()->beginTransaction();
        $add['show_up']  = 1;
        $add['hit']      = 0;
        $add['shop_id']  = $sid;
        $add['add_time'] = time();
        $add['add_date'] = date('Y-m-d', time());
        $add['mode']     = 1;
        try {
            foreach ($idArr as $v) {
                if (!$v) {
                    $add['aid'] = $v;
                    $build->insert($add);
                }
            }
            $build->getConnection()->commit();
        } catch (\Exception $e) {
            $build->getConnection()->rollBack();
        }
    }

    /**
     * @param $array_query
     *
     * @return string
     */
    public function getUrlQuery($array_query)
    {
        $tmp = [];
        foreach ($array_query as $k => $param) {
            $tmp[] = $k . '=' . $param;
        }
        $params = implode('&', $tmp);

        return $params;
    }

    /**
     * @param $query
     *
     * @return array
     */
    public function convertUrlQuery($query)
    {
        $queryParts = explode('&', $query);
        $params     = [];
        foreach ($queryParts as $param) {
            $item             = explode('=', $param);
            $params[$item[0]] = $item[1];
        }

        return $params;
    }

    /**
     * @return bool
     */
    public function is_weixin()
    {
        if (strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    public function apple()
    {
        //log::write("签到");
        echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN"><HTML><HEAD><TITLE>Success</TITLE></HEAD><BODY>Success</BODY></HTML>';
    }
}