<?php
/**
 * User: yongli
 * Date: 17/9/19
 * Time: 00:22
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers;

class BaseAgent extends Base
{
    public $aid;//代理商用户ID

    /**
     * 当前页码
     *
     * @var int
     */
    public $page = 1;

    /**
     * 每页显示条数
     * 
     * @var int
     */
    public $perPage = 15;

    /**
     * 初始化
     */
    public function initialization()
    {
        parent::initialization();
        $this->page    = $this->request->getGet('per_page') ? $this->request->getGet('per_page') :  $this->page;

        $this->perPage = $this->request->getGet('perPage') ? $this->request->getGet('perPage') : $this->perPage;
        if (!isset($_SESSION['aid']) || !$_SESSION['aid']) {
            $this->redirect('index/index/alog');
        } else {
            $this->aid = session('aid');
            $this->loadMenu();
        }
    }

    /**
     * 加载菜单
     */
    private function loadMenu()
    {
        $path = CONF_PATH . GROUP_NAME . "/Menu.php";
        if (is_file($path)) {
            $config = require $path;
        }
        $this->assign("menu", $config);
    }
}