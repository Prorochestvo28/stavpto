<?php

namespace App\Http\Controllers;

use App\Models\ApprovalStep;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ApprovalsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $steps = ApprovalStep::query()
            ->where('assignee_id', $user->id)
            ->with([
                'process.document',
                'process' => fn ($q) => $q->with(['steps.assignee.department', 'initiator']),
            ])
            ->whereHas('process')
            ->orderByDesc('id')
            ->limit(500)
            ->get();

        $actionable = $steps->filter(fn (ApprovalStep $s) => $s->isActionableBy($user))->values();

        $queued = $steps->filter(
            fn (ApprovalStep $s) => $s->isPending()
                && $s->process->isInProgress()
                && ! $s->isActionableBy($user)
        )->values();

        $archive = $steps->filter(
            fn (ApprovalStep $s) => ! $s->isPending() || ! $s->process->isInProgress()
        )->values();

        return view('approvals.index', [
            'actionablePaginator' => $this->paginateCollection($request, $actionable, 15, 'ap_a'),
            'queuedPaginator' => $this->paginateCollection($request, $queued, 15, 'ap_q'),
            'archivePaginator' => $this->paginateCollection($request, $archive, 15, 'ap_z'),
            'actionableStepIds' => $actionable->pluck('id')->all(),
        ]);
    }

    /**
     * @param  Collection<int, ApprovalStep>  $items
     */
    private function paginateCollection(Request $request, Collection $items, int $perPage, string $pageName): LengthAwarePaginator
    {
        $total = $items->count();
        $page = max(1, (int) $request->input($pageName, 1));

        return (new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'pageName' => $pageName,
            ]
        ))->withQueryString();
    }
}
