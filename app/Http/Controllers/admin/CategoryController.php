<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\File;
use App\Models\Film;
use App\Models\Grammer;
use App\Models\IdiomDefinition;
use App\Models\Music;
use App\Models\WordDefinition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function categoriesList(Request $request)
    {
        $search_key = $request->search_key;

        $categories = Category::orderBy('id', "DESC");

        if($request->input('sort_by')){
            switch ($request->input('sort_by')){
                case 'newest':
                default:
                    $categories = $categories->orderBy('id' , 'DESC');
                    break;
                case 'oldest':
                    $categories = $categories->orderBy('id' , 'ASC');
                    break;
            }
        }

        if ($request->mode) {
            $categories = $categories->where('mode', $request->mode);
        } else {
            $categories = $categories->where(function ($query) {
                $query->where('mode', 'label')
                    ->orWhereNull('parent_id');
            });
        }

        if ($request->belongs_to) {
            if (in_array($request->belongs_to , ['word_definitions' , 'idiom_definitions'])) {
                $belongs_to = ['word_definitions' , 'idiom_definitions'];
            } else {
                $belongs_to = [$request->belongs_to];
            }
            $categories = $categories->where(function ($query) use ($belongs_to) {
                $query->where('mode', 'label')
                    ->orWhereIn('belongs_to', $belongs_to);
            });
        }

        if ($request->except) {
            $categories = $categories->where('id', '!=', $request->except);
        }

        if ($request->parent_id) {
            $categories = $categories->where('parent_id', $request->parent_id);
        }

        if ($search_key) {
            $categories = $categories->where(function ($query) use ($search_key) {
                $query->where('title', 'like', '%' . $search_key . '%')
                    ->orWhere('subtitle', 'like', '%' . $search_key . '%')
                    ->orWhere('description', 'like', '%' . $search_key . '%')
                    ->orWhere('id', '=', $search_key);
            });
        }

        if (intval($request->get_all) === 1) {
            $categories = $categories->with(['children' => function ($query) {
                $query->with(['children' => function ($query) {
                    $query->with(['children' => function ($query) {
                        $query->with(['children']);
                    }]);
                }]);
            }]);
            $categories = $categories->get();
        } else {
            $categories = $categories->paginate(50);
        }

        return response()->json([
            'data' => $categories,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public static function getCategory(Request $request)
    {
        $data = Category::with('parent')->find($request->input('id'));
        if (!$data) {
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "شناسه نامعتبر است",
            ], 400);
        }
        return response()->json([
            'data' => $data,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function categoryCreate(Request $request)
    {
        $messages = array(
            'title.required' => 'عنوان اجباری است',
            'subtitle.required' => 'عنوان اجباری است',
            'mode.required' => 'نوع دسته بندی اجباری است.',
            'description.required' => 'توضیحات اجباری است',
            'color.filled' => 'رنگ باید انتخاب شود',
            'belongs_to.required' => 'موضوع دسته بندی نمیتواند خالی باشد',
            'belongs_to.in' => 'موضوع دسته بندی باید یکی از موارد معتبر باشد',
            'permission_type.required' => 'نوع دسترسی نمیتواند خالی باشد',
            'permission_type.in' => 'نوع دسترسی باید یکی از موارد: paid, free باشد',
            'type.numeric' => 'نوع فیلم عدد می باشد',
            'parent.numeric' => 'فیلم مرتبط باید عدد باشد',
            'film.file' => 'نوع فایل آپلودی باید فایل باشد',
            'film.mimetypes' => 'نوع فایل فیلم باید ویدئو باشد',
            'poster.required' => 'پوستر باید انتخاب شود',
            'poster.file' => 'نوع پوستر باید فایل باشد',
            'poster.mimes' => 'نوع پوستر باید png,jpg باشد',
            'poster.dimensions' => 'پوستر باید 200 در 200 باشد',
            'priority.required' => 'اولویت الزامی است',
            'priority.numeric' => 'اولویت باید عدد باشد',
            'film_source_upload_path.required_if' => 'مسیر آپلود فیلم الزامی است',
        );

        if ($request->input('mode') == 'category') {
            $rules = [
                'title' => 'required',
                'subtitle' => 'required',
                'description' => 'required',
                'belongs_to' => 'required|in:grammers,musics,films,word_definitions,idiom_definitions',
                'priority' => 'required|numeric',
                'poster' => 'required|mimes:jpg,png|dimensions:min_width=200,min_height=200,max_width=200,max_height=200',
                'permission_type' => 'required|in:paid,free',
            ];
        } else {
            $rules = [
                'title' => 'required',
                'subtitle' => 'required',
                'mode' => 'required|in:category,label',
                'description' => 'required',
            ];
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن دسته بندی شکست خورد",
            ], 400);
        }

        $category = new Category();
        $category->title = $request->title;
        $category->subtitle = $request->subtitle;
        $category->mode = $request->mode;
        $category->color = $request->color ?? null;
        $category->description = $request->description;
        $category->status = 1;

        if ($request->input('mode') == 'category') {
            $category->parent_id = $request->parent_id ? intval($request->parent_id) : null;
            $category->is_parent = intval($request->is_parent) ? 1 : 0;
            $category->is_public = intval($request->is_public) ? 1 : 0;
            $category->need_level = intval($request->need_level) ? 1 : 0;
            $category->status = intval($request->status) ? 1 : 0;
            $category->priority = intval($request->priority);
            $category->permission_type = $request->permission_type;
            $category->belongs_to = $request->belongs_to;
            $category->save();

            if ($request->hasFile('poster')) {
                File::createFile($request->poster, $category, Category::POSTER_FILE_TYPE , true);
            }
        } else {
            $category->save();
        }

        return response()->json([
            'data' => $category,
            'errors' => null,
            'message' => "دسته بندی با موفقیت اضافه شد"
        ]);
    }

    public function categoryUpdate(Request $request)
    {
        $category = Category::find($request->input('id'));
        if (!$category) {
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "شناسه نامعتبر است",
            ], 400);
        }
        $messages = array(
            'title.required' => 'عنوان اجباری است',
            'subtitle.required' => 'عنوان اجباری است',
            'description.required' => 'توضیحات اجباری است',
            'color.filled' => 'رنگ باید انتخاب شود',
            'permission_type.required' => 'نوع دسترسی نمیتواند خالی باشد',
            'permission_type.in' => 'نوع دسترسی باید یکی از موارد: paid, free باشد',
            'type.numeric' => 'نوع فیلم عدد می باشد',
            'parent.numeric' => 'فیلم مرتبط باید عدد باشد',
            'film.file' => 'نوع فایل آپلودی باید فایل باشد',
            'film.mimetypes' => 'نوع فایل فیلم باید ویدئو باشد',
            'poster.required' => 'پوستر باید انتخاب شود',
            'poster.file' => 'نوع پوستر باید فایل باشد',
            'poster.mimes' => 'نوع پوستر باید png,jpg باشد',
            'poster.dimensions' => 'پوستر باید 200 در 200 باشد',
            'priority.required' => 'اولویت الزامی است',
            'priority.numeric' => 'اولویت باید عدد باشد',
            'film_source_upload_path.required_if' => 'مسیر آپلود فیلم الزامی است',
        );

        if ($category->mode == 'category') {
            $rules = [
                'title' => 'required',
                'subtitle' => 'required',
                'description' => 'required',
                'priority' => 'required|numeric',
                'poster' => 'mimes:jpg,png|dimensions:min_width=200,min_height=200,max_width=200,max_height=200',
                'permission_type' => 'required|in:paid,free',
            ];
        } else {
            $rules = [
                'title' => 'required',
                'subtitle' => 'required',
                'description' => 'required',
            ];
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "ویرایش دسته بندی شکست خورد",
            ], 400);
        }

        $category->title = $request->title;
        $category->subtitle = $request->subtitle;
        $category->color = $request->color ?? null;
        $category->description = $request->description;
        $category->status = 1;

        if ($category->mode == 'category') {
            $category->parent_id = $request->parent_id ? intval($request->parent_id) : null;
            $category->is_parent = intval($request->is_parent) ? 1 : 0;
            $category->is_public = intval($request->is_public) ? 1 : 0;
            $category->need_level = intval($request->need_level) ? 1 : 0;
            $category->status = intval($request->status) ? 1 : 0;
            $category->priority = intval($request->priority);
            $category->permission_type = $request->permission_type;
            $category->save();

            if ($request->hasFile('poster')) {
                File::deleteFile($category->files, Category::POSTER_FILE_TYPE);
                File::createFile($request->poster, $category, Category::POSTER_FILE_TYPE , true);
            }
        } else {
            $category->save();
        }

        return response()->json([
            'data' => $category,
            'errors' => null,
            'message' => "دسته بندی با موفقیت ویرایش شد"
        ]);
    }

    public function categoryItems(Request $request)
    {
        $category = Category::find($request->input('category_id'));
        if (!$category) {
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "شناسه نامعتبر است",
            ], 400);
        }

        $grammers = $category->grammers()->limit(200)->get()->map(function ($item) {
            return [
                'title' => Str::limit($item->english_name, 50),
                'subtitle' => Str::limit($item->persian_name, 50),
                'type' => 'grammer',
                'level' => $item->level,
                'related_id' => $item->id,
                'category_item_poster' => null
            ];
        });
        $musics = $category->musics()->limit(200)->get()->map(function ($item) {
            return [
                'title' => Str::limit($item->name, 50),
                'subtitle' => Str::limit($item->persian_name, 50),
                'type' => 'music',
                'level' => $item->level,
                'related_id' => $item->id,
                'category_item_poster' => $item->music_poster
            ];
        });
        $films = $category->films()->limit(200)->get()->map(function ($item) {
            return [
                'title' => Str::limit($item->english_name, 50),
                'subtitle' => Str::limit($item->persian_name, 50),
                'type' => 'film',
                'level' => $item->level,
                'related_id' => $item->id,
                'category_item_poster' => $item->film_poster
            ];
        });
        $word_definitions = $category->word_definitions()->limit(200)->get()->map(function ($item) {
            return [
                'title' => Str::limit($item->definition, 50),
                'subtitle' => Str::limit($item->word->english_word, 50),
                'type' => 'word_definition',
                'level' => $item->level,
                'related_id' => $item->word->id,
                'category_item_poster' => $item->word_definition_image
            ];
        });
        $idiom_definitions = $category->idiom_definitions()->limit(200)->get()->map(function ($item) {
            return [
                'title' => Str::limit($item->definition, 50),
                'subtitle' => Str::limit($item->idiom->phrase, 50),
                'type' => 'idiom_definition',
                'level' => $item->level,
                'related_id' => $item->idiom->id,
                'category_item_poster' => $item->idiom_definition_image
            ];
        });

        $list = collect()->merge($grammers)->merge($musics)->merge($films)->merge($word_definitions)->merge($idiom_definitions);

        if ($request->input('sort_by') === 'has_level') {
            $list = $list->whereNotNull('level');
        }
        if ($request->input('sort_by') === 'not_has_level') {
            $list = $list->whereNull('level');
        }

        return response()->json([
            'data' => $list,
            'errors' => null,
            'message' => "آیتم های موجود در دسته بندی دریافت شد"
        ]);
    }

    public function categorySync(Request $request)
    {
        $messages = array(
            'selected_categories.array' => 'دسته بندی های انتخاب شده باید آرایه باشند',
            'categorizeable_id.required' => 'شناسه دسته بندی شونده اجباری است',
            'categorizeable_type.required' => 'نوع دسته بندی شونده اجباری است',
        );

        $validator = Validator::make($request->all(), [
            'selected_categories' => 'array',
            'categorizeable_id' => 'required',
            'categorizeable_type' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => [],
                'errors' => $validator->errors(),
                'message' => "ناموفق",
            ]);
        }

        $type = $request->input('categorizeable_type');
        $id = $request->input('categorizeable_id');

        $model = $this->getCategorizeable($id, $type);
        if (!$model) {
            return response()->json([
                'data' => [],
                'errors' => [],
                'message' => "ناموفق",
            ]);
        }

        $model->categories()->sync($request->input('selected_categories'));

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => "دسته بندی های انتخاب شده دریافت شد"
        ]);
    }

    public function categoryAddGroup(Request $request)
    {
        $messages = array(
            'category_type.required' => 'نوع مدل باید انتخاب شده باشد',
            'category_list_ids.required' => 'شناسه های مورد نظر را وارد کنید',
            'category_id.required' => 'شناسه دسته بندی اجباری است',
        );

        $validator = Validator::make($request->all(), [
            'category_type' => 'required',
            'category_list_ids' => 'required',
            'category_id' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => [],
                'errors' => $validator->errors(),
                'message' => "ناموفق",
            ] , 400);
        }
        $id_list = [];
        foreach (preg_split("/\r\n|\n|\r/", $request->input('category_list_ids')) as $item) {
            if ($item && is_numeric($item) && !in_array($item, $id_list)) {
                $id_list[] = intval($item);
            }
        }
        if (count($id_list) > 300) {
            return response()->json([
                'data' => [],
                'errors' => [
                    'category_list_ids'=>['نمی توانید در هر درخواست بیشتر از 300 شناسه اضافه کنید']
                ],
                'message' => 'نمی توانید در هر درخواست بیشتر از 300 شناسه اضافه کنید',
            ], 400);
        }
        $type = $request->input('category_type');
        switch ($type) {
            case 'grammers':
                $model = new Grammer;
                break;
            case 'musics':
                $model = new Music;
                break;
            case 'films':
                $model = new Film;
                break;
            case 'word_definitions':
                $model = new WordDefinition;
                break;
            case 'idiom_definitions':
                $model = new IdiomDefinition;
                break;
            default:
                return response()->json([
                    'data' => [],
                    'errors' => [
                        'category_list_ids'=>['نوع مدل نامعتبر است']
                    ],
                    'message' => "نوع مدل نامعتبر است",
                ], 400);
        }

        $category = Category::find($request->input('category_id'));

        if (!$category) {
            return response()->json([
                'data' => [],
                'errors' => [],
                'message' => "دسته بندی یافت نشد",
            ], 400);
        }

        $count = $model->whereIn('id', $id_list)->count();
        if (count($id_list) !== $count) {
            return response()->json([
                'data' => [],
                'errors' => [
                    'category_list_ids'=>['یک یا چند مورد از شناسه ها نامعتبر هستند']
                ],
                'message' => "یک یا چند مورد از شناسه ها نامعتبر هستند",
            ], 400);
        }

        $model->categories()->sync($request->input('selected_categories'));
        $list = $model->with('categories')->whereIn('id', $id_list)->get();
        foreach ($list as $item) {
            $categories_ids = $item->categories->pluck('id')->toArray();
            $categories_ids[] = $category->id;
            $item->categories()->sync($categories_ids);
        }

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => "موارد انتخاب شده با موفقیت به دسته بندی اضافه شدند"
        ]);
    }

    private function getCategorizeable($id, $type)
    {
        switch ($type) {
            case 'grammers':
                $model = Grammer::find($id);
                break;
            case 'musics':
                $model = Music::find($id);
                break;
            case 'films':
                $model = Film::find($id);
                break;
            case 'word_definitions':
                $model = WordDefinition::find($id);
                break;
            case 'idiom_definitions':
                $model = IdiomDefinition::find($id);
                break;
            default:
                return null;
        }

        return $model;
    }
}
