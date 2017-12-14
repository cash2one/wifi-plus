<?php
/**
 * User: yongli
 * Date: 17/12/8
 * Time: 13:42
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers\WifiAdmin;

use App\Controllers\BaseAdmin;
use WifiAdmin\AdminModel;
use WifiAdmin\AuthListModel;

/**
 * Class IndexAction
 *
 * @package App\Controllers\WifiAdmin
 */
class IndexAction extends BaseAdmin
{

    /**
     * 首页
     */
    public function index()
    {
        $num =  AuthListModel::select('id')->count();
        $this->assign('m', $this->controller );
        $this->assign('m', $this->method );
        $this->assign('auth_list_count', $num);

        $this->display();
    }

    /**
     * @return mixed
     */
    public function pwd()
    {
        $post = $this->request->getPost();
        if ($post) {
            !$post['password'] ? call_back(2, '', '新密码不能为空') : '';

            if (!validate_pwd($post['password'])) {
                call_back(2, '', '密码由4-20个字符 ，数字，字母或下划线组成');
            }
            $info = AdminModel::select(['id','user','password'])->whereId($this->userId)->get()->toArray();
            $info = $info ? $info[0] : [];
            if (md5($post['old_pwd']) != $info['password']) {
                call_back(2, '', '旧密码不正确');
            }
            $post['update_time'] = time();
            $post['password']    = md5($post['password']);
            $status = AdminModel::whereId($this->userId)->update($post);
            $status ? call_back(0) : call_back(2, '', '操作失败!');
        } else {
            $this->assign('m', $this->controller);
            $this->assign('a', $this->method);
            $this->display();
        }
    }

    /**
     * @return mixed
     */
//    public function lience()
    //    {
    //        $liences = '';
    //        $L       = M('Liences');
    //        $rs      = $L->where()->find();
    //        if (!empty ($rs)) {
    //            $liences = $rs ['liences'];
    //        }
    //        if (isset($_POST) && !empty($_POST) && empty($liences)) {
    //            if ($L->create()) {
    //                $insertid       = $L->add();
    //                $data ['error'] = 0;
    //                $data ['msg']   = "增加授权码成功";
    //
    //                return $this->ajaxReturn($data);
    //            } else {
    //                $insertid       = $L->add();
    //                $data ['error'] = 1;
    //                $data ['msg']   = "增加授权码失败";
    //
    //                return $this->ajaxReturn($data);
    //            }
    //        } else {
    //            $this->assign('liences', $liences);
    //        }
    //        $this->display();
    //    }

    /**
     * 数据
     * Enter description here ...
     */
    public function systemData()
    {
       $num =  AuthListModel::select('id')->count();
        $this->assign('auth_list_count', $num);
        $this->display();
    }

    /**
     * 删除三天前数据
     *
     * @return mixed
     */
    public function delete_auths()
    {
        $oldDay = strtotime('-2 day');
        // 删除授权
        $status = AuthListModel::where('last_time', '<', $oldDay)->update(['is_delete'=>1]);
        $status ? call_back(0) : call_back(2, '', '清理成功!');
    }
}