<?php

namespace App\Admin\Forms;

use Dcat\Admin\Admin;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Http\JsonResponse;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Illuminate\Support\Facades\App;
use App\Service\Api\Factory;
use Exception;

class WbscRechargeForm extends Form implements LazyRenderable
{
    use LazyWidget;

    /**
     * 处理表单提交逻辑
     * @param array $input
     * @return JsonResponse
     */
    public function handle(array $input): JsonResponse
    {
        \DB::beginTransaction();
        try {
            $id = $this->payload['id'];
            $audit_status = $input['audit_status'];
            $recharge = App::make('wbscRecharge')->details($id);
            if(!$recharge || $recharge['status'] != 0 || $recharge['type'] != 2){
                throw new Exception('充值异常');
            }
            if(!in_array($audit_status, ['-1','1'])){
                throw new Exception('请选择审核状态');
            }
            if($audit_status == 1){
                // 充值流水
                Factory::UserWalletLogService()->add($recharge['address'], 'wbsc', 0, 201, $recharge['amount']);
            }
            $recharge->status = $audit_status;
            $recharge->audit_time = date('Y-m-d H:i:s');
            $res = $recharge->save();
            if(!$res){
                throw new Exception('审核失败');
            }
            \DB::commit();
            return $this->response()->success('审核成功')->refresh();
        } catch (\Exception $e) {
            \DB::rollBack();
            return $this->response()->error($e->getMessage());
        }
    }

    /**
     * 构造表单
     */
    public function form()
    {
        $request = $this->payload;
        $recharge = App::make('wbscRecharge')->details($request['id']);
        $this->display('address','用户地址')
            ->default($recharge['address']);
        $this->radio('type','充值类型')
            ->options(['1'=>'链上','2'=>'ERC-20'])
            ->disable()
            ->default($recharge['type']);
        $this->display('amount','充值数量')
            ->default($recharge['amount']);
        $this->display('voucher','充值凭证')
            ->default("<img data-action='preview-img' style='width:140px' src='".$recharge['voucher']."'>");
        $this->radio('audit_status','审核状态')
            ->options(['1' => '通过', '-1'=> '驳回'])
            // ->when(-1, function (Form $form) {
            //     $form->textarea('reject_reason', '驳回原因');
            // })
            ->required();
    }
}
