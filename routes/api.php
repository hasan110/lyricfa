<?php

use App\Http\Controllers\AlbumController;
use App\Http\Controllers\FilmController;
use App\Http\Controllers\FilmTextController;
use App\Http\Controllers\IdiomController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\MerchentZarinPalController;
use App\Http\Controllers\MusicController;
use App\Http\Controllers\PlayListController;
use App\Http\Controllers\PlayListMusicController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScoreMusicController;
use App\Http\Controllers\SingerController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TextController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserWordController;
use App\Http\Controllers\WordController;
use App\Http\Controllers\CommentMusicController;
use App\Http\Controllers\LikeMusicController;
use App\Http\Controllers\LikeSingerController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SmsVerifyController;
use App\Http\Controllers\UserSuggestionController;
use App\Http\Controllers\OrderMusicController;
use App\Http\Controllers\GrammerController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

//sms
Route::post('/check_activate_code', [SmsVerifyController::class, 'checkActivateCode']);// remove this webservice in next force update

Route::post('/send-verify-code', [SmsVerifyController::class, 'sendVerifyCode']);
Route::post('/check-verify-code', [SmsVerifyController::class, 'checkVerifyCode']);

//setting
Route::get('/get_setting', [SettingController::class, 'getSetting']);

Route::middleware('CheckApiAuthentication')->group(function () {

    Route::get('/get_user', [UserController::class, 'getUser']);
    Route::post('/save_fcm_refresh_token', [UserController::class, 'saveFcmRefreshTokenInServer']);
    Route::get('/get_home_page_data', [SettingController::class, 'getHomePageData']);

    //music
    Route::post('/music/list', [MusicController::class, 'getMusics']);
    Route::post('/get_music_all_info', [MusicController::class, 'getMusicCompleteInfo']);
    Route::post('/get_music_list', [MusicController::class, 'getMusicList']);
    Route::post('/get_n_music_list', [MusicController::class, 'getNMusicList']);
    Route::post('/get_last_music_list', [MusicController::class, 'getLastMusicList']);
    Route::post('/get_music_hardest', [MusicController::class, 'getMusicWithHardest']);
    Route::post('/get_n_last_music_list', [MusicController::class, 'getNLastMusicList']);
    Route::post('/add_view', [MusicController::class, 'addMusicViewOne'])->withoutMiddleware('throttle:api')->middleware('throttle:6,1');;
    Route::post('/get_singer_musics', [MusicController::class, 'getSingerMusics']);
    Route::post('/get_album_musics', [MusicController::class, 'getAlbumMusics']);
    Route::post('/get_singer_musics_no_paging', [MusicController::class, 'getSingerMusicsNoPaging']);
    Route::post('/get_requested_music', [MusicController::class, 'getRequestedMusic']);
    Route::post('/get_n_requested_music', [MusicController::class, 'getNRequestedMusic']);

    //films
    Route::post('/get_films', [FilmController::class, 'getList']);
    Route::post('/get_film_data', [FilmController::class, 'getData']);
    Route::post('/get_childs_by_id', [FilmController::class, 'getChildById']);

    //text film
    Route::post('/get_text_film', [FilmTextController::class, 'getTextList']);
    Route::post('/get_film_texts', [FilmTextController::class, 'getFilmTexts']);
    Route::post('/get_times', [FilmTextController::class, 'getTimesForPagin']);

    //comment music
    Route::post('/get_comment_music', [CommentMusicController::class, 'getMusicComment']);
    Route::post('/add_comment_music', [CommentMusicController::class, 'addMusicComment'])->withoutMiddleware('throttle:api')->middleware('throttle:6,1');;

    //lightener
    Route::post('/get_user_words', [UserWordController::class, 'getUserWordsById']);
    Route::post('/get_user_words_review', [UserWordController::class, 'getUserWordsReviews']);
    Route::post('/get_lightener_box_data', [UserWordController::class, 'getLightenerBoxData']);
    Route::post('/add_word_user', [UserWordController::class, 'insertUserWord'])->withoutMiddleware('throttle:api')->middleware('throttle:6,1');;
    Route::post('/edit_words_user', [UserWordController::class, 'editWordsUser']);
    Route::post('/remove_word_user', [UserWordController::class, 'removeWordUser']);

    //playlist
    Route::post('/insert_user_play_list', [PlayListController::class, 'insertUserPlayList'])->withoutMiddleware('throttle:api')->middleware('throttle:6,1');;
    Route::post('/get_user_play_list', [PlayListController::class, 'getUserPlayList']);
    Route::post('/remove_play_list_by_id', [PlayListController::class, 'removePlayListById']);
    Route::post('/edit_play_list_by_id', [PlayListController::class, 'editPlayListById']);
    Route::post('/insert_musics_user_play_list', [PlayListMusicController::class, 'insertMusicsPlayList']);
    Route::post('/get_user_playlist_musics', [PlayListMusicController::class, 'getMusicPlayListUser']);
    Route::post('/get_all_musics_playlist_musics', [PlayListMusicController::class, 'getAllMusicWithPlayList']);
    Route::post('/remove_music_from_playlist', [PlayListMusicController::class, 'removeMusicFromPlayList']);

    //singer
    Route::post('/get_singers', [SingerController::class, 'getSingersList']);
    Route::post('/get_n_singer', [SingerController::class, 'getNSingerList']);
    Route::post('/get_singer_by_id_complete', [SingerController::class, 'getSingerByIdWithLike']);

    //album
    Route::post('/get_albums', [AlbumController::class, 'getAlbumsList']);
    Route::post('/get_n_album', [AlbumController::class, 'getNAlbumList']);
    Route::post('/get_album_data', [AlbumController::class, 'getAlbumData']);

    //like music
    Route::post('/add_like_music', [LikeMusicController::class, 'addMusicLike']);
    Route::post('/remove_like_music', [LikeMusicController::class, 'removeMusicLike']);

    //like singer
    Route::post('/add_like_singer', [LikeSingerController::class, 'addSingerLike']);
    Route::post('/remove_like_singer', [LikeSingerController::class, 'removeSingerLike']);

    //text
    Route::post('/get_text_list', [TextController::class, 'getTextList']);
    Route::post('/get_text_music', [MusicController::class, 'getMusicWithTextPaginate']);

    //english persian words
    Route::post('/get_word', [WordController::class, 'getWord']);
    Route::post('/check_word', [WordController::class, 'checkWord']);

    //idioms
    Route::post('/get_word_idioms_by_rate', [IdiomController::class, 'getWordIdiomsByRate']);
    Route::post('/get_word_idioms', [IdiomController::class, 'getWordIdioms']);
    Route::post('/get_idiom_data', [IdiomController::class, 'getIdiomData']);

    //map
    Route::post('/get_base_word', [MapController::class, 'getBaseWord']);
    Route::post('/get_word_map_reasons', [MapController::class, 'getWordMapReasons']);

    //user suggestion
    Route::post('/insert_user_suggestion', [UserSuggestionController::class, 'insertUserSuggestion']);

    //order music
    Route::post('/add_order_music', [OrderMusicController::class, 'addOrderMusic'])->withoutMiddleware('throttle:api')->middleware('throttle:6,1');;

    //score
    Route::post('/get_user_score', [ScoreMusicController::class, 'getUserScore']);
    Route::post('/add_music_score', [ScoreMusicController::class, 'addMusicScore']);

    //zarin
    Route::post('/get_merchent_id', [MerchentZarinPalController::class, 'getMerchentId']);
    Route::post('/get_subscriptions', [SubscriptionController::class, 'getSubscriptions']);
    Route::post('/account_active', [ReportController::class, 'addPayReports']); // remove this webservice in next force update

    // slider
    Route::post('/get_slider_show', [SliderController::class, 'getSlidersForShow']);

    // grammer
    Route::get('/grammer_list', [GrammerController::class, 'grammerList']);
    Route::post('/get_grammer', [GrammerController::class, 'getGrammer']);
    Route::post('/get_grammer_prerequisites', [GrammerController::class, 'getGrammerPrerequisites']);
    Route::post('/find_grammer', [GrammerController::class, 'findGrammer'])->withoutMiddleware('throttle:api')->middleware('throttle:600,1');

    Route::post('/subscription/payment', [PaymentController::class, 'createSubscriptionPayment'])->withoutMiddleware('throttle:api')->middleware('throttle:6,1');

});
