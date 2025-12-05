<?php

namespace App\Admin\Actions\Grid\RowAction;

use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Widgets\Modal;
use App\Admin\Forms\WbscRechargeForm;

class RechargeExchange extends RowAction
{
    public function __construct()
    {
        parent::__construct();

        $this->title = admin_trans_label("<i class='feather icon-log-out' title='充值审核'></i> 充值审核");

    }

    /**
     * 渲染模态框
     */
    public function render()
    {
        return Modal::make()
            ->lg()
            ->title(admin_trans_label('充值审核'))
            ->body(WbscRechargeForm::make()->payload(['id' => $this->getKey()]))
            ->button($this->title);
    }
}
