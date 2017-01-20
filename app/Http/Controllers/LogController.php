<?php

namespace App\Http\Controllers;

class LogController extends Controller
{
    public function index()
    {
        $dir = dirname(dirname(dirname(dirname(__FILE__)))) . '/storage/logs/';
        $fileName = 'laravel-' . date('Y-m-d', time()) . '.log';

        $data['content'] = dump(file_get_contents($dir . $fileName));

        return view('logs.index', compact('data'));
    }
}
