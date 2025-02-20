<?php

use App\Http\Controllers\admin\AlbumController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\CommentMusicController;
use App\Http\Controllers\admin\FilmController;
use App\Http\Controllers\admin\FilmTextController;
use App\Http\Controllers\admin\LinkController;
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
use App\Http\Controllers\admin\IdiomController;
use App\Http\Controllers\admin\WordController;
use App\Http\Controllers\admin\GrammerController;
use App\Http\Controllers\admin\MapController;
use App\Http\Controllers\admin\ReplaceController;
use App\Http\Controllers\admin\PaymentController;

Route::get('/get_id', [MusicController::class, 'getGuessId']);

Route::get('/payment/verify', [PaymentController::class, 'verifyPayment'])->name('payment.verify');

//Admin
Route::post('/login_admin', [AdminController::class, 'login']);

Route::middleware('CheckAdminApiAuthentication')->group(function () {

    Route::prefix('index')->group(function () {
        Route::get('/statistics', [IndexController::class, 'statistics'])->name('index/statistics');
    });

    Route::prefix('idioms')->group(function () {
        Route::post('/list', [IdiomController::class, 'idiomsList'])->name('idioms/list');
        Route::post("/single", [IdiomController::class, "getIdiom"])->name('idioms/single');
        Route::post('/create', [IdiomController::class, 'createIdiom'])->name('idioms/create');
        Route::post('/update', [IdiomController::class, 'updateIdiom'])->name('idioms/update');
        Route::post('/update_level', [IdiomController::class, 'updateIdiomLevel'])->name('idioms/update/level');
        Route::post("/remove", [IdiomController::class, "removeIdiom"])->name('idioms/remove');
        Route::post("/definition/remove", [IdiomController::class, "removeIdiomDefinition"])->name('idioms/definition/remove');
        Route::post("/add_image", [IdiomController::class, "addImageToDefinition"])->name('idioms/add_image');
    });

    Route::prefix('notifications')->group(function () {
        Route::post('/send', [NotificationController::class, 'sendFCM'])->name('notifications/send');
        Route::post('/create', [NotificationController::class, 'addNotification'])->name('notifications/create');
        Route::post('/list', [NotificationController::class, 'getNotifications'])->name('notifications/list');
        Route::post('/get-music-data', [NotificationController::class, 'getMusicData'])->name('notifications/get_music_data');
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
        Route::post('/search', [TextController::class, 'searchTexts'])->name('texts/search');
        Route::post('/list', [TextController::class, 'textsList'])->name('texts/list');
        Route::post("/update", [TextController::class, "textsUpdate"])->name('texts/update');
        Route::post("/upload", [TextController::class, "textsUpdateUsingFile"])->name('texts/upload');
        Route::post("/download", [TextController::class, "downloadTexts"])->name('texts/download');
        Route::post("/add", [TextController::class, "addText"])->name('texts/add');
        Route::post("/remove", [TextController::class, "removeText"])->name('texts/remove');
        Route::post("/load", [TextController::class, "loadText"])->name('texts/load');
        Route::post("/join", [TextController::class, "joinText"])->name('texts/join');
    });

    Route::prefix('albums')->group(function () {
        Route::post('/list', [AlbumController::class, 'albumsList'])->name('albums/list');
        Route::post('/single', [AlbumController::class, 'getAlbum'])->name('albums/single');
        Route::post("/create", [AlbumController::class, "albumsCreate"])->name('albums/create');
        Route::post("/update", [AlbumController::class, "albumsUpdate"])->name('albums/update');
    });

    Route::prefix('categories')->group(function () {
        Route::post('/list', [CategoryController::class, 'categoriesList'])->name('categories/list');
        Route::post('/single', [CategoryController::class, 'getCategory'])->name('categories/single');
        Route::post("/create", [CategoryController::class, "categoryCreate"])->name('categories/create');
        Route::post("/update", [CategoryController::class, "categoryUpdate"])->name('categories/update');
        Route::post("/items", [CategoryController::class, "categoryItems"])->name('categories/items');
        Route::post("/sync", [CategoryController::class, "categorySync"])->name('categories/sync');
        Route::post("/add/group", [CategoryController::class, "categoryAddGroup"])->name('categories/add/group');
    });

    Route::prefix('links')->group(function () {
        Route::post('/add', [LinkController::class, 'addLink'])->name('links/add');
        Route::post('/delete', [LinkController::class, 'deleteLink'])->name('links/delete');
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
        Route::post("/update", [FilmTextController::class, "updateListTexts"])->name('film_texts/update');
        Route::post("/upload", [FilmTextController::class, "uploadFileGetInfoAndSave"])->name('film_texts/upload');
        Route::post("/download", [FilmTextController::class, "downloadFilmTextFile"])->name('film_texts/download');
    });

    Route::prefix('words')->group(function () {
        Route::post('/list',   [WordController::class, 'WordsList'])->name('words/list');
        Route::post("/single", [WordController::class, "getWord"])->name('words/single');
        Route::post("/update", [WordController::class, "updateWord"])->name('words/update');
        Route::post("/update_level", [WordController::class, "updateWordLevel"])->name('words/update/level');
        Route::post("/create", [WordController::class, "createWord"])->name('words/create');
        Route::post("/remove", [WordController::class, "removeWord"])->name('words/remove');
        Route::post("/definition/remove", [WordController::class, "removeWordDefinition"])->name('words/definition/remove');
        Route::get( "/types",  [WordController::class, "getTypes"])->name('words/types');
        Route::post( "/add_image",  [WordController::class, "addImageToDefinition"])->name('words/add_image');
    });

    Route::prefix('replace')->group(function () {
        Route::post('/process-text', [ReplaceController::class, 'processText'])->name('replace/process_text');
        Route::post('/apply',        [ReplaceController::class, 'apply'])->name('replace/apply');
    });

    Route::prefix('replace_rule')->group(function () {
        Route::post('/list',   [ReplaceController::class, 'ruleList'])->name('replace_rule/list');
        Route::post("/create", [ReplaceController::class, "createRule"])->name('replace_rule/create');
        Route::post("/remove", [ReplaceController::class, "removeRule"])->name('replace_rule/remove');
    });

    Route::prefix('grammers')->group(function () {
        Route::post('/list',   [GrammerController::class, 'GrammersList'])->name('grammers/list');
        Route::post("/single", [GrammerController::class, "getGrammer"])->name('grammers/single');
        Route::post("/update", [GrammerController::class, "updateGrammer"])->name('grammers/update');
        Route::post("/create", [GrammerController::class, "createGrammer"])->name('grammers/create');
        Route::post("/remove", [GrammerController::class, "removeGrammer"])->name('grammers/remove');
        Route::prefix('rules')->group(function () {
            Route::post('/list',   [GrammerController::class, 'GrammerRulesList'])->name('grammer_rules/list');
            Route::post('/create', [GrammerController::class, 'createGrammerRule'])->name('grammer_rules/create');
            Route::post('/update', [GrammerController::class, 'updateGrammerRule'])->name('grammer_rules/update');
            Route::post('/remove', [GrammerController::class, 'removeGrammerRule'])->name('grammer_rules/remove');
        });
    });

    Route::prefix('maps')->group(function () {
        Route::post('/list', [MapController::class, 'MapsList'])->name('maps/list');
        Route::post("/single", [MapController::class, "getMap"])->name('maps/single');
        Route::post("/update", [MapController::class, "updateMap"])->name('maps/update');
        Route::post("/create", [MapController::class, "createMap"])->name('maps/create');
        Route::post("/remove", [MapController::class, "removeMap"])->name('maps/remove');
        Route::prefix('reasons')->group(function () {
            Route::post('/list', [MapController::class, 'MapReasonsList'])->name('map_reasons/list');
            Route::post('/create', [MapController::class, 'createMapReason'])->name('map_reasons/create');
            Route::post('/update', [MapController::class, 'updateMapReason'])->name('map_reasons/update');
            Route::post('/remove', [MapController::class, 'removeMapReason'])->name('map_reasons/remove');
            Route::post('/group-edit', [MapController::class, 'groupEditWordMapReason'])->name('map_reasons/group_edit');
        });
    });

});

Route::get('/', [IndexController::class, 'index']);
Route::get('/{app?}', [IndexController::class, 'index'])->where('app', '.*');

