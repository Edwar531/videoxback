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
            // Usuario
            Route::post('user', 'front\UserController@user');
            Route::post('change-image-user', 'front\UserController@change_image_user');
            Route::post('delete-image-user', 'front\UserController@delete_image_user');
            Route::post("update-alias", 'front\UserController@update_alias');


            Route::post("generate-token-update-email", 'front\UserController@generate_token_update_email');

            Route::post("confirm-update-email", 'front\UserController@confirm_update_email');
            Route::post("change-password-profile", 'front\UserController@change_password_profile');
            Route::post("update-personal-info", 'front\UserController@update_personal_info');
            Route::post("update-document-data", 'front\UserController@update_document_data');
            Route::post("update-contact-information", 'front\UserController@update_contact_information');

            Route::post("update-paypal", 'front\UserController@update_paypal');
            Route::post("add-bank", 'front\UserController@add_bank');
            Route::post("update-bank", 'front\UserController@update_bank');






            Route::get("data-contact", 'front\UserController@data_contact');
            Route::post("get-states", 'front\UserController@get_states');
            Route::post("get-cities", 'front\UserController@get_cities');

            Route::post('authenticated', 'AuthController@authenticated');

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
            // Video
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
        Route::get('mail-view','AuthController@mail_view');
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




