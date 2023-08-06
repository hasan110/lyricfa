<?php

namespace App\Http\Controllers;

use App\Models\Word;
use App\Models\WordEnEn;
use App\Models\Idiom;

use App\Models\Map;
use Illuminate\Http\Request;
use function PHPUnit\Framework\isNull;

class WordController extends Controller
{
    function getAllWords(Request $request)
    {

        $words = Word::paginate(25);

        $arr = [
            'data' => $words,
            'errors' => [
            ],
            'message' => "اطلاعات با موفقیت گرفته شد"
        ];
        return response()->json($arr, 200);
    }


    function getWord(Request $request)
    {


        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        if ($user) {
            $word = $request->word;
            $comment_user = UserWordController::getCommentUser($word, $user->id );
            $get_word = Word::where('english_word', $word)->first();

            if (!$get_word) {
                $map = Map::where('word', $word)->first();

                if (!$map) {
                    $mainResponse = ['word' => $word, 'mean' => null, "english_mean" => null, "idioms" => null, "user_comment" => $comment_user];
                    $response[] = $mainResponse;
                } else {
                    $ciBase = $map->ci_base;
                    $get_base_word = Word::where('english_word', $ciBase)->first();
                    $get_english_word = WordEnEn::where('ci_word', $ciBase)->first();
                    $get_idioms = Idiom::where('base', $ciBase)->get();
                    $mainResponse = ['word' => $word, 'mean' => $get_base_word, "english_mean" => $get_english_word, "idioms" => $get_idioms, "user_comment" => $comment_user];
                }
            } else {
                $get_english_word = WordEnEn::where('ci_word', $word)->first();
                $get_idioms = Idiom::where('base', $word)->get();
                $mainResponse = ['word' => $word, 'mean' => $get_word, "english_mean" => $get_english_word, "idioms" => $get_idioms, "user_comment" => $comment_user];
            }


            if (!$mainResponse['mean'] && !$mainResponse['english_mean'] && !$mainResponse['idioms'] && $this->charIsBigLetter($word[0])) {

                $word = strtolower($word);
                $get_word = Word::where('english_word', $word)->first();

                if (!$get_word) {
                    $map = Map::where('word', $word)->first();

                    if (!$map) {
                        $mainResponse = ['word' => $word, 'mean' => null, "english_mean" => null, "idioms" => null, "user_comment" => $comment_user];
                        $response[] = $mainResponse;
                    } else {
                        $ciBase = $map->ci_base;
                        $get_base_word = Word::where('english_word', $ciBase)->first();
                        $get_english_word = WordEnEn::where('ci_word', $ciBase)->first();
                        $get_idioms = Idiom::where('base', $ciBase)->get();
                        $mainResponse = ['word' => $word, 'mean' => $get_base_word, "english_mean" => $get_english_word, "idioms" => $get_idioms, "user_comment" => $comment_user];
                    }
                } else {
                    $get_english_word = WordEnEn::where('ci_word', $word)->first();
                    $get_idioms = Idiom::where('base', $word)->get();
                    $mainResponse = ['word' => $word, 'mean' => $get_word, "english_mean" => $get_english_word, "idioms" => $get_idioms, "user_comment" => $comment_user];
                }

                $responseCheck = ['data' => $mainResponse, 'errors' => [], 'message' => "اطلاعات با موفقیت گرفته شد"];
                return response()->json($responseCheck, 200);
            } else {
                $responseCheck = ['data' => $mainResponse, 'errors' => [], 'message' => "اطلاعات با موفقیت گرفته شد"];
                return response()->json($responseCheck, 200);
            }

        } else {
            $arr = [
                'data' => null,
                'errors' => [
                ],
                'message' => "کاربر احراز هویت نشده است"
            ];
            return response()->json($arr, 401);
        }


    }


    public function charIsBigLetter($char)
    {
        if ($char >= chr(65) && $char <= chr(90)) {
            return true;
        }
        return false;
    }

    function getListWordMapEnEf(Request $request)
    {


        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        if ($user) {
            $request->validate([
                'words' => "required|array"
            ]);

            $arr = $request->words;
            $response = [];
            foreach ($arr as $item) {


                $get_word = Word::where('english_word', $item)->first();
                $comment_user = UserWordController::getCommentUser($item, $user->id );

                if (!$get_word) {
                    $map = Map::where('word', $item)->first();

                    if (!$map) {
                        $mainResponse = ['word' => $item, 'mean' => null, "english_mean" => null, "idioms" => null, "user_comment" => $comment_user];
                    } else {
                        $ciBase = $map->ci_base;
                        $get_base_word = Word::where('english_word', $ciBase)->first();
                        $get_english_word = WordEnEn::where('ci_word', $ciBase)->first();
                        $get_idioms = Idiom::where('base', $ciBase)->get();
                        $mainResponse = ['word' => $item, 'mean' => $get_base_word, "english_mean" => $get_english_word, "idioms" => $get_idioms, "user_comment" => $comment_user];
                    }
                } else {
                    $get_english_word = WordEnEn::where('ci_word', $item)->first();
                    $get_idioms = Idiom::where('base', $item)->get();
                    $mainResponse = ['word' => $item, 'mean' => $get_word, "english_mean" => $get_english_word, "idioms" => $get_idioms, "user_comment" => $comment_user];

                }

                if (!$mainResponse['mean'] && !$mainResponse['english_mean'] && !$mainResponse['idioms'] && $this->charIsBigLetter($item[0])) {
                    $item = strtolower($item);
                    $get_word = Word::where('english_word', $item)->first();

                    if (!$get_word) {
                        $map = Map::where('word', $item)->first();

                        if (!$map) {
                            $mainResponse = ['word' => $item, 'mean' => null, "english_mean" => null, "idioms" => null, "user_comment" => $comment_user];
                        } else {
                            $ciBase = $map->ci_base;
                            $get_base_word = Word::where('english_word', $ciBase)->first();
                            $get_english_word = WordEnEn::where('ci_word', $ciBase)->first();
                            $get_idioms = Idiom::where('base', $ciBase)->get();
                            $mainResponse = ['word' => $item, 'mean' => $get_base_word, "english_mean" => $get_english_word, "idioms" => $get_idioms, "user_comment" => $comment_user];
                        }
                    } else {
                        $get_english_word = WordEnEn::where('ci_word', $item)->first();
                        $get_idioms = Idiom::where('base', $item)->get();
                        $mainResponse = ['word' => $item, 'mean' => $get_word, "english_mean" => $get_english_word, "idioms" => $get_idioms, "user_comment" => $comment_user];

                    }
                    $response[] = $mainResponse;
                } else {
                    $response[] = $mainResponse;
                }

            }
            $responseCheck = ['data' => $response, 'errors' => [], 'message' => "اطلاعات با موفقیت گرفته شد"];
            return response()->json($responseCheck, 200);
        } else {
            $arr = [
                'data' => null,
                'errors' => [
                ],
                'message' => "کاربر احراز هویت نشده است"
            ];
            return response()->json($arr, 401);
        }


    }
}
