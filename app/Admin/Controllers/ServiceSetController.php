<?php

namespace App\Admin\Controllers;

use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Widgets\Tab;

use App\Admin\Forms\ServiceSetForm;

class ServiceSetController extends AdminController
{
    /**
     * 页面
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content->header('联系客服')
            ->body(function (Row $row){
                $tab = new Tab();
                $tab->add('联系客服', new ServiceSetForm());
                $row->column(12, $tab->withCard());
            });
    }
}
