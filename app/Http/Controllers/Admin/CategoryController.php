<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Category;
use App\Model\MainCategory;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use App\Model\Translation;

class CategoryController extends Controller
{
    function index()
    {
        $categories=Category::where(['position'=>0])->latest()->paginate(10);
        return view('admin-views.category.index',compact('categories'));
    }
    function indexMain()
    {
        $categories=MainCategory::where(['position'=>0])->latest()->paginate(10);
        return view('admin-views.category.main-category.index',compact('categories'));
    }

    function sub_index()
    {
        $categories=Category::with(['parent'])->where(['position'=>1])->latest()->paginate(10);
        return view('admin-views.category.sub-index',compact('categories'));
    }

    public function search(Request $request){
        $key = explode(' ', $request['search']);
        $categories=Category::where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('name', 'like', "%{$value}%");
            }
        })->get();
        return response()->json([
            'view'=>view('admin-views.category.partials._table',compact('categories'))->render()
        ]);
    }

    function sub_sub_index()
    {
        return view('admin-views.category.sub-sub-index');
    }

    function sub_category_index()
    {
        return view('admin-views.category.index');
    }

    function sub_sub_category_index()
    {
        return view('admin-views.category.index');
    }

    function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ], [
            'name.required' => 'Name is required!',
        ]);

        if (!empty($request->file('image'))) {
            $image_name =  Helpers::upload('category/', 'png', $request->file('image'));
        } else {
            $image_name = 'def.png';
        }

        $category = new Category();
        $category->name = $request->name[array_search('en', $request->lang)];
        $category->image = $image_name;
        $category->parent_id = $request->parent_id == null ? 0 : $request->parent_id;
        $category->position = $request->position;
        $category->save();

        $data = [];
        foreach($request->lang as $index=>$key)
        {
            if($request->name[$index] && $key != 'en')
            {
                array_push($data, Array(
                    'translationable_type'  => 'App\Model\Category',
                    'translationable_id'    => $category->id,
                    'locale'                => $key,
                    'key'                   => 'name',
                    'value'                 => $request->name[$index],
                ));
            }
        }
        if(count($data))
        {
            Translation::insert($data);
        }

        return back();
    }

    function storeMain(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ], [
            'name.required' => 'Name is required!',
        ]);

        if (!empty($request->file('image'))) {
            $image_name =  Helpers::upload('category/', 'png', $request->file('image'));
        } else {
            $image_name = 'def.png';
        }

        $category = new MainCategory();
        $category->name = $request->name[array_search('en', $request->lang)];
        $category->image = $image_name;
        $category->parent_id = $request->parent_id == null ? 0 : $request->parent_id;
        $category->position = $request->position;
        $category->save();

        $data = [];
        foreach($request->lang as $index=>$key)
        {
            if($request->name[$index] && $key != 'en')
            {
                array_push($data, Array(
                    'translationable_type'  => 'App\Model\MainCategory',
                    'translationable_id'    => $category->id,
                    'locale'                => $key,
                    'key'                   => 'name',
                    'value'                 => $request->name[$index],
                ));
            }
        }
        if(count($data))
        {
            Translation::insert($data);
        }

        return back();
    }

    public function edit($id)
    {
        $category = category::withoutGlobalScopes()->with('translations')->find($id);
        return view('admin-views.category.edit', compact('category'));
    }

    public function editMain($id)
    {
        $category = MainCategory::withoutGlobalScopes()->with('translations')->find($id);
        return view('admin-views.category.main-category.edit', compact('category'));
    }

    public function status(Request $request)
    {
        $category = category::find($request->id);
        $category->status = $request->status;
        $category->save();
        Toastr::success('Category status updated!');
        return back();
    }

    public function statusMain(Request $request)
    {
        $category = MainCategory::find($request->id);
        $category->status = $request->status;
        $category->save();
        Toastr::success('Main Category status updated!');
        return back();
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
        ], [
            'name.required' => 'Name is required!',
        ]);
        $category = category::find($id);
        $category->name = $request->name[array_search('en', $request->lang)];
        $category->image = $request->has('image') ? Helpers::update('category/', $category->image, 'png', $request->file('image')) : $category->image;
        $category->save();
        foreach($request->lang as $index=>$key)
        {
            if($request->name[$index] && $key != 'en')
            {
                Translation::updateOrInsert(
                    ['translationable_type'  => 'App\Model\Category',
                    'translationable_id'    => $category->id,
                    'locale'                => $key,
                    'key'                   => 'name'],
                    ['value'                 => $request->name[$index]]
                );
            }
        }
        Toastr::success('Category updated successfully!');
        return back();
    }

    public function updateMain(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
        ], [
            'name.required' => 'Name is required!',
        ]);
        $category = MainCategory::find($id);
        $category->name = $request->name[array_search('en', $request->lang)];
        $category->image = $request->has('image') ? Helpers::update('category/', $category->image, 'png', $request->file('image')) : $category->image;
        $category->save();
        foreach($request->lang as $index=>$key)
        {
            if($request->name[$index] && $key != 'en')
            {
                Translation::updateOrInsert(
                    ['translationable_type'  => 'App\Model\MainCategory',
                    'translationable_id'    => $category->id,
                    'locale'                => $key,
                    'key'                   => 'name'],
                    ['value'                 => $request->name[$index]]
                );
            }
        }
        Toastr::success('Main Category updated successfully!');
        return back();
    }

    public function delete(Request $request)
    {
        $category = category::find($request->id);
        Helpers::delete('category/' . $category['image']);
        if ($category->childes->count()==0){
            $category->delete();
            Toastr::success('Category removed!');
        }else{
            Toastr::warning('Remove subcategories first!');
        }
        return back();
    }

    public function deleteMain(Request $request)
    {
        $category = MainCategory::find($request->id);
        Helpers::delete('category/' . $category['image']);
        if ($category->childes->count()==0){
            $category->delete();
            Toastr::success('Category removed!');
        }else{
            Toastr::warning('Remove subcategories first!');
        }
        return back();
    }
}
