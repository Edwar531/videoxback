<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// $this->middleware('auth:api', ['except' => ['login','register','validateToken']]);
Route::middleware(['cors'])->group(function () {

        // UPLOAD IMAGE TinyMCE
        Route::group(['middleware' => ['api','auth']], function () {
            Route::resource('user', 'userController');
            Route::post('refresh-token', 'AuthController@refresh');
            Route::post('validate-token', 'AuthController@validateToken');
            // galería
            Route::get('galleries-user', 'front\GalleryController@galleries_user');



            Route::post('new-gallery', 'front\GalleryController@new_gallery');
            Route::get('edit-gallery/{id}', 'front\GalleryController@edit_gallery');
            Route::post('update-gallery', 'front\GalleryController@update_gallery');

            Route::post('add-gallery', 'front\GalleryController@add_gallery');
            Route::post('upload-image', 'front\GalleryController@upload_image');
            Route::post('images-gallery', 'front\GalleryController@images_gallery');
            // 'galleries';
            // 'add-gallery';
            // 'edit-gallery';
            // 'update-gallery';
            // 'delete-gallery';
            // 'add-image';
            // 'update-image';
            // 'delete-image';
            Route::post('new-video', 'front\VideoController@new_video');
            Route::post('update-info-video', 'front\VideoController@update_info_video');
            Route::get('edit-video/{id}', 'front\VideoController@edit_video');
            Route::post('upload-video', 'front\VideoController@upload_video');
            // Route::post('upload-location-info', 'front\VideoController@upload_location_info');
            Route::post('save-update-video', 'front\VideoController@save_update_video');
            Route::get('videos-user', 'front\VideoController@videos_user');
        });

        Route::get('galleries', 'front\GalleryController@galleries');

        Route::get('get-video', 'front\VideoController@get_video');
        Route::get('videos','front\VideoController@videos');

        Route::get('mail_view','AuthController@mail_view');
        Route::get('mail_confirm/{email}/{token}', 'AuthController@mail_confirm');

        Route::post('login', 'AuthController@login');
        Route::post('register', 'AuthController@register');

        Route::get('/',function(){
            if(Auth::check()){
                return 'Logueado';
            }else{
                return 'No logueado';
            }
        })->name('home');

});




