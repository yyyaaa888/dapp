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
use Web3p\EthereumTx\Transaction;
use Web3p\RLP\RLP;
use Web3\Web3;
use Web3\Contract;

class WithdrawForm extends Form implements LazyRenderable
{
    use LazyWidget;

    /**
     * 处理表单提交逻辑
     * @param array $input
     * @return JsonResponse
     */
    public function handle(array $input): JsonResponse
    {
        $withdraw_id = $this->payload['withdraw_id'];
        $audit_status = $input['audit_status'];
        $withdraw = App::make('userWithdraw')->details($withdraw_id);
        if($withdraw['status'] != '0' || $withdraw['is_launch'] != 0){
            throw new Exception('审核异常');
        }
        if(!in_array($audit_status, ['-1','1'])){
            throw new Exception('请选择审核状态');
        }
        \DB::beginTransaction();
        try {
            $withdraw->audit_time = date('Y-m-d H:i:s');
            $withdraw->status = $audit_status;
            if($audit_status == '-1'){
                Factory::UserWalletLogService()->add($withdraw['address'], 'pepe', 0, 203, $withdraw['amount']);
            }
            $res = $withdraw->save();
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
        $withdraw = App::make('userWithdraw')->details($request['withdraw_id']);
        $this->display('address','提现地址')
            ->default($withdraw['address']);
        $this->display('amount','提现数量')
            ->default($withdraw['amount']);
        $this->display('fee','手续费')
            ->default($withdraw['fee']);
        $this->display('entry_amount','到账数量')
            ->default($withdraw['entry_amount']);
        $this->radio('audit_status','审核状态')
            ->options(['1' => '通过', '-1'=> '驳回'])
            ->when(-1, function (Form $form) {
                $form->textarea('reject_reason', '驳回原因');
            })
            ->required();
    }
}
