<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
// use Socialite;


class SocialAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * List of providers configured in config/services acts as whitelist
     *
     * @var array
     */
    protected $providers = [
        'github',
        'facebook',
        'google',
        'twitter'
    ];

    /**
     * Show the social login page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show()
    {
        return view('auth.social');
    }

    /**
     * Redirect to provider for authentication
     *
     * @param $driver
     * @return mixed
     */
    public function redirectToProvider($driver)
    {
        if (!$this->isProviderAllowed($driver)) {
            return $this->sendFailedResponse("{$driver} is not currently supported");
        }

        try {

            return Socialite::driver($driver)->redirect();
        } catch (Exception $e) {
            return $this->sendFailedResponse($e->getMessage());
        }
    }

    /**
     * Handle response of authentication redirect callback
     *
     * @param $driver
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleProviderCallback($driver)
    {
        try {
            $user = Socialite::driver($driver)->user();
        } catch (Exception $e) {
            return $this->sendFailedResponse($e->getMessage());
        }

        // check for email in returned user
        return empty($user->email)
            ? $this->sendFailedResponse("No email id returned from {$driver} provider.")
            : $this->loginOrCreateAccount($user, $driver);
    }

    /**
     * Send a successful response
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendSuccessResponse()
    {
        return redirect()->intended('home');
    }

    /**
     * Send a failed response with a msg
     *
     * @param null $msg
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendFailedResponse($msg = null)
    {

        return redirect()->back()
            ->withErrors(['msg' => $msg ?: 'Unable to login, try with another provider to login.']);
    }

    protected function loginOrCreateAccount($providerUser, $driver)
    {
        // check for already has account
        $user = User::where('email', $providerUser->getEmail())->first();

        // if user already found
        if ($user) {
            $user->update([
                'avatar' => $providerUser->avatar,
                'provider' => $driver,
                'provider_id' => $providerUser->id,
                'access_token' => $providerUser->token,
                'login_by' => $driver
            ]);
        } else {
            $user = User::create([
                'fullname' => $providerUser->getName(),
                'email' => $providerUser->getEmail(),
                'avatar' => $providerUser->getAvatar(),
                'provider' => $driver,
                'provider_id' => $providerUser->getId(),
                'access_token' => $providerUser->token,
                'refresh_token' => $providerUser->refreshToken,
                'login_by' => $driver,
                // user can use reset password to create a password
                'password' => ''
            ]);
        }

        Auth::login($user, true);

        return $this->sendSuccessResponse();
    }

    /**
     * Check for provider allowed and services configured
     *
     * @param $driver
     * @return bool
     */
    private function isProviderAllowed($driver)
    {
        return in_array($driver, $this->providers) && config()->has("services.{$driver}");
    }

    public function socialViaAPI(Request $request, $driver)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'device_type' => 'required|in:android,ios',
                'device_token' => 'required',
                'accessToken' => 'required',
                'device_id' => 'required',
                'login_by' => $driver
            ]
        );

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->messages()->all()]);
        }

        $user = Socialite::driver($driver)->stateless();
        $socialDrive = $user->userFromToken($request->accessToken);
        // return response()->json('Mzigo unakwama hapa');

        try {
            $socialSql = User::where('provider_id', $socialDrive->id)->get();
            if ($socialDrive->email != "") {
                $socialSql->where('email', $socialDrive->email);
            }
            $authUser = $socialSql->first();
            if ($authUser) {
                $authUser->provider_id = $socialDrive->id;
                $authUser->device_type = $request->device_type;
                $authUser->device_token = $request->device_token;
                $authUser->device_id = $request->device_id;
                $authUser->mobile = $request->mobile ?: '';
                $authUser->login_by = $driver;
                $authUser->save();
            } else {
                $authUser = new User();
                $authUser->email = $socialDrive->email;
                $name = explode(' ', $socialDrive->name, 2);
                $authUser->first_name = $name[0];
                $authUser->last_name = isset($name[1]) ? $name[1] : '';
                $authUser->password = bcrypt($socialDrive->id);
                $authUser->provider_id = $socialDrive->id;
                $authUser->device_type = $request->device_type;
                $authUser->device_token = $request->device_token;
                $authUser->device_id = $request->device_id;
                $authUser->mobile = $request->mobile ?: '';
                $fileContents = file_get_contents($socialDrive->getAvatar());
                File::put(public_path() . '/storage/user/profile/' . $socialDrive->getId() . ".jpg", $fileContents);
                //To show picture
                $picture = 'user/profile/' . $socialDrive->getId() . ".jpg";
                $authUser->picture = $picture;
                $authUser->login_by = $driver;
                $authUser->save();
            }
            if ($authUser) {
                $userToken = $authUser->token() ?: $authUser->createToken('socialLogin');
                return response()->json([
                    "status" => true,
                    "token_type" => "Bearer",
                    "access_token" => $userToken->accessToken
                ]);
            } else {
                return response()->json(['status' => false, 'message' => "Invalid credentials!"]);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Something Went Wrong']);
        }
    }
}
