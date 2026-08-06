<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SuccessStory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SuccessStoryController extends Controller
{
    private function canManage(?User $user): bool
    {
        return $user && (
            $user->hasPermissionTo('story.manage')
            || $user->hasRole(['general_director', 'admin', 'super_admin', 'system_admin', 'incubator_manager', 'branch_manager'])
        );
    }

    public function index(Request $request)
    {
        $canManage = $this->canManage($request->user());

        $q = SuccessStory::with(['incubator', 'branch', 'creator'])
            ->when($request->sector,       fn($q) => $q->where('sector', $request->sector))
            ->when($request->incubator_id,fn($q) => $q->where('incubator_id', $request->incubator_id))
            ->when($request->branch_id,   fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->featured,   fn($q) => $q->where('is_featured', true))
            ->when($request->search,     fn($q) => $q->where(function($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('hero_name', 'like', "%{$request->search}%")
                  ->orWhere('project_name', 'like', "%{$request->search}%");
            }));

        if ($canManage && $request->filled('status')) {
            $q->where('status', $request->status);
        } else {
            $q->published();
        }

        return response()->json($q->latest('published_at')->paginate($request->per_page ?? 12));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'                => 'required|string|max:255',
            'summary'              => 'required|string',
            'body'                 => 'required|string',
            'hero_name'            => 'required|string|max:255',
            'hero_title'           => 'nullable|string|max:255',
            'hero_photo_url'       => 'nullable|url',
            'project_name'         => 'nullable|string|max:255',
            'sector'               => 'nullable|string',
            'incubated_project_id' => 'nullable|exists:incubated_projects,id',
            'incubator_id'         => 'nullable|exists:incubators,id',
            'branch_id'            => 'nullable|exists:branches,id',
            'revenue_achieved'     => 'nullable|numeric|min:0',
            'jobs_created'         => 'nullable|integer|min:0',
            'years_in_incubator'   => 'nullable|integer|min:0',
            'current_stage'        => 'nullable|string|max:255',
            'featured_quote'       => 'nullable|string',
            'cover_image_url'      => 'nullable|url',
            'video_url'            => 'nullable|url',
            'gallery'              => 'nullable|array',
            'gallery.*'            => 'url',
            'status'               => 'nullable|in:draft,published,archived',
            'is_featured'          => 'nullable|boolean',
        ]);

        if (($data['status'] ?? 'draft') === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $story = SuccessStory::create($data);
        return response()->json($story->load(['incubator', 'branch', 'creator']), 201);
    }

    public function show(Request $request, $id)
    {
        $story = SuccessStory::with(['project', 'incubator', 'branch', 'creator', 'approver'])
            ->findOrFail($id);

        if ($story->status !== 'published' && !$this->canManage($request->user())) {
            throw new NotFoundHttpException();
        }

        if ($story->status === 'published') {
            $story->incrementViews();
        }

        return response()->json($story);
    }

    public function showBySlug(Request $request, $slug)
    {
        $story = SuccessStory::with(['project', 'incubator', 'branch', 'creator'])
            ->where('slug', $slug)->firstOrFail();

        if ($story->status !== 'published' && !$this->canManage($request->user())) {
            throw new NotFoundHttpException();
        }

        if ($story->status === 'published') {
            $story->incrementViews();
        }

        return response()->json($story);
    }

    public function update(Request $request, $id)
    {
        $story = SuccessStory::findOrFail($id);

        $data = $request->validate([
            'title'                => 'sometimes|string|max:255',
            'summary'              => 'sometimes|string',
            'body'                 => 'sometimes|string',
            'hero_name'            => 'sometimes|string|max:255',
            'hero_title'           => 'nullable|string|max:255',
            'hero_photo_url'       => 'nullable|url',
            'project_name'         => 'nullable|string|max:255',
            'sector'               => 'nullable|string',
            'incubated_project_id' => 'nullable|exists:incubated_projects,id',
            'incubator_id'         => 'nullable|exists:incubators,id',
            'branch_id'            => 'nullable|exists:branches,id',
            'revenue_achieved'     => 'nullable|numeric|min:0',
            'jobs_created'         => 'nullable|integer|min:0',
            'years_in_incubator'   => 'nullable|integer|min:0',
            'current_stage'        => 'nullable|string|max:255',
            'featured_quote'       => 'nullable|string',
            'cover_image_url'      => 'nullable|url',
            'video_url'            => 'nullable|url',
            'gallery'              => 'nullable|array',
            'status'               => 'nullable|in:draft,published,archived',
            'is_featured'          => 'nullable|boolean',
        ]);

        if (isset($data['status']) && $data['status'] === 'published' && !$story->published_at) {
            $data['published_at'] = now();
            $data['approved_by']  = Auth::id();
        }

        $story->update($data);
        return response()->json($story->fresh(['incubator', 'branch']));
    }

    public function destroy($id)
    {
        SuccessStory::findOrFail($id)->delete();
        return response()->json(['message' => 'تم الحذف']);
    }

    public function stats()
    {
        return response()->json([
            'total'     => SuccessStory::count(),
            'published' => SuccessStory::where('status', 'published')->count(),
            'featured'  => SuccessStory::where('is_featured', true)->count(),
            'draft'     => SuccessStory::where('status', 'draft')->count(),
            'total_jobs'=> (int) SuccessStory::where('status', 'published')->sum('jobs_created'),
            'top_viewed'=> SuccessStory::published()->orderByDesc('views_count')->limit(5)
                            ->get(['id','title','hero_name','views_count']),
        ]);
    }
}
