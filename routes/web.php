<?php

use App\Http\Controllers\admin\AlbumController;
use App\Http\Controllers\admin\CommentMusicController;
use App\Http\Controllers\admin\FilmController;
use App\Http\Controllers\admin\FilmTextController;
use App\Http\Controllers\admin\NotificationController;
use App\Http\Controllers\admin\OrderMusicController;
use App\Http\Controllers\admin\ReportController;
use App\Http\Controllers\admin\SliderController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\admin\SettingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\IndexController;
use App\Http\Controllers\admin\MusicController;
use App\Http\Controllers\admin\SingerController;
use App\Http\Controllers\admin\TextController;
use App\Http\Controllers\admin\AdminController;
use App\Http\Controllers\admin\TextIdiomsController;
use App\Http\Controllers\admin\IdiomController;

Route::get('/get_id', [MusicController::class, 'getGuessId']);
Route::get('/generateCodeIntroduce/run', [\App\Http\Controllers\TestController::class, 'generateCodeIntroduce']);

//Admin
Route::post('/login_admin', [AdminController::class, 'login']);

Route::middleware('CheckAdminApiAuthentication')->group(function () {

    Route::prefix('index')->group(function () {
        Route::get('/statistics', [IndexController::class, 'statistics'])->name('index/statistics');
    });

    Route::prefix('idioms')->group(function () {
        Route::post('/create', [IdiomController::class, 'createIdiom'])->name('idioms/create');
        Route::post('/update', [IdiomController::class, 'updateIdiom'])->name('idioms/update');
    });


    Route::prefix('idioms_text')->group(function () {
        Route::post('/create', [TextIdiomsController::class, 'createTextIdiom'])->name('idioms_text/create');
        Route::post('/list', [TextIdiomsController::class, 'getTextIdioms'])->name('idioms_text/list');
        Route::post('/remove', [TextIdiomsController::class, 'deleteTextIdiom'])->name('idioms_text/remove');
    });


    Route::prefix('notifications')->group(function () {
        Route::post('/send', [NotificationController::class, 'sendFCM'])->name('notifications/send');
        Route::post('/create', [NotificationController::class, 'addNotification'])->name('notifications/create');
        Route::post('/list', [NotificationController::class, 'getNotifications'])->name('notifications/list');
    });

    Route::get('/get_admin_data', [AdminController::class, 'get_admin_data']);
    Route::prefix('musics')->group(function () {
        Route::post('/list', [MusicController::class, 'MusicsList'])->name('musics/list');
        Route::post("/create", [MusicController::class, "musicsCreate"])->name('musics/create');
        Route::post("/update", [MusicController::class, "musicsUpdate"])->name('musics/update');
        Route::get('/music/{id}', [MusicController::class, 'getMusicCompleteInfo'])->name('musics/music');
    });

    Route::prefix('singers')->group(function () {
        Route::post('/list', [SingerController::class, 'singersList'])->name('singers/list');
        Route::post('/single', [SingerController::class, 'getSinger'])->name('singers/single');
        Route::post("/create", [SingerController::class, "singersCreate"])->name('singers/create');
        Route::post("/update", [SingerController::class, "singersUpdate"])->name('singers/update');
    });

    Route::prefix('texts')->group(function () {
        Route::post('/list', [TextController::class, 'textsList'])->name('texts/list');
        Route::post("/create", [TextController::class, "textsCreate"])->name('texts/create');
        Route::post("/update", [TextController::class, "textsUpdate"])->name('texts/update');
          Route::post("/upload", [TextController::class, "uploadFileGetInfoAndSave"])->name('texts/upload');
        Route::post("/download", [TextController::class, "downloadMusicTextFile"])->name('texts/download');
    });

    Route::prefix('albums')->group(function () {
        Route::post('/list', [AlbumController::class, 'albumsList'])->name('albums/list');
        Route::post('/single', [AlbumController::class, 'getAlbum'])->name('albums/single');
        Route::post("/create", [AlbumController::class, "albumsCreate"])->name('albums/create');
        Route::post("/update", [AlbumController::class, "albumsUpdate"])->name('albums/update');
    });

    Route::prefix('users')->group(function () {
        Route::post('/list', [UserController::class, 'usersList'])->name('users/list');
        Route::post('/single', [UserController::class, 'singleUser'])->name('users/single');
        Route::post("/add_subs", [ReportController::class, "addPanelReports"])->name('users/add_subs');
    });

    Route::prefix('user_comments')->group(function () {
        Route::post('/list', [CommentMusicController::class, 'getMusicCommentsNotConfirmed'])->name('user_comments/list');
        Route::post('/change_status', [CommentMusicController::class, 'changeStatusMusicComment'])->name('user_comments/changeStatus');
    });

    Route::prefix('orders')->group(function () {
        Route::post('/list', [OrderMusicController::class, 'ordersList'])->name('orders/list');
        Route::post('/single', [OrderMusicController::class, 'getOrder'])->name('orders/single');
        Route::post('/edit', [OrderMusicController::class, 'editOrderMusic'])->name('orders/edit');
        Route::post('/remove', [OrderMusicController::class, 'removeMusicComment'])->name('orders/remove');
    });

    Route::prefix('setting')->group(function () {
        Route::post('/single', [SettingController::class, 'getSetting'])->name('setting/single');
        Route::post('/edit', [SettingController::class, 'editSetting'])->name('setting/edit');
    });


    Route::prefix('sliders')->group(function () {
        Route::post('/list', [SliderController::class, 'slidersList'])->name('sliders/list');
        Route::post("/single", [SliderController::class, "getSlider"])->name('sliders/single');
        Route::post("/update", [SliderController::class, "sliderUpdate"])->name('sliders/update');
        Route::post("/create", [SliderController::class, "sliderCreate"])->name('sliders/create');
        Route::post("/remove", [SliderController::class, "removeSlider"])->name('sliders/remove');
    });

    Route::prefix('reports')->group(function () {
        Route::post('/admin_list', [ReportController::class, 'reportsAdminList'])->name('reports/admin_list');
        Route::post('/user_list', [ReportController::class, 'reportsUserList'])->name('reports/user_list');
    });

    Route::prefix('movies')->group(function () {
        Route::post('/list', [FilmController::class, 'getListForShow'])->name('movies/list');
        Route::post('/list_parts', [FilmController::class, 'getChildById'])->name('movies/list_parts');
        Route::post("/create", [FilmController::class, "filmCreate"])->name('movies/create');
        Route::post("/update", [FilmController::class, "filmUpdate"])->name('movies/update');
        Route::get('/movie/{id}', [FilmController::class, 'getFilmByIdRequest'])->name('movies/movie');
    });

    Route::prefix('film_texts')->group(function () {
        Route::post('/list', [FilmTextController::class, 'getTextList'])->name('film_texts/list');
        Route::post("/create", [FilmTextController::class, "insertListTexts"])->name('film_texts/create');
        Route::post("/update", [FilmTextController::class, "updateListTexts"])->name('film_texts/update');
    });

});

Route::get('/', [IndexController::class, 'index']);
Route::get('/{app?}', [IndexController::class, 'index'])->where('app', '.*');

