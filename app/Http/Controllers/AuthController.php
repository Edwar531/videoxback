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
            'usuarioOcorreo' => 'required',
            'clave' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'errors' => $validator->errors()]);
        }

        // verifica si existe usuario
        $user = User::where("alias", $request->usuarioOcorreo)->orWhere("correo", $request->usuarioOcorreo)->first();
        if ($user == Null) {
            return response()->json(["error" => "El alias o correo no ha sido registrado."]);
        }

        if (Hash::check($request->clave, $user->clave)) {


            if ($user->correo_verificado_en == null) {
                return response()->json(["error" => "El correo de esta cuenta aún no ha sido verificado."]);
            }

            $token = JWTAuth::fromUser($user);
            $user = $user->only('id', 'alias', 'correo', 'nombre_completo');
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
        return view("mails.email_confirm");
    }

    public function mail_confirm(Request $request){
        $user = User::where("correo",$request->email)->first();
        if( $user == Null){
            return redirect()->to(env("ENDPOINT_FRONT")."?account=not-confirmed");
        }
        if($user->token_correo == $request->token){
            $user->correo_verificado_en = \Carbon\Carbon::now()->format("Y-m-d H:i:s");
            $user->save();
            return redirect()->to(env("ENDPOINT_FRONT")."?account=confirmed");
        }

        return redirect()->to(env("ENDPOINT_FRONT")."?account=not-confirmed");
    }

    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'alias' => 'required|unique:users|max:20',
            'nombres' => 'required|max:20',
            'apellidos' => 'required|max:20',
            'correo' => 'required|max:30|email|unique:users',
            'clave' => 'required|max:30|min:8',
            'confirmar_clave' => 'required|max:30|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'errors' => $validator->errors()]);
        }

        if($request->clave != $request->confirmar_clave){
            return response()->json(['result' => 'error', 'Los campos de las contraseñas deben coincidir.']);
        }

        $user = new User();
        $user->alias = $request->alias;
        $user->correo = $request->correo;
        $user->nombres = $request->nombres;
        $user->apellidos = $request->apellidos;
        $user->clave = bcrypt($request->clave);
        $user->nombre_completo = $user->nombres." ".$user->apellidos;
        $user->role = "Cliente";
        $token_correo = str_replace('/', '', Hash::make(\Carbon\Carbon::now()->format("YmdHis").Str::random(10)));
        $user->token_correo = $token_correo;
        $user->save();

        $data = ['data'=>['email'=>$user->correo,'token'=>$token_correo]];

        Mail::send('mails.email_confirm',$data,function($message) use($user){
            $message->subject('Confirma tu cuenta de Onlifetixxx');
            // $message->to('edtalentoinformatico@gmail.com');
            $message->to($user->correo);

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
