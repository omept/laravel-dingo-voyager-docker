<?php


namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;


$api = app('Dingo\Api\Routing\Router');


$api->version('v1', function ($api) {


    $api->group([
        'namespace' => 'App\Http\Controllers'
//        'middleware' => 'api_cors'
    ],
        function ($api) {

            $api->post('/auth/login', 'APIAuthController@login');
            $api->post('/auth/sign-up', 'APIAuthController@sign_up');
            $api->post('/auth/send-password-reset-link', 'APIAuthController@send_password_reset_link');
            $api->get('/auth/user-types', 'APIAuthController@user_types');


            $api->group([
                'middleware' => 'jwt.auth'
            ],
                function ($api) {


                    $api->post('/auth/update-password', 'APIAuthController@update_password');
                    $api->post('auth/profile-update', 'APIAuthController@profile_update');

                    $api->get('/auth/user', [
                        'uses' => 'APIAuthController@getUser',
                        'as' => 'api.auth.user'
                    ]);

                    $api->patch('/auth/refresh', [
                        'uses' => 'APIAuthController@patchRefresh',
                        'as' => 'api.auth.refresh'
                    ]);

                    $api->delete('/auth/invalidate', [
                        'uses' => 'APIAuthController@deleteInvalidate',
                        'as' => 'api.auth.invalidate'
                    ]);


                });


        });

});