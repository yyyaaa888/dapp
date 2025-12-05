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

class HelpSetForm extends Form
{

    /**
     * 处理表单提交逻辑
     * @param array $input
     * @return JsonResponse
     */
    public function handle(array $input): JsonResponse
    {
        $setting = App::make('setting')->detail('help');
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
        $this->editor('content','帮助中心');
    }

    /**
     * 返回表单数据
     *
     * @return array
     */
    public function default()
    {
        return App::make('setting')->getItem('help');
    }
}
