<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController; // This is the crucial line

class Controller extends BaseController // This makes your controllers inherit core Laravel functionality
{
    use AuthorizesRequests, ValidatesRequests;
}