<?php

namespace App\Admin\Actions\Tree\RowAction;

use App\Admin\Forms\CheckTrackUpdateForm;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Tree\RowAction;
use Dcat\Admin\Widgets\Modal;
use Illuminate\Http\Request;

class DeviceColumnUpdateAction extends RowAction
{
    /**
     * @return string
     */
    protected $title = 'Title';

    /**
     * Handle the action request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        //
        $key = $this->getKey();

        return $this->response()
            ->success('Processed successfully.')
            ->redirect('/');
    }

    public function render()
    {
        // 实例化表单类并传递自定义参数
        $form = CheckTrackUpdateForm::make()->payload(['id' => $this->getKey()]);

        return Modal::make()
            ->lg()
            ->title(admin_trans_label('Update Track'))
            ->body($form)
            ->button($this->title);
    }
}
