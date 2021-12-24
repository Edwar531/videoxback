<?php

namespace App\Http\Controllers;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Mail;
use Illuminate\Support\Str;
class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */


    public function authenticated(Request $request)
    {
        return response()->json(["result" => "ok"]);
    }

    public function __construct()
    {
        // $this->middleware('auth:api', ['except' => ['login','register','validateToken']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateToken(Request $request)
    {
        return $this->respondWithToken($request->token);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'alias_or_email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'errors' => $validator->errors()]);
        }

        // verifica si existe usuario
        $user = User::where("alias", $request->alias_or_email)->orWhere("email", $request->alias_or_email)->first();
        if ($user == Null) {
            return response()->json(["error" => "El alias o correo no ha sido registrado."]);
        }

        if (Hash::check($request->password, $user->password)) {


            if ($user->date_email_verified == null) {
                return response()->json(["error" => "El correo de esta cuenta aún no ha sido verificado."]);
            }

            $token = JWTAuth::fromUser($user);
            $user = $user->only('id', 'alias', 'email', 'name','last_name');
            $user['token'] = $token;
            $user['expiration'] = \Carbon\Carbon::now()->format('Y/m/d H:i:s');
            return response()->json($user);
        }
        return response()->json(["error" => "La contraseña ingresada es incorrecta."]);
    }

    // public function mail_contact(Request $request){
    //     // return response()->json(["result"=>"message contact","message"=>"XXXXXXX"]);
    //   $request->validate([
    //       'email'=>'required|min:4|email',
    //       'message'=>'required|min:4'
    //   ]);



    //   return response()->json(["result"=>"success","message"=>"Mensaje enviado con éxito."]);
    // }

    public function mail_view(Request $request){
        // return view("mails.email_confirm");
        return view("mails.change_email");

    }

    public function mail_confirm(Request $request){
        $user = User::where("email",$request->email)->first();
        if( $user == Null){
            return redirect()->to(env("ENDPOINT_FRONT")."?account=not-confirmed");
        }
        if($user->token_email == $request->token){
            $user->date_email_verified = \Carbon\Carbon::now()->format("Y-m-d H:i:s");
            $user->save();
            return redirect()->to(env("ENDPOINT_FRONT")."?account=confirmed");
        }

        return redirect()->to(env("ENDPOINT_FRONT")."?account=not-confirmed");
    }

    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'alias' => 'required|unique:users|max:25',
            'name' => 'required|max:30',
            'last_anme' => 'required|max:30',
            'correo' => 'required|max:40|email|unique:users',
            'password' => 'required|max:30|min:8',
            'confirm_password' => 'required|max:30|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'errors' => $validator->errors()]);
        }

        if($request->password != $request->confirm_password){
            return response()->json(['result' => 'error', 'Los campos de las contraseñas deben coincidir.']);
        }

        $user = new User();
        $user->alias = $request->alias;
        $user->email = $request->email;
        $user->name = $request->name;
        $user->last_anme = $request->last_anme;
        $user->password = bcrypt($request->password);

        $user->role = "Cliente";
        $token_email = str_replace('/', '', Hash::make(\Carbon\Carbon::now()->format("YmdHis").Str::random(10)));
        $user->token_email = $token_email;
        $user->save();

        $data = ['data'=>['email'=>$user->email,'token'=>$token_email]];

        Mail::send('mails.email_confirm',$data,function($message) use($user){
            $message->subject('Confirma tu cuenta de Onlifetixxx');
            // $message->to('edtalentoinformatico@gmail.com');
            $message->to($user->email);

        });

        return response()->json(["result"=>"ok","message"=>"Te has registrado con éxito, confirma el mensaje enviado a tu correo electrónico para poder ingresar."]);

    }
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $token = auth()->refresh();
        $user = auth()->user()->only('id', 'name', 'email');
        $user['token'] = $token;
        $user['expiration'] = \Carbon\Carbon::now()->format('Y/m/d H:i:s');

        return response()->json(compact('user'));
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 1440
        ]);
    }
    //   'expires_in' => auth()->factory()->getTTL() * 60
}
