<?php

namespace App\Admin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\App;
use Dcat\Admin\Widgets\Alert;

class InvestController extends AdminController
{
    protected $title = '投资列表';

    public function statistics($collection,$grid)
    {
        $query = App::make('invest');
        // 拿到表格筛选 where 条件数组进行遍历
        $grid->model()->getQueries()->unique()->each(function ($value) use (&$query) {
            if (in_array($value['method'], ['paginate', 'get', 'withdrawBy', 'withdrawByDesc'], true)) {
                return;
            }
            $query = call_user_func_array([$query, $value['method']], $value['arguments'] ?? []);
        });
        $data = $query->get();
        $cake = $data->sum('cake');
        $caka = $data->sum('caka');
        $content = '<code>'.'总CAKE数量：'. $cake .'</code> ';
        $content .= '<code>'.'总CAKA数量：'. $caka .'</code> ';
        return Alert::make($content, '统计')->info();
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(App::make('invest'), function (Grid $grid) {
            // 统计
            $grid->header(function ($query) use ($grid){
                return $this->statistics($query,$grid);
            });
            
            $grid->model()->orderBy('invest_id', 'desc');
            $grid->column('invest_id','序号')
                ->sortable();
            $grid->column('address','用户地址');
            $grid->column('cake','CAKE');
            $grid->column('caka','CAKA');
            $grid->column('multiple','杠杆');
            $grid->column('total_cake','总收益');
            $grid->column('obtain_cake','释放收益');
            $grid->column('created_at','交易时间');
            $grid->column('trade_hash','交易哈希');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('address','用户地址')
                    ->width(4);
                $filter->between('created_at','交易时间')
                    ->datetime()
                    ->width(4);
            });
            
            // $grid->disableRowSelector();
            $grid->disableEditButton();
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
        return Form::make(App::make('invest'), function (Form $form) {
            
        });
    }
}
