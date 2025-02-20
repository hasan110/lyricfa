<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    use HasFactory;
    protected $guarded = [];

    public const TYPES = [
        'synonym' => 'مترادف',
        'opposite' => 'متضاد',
        'homophone' => 'هم آوا',
    ];

    public static function get_links($id, $type)
    {
        $final_list = [];
        $list = [];
        $links_one = Link::where('link_from_type', $type)->where('link_from_id', $id)->get();
        foreach ($links_one as $link) {
            $data = [];
            if ($link->link_to_type === 'word_definition') {
                $word_definition = WordDefinition::with('word')->find($link->link_to_id);
                if ($word_definition && $word_definition->word) {
                    $data = [
                        'id' => $word_definition->id,
                        'link_id' => $link->id,
                        'type' => 'word_definition',
                        'text' => $word_definition->word->english_word,
                    ];
                }
            }
            if ($link->link_to_type === 'idiom_definition') {
                $idiom_definition = IdiomDefinition::with('idiom')->find($link->link_to_id);
                if ($idiom_definition && $idiom_definition->idiom) {
                    $data = [
                        'id' => $idiom_definition->id,
                        'link_id' => $link->id,
                        'type' => 'idiom_definition',
                        'text' => $idiom_definition->idiom->phrase,
                    ];
                }
            }
            if (!empty($data)) {
                $list[$link->type][] = $data;
            }
        }
        $links_two = Link::where('link_to_type', $type)->where('link_to_id', $id)->get();
        foreach ($links_two as $link) {
            $data = [];
            if ($link->link_from_type === 'word_definition') {
                $word_definition = WordDefinition::with('word')->find($link->link_from_id);
                if ($word_definition && $word_definition->word) {
                    $data = [
                        'id' => $word_definition->id,
                        'link_id' => $link->id,
                        'type' => 'word_definition',
                        'text' => $word_definition->word->english_word,
                    ];
                }
            }
            if ($link->link_from_type === 'idiom_definition') {
                $idiom_definition = IdiomDefinition::with('idiom')->find($link->link_from_id);
                if ($idiom_definition && $idiom_definition->idiom) {
                    $data = [
                        'id' => $idiom_definition->id,
                        'link_id' => $link->id,
                        'type' => 'idiom_definition',
                        'text' => $idiom_definition->idiom->phrase,
                    ];
                }
            }
            if (!empty($data)) {
                $list[$link->type][] = $data;
            }
        }

        foreach ($list as $type => $list_data) {
            $final_list[] = [
                'type' => $type,
                'title' => self::TYPES[$type],
                'list' => $list_data,
            ];
        }

        return $final_list;
    }
}
