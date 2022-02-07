<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use App\Notifications\ResetPasswordOTP;
use Illuminate\Support\Facades\Notification;

class AuthController extends Controller
{
    /**
     * Create user
     * @param  [string] name
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @return [string] message
     */


    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(),  [

            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'mobile' => 'required',
            'email' => 'required|email|max:255|unique:users',
            'device_type' => 'required|in:android,ios',
            'password' => 'required|min:6|string|confirmed',
            'login_by' => 'required|in:manual,facebook,google,github',
            'social_unique_id' => ['required_if:login_by,facebook,google,github', 'unique:users'],
            'device_token' => 'required',
            'device_id' => 'required',

        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $user = $request->all();

        $user['password'] = bcrypt($request->password);
        $user = User::create($user);
        $userToken = $user->createToken('Personal Access Token');
        $user['access_token'] = $userToken->accessToken;
        // if(Setting::get('send_email', 0) == 1) {
        //     // send welcome email here
        //     Helper::site_registermail($user);
        // }

        return $user;
    }


    /**
     * Login user and create token
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     * @return [string] access_token
     * @return [string] token_type
     * @return [string] expires_at
     */

    public function login(Request $request)
    {
        $tokenRequest = $request->create('/oauth/token', 'POST', $request->all());
        $request->request->add([
            "client_id"     => $request->client_id,
            "client_secret" => $request->client_secret,
            "grant_type"    => 'password',
            "scope"         => '',
            "username"      => $request->username,
            "password"      => $request->password
        ]);

        $response = Route::dispatch($tokenRequest);

        $json = (array) json_decode($response->getContent());

        if (!empty($json['error'])) {
            $json['error'] = $json['message'];
        }

        $response->setContent(json_encode($json));

        $update = User::where('email', $request->username)->update(['device_token' => $request->device_token, 'device_id' => $request->device_id, 'device_type' => $request->device_type]);

        return $response;
    }

    /**
     * Change user password
     *
     * @return [string] message
     */

    public function change_password(Request $request)
    {

        $this->validate($request, [
            'password' => 'required|min:6',
            'old_password' => 'required',
        ]);
        $user = request()->user();

        if (Hash::check($request->old_password, $user->password)) {
            $user->password = bcrypt($request->password);
            $user->save();

            if ($request->ajax()) {
                return response()->json(['message' => 'Password Updated']);
            } else {
                return back()->with('flash_success', 'Password Updated');
            }
        } else {
            if ($request->ajax()) {
                return response()->json(['error' => 'Incorrect old password'], 422);
            } else {
                return back()->with('flash_error', 'Incorrect old password');
            }
        }
    }

    /**
     * Forgot Password.
     *
     * @return \Illuminate\Http\Response
     */


    public function forgot_password(Request $request)
    {

        $this->validate($request, [
            'email' => 'required|email|exists:users,email',
        ]);

        try {

            $user = User::where('email', $request->email)->first();

            $otp = mt_rand(100000, 999999);

            $user->otp = $otp;
            $user->save();

            Notification::send($user, new ResetPasswordOTP($otp));

            return response()->json([
                'message' => 'OTP sent to your email!',
                'user' => $user
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Something Went Wrong'], 500);
        }
    }


    /**
     * Reset Password.
     *
     * @return \Illuminate\Http\Response
     */

    public function reset_password(Request $request)
    {

        $this->validate($request, [
            'password' => 'required|min:6',
            'id' => 'required|numeric|exists:users,id'

        ]);

        try {

            $User = User::findOrFail($request->id);
            $User->password = bcrypt($request->password);
            $User->save();
            if ($request->ajax()) {
                return response()->json(['message' => 'Password Updated']);
            }
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Hing Went Wrong'], 500);
            }
        }
    }


    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */

    public function logout(Request $request)
    {
        try {
            User::where('id', $request->user()->id)->update(['device_id' => '', 'device_token' => '']);
            $request->user()->token()->revoke();
            return response()->json(['message' => 'Successfully logged out']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Something Went Wrong'], 500);
        }
    }



    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
