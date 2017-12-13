<?php
/**
 * User: yongli
 * Date: 17/12/8
 * Time: 13:48
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers\WifiAdmin;

use App\Controllers\BaseAdmin;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Class PubAction
 *
 * @package App\Controllers\WifiAdmin
 */
class Pub extends BaseAdmin
{
    /**
     * 上网统计
     */
    public function getAuthRpt()
    {
        $way = $this->request->getGet('mode');
        switch (strtolower($way)) {
            case 'today':
                $sql = 'select t,CONCAT(CURDATE()," ",t,"点") as show_date, COALESCE(ct,0)  as ct ,COALESCE(ct_reg,0)  as ct_reg,COALESCE(ct_phone,0)  as ct_phone,COALESCE(ct_key,0)  as ct_key,COALESCE(ct_log,0)  as ct_log from wifi_hours a left JOIN ';
                $sql .= '( select thour ,count(*) as ct ,count(case when mode=0 then 1 else null end) as ct_reg,count(case when mode=1 then 1 else null end) as ct_phone,count(case when mode=2 then 1 else null end) as ct_key,count(case when mode=3 then 1 else null end) as ct_log from ';
                $sql .= '(select shop_id,mode,FROM_UNIXTIME(login_time,"%H") as thour, ';
                $sql .= ' FROM_UNIXTIME(login_time,"%Y-%m-%d") as d from wifi_auth_list ) a ';
                $sql .= 'where d="' . date("Y-m-d") . '"';
                $sql .= ' group by thour ) ';
                $sql .= ' b on a.t=b.thour ';
                break;
        }
        $result = DB::select($sql);
        call_back(0, $result);
    }

    /**
     * 用户统计
     */
    public function getUserChart()
    {
        $way   = $this->request->getGet('mode');
        $where = ' where shop_id=' . $this->uid;
        switch (strtolower($way)) {
            case "today":
                $sql = 'select t,CONCAT(CURDATE()," ",t,"点") as showdate, COALESCE(totalcount,0)  as totalcount, COALESCE(regcount,0)  as regcount ,COALESCE(phonecount,0) as phonecount from wifi_hours a left JOIN ';
                $sql .= '(select thour, count(id) as total_count , count(CASE when mode=0 then 1 else null end) as reg_count, count(CASE when mode=1 then 1 else null end) as phone_count from ';
                $sql .= '(select  FROM_UNIXTIME(add_time, "%H") as thour,id,mode from wifi_member ';
                $sql .= ' where add_date="' . date('Y-m-d', time()) . '" and ( mode=0 or mode=1 ) ';
                $sql .= ' )a group by thour ) c ';
                $sql .= 'on a.t=c.thour ';
                break;

        }
        $result = DB::select($sql);
        call_back(0, $result);
    }

    /**
     * 广告报表
     */
    public function getAdRpt()
    {
        $way = $this->request->getGet('mode');
        switch (strtolower($way)) {
            case 'today':
                $sql = 'select t,CONCAT(CURDATE()," ",t,"点") as show_date, COALESCE(show_up,0)  as showup, COALESCE(hit,0)  as hit,COALESCE(hit/show_up*100,0) as rt from wifi_hours a left JOIN ';
                $sql .= '(select thour, sum(show_up)as show_up,sum(hit) as hit from ';
                $sql .= '(select  FROM_UNIXTIME(add_time,"%H") as thour,show_up ,hit from wifi_ad_count ';
                $sql .= 'where add_date="' . date('Y-m-d', time()) . '" and mode=1 ';
                $sql .= ')a group by thour ) c ';
                $sql .= '  on a.t=c.thour ';
                break;

        }
        $result = DB::select($sql);
        call_back(0, $result);
    }

    /**
     * 推送广告报表
     */
    public function getPubAdRpt()
    {
        $way = $this->request->getGet('mode');
        switch (strtolower($way)) {
            case 'today':
                $sql = 'select t,CONCAT(CURDATE()," ",t,"点") as show_date, COALESCE(show-up,0)  as show_up, COALESCE(hit,0)  as hit,COALESCE(hit/show_up*100,0) as rt from wifi_hours a left JOIN ';
                $sql .= '(select thour, sum(show_up)as show_up,sum(hit) as hit from ';
                $sql .= '(select  FROM_UNIXTIME(add_time,"%H") as thour,show_up ,hit from wifi_ad_count ';
                $sql .= 'where add_date="' . date('Y-m-d', time()) . '" and mode=99 ';
                $sql .= ')a group by thour ) c ';
                $sql .= 'on a.t=c.thour ';
                break;
        }
        $result = DB::select($sql);
        call_back(0, $result);
    }
}