<?php

namespace App\Admin\Controllers;

use App\Admin\Metrics\Examples;
use App\Http\Controllers\Controller;
use Dcat\Admin\Http\Controllers\Dashboard;
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        return $content
            ->header('主页')
            ->body(function (Row $row) {
                $row->column(12, function (Column $column) {
                    $column->row(new Examples\ProjectStatistics());
                });
            });
    }

    /**
     * 文件上传
     * @Author   Chen
     */
    public function uploadFile(Request $request)
    {
        // 获取文件信息
        $file = $request->file('file');
        if(empty($file)){
            $this->errorMsg('请选择上传文件');
        }
        // 文件大小
        $fileSize = $file->getClientSize();
        if($fileSize / 1024 / 1024 > 20){
            $this->errorMsg('上传文件不能大于20M');
        }
        // 文件类型
        $mimeType = explode('/',$file->getMimeType());
        if(!in_array($mimeType[0], ['video','image'])){
            $this->errorMsg('上传的文件类型有误');
        }
        $path = 'images';
        $res = Storage::disk('admin')->put($path,$file);
        return response()->json([
            'status'=>true,
            'data'=>[
                'url'=>env('APP_URL') .'/uploads/'. $res
            ],
        ]);
    }
}
