<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Life Pharmacy API Documentation",
 *     description="API documentation for Life Pharmacy e-commerce platform",
 *     @OA\Contact(
 *         email="admin@lifepharmacy.com",
 *         name="Life Pharmacy Support"
 *     ),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://my-default-host.com",
 *     description="Life Pharmacy API Server"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
