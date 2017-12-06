<?php
/**
 * User: yongli
 * Date: 17/9/19
 * Time: 00:38
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers\Api;

use App\Controllers\BaseApi;
use Index\WapCateLogModel;
use Index\WapModel;

/**
 * Class Portal
 *
 * @package App\Controllers\Api
 */
class Portal extends BaseApi
{
    /**
     * @var
     */
    private $tpl;

    /**
     * @var int
     */
    private $uid = 0;

    /**
     * @var array
     */
    private $classInfo = [];

    /**
     * 
     */
    public function initialization()
    {
        parent::initialization();
        $this->getShopInfo();
    }

    /**
     * 获取用户ID
     */
    private function getShopInfo()
    {
        $gw_id = $this->request->getGet('gw_id');
        $info  = \RouteMapModel::select('*')->with([
            'getShop' => function ($query) {
                $query->select([
                    'shop_name',
                    'auth_mode',
                    'max_count',
                    'link_flag',
                    'sh',
                    'eh',
                    'pid',
                    'count_flag',
                    'count_max',
                    'tpl_path'
                ]);
            }
        ])->whereGwId($gw_id)->get()->toArray();
        $info  = $info ? $info[0] : [];
        $this->uid    = $info['shop_id'];
        if (!is_numeric($this->uid)) {
            call_back(2, '', '参数不正确!');
        }
        $result = WapModel::select('*')->whereUid($info['shop_id'])->get()->toArray();
        if ($result) {
            Cookie('wapuid', $this->uid);
            $this->tpl = $result;
            $cateLog = WapCateLogModel::select('*')->whereUid($this->uid)->get()->toArray();
            $this->classInfo = $cateLog;
            $this->assign('siteInfo', $result);
            $this->assign('classInfo', $cateLog);
        }
    }

    /**
     * 
     */
    public function index()
    {
        $info = \ShopModel::select('*')->whereId($this->uid)->get()->toArray();
        $info = $info ? $info[0] : [];
        if ($info['auth_action'] == 1) {
            $jump = $info['jump_url'];
            redirect($jump, 2, '页面跳转中...');
        }
        if ($info['auth_action'] == 2) {
            $jump = cookie('gw_url');
            redirect($jump, 2, '页面跳转中...');
        }
        if ($info['auth_action'] == 3) {
            redirect(U('api/wap/index', ['sid' => 1]), 2, '页面跳转中...');
        }

    }
}