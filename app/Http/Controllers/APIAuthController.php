<?php

namespace App\Http\Controllers;


use App\Models\MealType;
use App\Models\User;
use App\Models\UserType;
use App\Transformers\MealTypeTransformer;
use App\Transformers\UserTransformer;
use App\Transformers\UserTypeTransformer;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;


class APIAuthController extends  Controller
{
    use Helpers;


    /**
     * @SWG\Post(
     *   path="/api/auth/login",
     *   summary="Logs the user in and sends JWT token to be sent with every request with the user's detail",
     *      tags={"Authentication"},
     *   @SWG\Response(
     *     response=200,
     *     description="authorization token with user's data"
     *   ),
     *   @SWG\Parameter(
     *     name="email",
     *     description="User's email   ",
     *     required=true,
     *     in= "formData",
     *     type="string"
     * ),
     *     @SWG\Parameter(
     *     name="password",
     *     description="User's password",
     *     required=true,
     *     in="formData",
     *     type="string"
     * )
     * )
     **/


    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|max:255',
                'password' => 'required'
            ]);

            if ($validator->fails())
                throw new ValidationException($validator);

            $credentials = $request->only('email', 'password');

            if (!$token = JWTAuth::attempt($credentials)) {
                $message = "Invalid credentials";
                return problemResponse($message, '404', $request);
            }

            // All good so return the token
            return $this->onAuthorized($token, $request);
        } catch (JWTException $e) {
            // Something went wrong whilst attempting to encode the token

            return $this->onJwtGenerationError($e);

        } catch (ValidationException $e) {
            $message = "Email and password are required";
            return problemResponse($message, '404', $request);
        } catch (\Exception $e) {
//            $message = $e->getMessage();
            $message = 'Something went wrong while processing your request.';
            return problemResponse($message, '500', $request, $e);
        }

    }

    /**
     * @SWG\Post(
     *   path="/api/auth/sign-up",
     *   summary="Register the user and sends JWT token to be sent with every request with the user's detail",
     *      tags={"Authentication"},
     *   @SWG\Response(
     *     response=200,
     *     description="authorization token with user's data"
     *   ),
     *   @SWG\Parameter(
     *     name="email",
     *     description="User's email  ",
     *     required=true,
     *     in= "formData",
     *     type="string"
     * ),
     *   @SWG\Parameter(
     *     name="name",
     *     description="User's name  ",
     *     required=false,
     *     in= "formData",
     *     type="string"
     * ),
     *     @SWG\Parameter(
     *     name="password",
     *     description="User's password",
     *     required=true,
     *     in="formData",
     *     type="string"
     * ),
     *     @SWG\Parameter(
     *     name="phone",
     *     description="User's phone",
     *     required=true,
     *     in="formData",
     *     type="string"
     * ),
     *     @SWG\Parameter(
     *     name="date_of_birth",
     *     description="User's date_of_birth",
     *     required=false,
     *     in="formData",
     *     type="string"
     * ),
     *     @SWG\Parameter(
     *     name="user_type_id",
     *     description="User's user_type_id",
     *     required=true,
     *     in="formData",
     *     type="string"
     * ),
     *     @SWG\Parameter(
     *     name="gender",
     *     description="User's gender. 1=male; 0=female",
     *     required=false,
     *     default=1,
     *     in="formData",
     *     type="string"
     * ),
     *     @SWG\Parameter(
     *     name="app_version",
     *     description="User's app_version",
     *     required=true,
     *     in="formData",
     *     type="string"
     * )
     * )
     */
    public function sign_up(Request $request)
    {

        $errors = [];
        try {
            $request->gender = 1;
            $request->date_of_birth = now()->toDateString();

            $validator = Validator::make($request->all(), [
                'email' => 'required|unique:users|max:255',
                'password' => 'required',
                'gender' => 'required',

                'phone' => 'required|unique:users',
                'date_of_birth' => 'required',
                'app_version' => 'required',
                'user_type_id' => 'required',
//                'current_otp' => '',
//                'is_phone_verified'
            ]);

            if ($validator->fails()) {
//                $errors = $validator->messages()->toArray();
                throw new ValidationException($validator);
            }


            $user_type = UserType::where('id', $request->user_type_id)->first();

            if (!$user_type) {
                $message = "Invalid User Type.";
                return problemResponse($message, '404', $request);
            }

            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
//                'name' => $request->name,
                'phone' => $request->phone,
                'name' => isset($request->name) ? $request->name : '',

                'gender' => $request->gender,
                'user_type_id' => $request->user_type_id,
                'app_version' => $request->app_version,
            ]);

            $credentials = $request->only('email', 'password');

            if (!$token = JWTAuth::attempt($credentials)) {
                $message = "Error occurred while creating your auth token. The credentials passed failed to match internally. ";
                $user->delete();
                return problemResponse($message, '404', $request);
            }

            // All good so return the token
            return $this->onAuthorized($token, $request);
        } catch (ValidationException $e) {
            $message = "Validation error occurred. " . (implode(', ', array_flatten($e->errors())));
            return problemResponse($message, '404', $request);
        } catch (\Exception $e) {
            if (isset($user)) {
                $user->delete();
            }
            $message = 'Something went wrong while processing your request.';
            return problemResponse($message, '500', $request, $e);
        }

    }


    /**
     * @SWG\Post(
     *   path="/api/auth/send-password-reset-link",
     *   summary="send password reset link",
     *      tags={"Authentication"},
     *   @SWG\Response(
     *     response=200,
     *     description="send password reset link"
     *   ),
     *   @SWG\Parameter(
     *     name="email",
     *     description="send User's password reset link",
     *     required=true,
     *     in= "formData",
     *     type="string"
     * )
     * )
     */
    public function send_password_reset_link(Request $request)
    {


        try {
            $request->gender = 1;
            $request->date_of_birth = now()->toDateString();

            $validator = Validator::make($request->all(), [
                'email' => 'required|exists:users,email',
            ]);

            if ($validator->fails()) {
                $message = "Invalid email.";
                return problemResponse($message, '404', $request);
            }

            $user = User::where('email', $request->email)->first();
            $token = app('auth.password.broker')->createToken($user);

            $user->sendPasswordResetNotification($token);

            $message = "Update password link has been sent to your email.";
            return validResponse($message, [], $request);


        } catch (\Exception $e) {
            $message = $e->getMessage();
            return problemResponse($message, '500', $request, $e);
        }

    }


    /**
     * @SWG\Post(
     *   path="/api/auth/profile-update",
     *   summary="Updates the user's details",
     *      tags={"Authentication"},
     *   @SWG\Response(
     *     response=200,
     *     description="authorization token with user's data"
     *   ),
     *   @SWG\Parameter(
     *     name="email",
     *     description="User's email  ",
     *     required=false,
     *     in= "formData",
     *     type="string"
     * ),
     *     @SWG\Parameter(
     *     name="phone",
     *     description="User's phone",
     *     required=false,
     *     in="formData",
     *     type="string"
     * ),
     *     @SWG\Parameter(
     *     name="date_of_birth",
     *     description="User's date_of_birth",
     *     required=false,
     *     in="formData",
     *     type="string"
     * ),
     *     @SWG\Parameter(
     *     name="gender",
     *     description="User's gender. 1=male; 0=female",
     *     required=false,
     *     in="formData",
     *     type="string"
     * ),
     *     @SWG\Parameter(
     *     name="profile_pic_url",
     *     description="User's profile pic url",
     *     required=false,
     *     in="formData",
     *     type="string"
     * ),
     *     @SWG\Parameter(
     *     name="user_type_id",
     *     description="User's user_type_id",
     *     required=false,
     *     in="formData",
     *     type="string"
     * ),
     *     @SWG\Parameter(
     *     name="name",
     *     description="User's name",
     *     required=false,
     *     in="formData",
     *     type="string"
     * ),
     *     @SWG\Parameter(
     *     name="water_measurement",
     *     description="User's water measurement",
     *     required=false,
     *     in="formData",
     *     type="string"
     * ),
     *     @SWG\Parameter(
     *     name="water_daily_intake_target",
     *     description="User's water daily intake target",
     *     required=false,
     *     in="formData",
     *     type="string"
     * ),
     *     @SWG\Parameter(
     *     name="push_notification_token",
     *     description="User's onesignal push_notification_token",
     *     required=false,
     *     in="formData",
     *     type="string"
     * ),
     *     @SWG\Parameter(
     *     name="token",
     *     description="User's authorization token",
     *     required=true,
     *     in="formData",
     *     type="string"
     * ),
     *     @SWG\Parameter(
     *     name="app_version",
     *     description="User's app_version",
     *     required=false,
     *     in="formData",
     *     type="string"
     * )
     * )
     */
    public function profile_update(Request $request)
    {


        try {
            $validator = Validator::make($request->all(), [
//                'email' => 'required',
//                'gender' => 'required',
//                'phone' => 'required',
//                'date_of_birth' => 'required',
//                'app_version' => 'required',
//                'user_type_id' => 'required',
//                'profile_pic_url' => 'required',
//                'name' => 'required',
            ]);

            if ($validator->fails())
                throw new ValidationException($validator);

            if ($request->user_type_id) {
                $user_type = UserType::where('id', $request->user_type_id)->first();

                if (!$user_type) {
                    $message = "Invalid User Type.";
                    return problemResponse($message, '404', $request);
                }
            }


            $updater = array_filter([
                'email' => $request->email,
                'name' => $request->name,
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'profile_pic_url' => $request->profile_pic_url,
                'gender' => $request->gender,
                'user_type_id' => $request->user_type_id,
                'app_version' => $request->app_version,
                'water_measurement' => $request->water_measurement,
                'water_daily_intake_target' => $request->water_daily_intake_target,
                'push_notification_token' => $request->push_notification_token,
            ]);

            $user = auth()->user();
            if ($updater)
                User::where('id', $user->id)->update($updater);

            $transformer = new UserTransformer();
            $data = ['user' => $transformer->transform($user)];
            return validResponse("User update successful.", $data, $request);
        } catch (ValidationException $e) {
            $message = "Validation error occurred. " . (implode(', ', array_flatten($e->errors())));
            return problemResponse($message, '404', $request);
        } catch (\Exception $e) {
//            $message = $e->getMessage();
            $message = 'Something went wrong while processing your request.';
            return problemResponse($message, '500', $request, $e);
        }

    }


    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard();
    }



    /**
     * Get authenticated user.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Get(
     *   path="/api/auth/user",
     *   summary="Get logged in user details using token gotten from login",
     *      tags={"Authentication"},
     *   @SWG\Response(
     *     response=200,
     *     description="user's data"
     *   ),
     *   @SWG\Parameter(
     *     name="token",
     *     description="authorization token",
     *     required=true,
     *     in= "query",
     *     type="string"
     * )
     * )
     */
    public function getUser(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'token' => 'required'
            ]);

            if ($validator->fails())
                throw new ValidationException($validator);

            if (count($this->user()) < 1) {
                return problemResponse("User not found", 404, $request);
            }

            $transformer = new UserTransformer();

            $data = ['user' => $transformer->transform($this->user())];
            return validResponse("User", $data, $request);

        } catch (ValidationException $e) {
            $message = "Phone and pin are required";
            return problemResponse($message, '404', $request);


        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return problemResponse("Expired Session", '401', $request);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return problemResponse('TOKEN INVALID', '401', $request);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return problemResponse('Something went wrong while generating your token', "500", $request);
        } catch (\Exception $e) {
            $message = 'Something went wrong while processing your request.';
            return problemResponse($message, '500', $request, $e);
        }

    }


    /**
     * What response should be returned on error while generate JWT.
     *
     * @return JsonResponse
     */
    protected function onJwtGenerationError($e)
    {
        return problemResponse($e->getMessage(), "500", null);

    }

    /**
     * What response should be returned on authorized.
     *
     * @return JsonResponse
     */
    protected function onAuthorized($token, $request)
    {

        $transformer = new UserTransformer();
        $user = auth()->user();
        $data = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60,
            'user' => $transformer->transform($user)

        ];

        return validResponse("Authorisation successful", $data, $request);


    }

    /**
     * What response should be returned on invalid credentials.
     *
     * @return JsonResponse
     */
    protected function onUnauthorized($request)
    {
        return problemResponse('Invalid credentials', '401', $request);

    }


    /**
     * Invalidate a token.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @SWG\Delete(
     *   path="/api/auth/invalidate",
     *   summary="Invalidate/Delete user authorization token using previous token",
     *         tags={"Authentication"},
     *   @SWG\Response(
     *     response=200,
     *     description="success message"
     *   ),
     *   @SWG\Parameter(
     *     name="token",
     *     description="authorization token",
     *     required=true,
     *     in= "query",
     *     type="string"
     * )
     * )
     */
    public function deleteInvalidate(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'token' => 'required'
            ]);

            if ($validator->fails())
                throw new ValidationException($validator);


            $token = JWTAuth::parseToken();

            $token->invalidate();
            $message = "token invalidated successfully";
            return validResponse($message, [], $request);

        } catch (ValidationException $e) {
            $message = "token is required";
            return problemResponse($message, '404', $request);

        } catch (\Exception $e) {
            $message = 'Something went wrong while processing your request.';
            return problemResponse($message, '500', $request, $e);
        }


    }


    /**
     * @SWG\Post(
     *   path="/api/auth/update-password",
     *   summary="Update password of an account",
     *      tags={"Authentication"},
     *   @SWG\Response(
     *     response=200,
     *     description="Update the password of an account."
     *   ),
     *   @SWG\Parameter(
     *     name="old_password",
     *     description="User's old password   ",
     *     required=true,
     *     in= "formData",
     *     type="string"
     * ),
     *     @SWG\Parameter(
     *     name="password",
     *     description="User's password",
     *     required=true,
     *     in="formData",
     *     type="string"
     * ),
     *     @SWG\Parameter(
     *     name="confirm_password",
     *     description="User's confirm_password",
     *     required=true,
     *     in="formData",
     *     type="string"
     * ),
     *   @SWG\Parameter(
     *     name="token",
     *     description="authorization token",
     *     required=true,
     *     in= "query",
     *     type="string"
     * )
     * )
     */
    public function update_password(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'old_password' => 'required',
                'password' => 'required',
                'confirm_password' => 'required|same:password'
            ]);

            if ($validator->fails())
                throw new ValidationException($validator);


            if (!Hash::check($request->old_password, auth()->user()->password)) {
                $message = "Old password sent is wrong";
                return problemResponse($message, '404', $request);
            }

            $password = Hash::make($request->password);

            User::where('id', auth()->user()->id)->update(['password' => $password]);

            $message = 'Password updated successfully';

            return validResponse($message, [], $request);

        } catch (ValidationException $e) {
            $message = "Validation failed. New passwords don't match";
            return problemResponse($message, '404', $request);

        } catch (\Exception $e) {
            $message = 'Something went wrong while processing your request.';
            return problemResponse($message, '500', $request, $e);
        }

    }





    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Patch(
     *   path="/api/auth/refresh",
     *   summary="Refresh user authorization token details using previous token",
     *          tags={"Authentication"},
     *   @SWG\Response(
     *     response=200,
     *     description="new authorization token"
     *   ),
     *   @SWG\Parameter(
     *     name="token",
     *     description="authorization token",
     *     required=true,
     *     in= "query",
     *     type="string"
     * )
     * )
     */
    public function patchRefresh(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'token' => 'required'
            ]);

            if ($validator->fails())
                throw new ValidationException($validator);


            $token = JWTAuth::parseToken();

            $newToken = $token->refresh();
            $message = 'Token refreshed successfully';

            return validResponse($message, [
                'token' => $newToken
            ], $request);

        } catch (ValidationException $e) {
            $message = "token is required";
            return problemResponse($message, '404', $request);

        } catch (\Exception $e) {
            $message = 'Something went wrong while processing your request.';
            return problemResponse($message, '500', $request, $e);
        }
    }


    /**
     * @SWG\Get(
     *   path="/api/auth/user-types",
     *   summary="Get app  user type details",
     *      tags={"Config"},
     *   @SWG\Response(
     *     response=200,
     *     description="user's data"
     *   )
     * )
     */
    public function user_types(Request $request)
    {
        try {

            $transformer = new UserTypeTransformer();

            $data = ['user_types' => $transformer->collect(UserType::where('name', 'not like', "%admin%")->get())];
            return validResponse("User Types", $data, $request);

        } catch (\Exception $e) {
            $message = 'Something went wrong while processing your request.';
            return problemResponse($message, '500', $request, $e);
        }

    }

}
