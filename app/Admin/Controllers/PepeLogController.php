<?php

namespace App\Admin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\App;
use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Alert;

class PepeLogController extends AdminController
{
    protected $title = 'PEPE明细';

    public function statistics($collection,$grid)
    {
        $query = App::make('tradeLog');
        // 拿到表格筛选 where 条件数组进行遍历
        $grid->model()->getQueries()->unique()->each(function ($value) use (&$query) {
            if (in_array($value['method'], ['paginate', 'get', 'withdrawBy', 'withdrawByDesc'], true)) {
                return;
            }
            $query = call_user_func_array([$query, $value['method']], $value['arguments'] ?? []);
        });
        $data = $query->get();
        $amount = $data->sum('amount');
        $content = '<code>'.'总交易额：'. $amount .'</code> ';
        return Alert::make($content, '统计')->info();
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(App::make('tradeLog'), function (Grid $grid) {
            // 统计
            $grid->header(function ($query) use ($grid){
                return $this->statistics($query,$grid);
            });

            $logType = App::make('tradeLog')->logType();
            $cashierType = App::make('tradeLog')->cashierType();
            $grid->model()->where('wallet_type', 'pepe');
            $grid->model()->orderBy('log_id', 'desc');
            $grid->column('log_id','序号')
                ->sortable();
            $grid->column('address','用户地址');
            $grid->column('log_type','交易类型')
                ->display(function() use ($logType){
                    return $logType[$this->log_type];
                });
            $grid->column('cashier_type','收支类型')
                ->display(function() use ($cashierType){
                    return $cashierType[$this->cashier_type];
                });
            $grid->column('amount','交易金额');
            $grid->column('balance','最终余额');
            $grid->column('remarks','备注');
            $grid->column('created_at','交易时间');

            $grid->filter(function (Grid\Filter $filter) use ($logType,$cashierType){
                $filter->equal('address','用户地址')
                    ->width(4);
                $filter->equal('log_type','交易类型')
                    ->select($logType)
                    ->width(2);
                $filter->equal('cashier_type','收支类型')
                    ->select($cashierType)
                    ->width(2);
                $filter->between('created_at','交易时间')
                    ->datetime()
                    ->width(4);
            });

            if (Admin::user()->can('profit-log-export')) {
                // 导出
                $grid->export(['log_id'=>'序号','address'=>'用户地址','log_type'=>'交易类型','cashier_type'=>'收支类型','amount'=>'交易金额','balance'=>'最终余额','remarks'=>'备注','created_at'=>'交易时间'])
                    ->filename('PEPE明细-'.date('Y-m-d H:i',time()))
                    ->rows(function ($rows) use ($logType,$cashierType) {
                        foreach ($rows as &$row) {
                            $row['cashier_type'] = $cashierType[$row['cashier_type']];
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
        return Form::make(App::make('tradeLog'), function (Form $form) {
            
        });
    }
}
