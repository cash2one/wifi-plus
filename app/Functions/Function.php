<?php
/**
 * User: yongli
 * Date: 17/4/28
 * Time: 16:37
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
use Config\Services;

if (!function_exists('callBack')) {
    /**
     * 接口返回函数
     *
     * @param int    $errCode
     * @param array  $data
     * @param string $msg
     */
    function call_back($errCode = 0, $data = [], $msg = '')
    {
        $errorResult = Services::error()->getAllError();
        $msg         = $msg ??  $errorResult[$errCode];
        $data        = [
            'code' => $errCode,
            'data' => $data,
            'msg'  => $msg,
        ];
        echo json_encode($data);
        die();

    }
}
/**
 * 设置分页
 *
 * @param     $row          总条数
 * @param     $url          跳转链接
 * @param     $uri_segment  当前页码
 * @param int $per_page     每页显示多少条
 *
 * @return mixed
 */
function set_page_config($row, $url, $uri_segment, $per_page = 10)
{
    $config['base_url']          = $url;
    $config['total_rows']        = $row;
    $config['per_page']          = $per_page;//每页显示多少条
    $config['uri_segment']       = $uri_segment;
    $config['num_links']         = 2;//数量链接
    $config['page_query_string'] = true;
    $config['full_tag_open']     = '<ul class="pagination">';
    $config['full_tag_close']    = '</ul>';
    $config['first_link']        = '首页';
    $config['first_tag_open']    = '<li class="pre">';
    $config['first_tag_close']   = '</li>';
    $config['last_link']         = '最后一页';
    $config['last_tag_open']     = '<li>';
    $config['last_tag_close']    = '</li>';
    $config['next_link']         = '下一页';
    $config['next_tag_open']     = '<li class="next">';//下一页
    $config['next_tag_close']    = '</li>';
    $config['prev_link']         = '上一页';
    $config['prev_tag_open']     = '<li>';
    $config['prev_tag_close']    = '</li>';
    $config['cur_tag_open']      = '<li class="active"><a>';//当前页
    $config['cur_tag_close']     = '</a></li>';
    $config['num_tag_open']      = '<li class="num">';
    $config['num_tag_close']     = '</li>';
    $config['use_page_numbers']  = true;

    return $config;
}

/**
 * 并行查询 Post
 *
 * @param      $url_array
 * @param  int $wait_usec
 *
 * @return array|bool
 */
function multi_curl_post($url_array, $wait_usec = 0)
{
    if (!is_array($url_array)) {
        return false;
    }
    $wait_usec = intval($wait_usec);
    $data      = [];
    $handle    = [];
    $running   = 0;
    $mh        = curl_multi_init(); // multi curl handler
    $i         = 0;
    foreach ($url_array as $url_info) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_info['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return don't print
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 7);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $url_info['data']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($url_info['data'])
        ]);
        // 把 curl resource 放进 multi curl handler 里
        curl_multi_add_handle($mh, $ch);
        $handle[$i++] = $ch;
    }
    // 执行
    do {
        curl_multi_exec($mh, $running);
        if ($wait_usec > 0) { // 每个 connect 要间隔多久
            usleep($wait_usec); // 250000 = 0.25 sec
        }
    } while ($running > 0);
    // 读取资料
    foreach ($handle as $i => $ch) {
        $content  = curl_multi_getcontent($ch);
        $data[$i] = (curl_errno($ch) == 0) ? $content : false;
    }
    // 移除 handle
    foreach ($handle as $ch) {
        curl_multi_remove_handle($mh, $ch);
    }
    curl_multi_close($mh);

    return $data;
}

/**
 * 生成随机字符串
 *
 * @param $length
 *
 * @return string
 */
function random($length)
{
    $hash  = '';
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
    $max   = strlen($chars) - 1;
    PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
    for ($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }

    return $hash;
}

/**
 * 获取指定长度的随机密码
 *
 * @param int $length
 *
 * @return string
 */
function get_password($length = 6)
{
    $str = substr(md5(time()), 0, $length);

    return $str;
}

/**
 * 认证页面
 *
 * @param $pos
 *
 * @return string
 */
function get_ad_pos($pos)
{
    switch ($pos) {
        case 0:
            return '首页';
            break;
        case 1:
            return '认证页面';
            break;
        default:
            return '认证页面';
            break;

    }
}

/**
 * 状态
 *
 * @param $id
 *
 * @return string
 */
function get_status($id)
{
    switch ($id) {
        case 0:
            return '停用';
            break;
        case 1:
            return '正常';
            break;
        default:
            return '正常';
            break;

    }
}

/**
 * @param $mode
 *
 * @return string
 */
function get_ad_mode($mode)
{
    switch ($mode) {
        case 0:
            return '图片广告';
            break;
        case 1:
            return '图文广告';
            break;
        default:
            return '图片广告';
            break;

    }
}

/**
 * @param $id
 *
 * @return string
 */
function get_pay_model($id)
{
    $str = [0 => '扣款', 1 => '充值'];

    return isset($str[$id]) ? $str[$id] : '';
}

/**
 * @param $pos
 *
 * @return string
 */
function get_auth_Way($pos)
{
    $str = [0 => '注册认证', 1 => '手机认证', 2 => '免认证'];

    return isset($str[$pos]) ? $str[$pos] : '注册认证';
}

/**
 * @return mixed
 */
function get_agent()
{
    $agent = $_SERVER['HTTP_USER_AGENT'];

    return $agent;
}

/**
 * 获取系统信息
 */
function get_os()
{
    $agent = $_SERVER['HTTP_USER_AGENT'];
    $os    = false;
    if (eregi('win', $agent) && strpos($agent, '95')) {
        $os = 'Windows 95';
    } elseif (eregi('win 9x', $agent) && strpos($agent, '4.90')) {
        $os = 'Windows ME';
    } elseif (eregi('win', $agent) && ereg('98', $agent)) {
        $os = 'Windows 98';
    } elseif (eregi('win', $agent) && eregi('nt 5.1', $agent)) {
        $os = 'Windows XP';
    } elseif (eregi('win', $agent) && eregi('nt 5.2', $agent)) {
        $os = 'Windows 2003';
    } elseif (eregi('win', $agent) && eregi('nt 5', $agent)) {
        $os = 'Windows 2000';
    } elseif (eregi('win', $agent) && eregi('nt', $agent)) {
        $os = 'Windows NT';
    } elseif (eregi('win', $agent) && ereg('32', $agent)) {
        $os = 'Windows 32';
    } elseif (eregi('linux', $agent)) {
        $os = 'Linux';
    } elseif (eregi('unix', $agent)) {
        $os = 'Unix';
    } elseif (eregi('sun', $agent) && eregi('os', $agent)) {
        $os = 'SunOS';
    } elseif (eregi('ibm', $agent) && eregi('os', $agent)) {
        $os = 'IBM OS/2';
    } elseif (eregi('Mac', $agent) && eregi('PC', $agent)) {
        $os = 'Macintosh';
    } elseif (eregi('PowerPC', $agent)) {
        $os = 'PowerPC';
    } elseif (eregi('AIX', $agent)) {
        $os = 'AIX';
    } elseif (eregi('HPUX', $agent)) {
        $os = 'HPUX';
    } elseif (eregi('NetBSD', $agent)) {
        $os = 'NetBSD';
    } elseif (eregi('BSD', $agent)) {
        $os = 'BSD';
    } elseif (ereg('OSF1', $agent)) {
        $os = 'OSF1';
    } elseif (ereg('IRIX', $agent)) {
        $os = 'IRIX';
    } elseif (eregi('FreeBSD', $agent)) {
        $os = 'FreeBSD';
    } elseif (eregi('teleport', $agent)) {
        $os = 'teleport';
    } elseif (eregi('flashget', $agent)) {
        $os = 'flashget';
    } elseif (eregi('webzip', $agent)) {
        $os = 'webzip';
    } elseif (eregi('offline', $agent)) {
        $os = 'offline';
    } else {
        $os = 'Unknown';
    }

    return $os;
}

/**
 * 获取浏览器信息
 */
function get_user_browser()
{
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'Maxthon')) {
        $browser = 'Maxthon';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 12.0')) {
        $browser = 'IE12.0';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 11.0')) {
        $browser = 'IE11.0';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 10.0')) {
        $browser = 'IE10.0';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 9.0')) {
        $browser = 'IE9.0';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 8.0')) {
        $browser = 'IE8.0';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7.0')) {
        $browser = 'IE7.0';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0')) {
        $browser = 'IE6.0';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'NetCaptor')) {
        $browser = 'NetCaptor';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Netscape')) {
        $browser = 'Netscape';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Lynx')) {
        $browser = 'Lynx';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Opera')) {
        $browser = 'Opera';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome')) {
        $browser = 'Chrome';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox')) {
        $browser = 'Firefox';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Safari')) {
        $browser = 'Safari';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'iphone') || strpos($_SERVER['HTTP_USER_AGENT'], 'ipod')) {
        $browser = 'iphone';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'ipad')) {
        $browser = 'iphone';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'android')) {
        $browser = 'android';
    } else {
        $browser = 'other';
    }

    return $browser;
}

/**
 * @param $val
 * @param $data
 */
function show_auth_check($val, $data)
{
    if (strpos($data, "#" . $val . "#") > -1) {
        echo 'checked';
    } else {
        if (strpos($data, "#" . $val . "=") > -1) {
            echo 'checked';
        }
    }
}

/**
 * @param $val
 * @param $key
 *
 * @return mixed
 */
function echo_json_key($val, $key)
{
    $json = json_decode($val);
    $str  = ['pwd' => $json->pwd, 'user' => $json->user];

    return isset($str[$key]) ? $str[$key] : '';

}

/**
 * @param $val
 * @param $data
 *
 * @return mixed
 */
function show_auth_data($val, $data)
{
    $tmp = explode('#', $data);
    foreach ($tmp as $v) {
        if ($v != '#' && $v != '') {
            $arr[] = $v;
        }
    }
    foreach ($arr as $v) {
        $temp = explode('=', $v);
        if (count($temp) > 1 && $temp[0] == $val) {
            //$dt=json_decode($temp[1]);
            return $temp[1];
            break;
        }
    }

}

/**
 * 导出数据为excel表格
 *
 * @param array  $data     一个二维数组,结构如同从数据库查出来的数组
 * @param array  $title    excel的第一行标题,一个数组,如果为空则没有标题
 * @param string $filename 下载的文件名
 *                         $stu = M ('User');
 *                         $arr = $stu -> select();
 *                         exportexcel($arr,array('id','账户','密码','昵称'),'文件名!');
 */
function export_excel($data = [], $title = [], $filename = 'report')
{
    header("Content-type:application/octet-stream");
    header("Accept-Ranges:bytes");
    header("Content-type:application/vnd.ms-excel");
    header("Content-Disposition:attachment;filename=" . $filename . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    //导出xls 开始
    if (!empty($title)) {
        foreach ($title as $k => $v) {
            $title[$k] = iconv("UTF-8", "GB2312", $v);

        }
        $title = implode("\t", $title);
        echo "$title\n";
    }
    if (!empty($data)) {
        foreach ($data as $key => $val) {
            foreach ($val as $ck => $cv) {
                $data[$key][$ck] = iconv("UTF-8", "GB2312", $cv);
            }
            $data[$key] = implode("\t", $data[$key]);

        }
        echo implode("\n", $data);
    }
}

/**
 * 输出excel，配置标题和匹配的字段
 *
 * @param array  $data
 * @param array  $title
 * @param string $filename
 */
function export_excel_by_key($data = [], $title = [], $filename = 'report')
{
    header("Content-type:application/octet-stream");
    header("Accept-Ranges:bytes");
    header("Content-type:application/vnd.ms-excel");
    header("Content-Disposition:attachment;filename=" . $filename . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    //导出xls 开始
    if (!empty($title)) {
        foreach ($title as $k) {
            //$excel_head[]=iconv("UTF-8", "GB2312",$k[0]);
            $excel_head[] = iconv("UTF-8", "GB2312", $k[0]);
            $field[]      = $k[1];
        }
        $excel_head = implode("\t", $excel_head);
        echo $excel_head . "\n";
    }
    if (!empty($data)) {
        foreach ($data as $obj) {
            $line = null;
            foreach ($field as $fv) {
                $line[] = iconv("UTF-8", "GB2312", $obj[$fv]);
                //$Line[]=$data[$key][$fv];
            }
            $line = implode("\t", $line);
            echo $line . "\n";
        }
    }
}

/**
 * @param $pos
 *
 * @return string
 */
function sms_state($pos)
{
    $str = [0 => '等待发送', 1 => '已发送', 2 => '发送失败,等待重发'];

    return isset($str[$pos]) ? $str[$pos] : '等待发送';
}

/**
 * @param $id
 *
 * @return string
 */
function get_cate_log($id)
{
    $str = [0 => '图文', 1 => '链接', 2 => '电话', 3 => '地图导航'];

    return isset($str[$id]) ? $str[$id] : '图文';
}

/**
 * @param $list
 * @param $id
 *
 * @return string
 */
function get_art_cate($list, $id)
{
    $rs = "";
    foreach ($list as $k => $v) {
        if ($v['id'] == $id) {
            $rs = $v['title'];
            break;
        }
    }

    return $rs;
}

/**
 * @param $id
 *
 * @return string
 */
function get_news_mode($id)
{
    $str = [1 => '新闻中心', 2 => '产品动态'];

    return isset($str[$id]) ? $str[$id] : '';
}

/**
 * @param $vo
 * @param $cid
 *
 * @return string
 */
function show_class_common($vo, $cid)
{
    $html2 = '';
    switch ($vo['mode']) {
        case 0:
            $html2 .= U('Api/Wap/lists', ['classid' => $vo['id'], 'sid' => $vo['uid'], 'cid' => $cid]);

            return $html2;
            break;
        case 1:
            $html2 = $vo['titleurl'];

            return $html2;
            break;
        case 2:
            $html2 .= "tel:" . $vo['tel'];

            return $html2;
            break;
        case 3:
            //http://api.map.baidu.com/marker?location=40.047669,116.313082&title=我的位置&content=百度奎科大厦&output=html&src=yourComponyName|yourAppName
            //http://api.map.baidu.com/marker?location=39.892963,116.313504&title=%E5%BE%AE%E7%9B%9F&name=%E5%BE%AE%E7%9B%9F&content=%E4%B8%8A%E6%B5%B7%E5%B8%82%E6%9D%A8%E6%B5%A6%E5%8C%BA%E4%BA%94%E8%A7%92%E5%9C%BA&output=html&src=weiba|weiweb
            //坐标是纬度进度
            $url = "http://api.map.baidu.com/marker?location=" . $vo['point_y'] . "," . $vo['point_x'] . "&title=" . '福清' . "&content=" . "福清市" . "&output=html&src=weiyibai|weiyibai";

            return $url;
            break;

    }

}

/**
 * @param $vo
 *
 * @return string
 */
function show_map_url($vo)
{
    //dump($vo);
    return "http://api.map.baidu.com/marker?location=" . $vo['point_y'] . "," . $vo['point_x'] . "&title=" . urlencode($vo['shop_name']) . "&content=" . urlencode($vo['address']) . "&output=html&src=weiyibai|weiyibai";
}

/**
 * @param $vo
 *
 * @return string
 */
function show_map_url_shop($vo)
{
    //dump($vo);
    return "http://api.map.baidu.com/marker?location=" . $vo['point_y'] . "," . $vo['point_x'] . "&title=" . urlencode($vo['shop_name']) . "&content=" . urlencode($vo['shopaddress']) . "&output=html&src=weiyibai|weiyibai";
}

/**
 * @return string
 */
function get_ser_data()
{
    $data['host']   = $_SERVER['HTTP_HOST'];
    $data['server'] = $_SERVER['SERVER_NAME'];
    $data['soft']   = $_SERVER['SERVER_SOFTWARE'];
    $data['ip']     = $_SERVER['SERVER_ADDR'];
    $data['port']   = $_SERVER['port'];
    $data['doc']    = $_SERVER['DOCUMENT_ROOT'];

    return base64_encode(json_encode($data));
}

/**
 * @return string
 */
function get_version()
{
    return json_encode(['auth' => get_ser_data(), 'ver' => '5910416']);
}

/**
 * @param $info
 *
 * @return string
 */
function cut_nws_info($info)
{
    $str = strip_tags($info);

    return substr($str, 0, 20);
}

/**
 * 验证手机号
 *
 * @param $val
 *
 * @return bool
 */
function is_phone($val)
{
    if (ereg("^1[1-9][0-9]{9}$", $val)) {
        return true;
    }

    return false;

}

/**
 * 验证url
 *
 * @param $val
 *
 * @return bool
 */
function is_Url($val)
{
    $val     = trim($val);
    $tmpUrl1 = substr($val, 0, 7);
    if ($tmpUrl1 == 'http://') {
        return true;
    }
    $tmpUrl2 = substr($val, 0, 8);
    if ($tmpUrl2 == 'https://') {
        return true;
    }

    return false;

}

/**
 * 验证密码
 *
 * @param $val
 *
 * @return bool
 */
function validate_pwd($val)
{
    if (ereg("^[a-zA-Z0-9_]{4,20}$", $val)) {
        return true;
    }

    return false;
}


