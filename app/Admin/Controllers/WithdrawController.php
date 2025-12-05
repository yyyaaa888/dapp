<?php

namespace App\Admin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\App;
use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Alert;
use App\Admin\Actions\Grid\RowAction\WithdrawAction;
use App\Admin\Actions\Grid\RowAction\LaunchWithdraw;

class WithdrawController extends AdminController
{
    protected $title = '提现记录';

    public function statistics($collection,$grid)
    {
        $query = App::make('userWithdraw');
        // 拿到表格筛选 where 条件数组进行遍历
        $grid->model()->getQueries()->unique()->each(function ($value) use (&$query) {
            if (in_array($value['method'], ['paginate', 'get', 'withdrawBy', 'withdrawByDesc'], true)) {
                return;
            }
            $query = call_user_func_array([$query, $value['method']], $value['arguments'] ?? []);
        });
        $data = $query->get();
        $amount = $data->sum('amount');
        $content = '<code>'.'总提现额：'. sctonum($amount) .'</code> ';
        return Alert::make($content, '统计')->info();
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(App::make('userWithdraw'), function (Grid $grid) {
            // 统计
            $grid->header(function ($query) use ($grid){
                return $this->statistics($query,$grid);
            });

            $withdrawType = App::make('userWithdraw')->withdrawType();
            $status = App::make('userWithdraw')->status();
            $grid->model()->orderBy('withdraw_id', 'desc');
            $grid->column('withdraw_id','序号')
                ->sortable();
            $grid->column('address','用户地址');
            $grid->column('withdraw_type','提现类型')
                ->display(function () {
                    switch ($this->withdraw_type) {
                    case 1:
                        return '<span class="badge" style="background:#3085d6">TP</span>';
                        break;
                    case 2:
                        return '<span class="badge" style="background:#dda451">币安</span>';
                        break;
                    }
                });
            $grid->column('withdraw_address','提现地址');
            $grid->column('amount','提现数量');
            $grid->column('fee','手续费');
            $grid->column('entry_amount','到账数量');
            $grid->column('status','提现状态')
                ->using($status)
                ->dot(['-1' => 'red', '0' => 'orange1', '1' => 'success']);
            $grid->column('created_at','提现时间');

            //自定义操作按钮
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                if($this->status == 0 && $this->is_launch == 0){
                    $actions->append(new WithdrawAction());
                    // $actions->append(new LaunchWithdraw());
                }
            });

            $grid->filter(function (Grid\Filter $filter) use ($status,$withdrawType){
                $filter->equal('address','用户地址')
                    ->width(4);
                $filter->equal('withdraw_type','提现类型')
                    ->select($withdrawType)
                    ->width(2);
                $filter->equal('status','提现状态')
                    ->select($status)
                    ->width(2);
                $filter->between('created_at','提现时间')
                    ->datetime()
                    ->width(4);
            });

            if (Admin::user()->can('withdraw-export')) {
                // 导出
                $grid->export(['withdraw_id'=>'序号','address'=>'用户地址','withdraw_type'=>'提现类型','withdraw_address'=>'提现地址','amount'=>'提现数量','fee'=>'手续费','entry_amount'=>'到账数量','status'=>'提现状态','created_at'=>'提现时间'])
                    ->filename('提现明细-'.date('Y-m-d H:i',time()))
                    ->rows(function ($rows) use ($status,$withdrawType) {
                        foreach ($rows as &$row) {
                            $row['withdraw_type'] = $withdrawType[$row['withdraw_type']];
                            $row['status'] = $status[$row['status']];
                        }
                        return $rows;
                    })
                    ->xlsx();
            }

            $grid->disableRowSelector();
            $grid->disableEditButton();
            // $grid->disableActions();
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
        return Form::make(App::make('userWithdraw'), function (Form $form) {
            
        });
    }
}
