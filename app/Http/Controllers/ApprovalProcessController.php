<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\ApprovalWorkflowService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ApprovalProcessController extends Controller
{
    public function store(Request $request, Document $document, ApprovalWorkflowService $workflow): RedirectResponse
    {
        abort_unless(
            (int) $request->user()->id === (int) $document->author_id || $request->user()->isAdmin(),
            403,
            'Запускать согласование может автор документа или администратор.'
        );

        $validator = Validator::make($request->all(), [
            'assignees' => ['required', 'array', 'min:1', 'max:50'],
            'assignees.*' => ['integer', Rule::exists('users', 'id')->where(fn ($q) => $q->where('is_active', true))],
            'initiator_comment' => ['nullable', 'string', 'max:2000'],
            'deadline' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->withFragment('sed-approval-send');
        }

        $data = $validator->validated();

        $ordered = [];
        foreach ($data['assignees'] as $id) {
            $id = (int) $id;
            if ($id > 0 && ! in_array($id, $ordered, true)) {
                $ordered[] = $id;
            }
        }

        if ($ordered === []) {
            return back()->withErrors(['assignees' => 'Выберите хотя бы одного согласующего.'])->withInput()->withFragment('sed-approval-send');
        }

        $levels = array_map(static fn (int $id) => [$id], $ordered);

        $tz = config('app.timezone');
        $deadlineAt = filled($request->input('deadline'))
            ? Carbon::parse($request->input('deadline'), $tz)->endOfDay()
            : now()->timezone($tz)->addWeek()->endOfDay();

        try {
            $workflow->startProcess(
                $document,
                $request->user(),
                $levels,
                $data['initiator_comment'] ?? null,
                $deadlineAt
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->withFragment('sed-approval-send');
        }

        return redirect()
            ->route('documents.show', $document)
            ->with('status', 'Маршрут согласования запущен. Документ переведён в статус «На согласовании».');
    }
}
