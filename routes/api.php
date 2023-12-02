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
use App\Http\Controllers\WordEnEnController;
use App\Http\Controllers\CommentMusicController;
use App\Http\Controllers\CommentSingerController;
use App\Http\Controllers\LikeMusicController;
use App\Http\Controllers\LikeSingerController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SmsVerifyController;
use App\Http\Controllers\UserSuggestionController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\OrderMusicController;
use Illuminate\Support\Facades\Route;

//panel
Route::get('/get_texts_bigger_id', [TextController::class, 'check10LastRowIsNull']);
Route::get('/show_profit_banner', [PositionController::class, 'showProfitBanner']);

//sms
Route::post('/send_sms', [SmsVerifyController::class, 'sendSms']);
Route::post('/check_activate_code', [SmsVerifyController::class, 'checkActivateCode']);

Route::post('/send-verify-code', [SmsVerifyController::class, 'sendVerifyCode']);
Route::post('/check-verify-code', [SmsVerifyController::class, 'checkVerifyCode']);

//setting
Route::get('/get_setting', [SettingController::class, 'getSetting']);

Route::middleware('CheckApiAuthentication')->group(function () {


    Route::get('/get_user', [UserController::class, 'getUser']);
    Route::post('/add_rewards', [UserController::class, 'addRewardsByUser']);
   Route::post('/save_fcm_refresh_token', [UserController::class, 'saveFcmRefreshTokenInServer']);


    //music
    Route::post('/get_music_all_info', [MusicController::class, 'getMusicCompleteInfo']);
    Route::post('/get_music_list', [MusicController::class, 'getMusicList']);
    Route::post('/search_singers_music', [MusicController::class, 'getMusicSingersSearchList']);
    Route::post('/get_music_video_list', [MusicController::class, 'getMusicVideoList']);
    Route::post('/get_n_music_list', [MusicController::class, 'getNMusicList']);
    Route::post('/get_last_music_list', [MusicController::class, 'getLastMusicList']);
    Route::post('/get_music_hardest', [MusicController::class, 'getMusicWithHardest']);
    Route::post('/get_n_last_music_list', [MusicController::class, 'getNLastMusicList']);
    Route::post('/add_view', [MusicController::class, 'addMusicViewOne']);
    Route::post('/get_singer_musics', [MusicController::class, 'getSingerMusics']);
    Route::post('/get_album_musics', [MusicController::class, 'getAlbumMusics']);
    Route::post('/get_sum_musics', [MusicController::class, 'getSumMusics']);
    Route::post('/get_singer_musics_no_paging', [MusicController::class, 'getSingerMusicsNoPaging']);
    Route::post('/get_requested_music', [MusicController::class, 'getRequestedMusic']);
    Route::post('/get_n_requested_music', [MusicController::class, 'getNRequestedMusic']);


    //films
    Route::post('/get_films', [FilmController::class, 'getListForShow']);
    Route::post('/get_childs_by_id', [FilmController::class, 'getChildById']);



    //comment music
    Route::post('/get_comment_music', [CommentMusicController::class, 'getMusicComment']);
    Route::post('/add_comment_music', [CommentMusicController::class, 'addMusicComment']);
    Route::post('/edit_comment_music', [CommentMusicController::class, 'editMusicComment']);
    Route::post('/remove_comment_music', [CommentMusicController::class, 'removeMusicComment']);


    //comment singer
    Route::post('/get_comment_singer', [CommentSingerController::class, 'getSingerComment']);
    Route::post('/add_comment_singer', [CommentSingerController::class, 'addSingerComment']);
    Route::post('/edit_comment_singer', [CommentSingerController::class, 'editSingerComment']);
    Route::post('/remove_comment_singer', [CommentSingerController::class, 'removeSingerComment']);


    //lightener
    Route::post('/get_user_words', [UserWordController::class, 'getUserWordsById']);
    Route::post('/get_user_words_review', [UserWordController::class, 'getUserWordsReviews']);
    Route::post('/get_lightener_box_data', [UserWordController::class, 'getLightenerBoxData']);
    Route::post('/add_word_user', [UserWordController::class, 'insertUserWord']);
    Route::post('/edit_words_user', [UserWordController::class, 'editWordsUser']);

    Route::post('/remove_word_user', [UserWordController::class, 'removeWordUser']);

    //playlist
    Route::post('/insert_user_play_list', [PlayListController::class, 'insertUserPlayList']);
    Route::post('/get_user_play_list', [PlayListController::class, 'getUserPlayList']);
    Route::post('/insert_music_user_play_list', [PlayListMusicController::class, 'insertMusicPlayList']);
    Route::post('/insert_musics_user_play_list', [PlayListMusicController::class, 'insertMusicsPlayList']);
    Route::post('/get_user_playlist_musics', [PlayListMusicController::class, 'getMusicPlayListUser']);
    Route::post('/get_all_musics_playlist_musics', [PlayListMusicController::class, 'getAllMusicWithPlayList']);
    Route::post('/remove_play_list_by_id', [PlayListController::class, 'removePlayListById']);
    Route::post('/edit_play_list_by_id', [PlayListController::class, 'editPlayListById']);

    Route::post('/remove_music_from_playlist', [PlayListMusicController::class, 'removeMusicFromPlayList']);

    //singer
    Route::post('/get_singer_by_id', [SingerController::class, 'getSingerById']);
    Route::post('/get_singers', [SingerController::class, 'getSingersList']);
    Route::post('/get_n_singer', [SingerController::class, 'getNSingerList']);


    //album
    Route::post('/get_album_by_id', [AlbumController::class, 'getAlbumById']);
    Route::post('/get_albums', [AlbumController::class, 'getAlbumsList']);
    Route::post('/get_n_album', [AlbumController::class, 'getNAlbumList']);


    //like music
    Route::post('/add_like_music', [LikeMusicController::class, 'addMusicLike']);
    Route::post('/remove_like_music', [LikeMusicController::class, 'removeMusicLike']);

    //like singer
    Route::post('/add_like_singer', [LikeSingerController::class, 'addSingerLike']);
    Route::post('/remove_like_singer', [LikeSingerController::class, 'removeSingerLike']);


    //text
    Route::post('/get_text_list', [TextController::class, 'getTextList']);
    Route::post('/get_text_include_word', [TextController::class, 'getTextIncludeWord']);
    Route::post('/get_text_music', [MusicController::class, 'getMusicWithTextPaginate']);


    //text film
    Route::post('/get_text_film', [FilmTextController::class, 'getTextList']);
    Route::post('/get_times', [FilmTextController::class, 'getTimesForPagin']);

    //english persian words
    Route::post('/get_all_words', [WordController::class, 'getAllWords']);
    Route::post('/get_word', [WordController::class, 'getWord']);
    Route::post('/get_word_or_base', [WordController::class, 'getListWordMapEnEf']);
    Route::post('/check_word', [WordController::class, 'checkWord']);


    //english english words
    Route::post('/get_english_all_words', [WordEnEnController::class, 'getAllWords']);
    Route::post('/get_english_word', [WordEnEnController::class, 'getWord']);

    //idioms
    Route::post('/get_idioms_word', [IdiomController::class, 'getIdiomsWord']);
    Route::post('/search_in_idioms', [IdiomController::class, 'searchIdiom']);
    Route::post('/get_word_idioms_by_rate', [IdiomController::class, 'getWordIdiomsByRate']);
    Route::post('/get_word_idioms', [IdiomController::class, 'getWordIdioms']);
    Route::post('/get_idiom_data', [IdiomController::class, 'getIdiomData']);
    Route::post('/get_text_idioms', [TextIdiomsController::class, 'getTextIdioms']);

    //map
    Route::post('/get_base_word', [MapController::class, 'getBaseWord']);


    //user suggestion
    Route::post('/insert_user_suggestion', [UserSuggestionController::class, 'insertUserSuggestion']);

    //order music
    Route::post('/add_order_music', [OrderMusicController::class, 'addOrderMusic']);


    //singer
    Route::post('/get_singer_by_id_complete', [SingerController::class, 'getSingerByIdWithLike']);


    //score
    Route::post('/get_average_music_score', [ScoreMusicController::class, 'getAverageMusicScore']);
    Route::post('/get_user_score', [ScoreMusicController::class, 'getUserScore']);
    Route::post('/add_music_score', [ScoreMusicController::class, 'addMusicScore']);




    //zarin
    Route::post('/get_merchent_id', [MerchentZarinPalController::class, 'getMerchentId']);
    Route::post('/get_subscriptions', [SubscriptionController::class, 'getSubscriptions']);
    Route::post('/account_active', [ReportController::class, 'addPayReports']);


    // slider
    Route::post('/get_slider_show', [SliderController::class, 'getSlidersForShow']);


});
