<?php

namespace App\Admin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\App;
use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Alert;

class UsdtRechargeController extends AdminController
{
    protected $title = 'USDT充值';

    public function statistics($collection,$grid)
    {
        $query = App::make('usdtRecharge');
        // 拿到表格筛选 where 条件数组进行遍历
        $grid->model()->getQueries()->unique()->each(function ($value) use (&$query) {
            if (in_array($value['method'], ['paginate', 'get', 'withdrawBy', 'withdrawByDesc'], true)) {
                return;
            }
            $query = call_user_func_array([$query, $value['method']], $value['arguments'] ?? []);
        });
        $data = $query->get();
        $amount = $data->sum('amount');
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
        return Grid::make(App::make('usdtRecharge'), function (Grid $grid) {
            // 统计
            $grid->header(function ($query) use ($grid){
                return $this->statistics($query,$grid);
            });

            $grid->model()->orderBy('recharge_id', 'desc');
            $grid->column('recharge_id','序号')
                ->sortable();
            $grid->column('address','用户地址');
            $grid->column('amount','充值数量');
            $grid->column('trade_hash','交易哈希');
            $grid->column('created_at','充值时间');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('address','用户地址')
                    ->width(4);
                $filter->between('created_at','充值时间')
                    ->datetime()
                    ->width(4);
            });

            if (Admin::user()->can('profit-log-export')) {
                // 导出
                $grid->export(['recharge_id'=>'序号','address'=>'用户地址','amount'=>'充值数量','trade_hash'=>'交易哈希','created_at'=>'充值时间'])
                    ->filename('USDT充值-'.date('Y-m-d H:i',time()))
                    ->rows(function ($rows) {
                        foreach ($rows as &$row) {
                            
                        }
                        return $rows;
                    })
                    ->xlsx();
            }

            $grid->disableRowSelector();
            $grid->disableActions();
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
        return Form::make(App::make('usdtRecharge'), function (Form $form) {
            
        });
    }
}
