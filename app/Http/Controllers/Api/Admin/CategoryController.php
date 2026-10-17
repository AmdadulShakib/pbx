<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{    public function index(Request $request)
    {
        $limit = $request->limit ? $request->limit : 20;
        $offset = $request->offset ? $request->offset : 1;
        $query = Category::query();

        $categories = $query->latest()->paginate($limit, ['*'], 'page', $offset);

        return response()->json($categories, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => error_processor($validator)], 403);
        }

        $category = new Category();
        
        $category->save();

        return response()->json(['success' => 'Successfully Added'], 200);
    }

    public function show(Request $request, $id)
    {
        $query = Category::query();

        $category = $query->where('id', $id)->firstOrFail();
        return response()->json($category, 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => error_processor($validator)], 403);
        }

        $query = Category::query();
        $category = $query->where('id', $id)->firstOrFail();
        
        $category->update();

        return response()->json(['success' => 'Successfully updated'], 200);
    }

    public function destroy(Request $request, $id)
    {
        $query = Category::query();
        $category = $query->where('id', $id)->firstOrFail();
        $category->delete();

        return response()->json(['success' => 'Successfully deleted'], 200);
    }
}
