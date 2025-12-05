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

class MatrixForm extends Form implements LazyRenderable
{
    use LazyWidget;

    /**
     * 处理表单提交逻辑
     * @param array $input
     * @return JsonResponse
     */
    public function handle(array $input): JsonResponse
    {
        $matrix_id = $this->payload['matrix_id'];
        $sort = $input['sort'];
        $matrix = App::make('matrix')->details($matrix_id);
        if($matrix['is_out'] != '0'){
            throw new Exception('不可修改状态');
        }
        \DB::beginTransaction();
        try {
            $matrix->sort = $sort;
            $res = $matrix->save();
            if(!$res){
                throw new Exception('修改失败');
            }
            \DB::commit();
            return $this->response()->success('修改成功')->refresh();
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
        $matrix = App::make('matrix')->details($request['matrix_id']);
        $this->text('sort','序号')
            ->default($matrix['sort'])
            ->required();
        $this->display('address','用户地址')
            ->default($matrix['address']);
        $this->display('grade_name','用户级别')
            ->default($matrix['grade_name']);
    }
}
