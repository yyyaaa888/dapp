<?php

namespace App\Admin\Controllers;

use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Widgets\Tab;

use App\Admin\Forms\HelpSetForm;

class HelpSetController extends AdminController
{
    /**
     * 页面
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content->header('帮助中心')
            ->body(function (Row $row){
                $tab = new Tab();
                $tab->add('帮助中心', new HelpSetForm());
                $row->column(12, $tab->withCard());
            });
    }
}
