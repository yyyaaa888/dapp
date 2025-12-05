<?php

namespace App\Admin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\App;
use Dcat\Admin\Admin;

class NoticeController extends AdminController
{
    protected $title = '系统公告';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(App::make('notice'), function (Grid $grid) {
            $grid->column('notice_id','序号')->sortable();
            $grid->column('cn_title','公告标题');
            $grid->column('cn_content','公告内容');
            $grid->column('status','显示状态')
                ->switch();
            $grid->column('created_at','创建时间');

            if (Admin::user()->can('shop-notice-create')) {
                $grid->showCreateButton();
            }

            if (Admin::user()->can('shop-notice-edit')) {
                $grid->showEditButton();
            }
            
            if (Admin::user()->can('shop-notice-delete')) {
                $grid->showDeleteButton();
                $grid->showRowSelector();
                $grid->showBatchDelete();
            }
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(App::make('notice'), function (Form $form) {
            $form->divider('中文');
            $form->text('cn_title','公告标题')
                ->required();
            $form->editor('cn_content','公告内容')
                ->required();
            $form->divider('英语');
            $form->text('en_title','公告标题')
                ->required();
            $form->editor('en_content','公告内容')
                ->required();
            $form->switch('status','显示状态')
                ->default(1);
        });
    }
}
