<?php

namespace App\Admin\Actions\Grid\RowAction;

use App\Admin\Forms\MatrixForm;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Widgets\Modal;

class MatrixAction extends RowAction
{

    public function __construct()
    {
        parent::__construct();
        $this->title = admin_trans_label("<a><i class='feather icon-plus' title='修改'></i>&nbsp;修改</a>");

    }

    /**
     * 渲染模态框
     * @return Modal|string
     */
    public function render()
    {
        $form = MatrixForm::make()->payload([
            'matrix_id' => $this->getKey()
        ]);

        return Modal::make()
            ->lg()
            ->title(admin_trans_label('修改'))
            ->body($form)
            ->button($this->title);
    }
}
