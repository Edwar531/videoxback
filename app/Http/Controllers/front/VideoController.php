<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Video;
use App\Models\Tag;
use App\Models\VideoRelationsTag;
use App\Models\User;



class VideoController extends Controller
{

    public function videos(Request $request)
    {
        // $videos =   $videos =Video::where('saved',"1")->orderBy("created_at","DESC")->get();
        $tags = Tag::orderBy('nombre', 'asc')->get(['id', 'nombre','slug']);
        if ($request->tag != null) {
            $videos = DB::table('videos')
            ->join('users', 'videos.user_id', '=', 'users.id')
            ->leftJoin('video_relations_tags', 'videos.id', '=', 'video_relations_tags.video_id')
            ->leftJoin('tags', 'video_relations_tags.tag_id', '=', 'tags.id')
            ->select('videos.*','users.alias AS alias')
            ->where("tags.slug", 'like', '%'.$request->tag.'%')->where('videos.saved', "1")->orderBy("videos.created_at", "DESC")
            ->distinct()
            ->get();
        }else if ($request->search != null) {
            $videos = DB::table('videos')
            ->join('users', 'videos.user_id', '=', 'users.id')
            ->leftJoin('video_relations_tags', 'videos.id', '=', 'video_relations_tags.video_id')
            ->leftJoin('tags', 'video_relations_tags.tag_id', '=', 'tags.id')
            ->select('videos.*','users.alias AS alias')
            ->where("tags.slug", 'like', '%'.$request->search.'%')->where('videos.saved', "1")->orderBy("videos.created_at", "DESC")
            ->orWhere("videos.nombre", 'like', '%' . $request->search . '%')->where('videos.saved', "1")->orderBy("videos.created_at", "DESC")
            ->distinct()
            ->get();
        }else if($request->limit != null){
            $videos = DB::table('videos')
            ->join('users', 'videos.user_id', '=', 'users.id')
            ->select('videos.*','users.alias AS alias')
            ->where('videos.saved', "1")->orderBy("videos.created_at", "DESC")
            ->distinct()
            ->get()->take($request->limit);
        }else{
            $videos = DB::table('videos')
            ->join('users', 'videos.user_id', '=', 'users.id')
            ->select('videos.*','users.alias AS alias')
            ->where('videos.saved', "1")->orderBy("videos.created_at", "DESC")

            ->get();
        }

        return response()->json(["videos" => $videos, "tags" => $tags,"request"=>$request->all()]);
    }
    public function get_video(Request $request)
    {
        $video = Video::find($request->id);
        $user = User::find($video->user_id);
        $video->alias_usuario = $user->alias;
        $tags = DB::table('video_relations_tags')
        ->where('video_relations_tags.video_id',$request->id)
        ->leftJoin('tags', 'video_relations_tags.tag_id', '=', 'tags.id')
        ->select('tags.*')
        ->distinct()
        ->get();
        $video->tags = $tags;

        return response()->json($video);
    }

    public function new_video(Request $request)
    {
        $video = new Video;
        $video->user_id = $request->id;
        $video->save();
        $tags = Tag::orderBy('nombre', 'asc')->get(['id', 'nombre']);
        return response()->json(["video" => $video, "tags" => $tags]);
    }

    public function edit_video(Request $request)
    {
        $video = video::find($request->id);
        $tags = Tag::orderBy('nombre', 'asc')->get(['id', 'nombre']);
        // $selected_tags = orderBy('nombre','asc')->get(['id','nombre']);
        $selected_tags = DB::table('video_relations_tags')
            ->join('tags', 'video_relations_tags.tag_id', '=', 'tags.id')
            ->select('tags.id AS id', 'tags.nombre AS nombre')
            ->where("video_relations_tags.video_id",$request->id)
            ->distinct()
            ->get();
        return response()->json(["video" => $video, "tags" => $tags, "selected_tags" => $selected_tags]);
    }

    public function update_info_video(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'nombre' => 'required|unique:videos,nombre,' . $request->id,
            'etiquetas' => 'required',
            'descripcion' => 'nullable|max:600',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error-validation', 'errors' => $validator->errors()]);
        }
        $video = video::find($request->id);
        $video->nombre = $request->nombre;
        $video->descripcion = $request->descripcion;

        $Relationstags = VideoRelationsTag::where('video_id', $request->id)->get();

        foreach ($Relationstags as $tag) {
            $tag->delete();
        }

        foreach ($request->etiquetas as $tag) {
            $newtag = new VideoRelationsTag;
            $newtag->video_id = $request->id;
            $newtag->tag_id = $tag["id"];
            $newtag->save();
        }

        $video->save();
        return response()->json(["result" => "ok", "message" => "no-message"]);
    }

    public function add_video(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|unique:videos',
            'descripcion' => 'nullable|max:600',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error-validation', 'errors' => $validator->errors()]);
        }

        $video = video::find($request->id);
        $video->nombre = $request->nombre;
        $video->descripcion = $request->descripcion;
        $video->save();

        return response()->json(["result" => "ok", "message" => "no-message"]);
    }

    public function galleries(Request $request)
    {
        $galleries = video::where("user_id", $request->id)->where('saved', 1)->orderBy('created_at', 'asc')->get();
        $imagesAll = Image::get();
        foreach ($galleries as $key => $ga) {
            $images = [];
            foreach ($imagesAll as $key => $img) {
                if ($img->video_id == $ga->id) {
                    $images[] = $img;
                }
            }
            $ga->images = $images;
        }
        return response()->json($galleries);
    }

    public function update_video(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|unique:galleries,nombre,' . $request->id,
            'description' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error-validation', 'errors' => $validator->errors()]);
        }
        $video = video::find($request->id);
        $video->nombre = $request->nombre;
        $video->descripcion = $request->descripcion;
        $video->saved = 1;
        $video->save();

        return response()->json(["result" => "ok", "message" => "Galeria guardada con éxito."]);
    }

    public function upload_location_info(Request $request)
    {
        $video = Video::find($request->id);
        $video->location = $request->location;
        $video->bucket = $request->bucket;
        $video->key = $request->key;
        $video->vista_previa = $request->vista_previa;
        $video->save();

        return response()->json(["result" => "ok", "message" => "no-message"]);
    }

    public function save_update_video(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|unique:galleries,nombre,' . $request->id,
            'descripcion' => 'nullable|max:600',
            'location' => 'required',
            'bucket' => 'required',
            'key' => 'required',
            'vista_previa' => 'required',
            'user_id' => 'required|integer',

        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error-validation', 'errors' => $validator->errors()]);
        }

        $video = Video::find($request->id);
        $video->nombre = $request->nombre;
        $video->descripcion = $request->descripcion;
        $video->user_id = $request->user_id;
        $video->location = $request->location;
        $video->bucket = $request->bucket;
        $video->key = $request->key;
        $video->vista_previa = $request->vista_previa;
        $video->saved = "1";
        $video->save();

        return response()->json(["result" => "ok", "message" => "El video ha sido publicado con éxito."]);
    }

    public function videos_user(Request $request)
    {
        $videos = Video::where('user_id', $request->user_id)->where('saved', "1")->get();
        return response()->json($videos);
    }
}
