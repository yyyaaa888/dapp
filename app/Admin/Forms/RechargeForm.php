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

class RechargeForm extends Form implements LazyRenderable
{
    use LazyWidget;

    /**
     * 处理表单提交逻辑
     * @param array $input
     * @return JsonResponse
     */
    public function handle(array $input): JsonResponse
    {
        $user_id = $this->payload['user_id'];
        $wallet_type = $input['wallet_type'];
        $mode = $input['mode'];
        $number = $input['number'];
        $remarks = $input['remarks'];
        $user = App::make('user')->details($user_id);
        if(!$user){
            return $this->response()->error('用户异常');
        }
        if((int)$number <= 0){
            return $this->response()->error('变动金额有误');
        }
        if($mode == 1){
            $number = $number;
        }else{
            $number = -$number;
        }
        \DB::beginTransaction();
        try {
            $res = Factory::UserWalletLogService()->add($user['address'], $wallet_type, 0, 101, $number, $remarks);
            if(!$res){
                throw new Exception('充值失败');
            }
            \DB::commit();
            return $this->response()->success('充值成功')->refresh();
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
        $user = App::make('user')->details($request['user_id']);
        $this->display('address','用户地址')
            ->default($user['address']);
        $this->radio('wallet_type','充值类型')
            ->when('usdt', function (Form $form) use ($user){
                $this->display('usdt','当前USDT')
                    ->default($user['usdt']);
            })
            ->when('wbsc', function (Form $form) use ($user){
                $this->display('wbsc','当前WBSC')
                    ->default($user['wbsc']);
            })
            ->options(['usdt'=>'USDT','wbsc'=>'WBSC'])
            ->default('usdt');
        $this->radio('mode','充值方式')
            ->options(['1' => '增加', '-1' => '減少'])
            ->default(1)
            ->required();
        $this->text('number', '充值数量')
            ->required();
        $this->textarea('remarks', '充值备注');
    }
}
