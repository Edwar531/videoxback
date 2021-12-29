<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Change_mail;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use ImageIntervention;
use App\Models\Image;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\User_bank;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Mail;
use Auth;

class UserController extends Controller
{
    public function user(Request $request)
    {
        $user = User::find($request->id);
        
        $image = Image::where('user_id', $request->id)->first();
        if ($image) {
            $user->imagen_de_perfil = $image->url_path;
        } else {
            $user->imagen_de_perfil = "";
        }
        $banks = User_bank::where("user_id",$user->id)->get();


        return response()->json(compact("user", "countries","banks"));
    }

    public function generateNameImage($file)
    {
        $fileName   = \Carbon\Carbon::now()->format('dmYHms') . Str::random(5);
        $exist = Image::where("name", $fileName)->first();
        if ($exist != Null) {
            return $this->generateNameImage($file);
        }
        return $fileName;
    }


    public function delete_image_user(Request $request)
    {
        $img = Image::where("user_id", $request->id)->first();
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
            if (!File::exists('public/images/users/' . $request->user_id . '/profile')) {
                File::makeDirectory('public/images/users/' . $request->user_id . '/profile');
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
            return response()->json(["result" => "ok", "message" => "Imagen subida con éxito.", "location" => $url_path . "?var=" . Str::random(4)]);
        } else {
            return response()->json("La imagen no pudo subirse.");
        }
    }

    public function update_alias(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'alias' => 'required|regex:/^[a-zA-Z0-9ZñÑáéíóúÁÉÍÓÚ_]*$/u|min:3|max:30|unique:users,alias,' . $request->id
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'errors' => $validator->errors()]);
        }

        $user = user::find($request->id);
        $user->alias = $request->alias;
        $user->save();

        return response()->json(['result' => 'ok', 'alias' => $request->alias, 'message' => "Datos actualizados con éxito."]);
    }

    public function generate_token_update_email(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'password' => 'required|max:60',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'errors' => $validator->errors()]);
        }
        if ($request->email == Auth::user()->email) {
            return response()->json(['result' => 'error', 'message' => "Para actualizar el correo de su cuetna debe ingresar uno alternativo a este."]);
        }
        if (!Hash::check($request->password, Auth::user()->password)) {
            return response()->json(["result" => "error", "message" => "La contraseña es incorrecta."]);
        }

        $change_email = Change_mail::where("user_id", Auth::user()->id)->get();
        foreach ($change_email as $c) {
            $c->delete();
        }

        $change_email = new Change_mail;
        $change_email->email = $request->email;
        $change_email->date = \Carbon\Carbon::now()->addHour()->format('Y-m-d H:i:s');
        $change_email->user_id = $request->id;
        $change_email->email = $request->email;

        $change_email->token = strtoupper(Str::random(8));
        $change_email->save();
        $data = ['change_email' => $change_email];

        Mail::send('mails.change_email', $data, function ($message) use ($change_email) {
            $message->subject('Confirma el cambio de correo de tu cuenta Onlifetixx');
            // $message->to('edtalentoinformatico@gmail.com');
            $message->to($change_email->email);
        });

        return response()->json(["result" => "ok", "newEmail" => $request->email]);
    }

    public function confirm_update_email(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
        ]);


        $user = Auth::user();

        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'errors' => $validator->errors()]);
        }

        $change_email = Change_mail::where("user_id", $user->id)->where("token", $request->code)->first();

        if ($change_email == Null) {
            return response()->json(['result' => 'error', 'message' => "El código insertado no es valido o es obsoleto."]);
        } else if (\Carbon\Carbon::now()->format("Y-m-d H:i:s") >  $change_email->date) {
            $change_email->delete();
            return response()->json(['result' => 'error', 'message' => "El código insertado no es valido o es obsoleto."]);
        }

        $user->email = $change_email->email;
        $change_email->delete();
        $user->save();
        return response()->json(['result' => 'ok', 'message' => "Los cambios han sido aplicados con éxito.", "email" => $user->email]);
    }

    public function update_email(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'password' => 'required|max:60',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'errors' => $validator->errors()]);
        }
    }

    public function change_password_profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|max:60',
            'new_password' => 'required|max:60',
            'repeat_new_password' => 'required|max:60',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'errors' => $validator->errors()]);
        }

        if ($request->new_password != $request->repeat_new_password) {
            return response()->json(['result' => 'error', 'message' => 'Los campos de "Nueva contraseña" y "Repita la nueva contraseña" deben coincidir.']);
        }

        $user = Auth::user();

        if (Hash::check($request->password, $user->password)) {
            $user->password = bcrypt($request->new_password);
            $user->save();
            return response()->json(['result' => 'ok', 'message' => "Su contraseña ha sido cambiada con éxito."]);
        } else {
            return response()->json(['result' => 'error', 'message' => "Su contraseña actual ingresada es incorrecta."]);
        }
    }

    public function update_personal_info(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:30|min:3',
            'last_name' => 'required|max:30|min:3',
            'date_of_birth' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'errors' => $validator->errors()]);
        }

        $yearDate = \Carbon\Carbon::parse($request->date_of_birth)->format('Y');
        $yearNow = \Carbon\Carbon::now()->format('Y');
        $age = $yearNow - $yearDate;

        $user = Auth::user();
        $user->name = $request->name;
        $user->last_name = $request->last_name;
        $user->date_of_birth = $request->date_of_birth;

        $user->age = $age;
        $user->save();

        return response()->json(['result' => 'ok', 'message' => "Datos actualizados con éxito.", "user" => $user]);
    }

    public function update_document_data(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document_type' => 'required',
            'document_number' => 'required|integer|max:99999999999999',
            'nationality' => 'required',
        ]);


        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'errors' => $validator->errors()]);
        }

        $user = Auth::user();
        $user->document_type = $request->document_type;
        $user->document_number = $request->document_number;
        $user->nationality = $request->nationality;
        $user->save();

        return response()->json(['result' => 'ok', 'message' => "Datos actualizados con éxito.", "user" => $user]);
    }

    public function update_contact_information(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'whatsapp' => 'max:30',
            'phone' => 'max:30',
            'country_id' => 'required|integer',
            'state_id' => 'nullable|integer',
            'city_id' => 'nullable|integer',
            'municipality' => 'max:40',
            'address' => 'max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'errors' => $validator->errors()]);
        }

        $user = Auth::user();
        $user->whatsapp = $request->whatsapp;
        $user->phone = $request->phone;
        $user->country_id = $request->country_id;
        if ($request->country_id) {
            $country = Country::find($request->country_id);
            $user->country = $country->name;
        } else {
            $user->country = Null;
        }
        $user->state_id = $request->state_id;
        if ($request->state_id) {
            $state = State::find($request->state_id);
            $user->state = $state->name;
        } else {
            $user->state = Null;
        }
        $user->city_id = $request->city_id;
        if ($request->city_id) {
            $city = City::find($request->city_id);
            $user->city = $city->name;
        } else {
            $user->city = Null;
        }
        $user->municipality = $request->municipality;
        $user->address = $request->address;
        $user->save();

        return response()->json(['result' => 'ok', 'message' => "Datos actualizados con éxito.", "user" => $user]);
    }


    public function update_paypal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'paypal' => 'required|max:60',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'errors' => $validator->errors()]);
        }

        $user = Auth::user();
        $user->paypal = $request->paypal;
        $user->save();

        return response()->json(['result' => 'ok', 'message' => "Datos actualizados con éxito.", "paypal" => $request->paypal]);

    }


    public function update_bank(Request $request){
        $validator = Validator::make($request->all(), [
            'country_id'=>'required',
            'name_bank' => 'required|max:60',
            'number' => 'numeric|max:999999999999999999999999999999999999999999999999999999999999',
            'type' => 'required|max:15',
            'owner' => 'required|max:60',
            'identification_owner' => 'integer|max:999999999999999',
            'user_id' => 'integer',
        ]);


        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'errors' => $validator->errors()]);
        }

        $user = Auth::user();
        $country = Country::find($request->country_id);
        if($request->id != null){
            $bank = User_bank::find($request->id);
        }else{
            $bank = new User_bank;
        }

        $bank->country_id = $request->country_id;
        $bank->country = $country->name;
        $bank->name_bank = $request->name_bank;
        $bank->number = $request->number;
        $bank->type = $request->type;
        $bank->owner = $request->owner;
        $bank->identification_owner = $request->identification_owner;
        $bank->user_id = $user->id;
        $bank->save();
        $banks = User_bank::where("user_id",$user->id)->get();

        return response()->json(['result' => 'ok', 'message' => "Cuenta de banco agregada con éxito.", "banks" => $banks]);
    }



    public function delete_bank(Request $request){
        $bank = User_bank::find($request->id);
        $bank->delete();
        $user = Auth::user();
        $banks = User_bank::where("user_id",$user->id)->get();

        return response()->json(['result' => 'ok', 'message' => "La Cuenta de banco ha sido eliminada con éxito.", "banks" => $banks]);


    }

    private function deleteImage($local_path)
    {
        if (File::exists($local_path)) {
            $local_path = str_replace("\\", "/", $local_path);
            $positionExt = strripos($local_path, '.');
            $ext = substr($local_path, $positionExt);
            $path_xs = str_replace($ext, '-xs' . $ext, $local_path);
            $path_sm = str_replace($ext, '-sm' . $ext, $local_path);
            File::delete($path_xs);
            File::delete($path_sm);
            File::delete($local_path);
        }
    }

    public function data_contact(Request $request)
    {
        $user = Auth::user();
        $countries = Country::orderBy('name', 'asc')->get();

        if ($user->country_id) {
            $states = State::where("country_id", $user->country_id)->orderBy('name', 'asc')->get();
        } else {
            $states = [];
        }

        if ($user->state_id) {
            $cities = City::where("state_id", $user->state_id)->orderBy('name', 'asc')->get();
        } else {
            $cities = [];
        }

        return response()->json(['countries' =>  $countries, 'states' =>  $states, 'cities' =>  $cities]);
    }

    public function get_states(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'country_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'errors' => $validator->errors()]);
        }
        $states = State::where("country_id", $request->country_id)->orderBy('name', 'asc')->get();
        return response()->json(['states' =>  $states]);
    }

    public function get_cities(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'state_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'errors' => $validator->errors()]);
        }
        $cities = City::where("state_id", $request->state_id)->orderBy('name', 'asc')->get();
        return response()->json(['cities' =>  $cities]);
    }
}
