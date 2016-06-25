<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\RadioStation;

class ArticleController extends Controller
{
    public function getArticle($id)
    {
        $data = RadioStation::find($id);

        return view('article.index', compact('data'));
    }
}
