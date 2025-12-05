<?php

use Dcat\Admin\Admin;
use Dcat\Admin\Grid;
use Dcat\Admin\Form;
use Dcat\Admin\Grid\Filter;
use Dcat\Admin\Show;

/**
 * Dcat-admin - admin builder based on Laravel.
 * @author jqh <https://github.com/jqhph>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 *
 * extend custom field:
 * Dcat\Admin\Form::extend('php', PHPEditor::class);
 * Dcat\Admin\Grid\Column::extend('php', PHPEditor::class);
 * Dcat\Admin\Grid\Filter::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */
Grid::resolving(function (Grid $grid) {
    // 开启字段选择器功能
    $grid->showColumnSelector();
    
    // 设置表格文字居中
    $grid->addTableClass(['table-text-center']);

    // $grid->tableCollapse(false);
    // 全局设置分页
    $grid->paginate(10);
    // 全局设置按照ID倒叙
    // $grid->model()->orderBy('id', 'desc');
    // 隐藏详情按钮
    $grid->disableViewButton();
    // 隐藏筛选按钮
    $grid->disableFilterButton();
    // 隐藏刷新按钮
    $grid->disableRefreshButton();
    // 显示横向滚动条
    // $grid->scrollbarX();

    // 筛选条件样式
    $grid->filter(function (Grid\Filter $filter){
        $filter->panel();
        $filter->expand(true);
    });
});

Form::resolving(function (Form $form) {
    $form->disableViewButton();
    $form->disableDeleteButton();
    $form->disableViewCheck();
    $form->disableCreatingCheck();
    $form->disableEditingCheck();
});