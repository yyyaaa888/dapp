<?php

namespace App\Admin\Actions\Grid\RowAction;

use App\Admin\Forms\WithdrawForm;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Widgets\Modal;

class WithdrawAction extends RowAction
{

    public function __construct()
    {
        parent::__construct();
        $this->title = admin_trans_label("<a><i class='feather icon-plus' title='提现审核'></i>&nbsp;提现审核</a>");

    }

    /**
     * 渲染模态框
     * @return Modal|string
     */
    public function render()
    {
        $form = WithdrawForm::make()->payload([
            'withdraw_id' => $this->getKey()
        ]);

        return Modal::make()
            ->lg()
            ->title(admin_trans_label('提现审核'))
            ->body($form)
            ->button($this->title);
    }
}
