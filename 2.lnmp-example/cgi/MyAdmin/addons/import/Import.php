<?php

namespace addons\import;

use think\Addons;
use app\common\library\Menu;

/**
 * import 可视化数据导入辅助
 */
class Import extends Addons
{
    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        $menu = [
            [
                'name'    => 'import/log',
                'title'   => '数据导入',
                'remark'  => '数据导入辅助',
                'icon'    => 'fa fa-file-excel-o',
                'sublist' => [
                    ['name' => 'import/log/index', 'title' => '查看'],
                    ['name' => 'import/log/add', 'title' => '添加'],
                    ['name' => 'import/log/edit', 'title' => '修改'],
                    ['name' => 'import/log/fileData', 'title' => '读取文件数据'],
                    ['name' => 'import/log/preview', 'title' => '预览'],
                    ['name' => 'import/log/del', 'title' => '删除'],
                ],
            ]
        ];
        Menu::create($menu);
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        Menu::delete('import/log');
        return true;
    }

    /**
     * 插件启用方法
     */
    public function enable()
    {
        Menu::enable('import/log');
    }

    /**
     * 插件禁用方法
     */
    public function disable()
    {
        Menu::disable('import/log');
    }

}
