<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use ImageIntervention;
use Illuminate\Support\Facades\File;
use App\Models\Gallery;
use App\Models\Image;
use Illuminate\Support\Str;


class GalleryController extends Controller
{
    //
    public function get_gallery(Request $request){
        $gallery = Gallery::where("slug",$request->slug)->first();

        if($gallery == null){
            return response()->json(["result"=>"not-exist"]);
        }

        return response()->json($gallery);

    }

    public function generate_code($code){
       $code = \Carbon\Carbon::now()->format("dmY").Str::random(8) .\Carbon\Carbon::now()->format("His");
    }

    public function galleries(Request $request){
        if($request->search == null || $request->search == "undefined"){
            $request->search = "";
        }
        if($request->limit != null and $request->limit != "null"){
            $galleries = Gallery::where('saved',"1")->orderBy('created_at','asc')->take($request->limit)->get();
        }else{
            $galleries = Gallery::where("nombre", 'like', '%'.$request->search.'%')->where('saved',"1")->orderBy('created_at','asc')->get();
        }

        $imagesAll = Image::get();
        foreach ($galleries as $key => $ga) {
            $images = [];
            foreach ($imagesAll as $key => $img) {
                if($img->gallery_id == $ga->id){
                    $images[] = $img;
                }
            }
            $ga->images = $images;
        }
        return response()->json(["galleries"=>$galleries,"limit"=>$request->search]);
    }

    public function galleries_user(Request $request){
        $galleries = Gallery::where("user_id",$request->id)->where('saved',1)->orderBy('created_at','asc')->get();
        $imagesAll = Image::get();
        foreach ($galleries as $key => $ga) {
            $images = [];
            foreach ($imagesAll as $key => $img) {
                if($img->gallery_id == $ga->id){
                    $images[] = $img;
                }
            }
            $ga->images = $images;
        }
        return response()->json($galleries);
    }

    public function new_gallery(Request $request)
    {
        $gallery = new Gallery;
        $gallery->user_id = $request->id;
        $gallery->estatus = 'publicado';
        $gallery->save();
        $images = [];
        return response()->json(["gallery"=>$gallery, "images"=> $images]);
    }

    public function edit_gallery(Request $request){
        $gallery = Gallery::find($request->id);
        $images = Image::where('gallery_id',$request->id)->get();
        return response()->json(["gallery"=>$gallery, "images"=> $images]);
    }

    public function update_gallery(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|unique:galleries,nombre,'.$request->id,
            'estatus' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error-validation', 'errors' => $validator->errors()]);
        }

        $gallery = Gallery::find($request->id);
        $gallery->nombre = $request->nombre;
        $gallery->estatus = $request->estatus;
        $gallery->saved = 1;
        $gallery->save();

        return response()->json(["result" => "ok", "message" => "Galeria guardada con éxito."]);
    }

    public function add_gallery(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|unique:galleries',
            'estatus' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error-validation', 'errors' => $validator->errors()]);
        }

        $gallery = Gallery::find($request->id);
        $gallery->nombre = $request->nombre;
        $gallery->estatus = $request->estatus;
        $gallery->saved = 1;
        $gallery->save();

        return response()->json(["result" => "ok", "message" => "Galeria guardada con éxito."]);
    }

    public function upload_image(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp',
            'gallery_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'errors' => $validator->errors()]);
        }

        $width_min = 350;
        $width_max = 1200;

        if ($request->hasFile('file')) {

            $file = ImageIntervention::make($request->file('file')->getRealPath());

            if ($file->width() < $width_min) {
                return response()->json(["result" => "error", "message" => "Por favor ingrese imágenes con tamaño superior a los " . $width_min . " píxeles."]);
            }

            $extension = $request->file('file')->getClientOriginalExtension();
            $fileName   = \Carbon\Carbon::now()->format('dmYHms').Str::random(5);
            $url_path = asset('public/images/articles/' . $request->gallery_id) . '/' . $fileName . '.' . $extension;
            $local_path = public_path('images/articles/' . $request->gallery_id) . '/' . $fileName . '.' . $extension;
            $image = new Image;

            $image->name = $fileName.$extension;
            $image->url_path = $url_path;
            $image->local_path = $local_path;
            $image->gallery_id = $request->gallery_id;
            if ($request->type == 'principal') {
                $imageAnt = Image::where('type', 'principal')->where('gallery_id', $request->gallery_id)->first();
                if ($imageAnt) {
                    $this->deleteImage($imageAnt->local_path);
                    $imageAnt->delete();
                }
                $image->type = $request->type;
            }
            $image->save();

            // make dir
            if (!File::exists('public/images')) {
                File::makeDirectory('public/images');
            }

            if (!File::exists('public/images/articles')) {
                File::makeDirectory('public/images/articles');
            }

            if (!File::exists('public/images/articles/' . $request->gallery_id)) {
                File::makeDirectory('public/images/articles/' . $request->gallery_id);
            }
            //move image to public/img folder
            if ($file->width() > $width_max) {
                $img = $file->resize($width_max, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $img->save('public/images/articles/' . $request->gallery_id . '/' . $fileName . '.' . $extension);
            } else {
                $file->save('public/images/articles/' . $request->gallery_id . '/' . $fileName . '.' . $extension);
            }
            $img = $file->resize($width_max, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            // crea thumb

            $img = $file->resize(400, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->save('public/images/articles/' . $request->gallery_id . '/' . $fileName . '-sm.' . $extension);

            $img = $file->resize(100, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->save('public/images/articles/' . $request->gallery_id . '/' . $fileName . '-xs.' . $extension);
            return response()->json(["result" => "success", "message" => "Imagen subida con éxito.", "location" => $url_path]);
        } else {
            return response()->json("La imagen no pudo subirse.");
        }
    }

    public function images_gallery(Request $request){
       $images = Image::where('gallery_id',$request->id)->orderBy("created_at","asc")->get();
       return response()->json($images);
    }
}
