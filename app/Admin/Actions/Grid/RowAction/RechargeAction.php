<?php

namespace App\Admin\Actions\Grid\RowAction;

use App\Admin\Forms\RechargeForm;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Widgets\Modal;

class RechargeAction extends RowAction
{

    public function __construct()
    {
        parent::__construct();
        $this->title = admin_trans_label("<a><i class='feather icon-plus' title='账户充值'></i>&nbsp;账户充值</a>");

    }

    /**
     * 渲染模态框
     * @return Modal|string
     */
    public function render()
    {
        $form = RechargeForm::make()->payload([
            'user_id' => $this->getKey()
        ]);

        return Modal::make()
            ->lg()
            ->title(admin_trans_label('账户充值'))
            ->body($form)
            ->button($this->title);
    }
}
