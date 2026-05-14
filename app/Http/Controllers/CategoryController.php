<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        Category::query()->create([
            'name' => $data['name'],
            'parent_id' => $data['parent_id'] ?? null,
            'path' => null,
        ]);

        return back()->with('status', 'Папка создана');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $category->update(['name' => $data['name']]);

        return back()->with('status', 'Название папки обновлено');
    }

    public function destroy(Category $category)
    {
        $parentId = $category->parent_id;
        $category->delete();

        if ($parentId) {
            return redirect()->route('categories.show', Category::query()->findOrFail($parentId))
                ->with('status', 'Папка удалена');
        }

        return redirect()->route('documents.index')->with('status', 'Папка удалена');
    }
}
