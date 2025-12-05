<?php

namespace App\Admin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\App;
use Dcat\Admin\Admin;

class BannerController extends AdminController
{

    protected $title = '图片轮播';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(App::make('banner'), function (Grid $grid) {
            $grid->column('banner_id','轮播ID')
                ->sortable();
            $grid->column('image','轮播图')
                ->image('',40,40);
            $grid->column('sort','排序');

            if (Admin::user()->can('shop-banner-create')) {
                $grid->showCreateButton();
            }

            if (Admin::user()->can('shop-banner-edit')) {
                $grid->showEditButton();
            }
            
            if (Admin::user()->can('shop-banner-delete')) {
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
        return Form::make(App::make('banner'), function (Form $form) {
            $form->image('image','轮播图')
                ->uniqueName()
                ->saveFullUrl()
                ->autoUpload()
                ->retainable()
                ->required()
                ->rules('required', [
                    'required' => '请上传图片',
                ])
                ->help('统一图片上传比例：800px * 500px');
            $form->text('sort','排序')
                ->default(0);
        });
    }
}
