<?php

namespace App\Admin\Controllers;

use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Widgets\Tab;

use App\Admin\Forms\BasicSetForm;

class BasicSetController extends AdminController
{
    /**
     * 页面
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content->header('基础配置')
            ->body(function (Row $row){
                $tab = new Tab();
                $tab->add('基础配置', new BasicSetForm());
                $row->column(12, $tab->withCard());
            });
    }
}
