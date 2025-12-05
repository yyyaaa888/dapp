<?php

namespace App\Admin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\App;
use Dcat\Admin\Widgets\Alert;
use App\Admin\Actions\Grid\RowAction\MatrixAction;
use Illuminate\Http\Request;

class MatrixController extends AdminController
{
    protected $title = '创业者列表';
    protected $grade_id = 2;

    public function __construct(Request $request)
    {
        switch ($request['grade_id']) {
            case 2:
                $title = '创业者列表';
                break;
            case 3:
                $title = '白银列表';
                break;
            case 4:
                $title = '黄金列表';
                break;
            case 5:
                $title = '钻石列表';
                break;
            case 6:
                $title = '皇冠列表';
                break;
            default:
                $title = '创业者列表';
                break;
        }
        $this->title = $title;
        $this->grade_id = $request['grade_id'];
    }

    public function statistics($collection,$grid)
    {
        $query = App::make('matrix');
        // 拿到表格筛选 where 条件数组进行遍历
        $grid->model()->getQueries()->unique()->each(function ($value) use (&$query) {
            if (in_array($value['method'], ['paginate', 'get', 'withdrawBy', 'withdrawByDesc'], true)) {
                return;
            }
            $query = call_user_func_array([$query, $value['method']], $value['arguments'] ?? []);
        });
        $data = $query->get();
        $count = $data->count();
        $content = '<code>'.'总人数：'. $count .'</code> ';
        return Alert::make($content, '统计')->info();
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(App::make('matrix'), function (Grid $grid) {
            // 统计
            $grid->header(function ($query) use ($grid){
                return $this->statistics($query,$grid);
            });
            $grid->model()->where('grade_id', $this->grade_id);
            $grid->model()->orderBy('sort', 'asc');
            $grid->column('sort','序号')
                ->sortable();
            $grid->column('address','用户地址');
            $grid->column('grade_name','用户级别');
            $grid->column('is_out','是否出局')
                ->using(['0'=>'否','1'=>'是'])
                ->badge(['0'=>'danger','1'=>'success']);
            $grid->column('created_at','加入时间');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('address','用户地址')
                    ->width(4);
                $filter->equal('is_out','是否出局')
                    ->select(['0'=>'否','1'=>'是'])
                    ->width(2);
                $filter->between('created_at','加入时间')
                    ->datetime()
                    ->width(4);
            });
            
            //自定义操作按钮
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                if($this->is_out == 0){
                    $actions->append(new MatrixAction());
                }
            });

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
        return Form::make(App::make('ticket'), function (Form $form) {
            
        });
    }
}
