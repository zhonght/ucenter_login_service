<?php

namespace Encore\WJScanLogin\Http\Controllers;

use Encore\Admin\Layout\Content;
use Illuminate\Routing\Controller;

class WJScanLoginController extends Controller
{
    public function index(Content $content)
    {
        return $content
            ->title('Title')
            ->description('Description')
            ->body(view('wj_scan_login::index'));
    }
}