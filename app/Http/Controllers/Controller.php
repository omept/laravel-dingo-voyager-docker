<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{

    /**
     * @SWG\Swagger(
     *     schemes={"http"},
     *     consumes={"application/x-www-form-urlencoded"},
     *     @SWG\Info(
     *         version="1.0",
     *         title=" API Starter",
     *         description="This is a simple API starter",
     *         @SWG\Contact(
     *             url="https://about.me/george_onwuasoanya"
     *         ),
     *     ),
     *     @SWG\ExternalDocumentation(
     *         description="Find out more about the package",
     *         url="http://goo.gl"
     *     )
     * )
     */
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
