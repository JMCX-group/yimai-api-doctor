<?php

namespace App\Http\Controllers;

class LogController extends Controller
{
    public function index()
    {
        $dir = dirname(dirname(dirname(dirname(__FILE__)))) . '/storage/logs/';
        $fileName = $dir . 'laravel-' . date('Y-m-d', time()) . '.log';

        if (file_exists($fileName)) {
            $content = file_get_contents($fileName);
            $data['content'] = dump($content);
        } else {
            $data['content'] = $fileName;
        }

        return view('logs.index', compact('data'));
    }
}
