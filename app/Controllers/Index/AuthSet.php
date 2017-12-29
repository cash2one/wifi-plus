<?php
/**
 * User: yongli
 * Date: 17/9/19
 * Time: 15:55
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers\Index;

use App\Controllers\Base;
use WifiAdmin\AuthTplModel;

/**
 * 上网认证模板设置控制器
 * Class AuthSet
 *
 * @package Index
 */
class AuthSet extends Base
{
    /**
     * 模板设置
     */
    public function tplSet()
    {
        // 获得商户模板id,模板目录、广告显示时间,认证方式
        $shop = $this->_getShop();
        // 获得所有的模板数据
        $list = AuthTplModel::select('*')->whereState(1)->orderBy('id', 'asc')->get()->toArray();
        $list = $list ?? [];
        $this->assign('tpl', $list);
        $this->assign('a', 'authtplset');
        $this->assign('info', $shop);
        $this->display();
    }

    /**
     * 执行模板设置
     */
    public function doTplSet()
    {
        // 获得商户模板id,模板目录、广告显示时间
        $shop  = $this->getShop();
        $tplId = $this->request->getGet('tpl') ? intval($this->request->getGet('tpl')) : 0;
        // 从数据库检测该模板是否存在
        $tpl = AuthTplModel::select('*')->whereId($tplId)->get()->toArray();
        // 模板信息不存在
        if (!$tpl) {
            call_back(2, '', '模板信息不存在!');
        }
        // 获得当前商户设置的认证模式
        $authMode = explode('#', $shop['auth_mode']);
        // 定义新数组，存放认证模式
        $auth = [];
        // 过滤数组
        foreach ($authMode as $key => $value) {
            if ($key % 2 == 0) {
                continue;
            }
            $auth[] = $value;
        }
        // 没有设置手机认证，就不能选认证模板
        if ($tplId == 1007) {
            !in_array(1, $auth) ? call_back(1, '', '设置手机认证模板失败!') : '';
        } else if ($tplId == 1008) {
            // 没有设置注册认证，就不能选注册认证模板
            !in_array(0, $auth) ? call_back(1, '', '设置注册认证模板失败!') : '';
        } else if ($tplId == 1009) {
            // 没有设置手微信关注认证，就不能选微信关注认证模板
            !in_array(4, $auth) ? call_back(1, '', '设置微信关注认证模板失败!') : '';
        }
        if ($shop) {
            //更新
            \ShopModel::select('*')->whereId($this->uid)->update(['tpl_id' => $tplId, 'tpl_path' => $tpl['key_name']]);
        }
        // 设置此认证模板成功
        call_back(0);

    }

    /**
     * 广告显示时间设置
     */
    public function adShowTimeSet()
    {
        // 获得商户模板id,模板目录、广告显示时间
        $shop = $this->getShop();
        $post = $this->request->getPost();
        if (!is_numeric($post['ad_show_time']) || $post['ad_show_time'] < 0) {
            call_back(2, '', '时间必须为数字,且不能为负数!');
        }
        // 保存商户广告显示时间
        \ShopModel::whereId($this->uid)->update($post);
        //            $this->success('设置成功', U('Authset/tplset', true, true, true));
        $this->assign('info', $shop);
        $this->display();
    }

    /**
     * 获得商户模板id,模板目录、广告显示时间
     *
     * @return mixed
     */
    private function _getShop()
    {
        $shop = \ShopModel::select([
            'tpl_id',
            'tpl_path',
            'ad_show_time',
            'auth_mode'
        ])->whereId($this->uid)->get()->toArray();
        $shop = $shop ? $shop[0] : [];

        return $shop;
    }

}