<?php
/**
 * Created by PhpStorm.
 * User: gem
 * Date: 7/13/17
 * Time: 5:35 PM
 */

namespace App\Transformers;


use App\Models\User;
use  \League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    /**
     * Turn this item object into a generic array
     *
     * @return array
     */
    public function transform(User $user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'pin' => $user->pin,
//            'password' => $user->password,
            'status' => $user->status,
            'user_type_id' => $user->user_type_id,
            'height' => $user->height,
            'username' => $user->username,
            'date_of_birth' => $user->date_of_birth,
            'app_version' => $user->app_version,
            'gender' => $user->gender == 1 ? "male" : "female",

//            'current_otp' => $user->current_otp,
//            'is_phone_verified' => $user->is_phone_verified,
        ];
    }

    public function collect($collection)
    {
        $transformer = new UserTransformer();
        return collect($collection)->map(function ($model) use ($transformer) {
            return $transformer->transform($model);
        });
    }

}