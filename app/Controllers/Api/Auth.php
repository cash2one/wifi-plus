<?php
/**
 * User: yongli
 * Date: 17/9/19
 * Time: 00:33
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers\Api;

use App\Controllers\BaseApi;

/**
 * Class Auth
 *
 * @package App\Controllers\Api
 */
class Auth extends BaseApi
{
    private $shop   = false;
    private $tplkey = '';
    private $token;
    private $mac;
    private $ip;
    private $gw_id;

    /**
     *
     */
    public function index()
    {

        if (!empty($_REQUEST['ip'])) {
            $this->ip = $_REQUEST['ip'];

        }
        if (!empty($_REQUEST['gw_id'])) {
            $this->gw_id = $_REQUEST['gw_id'];

        }
        $this->mac = '';
        if (!empty ($_REQUEST ['mac'])) {
            $this->mac = $_REQUEST ['mac'];
        }
        if (!empty ($_REQUEST ['token'])) {
            $tk = $_REQUEST ['token'];
            //			$tks = explode ( "_", $tk );
            $db     = new Model ();
            $authdb = D('Authlist');
            if (!empty($this->mac)) {
                //$where ['mac'] = $this->mac;
            }
            $where ['token'] = $tk;
            $rs              = $authdb->where($where)->field()->find();
            $mdb             = D('Member');
            $wherem['token'] = $tk;
            $user            = $mdb->where($wherem)->find();
            if ($user != false) {
                $mdata['mac'] = $this->mac;
                $mdb->where($wherem)->save($mdata);

            }
            if ($rs) {
                //update time
                if (empty ($rs ['over_time']) || $rs ['over_time'] == "") {
                    //no limit
                    $this->token = $tk;
                    echo("Auth: 1n");
                    echo("Messages: Allow Accessn");
                    $data ['mac']         = $this->mac;
                    $data ['login_ip']    = $this->ip;
                    $data ['pingcount']   = $rs ['pingcount'] + 1;
                    $data ['last_time']   = time(); //
                    $data ['update_time'] = time(); //
                    $authdb->where($where)->save($data);
                    exit ();
                } else {
                    //limit
                    $lf = $rs ['over_time'] - time();
                    if ($lf < 0) {
                        //log::write('超时了');
                        echo("Auth: 0n");
                        echo("Messages: No Accessn");
                        exit ();
                    } else {
                        $this->token = $tk;
                        echo("Auth: 1n");
                        echo("Messages: Allow Accessn");
                        $data ['mac']         = $this->mac;
                        $data ['login_ip']    = $this->ip;
                        $data ['pingcount']   = $rs ['pingcount'] + 1;
                        $data ['last_time']   = time(); //
                        $data ['update_time'] = time(); //
                        $authdb->where($where)->save($data);
                        exit ();
                    }
                }
            }
            else {
                echo("Auth: 0n");
                echo("Messages: No Accessn");
                exit ();
            }
        } else {
            echo("Auth: 0n");
            echo("Messages: No Accessn");
            exit ();
        }
    }
}