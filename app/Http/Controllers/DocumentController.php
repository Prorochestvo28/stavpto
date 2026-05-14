<?php

namespace App\Http\Controllers;

use App\Models\ApprovalProcess;
use App\Models\Category;
use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $categoryId = $request->integer('category');
        $category = $categoryId ? Category::query()->findOrFail($categoryId) : null;

        $categories = Category::query()
            ->when($category, fn ($q) => $q->where('parent_id', $category->id), fn ($q) => $q->whereNull('parent_id'))
            ->orderBy('name')
            ->get();

        $documents = Document::query()
            ->with(['departments:id,name', 'latestVersion', 'lastEditor:id,name,full_name,email'])
            ->when($category, fn ($q) => $q->where('category_id', $category->id), fn ($q) => $q->whereNull('category_id'))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = trim((string) $request->input('q'));
                $q->where('name', 'like', "%{$term}%");
            })
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('updated_at', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('updated_at', '<=', $request->input('date_to')))
            ->when($request->input('filter') === 'active', fn ($q) => $q->whereIn('status', ['draft', 'review']))
            ->when($request->input('filter') === 'archive', fn ($q) => $q->whereIn('status', ['approved', 'rejected']))
            ->orderByDesc('updated_at')
            ->paginate(10)
            ->withQueryString();

        $breadcrumbs = [];
        $cursor = $category;
        while ($cursor) {
            $breadcrumbs[] = $cursor;
            $cursor = $cursor->parent;
        }
        $breadcrumbs = array_reverse($breadcrumbs);

        $viewData = [
            'category' => $category,
            'categories' => $categories,
            'documents' => $documents,
            'breadcrumbs' => $breadcrumbs,
        ];

        if ($request->ajax()) {
            return response()->view('documents.partials.list-fragment', $viewData);
        }

        return view('documents.index', $viewData);
    }

    public function category(Category $category, Request $request)
    {
        $request->merge(['category' => $category->id]);

        return $this->index($request);
    }

    public function store(Request $request)
    {
        $authorId = $request->user()->id;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'file' => $this->documentFileValidationRules(true),
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'departments' => ['required', 'array', 'min:1'],
            'departments.*' => ['integer', 'exists:departments,id'],
            'description' => ['nullable', 'string', 'max:65535'],
        ], [
            'file.mimes' => 'Допустимые форматы: PDF, Word, Excel, PowerPoint, ODT, ODS, TXT, RTF, CSV (до 50 МБ).',
        ]);

        $departmentIds = Department::query()
            ->whereIn('id', $data['departments'])
            ->pluck('id')
            ->all();

        if ($departmentIds === []) {
            return back()->withErrors(['departments' => 'Выберите хотя бы один отдел'])->withInput();
        }

        $file = $request->file('file');

        return DB::transaction(function () use ($data, $departmentIds, $file, $authorId) {
            $document = Document::query()->create([
                'name' => $data['name'],
                'category_id' => $data['category_id'] ?? null,
                'status' => 'draft',
                'author_id' => $authorId,
                'last_edited_by' => $authorId,
            ]);

            $document->departments()->sync($departmentIds);

            $storagePath = $file->storeAs(
                "documents/{$document->id}",
                'v1_'.time().'_'.preg_replace('/[^a-zA-Z0-9._-]+/', '_', $file->getClientOriginalName()),
                'local'
            );

            DocumentVersion::query()->create([
                'document_id' => $document->id,
                'version_number' => 1,
                'file_name' => $file->getClientOriginalName(),
                'file_url' => $storagePath,
                'file_size' => (int) $file->getSize(),
                'change_comment' => $data['description'] ?? null,
                'author_id' => $authorId,
            ]);

            return redirect()->route('documents.show', $document)->with('status', 'Документ добавлен');
        });
    }

    public function update(Request $request, Document $document)
    {
        $request->merge([
            'category_id' => $request->filled('category_id') ? (int) $request->input('category_id') : null,
        ]);

        $authorId = $request->user()->id;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'departments' => ['required', 'array', 'min:1'],
            'departments.*' => ['integer', 'exists:departments,id'],
            'file' => $this->documentFileValidationRules(false),
            'change_comment' => ['nullable', 'string', 'max:65535'],
        ], [
            'file.mimes' => 'Допустимые форматы: PDF, Word, Excel, PowerPoint, ODT, ODS, TXT, RTF, CSV (до 50 МБ).',
        ]);

        $departmentIds = Department::query()
            ->whereIn('id', $data['departments'])
            ->pluck('id')
            ->all();

        if ($departmentIds === []) {
            return back()->withErrors(['departments' => 'Выберите хотя бы один отдел'])->withInput();
        }

        $file = $request->file('file');

        return DB::transaction(function () use ($data, $departmentIds, $file, $authorId, $document) {
            $document->update([
                'name' => $data['name'],
                'category_id' => $data['category_id'] ?? null,
                'last_edited_by' => $authorId,
            ]);
            $document->departments()->sync($departmentIds);

            if ($file && $file->isValid()) {
                $nextVer = (int) $document->versions()->max('version_number') + 1;
                $storagePath = $file->storeAs(
                    "documents/{$document->id}",
                    'v'.$nextVer.'_'.time().'_'.preg_replace('/[^a-zA-Z0-9._-]+/', '_', $file->getClientOriginalName()),
                    'local'
                );

                DocumentVersion::query()->create([
                    'document_id' => $document->id,
                    'version_number' => $nextVer,
                    'file_name' => $file->getClientOriginalName(),
                    'file_url' => $storagePath,
                    'file_size' => (int) $file->getSize(),
                    'change_comment' => $data['change_comment'] ?? null,
                    'author_id' => $authorId,
                ]);
            }

            return redirect()->route('documents.show', $document)->with('status', 'Документ обновлён');
        });
    }

    public function show(Document $document)
    {
        $document->load([
            'category:id,name',
            'author:id,name,email,full_name',
            'lastEditor:id,name,email,full_name',
            'departments:id,name',
            'versions' => fn ($q) => $q->orderByDesc('version_number'),
            'activeApprovalProcess.steps.assignee.department:id,name',
            'activeApprovalProcess.initiator',
            'comments.user:id,name,email,full_name',
        ]);

        $assignableUsers = User::query()
            ->where('is_active', true)
            ->with('department:id,name')
            ->orderByRaw('COALESCE(full_name, name)')
            ->orderBy('email')
            ->get(['id', 'name', 'full_name', 'email', 'department_id']);

        $categories = Category::query()->orderBy('name')->get(['id', 'name', 'parent_id']);
        $departments = Department::query()->orderBy('name')->get(['id', 'name']);

        $latest = $document->versions->first();
        $previewKind = 'none';
        if ($latest?->file_url && Storage::disk('local')->exists($latest->file_url)) {
            try {
                $mime = Storage::disk('local')->mimeType($latest->file_url);
            } catch (\Throwable) {
                $mime = null;
            }
            $previewKind = $this->previewKind($mime);
        }

        $user = auth()->user();
        $canStartApproval = $document->status === 'draft'
            && ! $document->activeApprovalProcess
            && ((int) $user->id === (int) $document->author_id || $user->isAdmin());

        $approvalActionStep = null;
        if ($document->activeApprovalProcess) {
            foreach ($document->activeApprovalProcess->steps as $step) {
                if ($step->isActionableBy($user)) {
                    $approvalActionStep = $step;
                    break;
                }
            }
        }

        $pastApprovalProcesses = $document->approvalProcesses()
            ->whereIn('status', [
                ApprovalProcess::STATUS_COMPLETED,
                ApprovalProcess::STATUS_REJECTED,
                ApprovalProcess::STATUS_CANCELLED,
            ])
            ->with(['steps.assignee.department', 'initiator'])
            ->orderByDesc('id')
            ->get();

        return view('documents.show', [
            'document' => $document,
            'previewKind' => $previewKind,
            'categories' => $categories,
            'departments' => $departments,
            'assignableUsers' => $assignableUsers,
            'canStartApproval' => $canStartApproval,
            'approvalActionStep' => $approvalActionStep,
            'pastApprovalProcesses' => $pastApprovalProcesses,
        ]);
    }

    public function preview(Document $document)
    {
        $version = $document->versions()->orderByDesc('version_number')->first();
        if (! $version || ! $version->file_url || ! Storage::disk('local')->exists($version->file_url)) {
            abort(404);
        }

        try {
            $mime = Storage::disk('local')->mimeType($version->file_url);
        } catch (\Throwable) {
            abort(415);
        }

        if ($this->previewKind($mime) === 'none') {
            abort(415);
        }

        $filename = $version->file_name ?: basename($version->file_url);

        return Storage::disk('local')->response(
            $version->file_url,
            $filename,
            ['Content-Type' => $mime],
            'inline'
        );
    }

    public function downloadLatest(Document $document)
    {
        $version = $document->versions()->orderByDesc('version_number')->first();
        if (! $version || ! $version->file_url) {
            abort(404);
        }

        if (! Storage::disk('local')->exists($version->file_url)) {
            abort(404);
        }

        return Storage::disk('local')->download($version->file_url, $version->file_name ?: 'file');
    }

    public function downloadVersion(Document $document, DocumentVersion $document_version)
    {
        abort_unless((int) $document_version->document_id === (int) $document->id, 404);

        if (! $document_version->file_url) {
            abort(404);
        }

        if (! Storage::disk('local')->exists($document_version->file_url)) {
            abort(404);
        }

        $downloadName = $document_version->file_name ?: ('document-v'.$document_version->version_number);

        return Storage::disk('local')->download($document_version->file_url, $downloadName);
    }

    public function reopenAsDraft(Request $request, Document $document): RedirectResponse
    {
        abort_unless(
            (int) $request->user()->id === (int) $document->author_id || $request->user()->isAdmin(),
            403
        );

        abort_unless($document->status === 'rejected', 403);

        abort_if(
            $document->approvalProcesses()->where('status', ApprovalProcess::STATUS_IN_PROGRESS)->exists(),
            403
        );

        $document->update(['status' => 'draft']);

        return back()->with('status', 'Документ снова в статусе «Черновик». Можно настроить новый маршрут согласования.');
    }

    public function destroy(Document $document)
    {
        foreach ($document->versions()->get() as $v) {
            if ($v->file_url) {
                Storage::disk('local')->delete($v->file_url);
            }
        }

        $document->delete();

        return redirect()->route('documents.index')->with('status', 'Документ удалён');
    }

    private function previewKind(?string $mime): string
    {
        if ($mime === null || $mime === '') {
            return 'none';
        }
        if ($mime === 'application/pdf') {
            return 'pdf';
        }
        if (str_starts_with($mime, 'image/')) {
            return 'image';
        }
        if (str_starts_with($mime, 'text/')) {
            return 'text';
        }

        return 'none';
    }

    /**
     * @return list<string|ValidationRule>
     */
    private function documentFileValidationRules(bool $required): array
    {
        $mimes = 'mimes:pdf,doc,docx,txt,xlsx,xls,ppt,pptx,odt,ods,rtf,csv';

        if ($required) {
            return ['required', 'file', 'max:51200', $mimes];
        }

        return ['nullable', 'file', 'max:51200', $mimes];
    }
}
