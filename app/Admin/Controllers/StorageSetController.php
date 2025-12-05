<?php

namespace App\Admin\Controllers;

use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Widgets\Tab;

use App\Admin\Forms\StorageSetForm;

class StorageSetController extends AdminController
{
    /**
     * 页面
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content->header('上传设置')
            ->body(function (Row $row){
                $tab = new Tab();
                $tab->add('文件上传设置', new StorageSetForm());
                $row->column(12, $tab->withCard());
            });
    }
}
