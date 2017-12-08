<?php
/**
 * User: yongli
 * Date: 17/12/8
 * Time: 13:52
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers\WifiAdmin;

/**
 * ·��������
 */
class RouteAction extends AdminAction{
    /**
     * [_initialize ���캯��]
     * @return [type] [description]
     */
    protected function _initialize(){
        parent::_initialize();
        $this->doLoadID(900);
    }

    /**
     * [index ·���б�]
     * @return [type] [description]
     */
    public function index(){
        // ����page��
        import('@.ORG.AdminPage');
        $db=D('Routemap');
        // �ж��Ƿ���POST�����ύ
        if (isset($_POST) && !empty($_POST)){
            // ��ѯ����
            if(isset($_POST['sname'])&&$_POST['sname']!=""){
                $map['sname']=$_POST['sname'];
                $where.=" and b.shopname like '%%%s%%'";
            }
            if(isset($_POST['slogin'])&&$_POST['slogin']!=""){
                $map['slogin']=$_POST['slogin'];
                $where.=" and b.account like '%%%s%%'";
            }
            if(isset($_POST['mac'])&&$_POST['mac']!=""){
                $map['mac']=$_POST['mac'];
                $where.=" and a.gw_id like '%%%s%%'";
            }
            if(isset($_POST['agent'])&&$_POST['agent']!=""){
                $map['agent']=$_POST['agent'];
                $where.=" and c.name like '%%%s%%'";
            }
            $_GET['p']=0;
        }else{
            if(isset($_GET['sname'])&&$_GET['sname']!=""){
                $map['sname']=$_GET['sname'];
                $where.=" and b.shopname like '%%%s%%'";

            }
            if(isset($_GET['slogin'])&&$_GET['slogin']!=""){
                $map['slogin']=$_GET['slogin'];
                $where.=" and b.account like '%%%s%%'";
            }
            if(isset($_GET['mac'])&&$$_GET['mac']!=""){
                $map['phone']=$_GET['mac'];
                $where.=" and a.gw_id like '%%%s%%'";
            }
            if(isset($_GET['agent'])&&$_GET['agent']!=""){
                $map['agent']=$_GET['agent'];
                $where.=" and c.name like '%%%s%%'";
            }
        }
        // ͳ��·��������
        $sqlcount=" select count(*) as ct from ". C('DB_PREFIX')."routemap a left join ". C('DB_PREFIX')."shop b on a.shopid=b.id  left join ". C('DB_PREFIX')."agent c on b.pid=c.id ";
        if(!empty($where)){
            $sqlcount.=" where true ".$where;
        }
        $rs=$db->query($sqlcount,$map);
        $count=$rs[0]['ct'];

        $page=new AdminPage($count,C('ADMINPAGE'));
        foreach($map as $k=>$v){
            $page->parameter.=" $k=".urlencode($v)."&";//��ֵ��Page";
        }
        $sql=" select a.* ,b.shopname from ". C('DB_PREFIX')."routemap a left join ". C('DB_PREFIX')."shop b on a.shopid=b.id  left join ". C('DB_PREFIX')."agent c on b.pid=c.id ";
        if(!empty($where)){
            $sql.=" where true ".$where;
        }
        $sql.=" order by a.id desc limit ".$page->firstRow.','.$page->listRows." ";
        // ���·������
        $result = $db->query($sql,$map);
        // ����ҳ��
        $this->assign('page',$page->show());
        // ����·������
        $this->assign('lists',$result);
        $this->display();

    }

    /**
     * [edit �༭·��]
     * @return [type] [description]
     */
    public function edit(){
        // �ж��Ƿ���POST�����ύ
        if (isset($_POST) && !empty($_POST)){
            $db= D('Routemap');
            $id = I('post.id','0','int');
            $where['id']=$id;
            $result =$db->where($where)->field('id')->find();
            if($result==false){
                $this->error('û�д�·����Ϣ');
                exit;
            }
            // �Զ���֤POST����
            if($db->create()){
                // ������µ�����
                if($db->where($where)->save()){
                    $this->success('���³ɹ�',U('index'));
                }else{
                    $this->error("����ʧ��");
                }
            }else{
                // �Զ���֤ʧ��
                $this->error($db->getError());
            }
        }else{
            // ��õ�ǰҪ�༭��·������
            $id=I('get.id','0','int');
            $where['id']=$id;
            $db=D('Routemap');
            $info=$db->where($where)->find();
            if(!$info){
                $this->error("��������ȷ");
            }
            // ��������
            $this->assign("info",$info);
            $this->display();
        }
    }

    /**
     * [del ɾ��·��]
     * @return [type] [description]
     */
    public function del(){
        // ���ɾ����·��id
        $id = I('get.id','0','int');
        // ��ѯ����
        $where['id']=$id;
        // ��õ�ǰҪɾ����·�ɵ���Ϣ
        $r = D('Routemap')->where($where)->find();
        if($r==false){
            $this->error('û�д�·����Ϣ');
        }else{
            // ɾ����ǰ·������
            if(D('Routemap')->where($where)->delete()){
                $this->success('ɾ���ɹ�');
            }else{
                $this->error('ɾ��ʧ��');
            }
        }
    }

}