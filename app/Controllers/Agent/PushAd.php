<?php
/**
 * User: yongli
 * Date: 17/9/19
 * Time: 00:31
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers\Agent;

use Agent\AgentPushSetModel;
use App\Controllers\BaseAgent;
use Agent\PushAdvModel;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * 推送广告
 * Class PushAd
 *
 * @package App\Controllers\Agent
 */
class PushAd extends BaseAgent
{

    /**
     * 初始化工作
     */
    public function initialization()
    {
        parent::initialization();
        $nav['m'] = $this->controller();
        $nav['a'] = 'pushadv';
        $this->assign('nav', $nav);
    }

    /**
     * 广告推送首页
     */
    public function index()
    {
        $build = PushAdvModel::select([
            'id',
            'title',
            'mode',
            'pic',
            'info',
            'sort',
            'show_count',
            'start_date',
            'end_date',
            'state',
            'aid'
        ])->whereAid($this->aid);
        // 总记录数
        $num = $build->count();
        // 广告数据
        $result = $build->skip(($this->page - 1) * $this->perPage)->take($this->perPage)->orderBy([
            'sort'        => 'desc',
            'create_time' => 'desc'
        ])->get()->toArray();
        // 获得分页配置
        $config = set_page_config($num, $this->url, 3, $this->perPage);
        // 实例化分页类
        $pagination = \Config\Services::pagination();
        // 初始化分页配置
        $pagination->initialize($config);
        $page = $pagination->create_links();
        foreach ($result as $key => &$rs) {
            $rs ['pic'] = $this->downloadUrl($rs['pic']);
        }
        $this->assign('page', $page);
        $this->assign('lists', $result);
        $this->display();
    }

    /**
     * 添加广告
     */
    public function add()
    {
        $post = $this->request->getPost();
        if ($post) {
            //			 import('ORG.Net.UploadFile');
            //	        $upload             = new UploadFile();
            //	        $upload->maxSize    = C('AD_SIZE') ;
            //	        $upload->allowExts  = C('AD_IMGEXT');
            //	        $upload->savePath   =  C('AD_PUSHSAVE');
            if (!$post['start_date'] || !$post['end_date']) {
                call_back(2, '', '请选择广告投放时间段');
            }
            $path = $this->uploadFile($_SESSION['uid'], $_FILES ['img'] ['name'], $_FILES['img']['tmp_name']);
            //		print_r($ret);exit;
            //7牛上传
            //	            $info           =  $upload->getUploadFileInfo();
            $add = [
                'aid'         => $this->aid,
                'pic'         => $path,
                'sort'        => $post['sort'] ?? 0,
                'start_date'  => strtotime($post['start_date']),
                'end_date'    => strtotime($post['end_date']),
                'create_time' => time(),
                'update_time' => time(),
                'create_by'   => $this->aid,
                'update_by'   => $this->aid,
            ];
            $id  = PushAdvModel::insertGetId($add);
            $id ? call_back(0) : call_back(2, '', '添加失败');
            //                $ad                 = D('Pushadv');
            //                $_POST['aid']       = $this->aid;
            //                $_POST['pic']       = $ret ['key'];
            //                $_POST['sort']      = isset($_POST['sort']) ? $_POST['sort'] : 0;
            //                $_POST['startdate'] = strtotime($_POST['startdate']);
            //                $_POST['enddate']   = strtotime($_POST['enddate']);
            //                if ($ad->create()) {
            //                    if ($ad->add()) {
            //                        $this->success('添加广告成功', U('pushadv/index', '', true, true, true));
            //                    } else {
            //                        $this->error('添加失败，请重新添加');
            //                    }
            //                } else {
            //                    $this->error($ad->getError());
            //                }
        } else {
            $this->display();
        }
    }

    /**
     * 编辑广告
     */
    public function edit()
    {
        $post = $this->request->getPost();
        if ($post) {
            if (!$post['start_date'] || !$post['end_date']) {
                call_back(2, '', '请选择广告投放时间段');
            }
            $id     = $post['id'] ? intval($post['id']) : 0;
            $result = PushAdvModel::select(['id', 'pic'])->whereId($id)->get()->toArray();
            $result = $result ? $result[0] : [];
            if (!$result) {
                call_back(2, '', '无此广告信息');
            }
            //            $id          = I('post.id', '0', 'int');
            //            $where['id'] = $id;
            //            $db          = D('Pushadv');
            //            $result      = $db->where($where)->field('id,pic')->find();
            //            if ($result == false) {
            //                $this->error('无此广告信息');
            //                exit;
            //            }
            if ($_FILES['img']['name']) {
                $path = $this->uploadFile($_SESSION['uid'], $_FILES ['img'] ['name'], $_FILES['img']['tmp_name']);
                // 七牛上传
                $add['pic'] = $path;
            } else {
                $add['pic'] = $result['pic'];
            }
            if ($result) {
                $add['aid']         = $this->aid;
                $add['startdate']   = strtotime($post['start_date']);
                $add['enddate']     = strtotime($post['end_date']);
                $add['update_time'] = time();
                $status             = PushAdvModel::whereId($id)->update($add);
                $status ? call_back(0) : call_back(2, '', '编辑失败!');
                //                if ($db->create()) {
                //                    if ($db->where($where)->save()) {
                //                        $this->success('修改成功', U('pushadv/index', '', true, true, true));
                //                    } else {
                //                        $this->error('操作出错');
                //                    }
                //                } else {
                //                    $this->error($db->getError());
                //                }
            }
        } else {
            $get    = $this->request->getGet();
            $id     = $get['id'] ? intval($get['id']) : 0;
            $result = PushAdvModel::select(['id', 'pic'])->whereId($id)->whereAid($this->aid)->get()->toArray();
            $result = $result ? $result[0] : [];
            if (!$result) {
                call_back(2, '', '无此广告信息');
            }
            $result['pic'] = $this->downloadUrl($result['pic']);
            $this->assign('info', $result);
            $this->display();
            //            $id           = isset($_GET['id']) ? intval($_GET['id']) : 0;
            //            $where['id']  = $id;
            //            $where['aid'] = $this->aid;
            //            $result       = D('Pushadv')->where($where)->find();
            //            if ($id) {
            //                if ($result) {
            //                    $result ['pic'] = $this->downloadUrl($result['pic']);
            //                    $this->assign('info', $result);
            //                    $this->display();
            //                } else {
            //                    $this->error('无此广告信息');
            //                }
            //            }
        }
    }

    /**
     * 删除
     *
     * @param $id
     */
    public function del($id)
    {
        $status = PushAdvModel::whereId($id)->update(['is_delete' => 1]);
        $status ? call_back(0) : call_back(2, '', '操作失败!');
        //        $id = isset($_GET['id']) ? intval($_GET[id]) : 0;
        //        if ($id) {
        //            $where['id']  = $id;
        //            $where['aid'] = $this->aid;
        //            $thumb        = D('Pushadv')->where($where)->field("id,pic")->select();
        //            if (D('Pushadv')->delete($id)) {
        //                if (file_exists(".{$thumb[0]['pic']}")) {
        //                    unlink(".{$thumb[0]['pic']}");
        //                }
        //                $this->success('删除成功', U('index'));
        //            } else {
        //                $this->error('操作出错');
        //            }
        //        }
    }

    /**
     *
     */
    public function set()
    {
        $post = $this->request->getPost();
        if ($post) {
            $post['aid'] = $this->aid;
            $wt          = $post['show_time'];
            if (!isNumber($wt)) {
                call_back(2, '', '广告展示时间以秒为单位,请输入展示的时间');
            }
            if ($wt < 3) {
                call_back(2, '', '最低展示时间不能小于3秒');
            }
            $result = AgentPushSetModel::select('id')->whereAid($this->aid)->get()->toArray();
            if ($result) {
                // update
                $status = AgentPushSetModel::whereAid($this->aid)->update($post);

            } else {
                // add
                $post['create_time'] = time();
                $post['update_time'] = time();
                $post['create_by']   = $this->aid;
                $post['update_by']   = $this->aid;
                $status              = AgentPushSetModel::insertGetId($post);
            }
            $status ? call_back(0) : call_back(2, '', '操作失败!');
            //            $db           = D('Agentpushset');
            //            $where['aid'] = $this->aid;
            //            $info         = $db->where($where)->find();
            //            if ($info) {
            //                //update
            //                if ($db->create()) {
            //                    $db->where($where)->save();
            //                    $this->success("操作成功");
            //                } else {
            //                    $this->error($db->getError());
            //                }
            //
            //            } else {
            //                //add
            //                //				dump('add');
            //                if ($db->create()) {
            //                    $id = $db->add();
            //                    $this->success("操作成功");
            //                } else {
            //                    $this->error($db->getError());
            //                }
            //            }
        } else {
            $info = AgentPushSetModel::select([
                'id',
                'aid',
                'push_flag',
                'show_time'
            ])->whereAid($this->aid)->get()->toArray();
            $info = $info ? $info[0] : [];
            $this->assign('info', $info);
            $this->display();
        }

    }

    /**
     * 相应的报表
     */
    public function rpt()
    {
        $way = $this->request->getGet('mode');
        if ($way) {
            $data = $this->_getadrpt($way);
            call_back(0, $data);
        }
        $this->display();
    }

    /**
     * 查询报表数据
     *
     * @param $way
     */
    private function _getAdRpt($way)
    {
        switch (strtolower($way)) {
            case 'today':
                $sql = " select t,CONCAT(CURDATE(),' ',t,'点') as show_date, COALESCE(show_up,0)  as show_up, COALESCE(hit,0)  as hit,COALESCE(hit/show_up*100,0) as rt from wifi_hours a left JOIN ";
                $sql .= '(select thour, sum(show_up)as show_up,sum(hit) as hit from ';
                $sql .= '(select  FROM_UNIXTIME(add_time,"%H") as thour,show_up ,hit from wifi_ad_count';
                $sql .= ' where add_date="' . date('Y-m-d') . '" and mode=50 and agent=' . $this->aid;
                $sql .= ' )a group by thour ) c ';
                $sql .= '  on a.t=c.thour ';
                break;
            case 'yesterday':
                $sql = " select t,CONCAT(CURDATE(),' ',t,'点') as show_date, COALESCE(show_up,0)  as show_up, COALESCE(hit,0)  as hit,COALESCE(hit/show_up*100,0) as rt from wifi_hours a left JOIN ";
                $sql .= '(select thour, sum(show_up)as show_up,sum(hit) as hit from ';
                $sql .= '(select  FROM_UNIXTIME(add_time,"%H") as thour,show_up ,hit from wifi_ad_count';
                $sql .= ' where add_date=DATE_ADD(CURDATE() ,INTERVAL -1 DAY) and mode=50 and agent=' . $this->aid;
                $sql .= ' )a group by thour ) c ';
                $sql .= '  on a.t=c.thour ';
                break;
            case 'week':
                $sql = "  select td as show_date,right(td,5) as td,datediff(td,CURDATE()) as t, COALESCE(show_up,0)  as show_up, COALESCE(hit,0)  as hit ,COALESCE(hit/show_up*100,0) as rt from ";
                $sql .= ' ( select CURDATE() as td ';
                for ($i = 1; $i < 7; $i++) {
                    $sql .= '  UNION all select DATE_ADD(CURDATE() ,INTERVAL -$i DAY) ';
                }
                $sql .= ' ORDER BY td ) a left join ';
                $sql .= '( select add_date,sum(show_up) as show_up ,sum(hit) as hit from wifi_ad_count';
                $sql .= ' where   add_date between DATE_ADD(CURDATE() ,INTERVAL -6 DAY) and CURDATE() and mode=50 and agent= . $this->aid  GROUP BY  add_date';
                $sql .= ' ) b on a.td=b.add_date ';
                break;
            case 'month':
                $t   = date("t");
                $sql = " select tname as show_date,tname as t, COALESCE(show_up,0)  as show_up, COALESCE(hit,0)  as hit,COALESCE(hit/show_up*100,0) as rt from wifi_day  a left JOIN";
                $sql .= '( select right(add_date,2) as td ,sum(show_up) as show_up ,sum(hit) as hit  from wifi_ad_count  ';
                $sql .= ' where   add_date >= "' . date('Y-m-01') . '" and mode=50 and agent= . $this->aid  GROUP BY  add_date';
                $sql .= ' ) b on a.tname=b.td ';
                $sql .= ' where a.id between 1 and  ' . $t;
                break;
            case 'query':
                $sDate = $this->request->getGet('sDate');
                $eDate = $this->request->getGet('eDate');
                import("ORG.Util.Date");
                //$sdt=Date("Y-M-d",$sdate);
                //$edt=Date("Y-M-d",$edate);
                $dt      = new Date($sDate);
                $leftDay = $dt->dateDiff($eDate, 'd');
                $sql     = " select td as show_date,right(td,5) as td,datediff(td,CURDATE()) as t,COALESCE(show_up,0)  as showup, COALESCE(hit,0)  as hit,COALESCE(hit/show_up*100,0) as rt from ";
                $sql .= ' ( select "' . $sDate . '" as td ';
                for ($i = 0; $i <= $leftDay; $i++) {
                    $sql .= '  UNION all select DATE_ADD(' . $sDate . ' ,INTERVAL ' . $i . ' DAY) ';
                }
                $sql .= ' ) a left join ';
                $sql .= '( select add_date,sum(show_up) as show_up ,sum(hit) as hit  from wifi_ad_count ';
                $sql .= ' where  add_date between ' . $sDate . ' and ' . $eDate . '  and mode=50 and agent=' . $this->aid . ' GROUP BY  add_date';
                $sql .= ' ) b on a.td=b.add_date ';
                break;
        }
        $data = DB::select($sql);

        return $data;
        //        $db = D('Adcount');
        //        $rs = $db->query($sql);
        //        $this->ajaxReturn(json_encode($rs));
    }
}