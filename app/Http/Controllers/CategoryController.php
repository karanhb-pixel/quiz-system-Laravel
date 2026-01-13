<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;

class CategoryController extends Controller
{
    /**
     * Display a All Categories.
     */
    public function index()
    {
        $categories = Category::withCount('quizzes')->get();
        return view('categories.index',compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        $validated = $request->validated();

         Category::create([
        'name'        => $validated['name'],
        'description' => $validated['description'] ?? null,
        'slug'        => str($validated['name'])->slug(),
        'creator'     => auth()->user()->name, // We know this exists because of your auth middleware
    ]);

        return redirect()->route('categories.index')
                        ->with('success','Category Created Successfully!');
    }

    /**
     * This route display all quizzes in category selected
     */
    public function show(Category $category)
    {
        $quizzes = $category->quizzes()
                            ->where('user_id',auth()->id())
                            ->withCount('questions')
                            ->latest()  
                            ->get();

        return view('categories.show', compact('category','quizzes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {

        if($category->delete()){
            return redirect()->route('categories.index')
                        ->with('success','Category Deleted Successfully!');
        }   

        return redirect()->back()
                        ->with('error','Something Wrong, Category could not be deleted.');
    }

   
}
