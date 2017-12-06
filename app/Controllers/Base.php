<?php
/**
 * User: yongli
 * Date: 17/9/19
 * Time: 00:17
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers;

use YP\Core\YP_Controller;

class Base extends YP_Controller
{
    public $p = '';//正式环境中这里要改成''

    /**
     * 用户ID
     *
     * @var
     */
    public $uid;

    /**
     * 构造函数
     */
    public function initialization()
    {
        parent::initialization();
        //读取模板主题路径
        $theme_path = $this->_getThemePath();
        $this->_isLogin();
        //        $public = [
        //            'css'  => $this->p . '/UI/Public/css',
        //            'js'   => $this->p . '/UI/Public/js',
        //            'img'  => $this->p . '/UI/Public/images/',
        //            'root' => $this->p . '/UI/Public'
        //        ];
        //        $theme  = [
        //            'css'  => $theme_path . '/style/css',
        //            'js'   => $theme_path . '/style/js',
        //            'img'  => $theme_path . '/style/images',
        //            'root' => $theme_path . '/'
        //        ];
        //        $style  = ['P' => $public, 'T' => $theme];
        //        $this->assign('Theme', $style);
        //        $this->assign('action', $this->getActionName());
        $this->uid = $_SESSION['uid'] ?? 20;

    }

    /**
     * 获得主题的路径
     *
     * @return string
     */
    private function _getThemePath()
    {
        $theme = 'default';
        $group = defined('GROUP_NAME') ? GROUP_NAME . '/' : '';
        // 模板目录
        //        define('TMP_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'UI' . DIRECTORY_SEPARATOR);
        //        return $theme_path = '/' . basename(TMP_PATH) . '/' . $group . $theme;
        //        if (1 == C('APP_GROUP_MODE')) { // 独立分组模式
        //            return $theme_path = '/' . dirname(BASE_LIB_PATH) . '/' . $group . basename(TMPL_PATH) . '/' . $theme;
        //        } else {
        //            return $theme_path = '/' . basename(TMPL_PATH) . '/' . $group . $theme;
        //        }
    }

    /**
     * 上传文件
     *
     * @param $uid
     * @param $file_name
     * @param $tmp_file
     *
     * @return array
     */
    protected function uploadFile($uid, $file_name, $tmp_file)
    {
        if (is_null($_FILES ['img'] ['name']) || $_FILES ['img'] ['name'] == "") {
            return [null, "没有选择图片,上传失败"];
        }
        $storeType = getenv('STORE_TYPE');
        if ($storeType == 1) {
            $subDir    = date('Y/m/d', time());
            $file      = pathinfo($_FILES['img']['name']);
            $uploadDir = FRONT_PATH . '/Upload/ad/' . $subDir;
            $upload    = new \YP\Libraries\YP_Upload($_FILES['img']['tmp_name'], time() . '.' . $file['extension']);
            $upload->move($uploadDir);
            if ($upload->getError()) {
                call_back(2, '', json_encode($upload->getError()));
            }
            //            import('ORG.Net.UploadFile');
            //            $upload            = new UploadFile ();
            //            $upload->maxSize   = C('AD_SIZE');
            //            $upload->allowExts = C('AD_IMGEXT');
            //            $upload->savePath  = C('AD_SAVE');
            //            if (!$upload->upload()) {
            //                return [null, $upload->getErrorMsg()];
            //            } else {
            //                $info     = $upload->getName();
            $save_name = $upload->getName();
            $key       = '/Upload/ad/' . $subDir;;

            return $key;
            //            }
        } else if ($storeType == 2) {
            require APP_PATH . 'ThirdParty/QiNiu/io.php';
            require APP_PATH . 'ThirdParty/QiNiu/rs.php';
            $key1      = md5($uid . time()) . $file_name;
            $qi_niu    = getenv('QINIU');
            $accessKey = $qi_niu ['accessKey'];
            $secretKey = $qi_niu ['secretKey'];
            $bucket    = $qi_niu ['bucket'];
            Qiniu_SetKeys($accessKey, $secretKey);
            $putPolicy       = new Qiniu_RS_PutPolicy ($bucket);
            $upToken         = $putPolicy->Token(null);
            $putExtra        = new Qiniu_PutExtra ();
            $putExtra->Crc32 = 1;
            $rs              = Qiniu_PutFile($upToken, $key1, $tmp_file, $putExtra);

            return $rs;
        } else { //百度
            require APP_PATH . 'ThirdParty/BaiDu/bcs.php';
            $bsc       = C('BSC');
            $key1      = md5($uid . time()) . $file_name;
            $accessKey = $bsc ['accessKey'];
            $secretKey = $bsc ['secretKey'];
            $bucket    = $bsc ['bucket'];
            $host      = $bsc ['host'];
            $baiDuBcs  = new BaiduBCS ($accessKey, $secretKey, $host);
            $response  = $baiDuBcs->get_bucket_acl($bucket);
            if ($response->status == '403') {
                $acl      = BaiduBCS::BCS_SDK_ACL_TYPE_PRIVATE;
                $response = $baiDuBcs->create_bucket($bucket, $acl);
            }
            if ($response->status != '200') {
                return [null, '创建bucket失败'];
            }
            $opt                                   = [];
            $opt ['acl']                           = BaiduBCS::BCS_SDK_ACL_TYPE_PUBLIC_WRITE;
            $opt [BaiduBCS::IMPORT_BCS_LOG_METHOD] = "bs_log";
            $opt ['curlopts']                      = [CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_TIMEOUT => 1800];
            //print_r($tmp_file);exit;
            $response = $baiDuBcs->create_object($bucket, '/' . $key1, $tmp_file, $opt);
            if ($response->status == '200') {
                return [['key' => $key1], null];
            } else {
                return [null, '上传失败'];
            }
        }

    }

    /**
     * 生成下载连接
     *
     * @param $file_name
     *
     * @return string
     */
    protected function downloadUrl($file_name)
    {
        $storeType = getenv('STORE_TYPE');
        if ($storeType == 1) {
            return $file_name;
        } else if ($storeType == 2) {
            require APP_PATH . 'ThirdParty/QiNiu/rs.php';
            $qi_niu    = getenv('QINIU');
            $accessKey = $qi_niu ['accessKey'];
            $secretKey = $qi_niu ['secretKey'];
            $domain    = $qi_niu ['domain'];
            Qiniu_SetKeys($accessKey, $secretKey);
            $baseUrl    = Qiniu_RS_MakeBaseUrl($domain, $file_name);
            $getPolicy  = new Qiniu_RS_GetPolicy ();
            $privateUrl = $getPolicy->MakeRequest($baseUrl, null);

            return $privateUrl;
        } else {
            return 'http://' . $_SERVER['SERVER_NAME'] . $this->p . '/index.php/download/img?img_name=' . $file_name;

        }
    }

    protected function delete($key1)
    {
        $storeType = C('STORE_TYPE');
        if ($storeType == 1) {
            //删除本地
            if (file_exists($key1)) {
                $result = unlink($key1);

                return $result;
            }

            return false;
        } else if ($storeType == 2) {
            import("qiniu.rs", dirname(__FILE__), '.php');
            $qiniu     = C('QINIU');
            $accessKey = $qiniu ['accessKey'];
            $secretKey = $qiniu ['secretKey'];
            $bucket    = $qiniu ['bucket'];
            Qiniu_SetKeys($accessKey, $secretKey);
            $client = new Qiniu_MacHttpClient(null);
            $err    = Qiniu_RS_Delete($client, $bucket, $key1);
            if ($err !== null) {
                return false;
            } else {
                echo true;
            }
        } else {
            //TODO 删除百度
        }
    }

    /**
     * 检测是否登录
     */
    private function _isLogin()
    {
        if (!isset($_SESSION['uid']) || !$_SESSION['uid']) {
            $url = 'http://' . $_SERVER['HTTP_HOST'];
            header('Location: ' . $url . '/Index/Index/log');
        }
    }
}