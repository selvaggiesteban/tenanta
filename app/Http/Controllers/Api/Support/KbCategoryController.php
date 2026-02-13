<?php

namespace App\Http\Controllers\Api\Support;

use App\Http\Controllers\Controller;
use App\Models\KbCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KbCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = KbCategory::withCount('publishedArticles as articles_count');

        if ($request->boolean('root_only', false)) {
            $query->whereNull('parent_id');
        }

        if ($request->boolean('public_only', false)) {
            $query->where('is_public', true);
        }

        $categories = $query->orderBy('order')->get();

        return response()->json([
            'data' => $categories->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'description' => $cat->description,
                'icon' => $cat->icon,
                'parent_id' => $cat->parent_id,
                'is_public' => $cat->is_public,
                'articles_count' => $cat->articles_count,
            ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:kb_categories,id',
            'order' => 'nullable|integer',
            'is_public' => 'nullable|boolean',
        ]);

        $category = KbCategory::create([
            ...$validated,
            'tenant_id' => Auth::user()->tenant_id,
            'is_public' => $validated['is_public'] ?? true,
        ]);

        return response()->json([
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ],
            'message' => 'Categoría creada exitosamente',
        ], 201);
    }

    public function show(KbCategory $kbCategory): JsonResponse
    {
        $kbCategory->load(['children', 'publishedArticles']);

        return response()->json([
            'data' => [
                'id' => $kbCategory->id,
                'name' => $kbCategory->name,
                'slug' => $kbCategory->slug,
                'description' => $kbCategory->description,
                'icon' => $kbCategory->icon,
                'is_public' => $kbCategory->is_public,
                'children' => $kbCategory->children->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                ]),
                'articles' => $kbCategory->publishedArticles->map(fn($a) => [
                    'id' => $a->id,
                    'title' => $a->title,
                    'slug' => $a->slug,
                    'excerpt' => $a->excerpt,
                    'views' => $a->views,
                ]),
            ],
        ]);
    }

    public function update(Request $request, KbCategory $kbCategory): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:kb_categories,id',
            'order' => 'nullable|integer',
            'is_public' => 'nullable|boolean',
        ]);

        $kbCategory->update($validated);

        return response()->json([
            'data' => [
                'id' => $kbCategory->id,
                'name' => $kbCategory->name,
                'slug' => $kbCategory->slug,
            ],
            'message' => 'Categoría actualizada exitosamente',
        ]);
    }

    public function destroy(KbCategory $kbCategory): JsonResponse
    {
        if ($kbCategory->articles()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar una categoría con artículos',
            ], 422);
        }

        $kbCategory->delete();

        return response()->json(null, 204);
    }
}
