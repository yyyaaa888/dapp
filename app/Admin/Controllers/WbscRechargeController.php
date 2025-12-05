<?php

namespace App\Admin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\App;
use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Alert;
use App\Admin\Actions\Grid\RowAction\RechargeExchange;

class WbscRechargeController extends AdminController
{
    protected $title = 'WBSC充值';

    public function statistics($collection,$grid)
    {
        $query = App::make('wbscRecharge');
        // 拿到表格筛选 where 条件数组进行遍历
        $grid->model()->getQueries()->unique()->each(function ($value) use (&$query) {
            if (in_array($value['method'], ['paginate', 'get', 'withdrawBy', 'withdrawByDesc'], true)) {
                return;
            }
            $query = call_user_func_array([$query, $value['method']], $value['arguments'] ?? []);
        });
        $data = $query->get();
        $amount = $data->sum('amount');
        $total_amount = $data->sum('total_amount');
        $content = '<code>'.'总数量：'. sctonum($amount) .'</code> ';
        return Alert::make($content, '统计')->info();
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(App::make('wbscRecharge'), function (Grid $grid) {
            // 统计
            $grid->header(function ($query) use ($grid){
                return $this->statistics($query,$grid);
            });
            $status = App::make('wbscRecharge')->status();
            $grid->model()->orderBy('recharge_id', 'desc');
            $grid->column('recharge_id','序号')
                ->sortable();
            $grid->column('address','用户地址');
            $grid->column('type','充值方式')
                ->display(function () {
                    switch ($this->type) {
                    case 1:
                        return '<span class="badge" style="background:#3085d6">链上</span>';
                        break;
                    case 2:
                        return '<span class="badge" style="background:#dda451">ERC-20</span>';
                        break;
                    }
                });
            $grid->column('status','充值状态')
                ->using($status)
                ->dot(['-1'=>'red','0'=>'gray','1'=>'success']);
            $grid->column('amount','充值数量');
            $grid->column('voucher','充值凭证')
                ->image('',40,40);
            $grid->column('trade_hash','交易哈希');
            $grid->column('created_at','充值时间');

            $grid->filter(function (Grid\Filter $filter) use ($status){
                $filter->equal('address','用户地址')
                    ->width(4);
                $filter->equal('type','充值方式')
                    ->select(['1'=>'链上','2'=>'ERC-20'])
                    ->width(2);
                $filter->equal('status','充值状态')
                    ->select($status)
                    ->width(2);
                $filter->between('created_at','充值时间')
                    ->datetime()
                    ->width(4);
            });

            //自定义操作按钮
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                if($this->status == 0){
                    $actions->append(new RechargeExchange());
                }
            });

            if (Admin::user()->can('profit-log-export')) {
                // 导出
                $grid->export(['recharge_id'=>'序号','address'=>'用户地址','type'=>'充值方式','amount'=>'充值数量','status'=>'充值状态','trade_hash'=>'交易哈希','created_at'=>'充值时间'])
                    ->filename('WBSC充值-'.date('Y-m-d H:i',time()))
                    ->rows(function ($rows)  use ($status){
                        foreach ($rows as &$row) {
                            $row['type'] = $row['type'] == 1 ? '链上' : 'ERC-20';
                            $row['status'] = $status[$row['status']];
                        }
                        return $rows;
                    })
                    ->xlsx();
            }

            $grid->disableRowSelector();
            // $grid->disableActions();
            $grid->disableEditButton();
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
        return Form::make(App::make('wbscRecharge'), function (Form $form) {
            
        });
    }
}
