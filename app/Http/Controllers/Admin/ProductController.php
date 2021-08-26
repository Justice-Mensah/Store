<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Category;
use App\Model\Product;
use App\Model\Review;
use App\Model\Translation;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function variant_combination(Request $request)
    {
        $options = [];
        $price = $request->price;
        $product_name = $request->name;

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_' . $no;
                $my_str = implode('', $request[$name]);
                array_push($options, explode(',', $my_str));
            }
        }

        $result = [[]];
        foreach ($options as $property => $property_values) {
            $tmp = [];
            foreach ($result as $result_item) {
                foreach ($property_values as $property_value) {
                    $tmp[] = array_merge($result_item, [$property => $property_value]);
                }
            }
            $result = $tmp;
        }
        $combinations = $result;
        return response()->json([
            'view' => view('admin-views.product.partials._variant-combinations', compact('combinations', 'price', 'product_name'))->render(),
        ]);
    }

    public function get_categories(Request $request)
    {
        $cat = Category::where(['parent_id' => $request->parent_id])->get();
        $res = '<option value="' . 0 . '" disabled selected>---Select---</option>';
        foreach ($cat as $row) {
            if ($row->id == $request->sub_category) {
                $res .= '<option value="' . $row->id . '" selected >' . $row->name . '</option>';
            } else {
                $res .= '<option value="' . $row->id . '">' . $row->name . '</option>';
            }
        }
        return response()->json([
            'options' => $res,
        ]);
    }

    public function index()
    {
        $categories = Category::where(['position' => 0])->get();
        return view('admin-views.product.index', compact('categories'));
    }

    public function list()
    {
        $products = Product::latest()->paginate(10);
        return view('admin-views.product.list', compact('products'));
    }

    public function search(Request $request)
    {
        $key = explode(' ', $request['search']);
        $products = Product::where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('name', 'like', "%{$value}%");
            }
        })->get();
        return response()->json([
            'view' => view('admin-views.product.partials._table', compact('products'))->render()
        ]);
    }

    public function view($id)
    {
        $product = Product::where(['id' => $id])->first();
        $reviews = Review::where(['product_id' => $id])->latest()->paginate(20);
        return view('admin-views.product.view', compact('product', 'reviews'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:products',
            'category_id' => 'required',
            'main_category_id' => 'required',
            'images' => 'required',
            'total_stock' => 'required|numeric|min:1',
            'price' => 'required|numeric|min:1',
        ], [
            'name.required' => 'Product name is required!',
            'category_id.required' => 'category  is required!',
            'main_category_id.required' => 'Main category  is required!',
        ]);



        if($request['discount'] == null){
            $request['discount'] = 0.00;
        }
        if($request['discount_type'] == null){
            $request['discount_type'] = 'percent';
        }

        if ($request['discount_type'] == 'percent') {
            $dis = ($request['price'] / 100) * $request['discount'];
        } else {
            $dis = $request['discount'];
        }

        if ($request['price'] <= $dis) {
            $validator->getMessageBag()->add('unit_price', 'Discount can not be more or equal to the price!');
        }


        $img_names = [];
        if (!empty($request->file('images'))) {
            foreach ($request->images as $img) {
                $image_data = Helpers::upload('product/', 'png', $img);
                array_push($img_names, $image_data);
            }
            $image_data = json_encode($img_names);
        } else {
            $image_data = json_encode([]);
        }

        $p = new Product;
        $p->name = $request->name[array_search('en', $request->lang)];

        $category = [];
        if ($request->category_id != null) {
            array_push($category, [
                'id' => $request->category_id,
                'position' => 1,
            ]);
        }
        if ($request->sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_category_id,
                'position' => 2,
            ]);
        }
        if ($request->sub_sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_sub_category_id,
                'position' => 3,
            ]);
        }

        $p->category_ids = json_encode($category);
        $p->description = $request->description[array_search('en', $request->lang)];

        $choice_options = [];
        if ($request->has('choice')) {
            foreach ($request->choice_no as $key => $no) {
                $str = 'choice_options_' . $no;
                if ($request[$str][0] == null) {
                    $validator->getMessageBag()->add('name', 'Attribute choice option values can not be null!');
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                $item['name'] = 'choice_' . $no;
                $item['title'] = $request->choice[$key];
                $item['options'] = explode(',', implode('|', preg_replace('/\s+/', ' ', $request[$str])));
                array_push($choice_options, $item);
            }
        }

        $main_category =[];

        $p->choice_options = json_encode($choice_options);
        $variations = [];
        $options = [];
        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_' . $no;
                $my_str = implode('|', $request[$name]);
                array_push($options, explode(',', $my_str));
            }
        }
        //Generates the combinations of customer choice options
        $combinations = Helpers::combinations($options);

        $stock_count = 0;
        if (count($combinations[0]) > 0) {
            foreach ($combinations as $key => $combination) {
                $str = '';
                foreach ($combination as $k => $item) {
                    if ($k > 0) {
                        $str .= '-' . str_replace(' ', '', $item);
                    } else {
                        $str .= str_replace(' ', '', $item);
                    }
                }
                $item = [];
                $item['type'] = $str;
                $item['price'] = abs($request['price_' . str_replace('.', '_', $str)]);
                $item['stock'] = abs($request['stock_' . str_replace('.', '_', $str)]);
                array_push($variations, $item);
                $stock_count += $item['stock'];
            }
        } else {
            $stock_count = (integer)$request['total_stock'];
        }

        if ((integer)$request['total_stock'] != $stock_count) {
            $validator->getMessageBag()->add('total_stock', 'Stock calculation mismatch!');
        }

        if ($validator->getMessageBag()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }
if($request->has('no_of_purchase') ){
    $p->no_to_purchase = $request->no_of_purchase;
}
        //combinations end
        $p->variations = json_encode($variations);
        $p->price = $request->price;
        $p->unit = $request->unit;
        $p->image = $image_data;
        // $p->main_category = $request->main_category_id;
        $p->main_category = $request->has('main_category_id') ? json_encode($request->main_category_id) : json_encode([]);
        // $p->set_menu = $request->item_type;

        $p->tax = $request->tax_type == 'amount' ? $request->tax : $request->tax;
        $p->tax_type = $request->tax_type;

        $p->discount = $request->discount_type == 'amount' ? $request->discount : $request->discount;

        $p->discount_type = $request->discount_type;
        if($request->discount_type == null){
            $request->discount_type = 'percent';
        }
        $p->total_stock = $request->total_stock;

        $p->attributes = $request->has('attribute_id') ? json_encode($request->attribute_id) : json_encode([]);
        $p->save();

        $data = [];
        foreach($request->lang as $index=>$key)
        {
            if($request->name[$index] && $key != 'en')
            {
                array_push($data, Array(
                    'translationable_type'  => 'App\Model\Product',
                    'translationable_id'    => $p->id,
                    'locale'                => $key,
                    'key'                   => 'name',
                    'value'                 => $request->name[$index],
                ));
            }
            if($request->description[$index] && $key != 'en')
            {
                array_push($data, Array(
                    'translationable_type'  => 'App\Model\Product',
                    'translationable_id'    => $p->id,
                    'locale'                => $key,
                    'key'                   => 'description',
                    'value'                 => $request->description[$index],
                ));
            }
        }


        Translation::insert($data);

        return response()->json([], 200);
    }

    public function edit($id)
    {
        $product = Product::withoutGlobalScopes()->with('translations')->find($id);
        $product_category = json_decode($product->category_ids);
        $categories = Category::where(['parent_id' => 0])->get();
        return view('admin-views.product.edit', compact('product', 'product_category', 'categories'));
    }

    public function status(Request $request)
    {
        $product = Product::find($request->id);
        $product->status = $request->status;
        $product->save();
        Toastr::success('Product status updated!');
        return back();
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'category_id' => 'required',
            'total_stock' => 'required|numeric|min:1',
            'price' => 'required|numeric|min:1',
        ], [
            'name.required' => 'Product name is required!',
            'category_id.required' => 'category  is required!',
        ]);

        if ($request['discount_type'] == 'percent') {
            $dis = ($request['price'] / 100) * $request['discount'];
        } else {
            $dis = $request['discount'];
        }

        if ($request['price'] <= $dis) {
            $validator->getMessageBag()->add('unit_price', 'Discount can not be more or equal to the price!');
        }

        $p = Product::find($id);
        $images = json_decode($p->image);
        if (!empty($request->file('images'))) {
            foreach ($request->images as $img) {
                $image_data = Helpers::upload('product/', 'png', $img);
                array_push($images, $image_data);
            }

        }

        if (!count($images)) {
            $validator->getMessageBag()->add('images', 'Image can not be empty!');
        }

        $p->name = $request->name[array_search('en', $request->lang)];

        $category = [];
        if ($request->category_id != null) {
            array_push($category, [
                'id' => $request->category_id,
                'position' => 1,
            ]);
        }
        if ($request->sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_category_id,
                'position' => 2,
            ]);
        }
        if ($request->sub_sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_sub_category_id,
                'position' => 3,
            ]);
        }

        $p->category_ids = json_encode($category);
        $p->description = $request->description[array_search('en', $request->lang)];

        $choice_options = [];
        if ($request->has('choice')) {
            foreach ($request->choice_no as $key => $no) {
                $str = 'choice_options_' . $no;
                if ($request[$str][0] == null) {
                    $validator->getMessageBag()->add('name', 'Attribute choice option values can not be null!');
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                $item['name'] = 'choice_' . $no;
                $item['title'] = $request->choice[$key];
                $item['options'] = explode(',', implode('|', preg_replace('/\s+/', ' ', $request[$str])));
                array_push($choice_options, $item);
            }
        }
        $p->choice_options = json_encode($choice_options);
        $variations = [];
        $options = [];
        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_' . $no;
                $my_str = implode('|', $request[$name]);
                array_push($options, explode(',', $my_str));
            }
        }

        //Generates the combinations of customer choice options
        $combinations = Helpers::combinations($options);
        $stock_count = 0;
        if (count($combinations[0]) > 0) {
            foreach ($combinations as $key => $combination) {
                $str = '';
                foreach ($combination as $k => $item) {
                    if ($k > 0) {
                        $str .= '-' . str_replace(' ', '', $item);
                    } else {
                        $str .= str_replace(' ', '', $item);
                    }
                }
                $item = [];
                $item['type'] = $str;
                $item['price'] = abs($request['price_' . str_replace('.', '_', $str)]);
                $item['stock'] = abs($request['stock_' . str_replace('.', '_', $str)]);
                array_push($variations, $item);
                $stock_count += $item['stock'];
            }
        } else {
            $stock_count = (integer)$request['total_stock'];
        }

        if ((integer)$request['total_stock'] != $stock_count) {
            $validator->getMessageBag()->add('total_stock', 'Stock calculation mismatch!');
        }

        if ($validator->getMessageBag()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        //combinations end
        $p->variations = json_encode($variations);
        $p->price = $request->price;
        $p->unit = $request->unit;
        // $p->image = json_encode(array_merge(json_decode($p['image'], true), json_decode($image_data, true)));
        // $p->set_menu = $request->item_type;
        $p->image = json_encode($images);
        // $p->available_time_starts = $request->available_time_starts;
        // $p->available_time_ends = $request->available_time_ends;

        $p->tax = $request->tax_type == 'amount' ? $request->tax : $request->tax;
        $p->tax_type = $request->tax_type;

        $p->discount = $request->discount_type == 'amount' ? $request->discount : $request->discount;
        $p->discount_type = $request->discount_type;
        $p->total_stock = $request->total_stock;

        $p->attributes = $request->has('attribute_id') ? json_encode($request->attribute_id) : json_encode([]);
        $p->save();


        foreach($request->lang as $index=>$key)
        {
            if($request->name[$index] && $key != 'en')
            {
                Translation::updateOrInsert(
                    ['translationable_type'  => 'App\Model\Product',
                    'translationable_id'    => $p->id,
                    'locale'                => $key,
                    'key'                   => 'name'],
                    ['value'                 => $request->name[$index]]
                );
            }
            if($request->description[$index] && $key != 'en')
            {
                Translation::updateOrInsert(
                    ['translationable_type'  => 'App\Model\Product',
                    'translationable_id'    => $p->id,
                    'locale'                => $key,
                    'key'                   => 'description'],
                    ['value'                 => $request->description[$index]]
                );
            }
        }

        return response()->json([], 200);
    }

    public function delete(Request $request)
    {
        $product = Product::find($request->id);
        foreach (json_decode($product['image'], true) as $img) {
            if (Storage::disk('public')->exists('product/' . $img)) {
                Storage::disk('public')->delete('product/' . $img);
            }
        }
        $product->delete();
        Toastr::success('Product removed!');
        return back();
    }

    public function remove_image($id, $name)
    {
        $product = Product::find($id);
        $img_arr = [];
        foreach (json_decode($product['image'], true) as $img) {
            if (strcmp($img, $name) != 0) {
                array_push($img_arr, $img);
            }
        }

        if(count($img_arr) == 0)
        {
            Toastr::error('Product must have at least one image!');
            return back();
        }

        if (Storage::disk('public')->exists('product/' . $name)) {
                Storage::disk('public')->delete('product/' . $name);
        }

        Product::where(['id' => $id])->update([
            'image' => json_encode($img_arr)
        ]);

        Toastr::success('Image removed successfully!');
        return back();
    }
}
