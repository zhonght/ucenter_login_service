<?php

namespace Weigather\WJUcenterLoginService\Actions;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Unbind extends RowAction
{
    public function name()
    {
        return '账号解绑';
    }

    public function handle(Model $model)
    {
        $trans = [
            'failed'    => '解绑失败',
            'succeeded' => '解绑成功',
        ];

        try {
            DB::transaction(function () use ($model) {
                $model->delete();
            });
        } catch (\Exception $exception) {
            return $this->response()->error("{$trans['failed']} : {$exception->getMessage()}");
        }

        return $this->response()->success($trans['succeeded'])->refresh();
    }

    public function dialog()
    {
        $this->question(trans('admin.delete_confirm'), '', ['confirmButtonColor' => '#d33']);
    }
}
