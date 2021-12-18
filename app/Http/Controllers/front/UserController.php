<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Change_mail;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use ImageIntervention;
use App\Models\Image;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Mail;


class UserController extends Controller
{
    public function user(Request $request){
        $user = User::find($request->id);
        $paises = ["Venezuela","Ecuador","México","Colombia","Perú","Chile"];
        $image = Image::where('user_id',$request->id)->first();
        if($image){
            $user->imagen_de_perfil = $image->url_path;
        }else{
            $user->imagen_de_perfil = "";
        }

        return response()->json(compact("user","paises"));
    }

    public function generateNameImage( $file ){
        $fileName   = \Carbon\Carbon::now()->format('dmYHms').Str::random(5);
        $exist = Image::where("name", $fileName )->first();
        if($exist != Null){
            return $this->generateNameImage($file);
        }
        return $fileName;
    }


    public function delete_image_user(Request $request)
    {
        $img = Image::where("user_id",$request->id)->first();
        $this->deleteImage($img->local_path);
        $img->delete();

        return response()->json(["result" => "ok", "message" => "Imagen eliminada con éxito."]);
    }

    public function change_image_user(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp',
            'user_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'errors' => $validator->errors()]);
        }

        $width_min = 50;
        $width_max = 200;

        if ($request->hasFile('file')) {
            $file = ImageIntervention::make($request->file('file')->getRealPath());

            $fileName = $this->generateNameImage($file);
            $extension = $request->file->getClientOriginalExtension();
            $url_path = asset('public/images/users/' . $request->user_id) . '/profile/' . $fileName . '.' . $extension;
            $local_path = public_path('images/users/' . $request->user_id) . '/profile/' . $fileName . '.' . $extension;
            $image = new Image;
            $image->name = $fileName;
            $image->url_path = $url_path;
            $image->local_path = $local_path;
            $image->user_id = $request->user_id;

            $imageAnt = Image::where('user_id', $request->user_id)->first();
            if ($imageAnt) {
                $this->deleteImage($imageAnt->local_path);
                $imageAnt->delete();
            }
            $image->save();

            // make dir
            if (!File::exists('public/images')) {
                File::makeDirectory('public/images');
            }

            if (!File::exists('public/images/users')) {
                File::makeDirectory('public/images/users');
            }

            if (!File::exists('public/images/users/' . $request->user_id)) {
                File::makeDirectory('public/images/users/' . $request->user_id);
            }
            if (!File::exists('public/images/users/' . $request->user_id.'/profile')) {
                File::makeDirectory('public/images/users/' . $request->user_id.'/profile');
            }
            //move image to public/img folder
            if ($file->width() > $width_max) {
                $img = $file->resize($width_max, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $img->save('public/images/users/' . $request->user_id . '/profile/' . $fileName . '.' . $extension);
            } else {
                $file->save('public/images/users/' . $request->user_id . '/profile/' . $fileName . '.' . $extension);
            }
            return response()->json(["result" => "ok", "message" => "Imagen subida con éxito.", "location" => $url_path."?var=".Str::random(4)]);
        } else {
            return response()->json("La imagen no pudo subirse.");
        }
    }

    public function update_alias(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'alias' => 'required|regex:/^[a-zA-Z0-9ZñÑáéíóúÁÉÍÓÚ_]*$/u|min:3|max:30|unique:users,alias,'.$request->id
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'errors' => $validator->errors()]);
        }

        $user = user::find($request->id);
        $user->alias = $request->alias;
        $user->save();

        return response()->json(['result' => 'ok','alias'=>$request->alias,'message'=>"Datos actualizados con éxito."]);

    }
    // 'alias' => 'required|unique:users|max:25',
    //         'nombres' => 'required|max:30',
    //         'apellidos' => 'required|max:30',
    //         'correo' => 'required|max:40|email|unique:users',
    //         'clave' => 'required|max:30|min:8',
    //         'confirmar_clave' => 'required|max:30|min:8',

    public function generate_token_update_email(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'contraseña' => 'required|max:60',
            'correo' => 'required|email',
        ]);

        // $email_exist = User::where('user')

        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'errors' => $validator->errors()]);
        }

        $user = User::find($request->id);
        if (!Hash::check($request->contraseña, $user->contraseña))
        {
            return response()->json(["result"=>"error","message"=>"La contraseña es incorrecta."]);
        }

        $change_email = new Change_mail;
        $change_email->email = $request->email;
        $change_email->date = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
        $change_email->user_id = $request->id;
        $change_email->token = Str::random(4);
        $change_email->save();

        Mail::send('mails.email_confirm',$change_email,function($message) use($change_email){
            $message->subject('Confirma el cambio de correo de tu cuenta Onlifetixx');
            // $message->to('edtalentoinformatico@gmail.com');
            $message->to($change_email->correo);
        });

        return response()->json(["result"=>"ok","message"=>'Se ha enviado un código al correo: "'.$change_email->correo.'". Ingrese el código en el siguiente campo y presione aceptar completar el cambio de correo de su cuenta.']);
    }

    public function update_email(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'contraseña' => 'required|max:60',
            'correo' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'errors' => $validator->errors()]);
        }

    }

    public function edit_password_profile(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'alias' => 'required|max:30'
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'errors' => $validator->errors()]);
        }
    }

    public function update_personal_info(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'alias' => 'required|max:30'
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'errors' => $validator->errors()]);
        }
    }

    public function update_document_data(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'alias' => 'required|max:30'
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'errors' => $validator->errors()]);
        }
    }

    public function update_contact_information(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'alias' => 'required|max:30'
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'errors' => $validator->errors()]);
        }
    }

    private function deleteImage($local_path){
        if(File::exists($local_path)) {
            $local_path = str_replace("\\","/",$local_path);
            $positionExt = strripos($local_path, '.');
            $ext = substr($local_path,$positionExt);
            $path_xs = str_replace($ext,'-xs'.$ext,$local_path);
            $path_sm = str_replace($ext,'-sm'.$ext,$local_path);
            File::delete($path_xs);
            File::delete($path_sm);
            File::delete($local_path);
        }
    }


}
