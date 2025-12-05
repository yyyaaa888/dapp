<?php

namespace App\Admin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\App;
use Dcat\Admin\Widgets\Alert;

class DepositController extends AdminController
{
    protected $title = '投注列表';

    public function statistics($collection,$grid)
    {
        $query = App::make('deposit');
        // 拿到表格筛选 where 条件数组进行遍历
        $grid->model()->getQueries()->unique()->each(function ($value) use (&$query) {
            if (in_array($value['method'], ['paginate', 'get', 'withdrawBy', 'withdrawByDesc'], true)) {
                return;
            }
            $query = call_user_func_array([$query, $value['method']], $value['arguments'] ?? []);
        });
        $amount = $query->sum('amount');
        $release_amount = $query->sum('release_amount');
        $content = '<code>'.'总投注数量：'. sctonum($amount) .'</code> ';
        $content .= '<code>'.'总收益数量：'. sctonum($release_amount) .'</code> ';
        return Alert::make($content, '统计')->info();
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(App::make('deposit'), function (Grid $grid) {
            // 统计
            $grid->header(function ($query) use ($grid){
                return $this->statistics($query,$grid);
            });
            $grid->model()->orderBy('deposit_id', 'desc');
            $grid->column('ticket_id','序号')
                ->sortable();
            $grid->column('address','用户地址');
            $grid->column('amount','投注数量');
            $grid->column('wbsc_price','投注价格');
            $grid->column('usdt_amount','USDT价值');
            $grid->column('deposit_type','投注类型')
                ->display(function () {
                    switch ($this->deposit_type) {
                    case 1:
                        return '<span class="badge" style="background:#3085d6">USDT</span>';
                        break;
                    case 2:
                        return '<span class="badge" style="background:#dda451">WBSC</span>';
                        break;
                    }
                });
            $grid->column('profit_days','已发放天数');
            $grid->column('release_amount','已发放收益');
            $grid->column('status','发放状态')
                ->display(function () {
                    switch ($this->status) {
                    case 0:
                        return '<span class="badge" style="background:#3085d6">发放中</span>';
                        break;
                    case 1:
                        return '<span class="badge" style="background:#dda451">已结束</span>';
                        break;
                    }
                });
            $grid->column('created_at','投注时间');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('address','用户地址')
                    ->width(4);
                $filter->equal('deposit_type','投注类型')
                    ->select(['1'=>'USDT','2'=>'WBSC'])
                    ->width(2);
                $filter->equal('status','发放状态')
                    ->select(['0'=>'发放中','1'=>'已结束'])
                    ->width(2);
                $filter->between('created_at','投注时间')
                    ->datetime()
                    ->width(4);
            });
            
            $grid->disableRowSelector();
            $grid->disableActions();
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
        return Form::make(App::make('deposit'), function (Form $form) {
            
        });
    }
}
