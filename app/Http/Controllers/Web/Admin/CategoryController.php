<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Exception;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 20);
        if ($perPage < 5) {
            $perPage = 5;
        }
        if ($perPage > 200) {
            $perPage = 200;
        }
        $query = Category::query();

        
        $data['categories'] = $query->orderBy('id', 'desc')->paginate($perPage)->appends($request->except('page'));
        
        return view('admin.category.index', $data);
    }

    public function create()
    {
        
        return view('admin.category.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
        ]);
        
        $validator->validate();

        $category = new Category();
        $category->save();
        

        return redirect()
            ->route('admin.category.index')
            ->with('success', 'Category created successfully');
    }

    public function edit($id)
    {
        $query = Category::query();
        $category = $query->where('id', $id)->firstOrFail();
        $data['category'] = $category;
        
        return view('admin.category.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            
        ]);
        
        $validator->validate();

        $query = Category::query();
        $category = $query->where('id', $id)->firstOrFail();
        $category->save();
        

        return redirect()
            ->route('admin.category.index')
            ->with('success', 'Category updated successfully');
    }

    public function destroy($id)
    {
        $query = Category::query();
        $category = $query->where('id', $id)->firstOrFail();
        $category->delete();

        return redirect()
            ->route('admin.category.index')
            ->with('success', 'Category deleted successfully');
    }
}
