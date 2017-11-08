<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    //
    public function show()
    {
        throw new \Symfony\Component\HttpKernel\Exception\ConflictHttpException('User was updated prior to your request.');
    }
}
