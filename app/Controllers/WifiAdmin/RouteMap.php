<?php
/**
 * User: yongli
 * Date: 17/12/8
 * Time: 13:52
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers\WifiAdmin;

use App\Controllers\BaseAdmin;

/**
 * ·��������
 */
class RouteMap extends BaseAdmin
{
    /**
     * 路由管理
     */
    public function routeList()
    {
        $this->doLoadID(300);
        // 获得商户id
        $id    = $this->request->getGet();
        $id    = $id ? intval($id) : 0;
        $build = \RouteMapModel::select('*')->whereShopId($id)->with([
            'getShop' => function ($query) {
                $query->select(['id', 'shop_name']);
            }
        ]);
        $num   = $build->count();
        // 获得分页配置
        $config = set_page_config($num, $this->url, 3, $this->perPage);
        // 实例化分页类
        $pagination = \Config\Services::pagination();
        // 初始化分页配置
        $pagination->initialize($config);
        // 生成页码
        $page = $pagination->create_links();
        // 得到分页后的数据
        $result = $build->skip(($this->page - 1) * $this->perPage)->take($this->perPage)->orderBy('id desc')->get()->toArray();
        // 分配页码
        $this->assign('page', $page);
        // 当前商户数据和旗下的路由数据
        $this->assign('lists', $result);
        $this->display();
    }

    /**
     * 添加路由
     */
    public function addRoute()
    {
        $this->doLoadID(300);
        $post = $this->request->getPost();
        // 判断是否有POST数据提交
        if ($post) {
            $post['create_time'] = time();
            $post['update_time'] = time();
            $post['create_by']   = $this->uid;
            $post['update_by']   = $this->uid;
            $status              = \RouteMapModel::insertGetId($post);
            $status ? call_back(0) : call_back(2, '', '操作失败!');
        } else {
            // 获得商户id
            $id = $this->request->getGet('id');
            if (!$id) {
                call_back(2, '', '参数不正确!');
            }
            // 获得当前商户的id,商户名等信息
            $info = \ShopModel::select(['id', 'shop_name'])->whereId($id)->get()->toArray();
            $info = $info ? $info[0] : [];
            !$info ? call_back(2, '', '该商家不存在!') : '';
            // 分配商户的id,商户名
            $this->assign("shop", $info);
            $this->display();
        }
    }

    /**
     * 编辑路由
     */
    public function editRoute()
    {
        $post = $this->request->getPost();
        // 判断是否有POST数据提交
        if ($post) {
            $post['update_time'] = time();
            $status              = \RouteMapModel::whereId($post['id'])->update($post);
            $status ? call_back(0) : call_back(2, '', '操作失败!');
        } else {
            // 获得要编辑的路由id
            $id = $this->request->getGet('id');
            // 获得要编辑的路由信息
            $info = \RouteMapModel::select('*')->whereId($id)->get()->toArray();
            $info = $info ? $info[0] : [];
            !$info ? call_back(2, '', '参数不正确!') : '';
            // 获得当前路由所属商户的商户名和商户id
            $shopInfo = \ShopModel::select(['id', 'shop_name'])->whereId($id)->get()->toArray();
            // 分配当前路由所属商户的商户名和商户id
            $this->assign("shop", $shopInfo);
            // 分配当前要编辑的路由信息
            $this->assign("info", $info);
            $this->display();
        }
    }

    /**
     * 删除路由
     *
     * @param $id
     */
    public function delRoute($id)
    {
        $info = \RouteMapModel::select('id')->whereId($id)->get()->toArray();
        $info = $info ? $info[0] : [];
        // 当前的路由信息不存在，就不执行删除
        if (!$info) {
            call_back(2, '', '没有此路由信息!');
        }
        // 删除当前的路由信息
        $status = \RouteMapModel::whereId($id)->update(['is_delete' => 1]);
        $status ? call_back(0) : call_back(2, '', '删除失败!');
    }

}