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

class StorageSetForm extends Form
{

    /**
     * 处理表单提交逻辑
     * @param array $input
     * @return JsonResponse
     */
    public function handle(array $input): JsonResponse
    {
        $setting = App::make('setting')->detail('storage');
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
        $this->radio('default','默认上传方式')
            ->when('qiniu', function (Form $form){
                $this->text('engine.qiniu.bucket','存储空间名称 Bucket');
                $this->text('engine.qiniu.access_key','ACCESS_KEY AK');
                $this->text('engine.qiniu.secret_key','SECRET_KEY SK');
                $this->text('engine.qiniu.domain','空间域名 Domain')
                    ->help('请补全http:// 或 https://，例如：http://static.cloud.com');
            })
            ->when('aliyun', function (Form $form){
                $this->text('engine.aliyun.bucket','存储空间名称 Bucket');
                $this->text('engine.aliyun.endpoint','所属地域 Endpoint')
                    ->help('请填写地域简称，例如：ap-beijing、ap-hongkong、eu-frankfurt');
                $this->text('engine.aliyun.access_key_id','AccessKeyId');
                $this->text('engine.aliyun.access_key_secret','AccessKeySecret');
                $this->text('engine.aliyun.domain','空间域名 Domain')
                    ->help('请补全http:// 或 https://，例如：http://static.cloud.com');
            })
            ->when('qcloud', function (Form $form){
                $this->text('engine.qcloud.bucket','存储空间名称 Bucket');
                $this->text('engine.qcloud.region','所属地域 Region')
                    ->help('请填写地域简称，例如：ap-beijing、ap-hongkong、eu-frankfurt');
                $this->text('engine.qcloud.app_id','AppId');
                $this->text('engine.qcloud.secret_id','SecretId');
                $this->text('engine.qcloud.secret_key','SecretKey');
                $this->text('engine.qcloud.domain','空间域名 Domain')
                    ->help('请补全http:// 或 https://，例如：http://static.cloud.com');
            })
            ->options(['local'=>'本地存储','qiniu'=>'七牛云存储','aliyun'=>'阿里云OSS','qcloud'=>'腾讯云COS'])
            ->default('local');
    }

    /**
     * 返回表单数据
     *
     * @return array
     */
    public function default()
    {
        return App::make('setting')->getItem('storage');
    }
}
