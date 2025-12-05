<?php

namespace App\Admin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use App\Admin\Actions\Grid\RowAction\RechargeAction;
use App\Service\Api\Factory;

class UserController extends AdminController
{
    protected $title = '用户列表';
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(App::make('user')->with(['direct:address,up_address,personal_amount,team_amount,max_amount']), function (Grid $grid) {
            $enable = App::make('user')->enable();
            $userGrade = App::make('user')->userGrade();
            $grid->model()->orderBy('user_id', 'desc');
            // $grid->column('user_id','序号');
            $grid->column('address','用户地址');
            $grid->column('up_address','上级地址');
            $grid->column('grade_id','用户等级')
                ->display(function() use ($userGrade){
                    return $this->grade_id ? $userGrade[$this->grade_id] : '';
                });
            $grid->column('usdt','USDT余额');
            $grid->column('wbsc','WBSC余额');
            $grid->column('direct_num','有效直推');
            $grid->column('total_deposit_amount','投注本金');
            $grid->column('total_deposit_profit','投注收益');
            $grid->column('release_deposit_profit','获得收益');
            $grid->column('personal_amount','个人业绩');
            $grid->column('team_amount','团队业绩');
            $grid->column('min_region_amount','小区业绩')
                ->display(function(){
                    if(!$this->direct->isEmpty()){
                        $directList = $this->direct->pluck('total_amount','address')->toArray();
                        // 最大区业绩
                        $maxAmount = max($directList);
                        // 最小区业绩
                        $minAmount = bcsub(array_sum($directList), max($directList), 3);
                        return $minAmount;
                    }else{
                        return sprintf("%.3f", 0);
                    }
                });
            $grid->column('max_region_amount','大区业绩')
                ->display(function(){
                    if(!$this->direct->isEmpty()){
                        $directList = $this->direct->pluck('total_amount','address')->toArray();
                        // 最大区业绩
                        $maxAmount = max($directList);
                        // 最小区业绩
                        $minAmount = bcsub(array_sum($directList), max($directList), 3);
                        return $maxAmount;
                    }else{
                        return sprintf("%.3f", 0);
                    }
                });
            $grid->column('is_deposit','入金状态')
                ->using(['0'=>'未入金','1'=>'已入金'])
                ->dot(['0'=>'orange1','1'=>'success']);
            $grid->column('is_node','节点状态')
                ->using(['0'=>'无','1'=>'小节点','2'=>'大节点'])
                ->dot(['0'=>'orange1','1'=>'success','2'=>'blue']);
            $grid->column('is_stop_profit','停止收益')
                ->using(['0'=>'正常','1'=>'停止'])
                ->dot(['0'=>'success','1'=>'red']);
            $grid->column('is_stop_withdraw','禁止提现')
                ->using(['0'=>'正常','1'=>'禁止'])
                ->dot(['0'=>'success','1'=>'red']);
            $grid->column('created_at','注册时间');

            $grid->fixColumns(0, -1);

            $grid->setActionClass(Grid\Displayers\DropdownActions::class);

            $grid->filter(function (Grid\Filter $filter) use ($enable,$userGrade){
                $filter->equal('address','用户地址')->width(4);
                $filter->equal('up_address','上级地址')->width(4);
                $filter->equal('grade_id','用户级别')
                    ->select($userGrade)
                    ->width(2);
                $filter->equal('is_deposit','入金状态')
                    ->select(['0'=>'未入金','1'=>'已入金'])
                    ->width(2);
                $filter->equal('is_node','节点状态')
                    ->select(['0'=>'无','1'=>'小节点','2'=>'大节点'])
                    ->width(2);
                $filter->equal('is_node','停止收益')
                    ->select(['0'=>'正常','1'=>'停止'])
                    ->width(2);
                $filter->equal('is_stop_withdraw','禁止提现')
                    ->select(['0'=>'正常','1'=>'禁止'])
                    ->width(2);
                $filter->between('created_at','注册时间')
                    ->datetime()
                    ->width(4);
            });
            
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                // 账户充值
                $actions->append(new RechargeAction());
                // USDT明细
                $actions->prepend("<a href='".admin_url('finance/usdt/log').'?address='.$this->address."' target='_blank'><i class='feather icon-file-minus'> USDT明细</i></a>");
                // WBSC明细
                $actions->prepend("<a href='".admin_url('finance/wbsc/log').'?address='.$this->address."' target='_blank'><i class='feather icon-file-minus'> WBSC明细</i></a>");
            });

            $grid->disableRowSelector();
            // $grid->disableEditButton();
            $grid->disableDeleteButton();
            $grid->disableCreateButton();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(App::make('user'), function (Form $form) {
            $form->display('address', '用户地址')
                ->required();
            $form->select('grade_id', '会员等级')
                ->options(App::make('user')->userGrade())
                ->default(0);
            $form->radio('is_node','节点状态')
                ->options(['0'=>'无','1'=>'小节点','2'=>'大节点'])
                ->default(0);
            $form->radio('is_stop_profit','停止收益')
                ->options(['0'=>'正常','1'=>'停止'])
                ->default(0);
            $form->radio('is_stop_withdraw','禁止提现')
                ->options(['0'=>'正常','1'=>'禁止'])
                ->default(0);
        });
    }
}
