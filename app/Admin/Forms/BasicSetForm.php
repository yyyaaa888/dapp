<?php

namespace App\Admin\Forms;

use Dcat\Admin\Admin;
use Dcat\Admin\Form\NestedForm;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Models\Administrator;
use Dcat\Admin\Widgets\Box;
use Dcat\Admin\Widgets\Form;
use Dcat\Admin\Widgets\Tab;
use Dcat\Admin\Http\JsonResponse;
use Illuminate\Support\Facades\App;

class BasicSetForm extends Form
{

    /**
     * 处理表单提交逻辑
     * @param array $input
     * @return JsonResponse
     */
    public function handle(array $input): JsonResponse
    {
        $setting = App::make('setting')->detail('basic');
        $setting->values = $input;
        $res = $setting->save();
        if ($res) {
            return $this->response()->success('保存成功')->refresh();
        }
        return $this->response()->error('保存失败');
    }

    /**
     * 构造表单
     */
    public function form()
    {
        $this->text('static_ratio','静态收益（%）')
            ->required();
        $this->text('min_direct_ratio','未满足直推收益（%）')
            ->required();
        $this->text('max_direct_ratio','满足直推收益（%）')
            ->required();
        $this->text('big_node_ratio','大节点全网分红（%）')
            ->required();
        $this->text('big_node_share_ratio','大节点推荐收益（%）')
            ->required();
        $this->text('min_node_ratio','小节点全网分红（%）')
            ->required();
        $this->text('min_node_share_ratio','小节点推荐收益（%）')
            ->required();
        $this->text('withdraw_ratio','提现手续费（USDT）')
            ->required();
        // $this->display('address','收款地址')
        //     ->default('');
    }

    /**
     * 返回表单数据
     *
     * @return array
     */
    public function default()
    {
        return App::make('setting')->getItem('basic');
    }
}
