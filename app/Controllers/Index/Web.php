<?php
/**
 * User: yongli
 * Date: 17/9/19
 * Time: 15:56
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers\Index;

use Index\WapModel;
use Index\ArtsModel;
use Index\WapTplModel;
use Index\WapCateLogModel;
use App\Controllers\BaseUser;

/**
 * Class Web
 *
 * @package App\Controllers\Index
 */
class Web extends BaseUser
{

    /**
     * 初始化配置
     */
    protected function _initialize()
    {
        parent::_initialize();
        $this->init();
    }

    /**
     *
     */
    private function init()
    {
        $this->assign('a', 'web3g');
    }

    /**
     *
     */
    public function index()
    {
        $wapInfo = WapModel::select('*')->whereUid($this->uid)->get()->toArray();
        $wapInfo = $wapInfo ? $wapInfo[0] : [];
        $this->assign('wapInfo', $wapInfo);
        $this->display();
    }

    /**
     *
     */
    public function doSet()
    {
        $postData = $this->request->getPost();
        if ($postData) {
            $wapInfo = WapModel::select('*')->whereUid($this->uid)->get()->toArray();
            $wapInfo = $wapInfo ? $wapInfo[0] : [];
            if ($wapInfo) { // 编辑
                $postData['update_time'] = time();
                $status                  = WapModel::whereId($postData['id'])->update($postData);
            } else { // 添加
                $postData['create_time'] = time();
                $postData['update_time'] = time();
                $status                  = WapModel::insertGetId($postData);
            }
            $status ? call_back(0) : call_back(2, '', '操作成功!');
            //            if ($wapInfo == false) {
            //                $_POST ['uid'] = session('uid');
            //                if ($db->create()) {
            //                    if ($db->add()) {
            //                        $this->success("操作成功", U('web/index'));
            //                    } else {
            //                        $this->error("操作成功", U('web/index'));
            //                    }
            //                } else {
            //                    $this->error($db->getError());
            //                }
            //            } else {
            //                if ($db->create()) {
            //                    if ($db->where($where)->save()) {
            //                        $this->success("操作成功", U('web/index'));
            //                    } else {
            //                        $this->error("操作成功", U('web/index'));
            //                    }
            //                } else {
            //                    $this->error($db->getError());
            //                }
            //            }
        }
    }

    /**
     * 列表
     */
    public function getCateLog()
    {
        $result = WapCateLogModel::select('*')->whereUid($this->uid)->get()->toArray();
        foreach ($result as &$value) {
            $value ['title_pic'] = $this->downloadUrl($value ['title_pic']);
        }
        $this->assign('lists', $result);
        $this->display();
    }

    /**
     * 添加
     */
    public function addCateLog()
    {
        $postData = $this->request->getPost();
        if ($postData) {
            list ($ret, $err) = $this->uploadFile($this->uid, $_FILES ['img'] ['name'], $_FILES['img']['tmp_name']);
            //7牛上传
            if ($err !== null) {
                call_back(2, '', '上传失败');
            }
            $postData ['uid']         = $this->uid;
            $postData ['title_pic']   = $ret ['key'];
            $postData ['create_time'] = time();
            $postData ['update_time'] = time();
            $status                   = WapCateLogModel::insertGetId($postData);
            $status ? call_back(0) : call_back(2, '', '添加失败!');
        } else {
            $this->display();
        }
    }

    /**
     * 编辑
     */
    public function editCateLog()
    {
        $postData = $this->request->getPost();
        if ($postData) {
            if (!is_numeric($postData['id'])) {
                call_back(2, '', '参数不正确');
            }
            $result = WapCateLogModel::select('id')->whereId($postData['id'])->whereUid($this->uid)->get()->toArray();
            $result = $result ? $result[0] : [];
            if (!$result) {
                call_back(2, '', '无此栏目信息');
            }
            if (!is_null($_FILES['img']['name']) && $_FILES['img']['name'] != '') {
                list ($ret, $err) = $this->uploadFile($this->uid, $_FILES ['img'] ['name'], $_FILES['img']['tmp_name']);
                //7牛上传
                if ($err !== null) {
                    call_back(2, '', '上传失败');
                }
                $postData['title_pic'] = $ret['key'];
            }
            $postData['uid']         = $this->uid;
            $postData['create_time'] = time();
            $postData['update_time'] = time();
            $status                  = WapCateLogModel::whereId($postData['id'])->whereUid($this->uid)->update($postData);
            $status ? call_back(0) : call_back(2, '', '添加失败!');
            //                $catedb           = D('Wapcatelog');
            //                if ($catedb->create()) {
            //                    if ($catedb->where($where)->save()) {
            //                        $this->success("添加成功", U('web/catelog'));
            //                    } else {
            //                        $this->error("添加失败");
            //                    }
            //                } else {
            //                    $this->error($catedb->getError());
            //                }
        } else {
            $id = $this->request->getGet()['id'];
            if (!is_numeric($id)) {
                call_back(2, '', '参数不正确');
            }
            $result = WapCateLogModel::select('*')->whereId($postData['id'])->whereUid($this->uid)->get()->toArray();
            $result = $result ? $result[0] : [];
            if (!$result) {
                call_back(2, '', '无此栏目信息');
            }
            $result['title_pic'] = $this->downloadUrl($result['title_pic']);
            $this->assign('info', $result);
            $this->display();
        }
    }

    /**
     * 删除
     */
    public function delCateLog()
    {
        $postData = $this->request->getGet();
        if (!is_numeric($postData)) {
            call_back(2, '', '参数不正确');
        }
        $result = ArtsModel::select('id')->whereCid($postData['id'])->whereUid($this->uid)->count();
        if ($result > 0) {
            $this->error("请先删除该栏目下的文章内容");
            call_back(2, '', '请先删除该栏目下的文章内容');
        }
        $status = WapCateLogModel::whereId($postData['id'])->whereUid($this->uid)->update(['is_delete' => 1]);
        $status ? call_back(0) : call_back(2, '', '操作失败!');
    }

    /**
     *
     */
    public function getArts()
    {
        $cateLog = WapCateLogModel::select(['id', 'title'])->whereUid($this->uid)->whereMode(0)->get()->toArray();
        $build   = ArtsModel::select('*')->whereUid($this->uid);
        $num     = $build->count();
        // 获得分页配置
        $config = set_page_config($num, $this->url, 3, $this->perPage);
        // 实例化分页类
        $pagination = \Config\Services::pagination();
        // 初始化分页配置
        $pagination->initialize($config);
        $page   = $pagination->create_links();
        $result = $build->skip(($this->page - 1) * $this->perPage)->take($this->perPage)->get()->toArray();
        foreach ($result as &$value) {
            $value['title_pic'] = $this->downloadUrl($value['title_pic']);
        }
        $this->assign('cateLog', $cateLog);
        $this->assign('lists', $result);
        $this->assign('page', $page);
        $this->display();
    }

    /**
     * 添加文章
     */
    public function addArts()
    {
        $postData = $this->request->getPost();
        if ($postData) {
            if ($postData['cid'] < 1) {
                call_back(2, '', '请选择所属栏目');
            }
            if (!is_null($_FILES['img']['name']) && $_FILES['img']['name'] != '') {
                list ($ret, $err) = $this->uploadFile($this->uid, $_FILES['img']['name'], $_FILES['img']['tmp_name']);
                //7牛上传
                if ($err !== null) {
                    call_back(2, '', '上传失败');
                }
                $postData['title_pic'] = $ret['key'];
            }
            $postData['uid']         = $this->uid;
            $postData['create_time'] = time();
            $postData['update_time'] = time();
            $status                  = ArtsModel::insertGetId($postData);
            $status ? call_back(0) : call_back(2, '', '添加失败!');
            //            if ($catedb->create()) {
            //                if ($catedb->add()) {
            //                    $this->success("添加成功", U('web/arts'));
            //                } else {
            //                    $this->error("");
            //                }
            //            } else {
            //                $this->error($catedb->getError());
            //            }
        } else {
            $cateLog = WapCateLogModel::select(['id', 'title'])->whereUid($this->uid)->whereMode(0)->get()->toArray();
            $this->assign('cateLog', $cateLog);
            $this->display();
        }

    }

    /**
     * 编辑文章
     */
    public function editArts()
    {
        $postData = $this->request->getPost();
        if ($postData) {
            if (!is_numeric($postData['id'])) {
                call_back(2, '', '参数不正确');
            }
            $arts = ArtsModel::select('id')->whereId($postData['id'])->whereUid($this->uid)->get()->toArray();
            $arts = $arts ? $arts[0] : [];
            if (!$arts) {
                call_back(2, '', '无此文章信息');
            }
            if (!is_null($_FILES['img']['name']) && $_FILES['img']['name'] != '') {
                list ($ret, $err) = $this->uploadFile($this->uid, $_FILES ['img'] ['name'], $_FILES['img']['tmp_name']);
                //7牛上传
                if ($err !== null) {
                    $this->error('上传失败');
                    call_back(2, '', '上传失败');
                }
                $postData['title_pic'] = $ret ['key'];
            }
            $postData['uid']         = $this->uid;
            $postData['update_time'] = time();
            $status                  = ArtsModel::whereId($postData['id'])->whereUid($this->uid)->update($postData);
            $status ? call_back(0) : call_back(2, '', '编辑失败!');
            //            $catedb        = D('Arts');
            //            if ($catedb->create()) {
            //                if ($catedb->where($where)->save()) {
            //                    $this->success("添加成功", U('web/arts'));
            //                } else {
            //                    $this->error("添加失败");
            //                }
            //            } else {
            //                $this->error($catedb->getError());
            //            }
        } else {
            $postData = $this->request->getGet();
            if (!is_numeric($postData['id'])) {
                call_back(2, '', '参数不正确');
            }
            $cateLog = WapCateLogModel::select(['id', 'title'])->whereUid($this->uid)->whereMode(0)->get()->toArray();
            $arts    = ArtsModel::select('*')->whereId($postData['id'])->whereUid($this->uid)->get()->toArray();
            //
            $arts['title_pic'] = $this->downloadUrl($arts['title_pic']);
            $this->assign('info', $arts);
            $this->assign('cateLog', $cateLog);
            $this->display();
        }
    }

    /**
     * 删除文章
     */
    public function delArts()
    {
        $id = $this->request->getGet('id');
        if (!is_numeric($id)) {
            call_back(2, '', '参数不正确');
        }
        $status = ArtsModel::whereId($id)->whereUid($this->uid)->update(['is_delete' => 1]);
        $status ? call_back(0) : call_back(2, '', '操作失败');
    }

    /**
     * 设置模板
     */
    public function setTemp()
    {
        $tpl = WapTplModel::select('*')->whereState()->orderBy('sort asc');
        //        $list = D('Waptpl')->cache('tplcfg', '60')->where('state')->order('sort asc')->select();
        $wapInfo = WapModel::select('*')->whereUid($this->uid)->get()->toArray();
        $this->assign('tpl', $tpl);
        $this->assign('info', $wapInfo);
        $this->display();
    }

    /**
     *
     */
    public function home()
    {
        $tplId = $this->request->getGet('tpl');
        $info  = WapModel::select('*')->whereUid($this->uid)->get()->toArray();
        $tpl   = WapTplModel::select('*')->whereId($tplId)->get()->toArray();
        if (!$tpl) {
            exit ();
        }
        if ($info) {
            //更新
            $status = WapModel::whereUid($this->uid)->update([
                'home_tpl'      => $tplId,
                'home_tpl_path' => $tpl['tpl_path']
            ]);
        } else {
            //添加
            $time                   = time();
            $data ['uid']           = $this->uid;
            $data ['home_tpl']      = $tplId;
            $data ['home_tpl_path'] = $tpl['tpl_path'];
            $data ['create_time']   = $time;
            $data ['update_time']   = $time;
            $data ['state']         = 1;
            $status                 = WapModel::insertGetId($data);
        }
        $status ? call_back(0) : call_back(2, '', '');
    }

    /**
     *
     */
    public function lists()
    {
        $tplId = $this->request->getGet('tpl');
        $info  = WapModel::select('*')->whereUid($this->uid)->get()->toArray();
        $tpl   = WapTplModel::select('*')->whereId($tplId)->get()->toArray();
        if (!$tpl) {
            exit ();
        }
        if ($info) {
            //更新
            $status = WapModel::whereUid($this->uid)->update([
                'home_tpl'      => $tplId,
                'home_tpl_path' => $tpl['tpl_path']
            ]);
        } else {
            //添加
            $time                   = time();
            $data ['uid']           = session('uid');
            $data ['list_tpl']      = $tplId;
            $data ['list_tpl_path'] = $tpl ['tpl_path'];
            $data ['create_time']   = $time;
            $data ['update_time']   = $time;
            $data ['state']         = 1;
            $status                 = WapModel::insertGetId($data);

        }
        $status ? call_back(0) : call_back(2, '', '');
    }

    /**
     *
     */
    public function info()
    {
        $tplId = $this->request->getGet('tpl');
        $info  = WapModel::select('*')->whereUid($this->uid)->get()->toArray();
        $tpl   = WapTplModel::select('*')->whereId($tplId)->get()->toArray();
        if (!$tpl) {
            exit ();
        }
        if ($info) {
            //更新
            $status = WapModel::whereUid($this->uid)->update([
                'home_tpl'      => $tplId,
                'home_tpl_path' => $tpl['tpl_path']
            ]);
        } else {
            //添加
            $time                   = time();
            $data ['uid']           = session('uid');
            $data ['list_tpl']      = $tplId;
            $data ['list_tpl_path'] = $tpl ['tpl_path'];
            $data ['create_time']   = $time;
            $data ['update_time']   = $time;
            $data ['state']         = 1;
            $status                 = WapModel::insertGetId($data);

        }
        $status ? call_back(0) : call_back(2, '', '');
    }
}