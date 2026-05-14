<?php

namespace App\Http\Controllers;

use App\Models\ApprovalStep;
use App\Services\ApprovalWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ApprovalStepController extends Controller
{
    public function approve(Request $request, ApprovalStep $approvalStep, ApprovalWorkflowService $workflow): RedirectResponse
    {
        try {
            $data = $request->validate([
                'signature_pin' => ['required', 'string', 'regex:/^\d{4,6}$/'],
                'comment' => ['nullable', 'string', 'max:2000'],
                'return_fragment' => ['nullable', 'string', Rule::in(['sed-approval-decision', 'sed-doc-approval-decision'])],
            ]);

            $workflow->approve($approvalStep, $request->user(), $data['signature_pin'], $data['comment'] ?? null);
        } catch (ValidationException $e) {
            return $this->redirectBackWithApprovalModal($request, $approvalStep, 'approve', $e);
        }

        return redirect()->back()->with('status', 'Решение зафиксировано: согласовано.');
    }

    public function reject(Request $request, ApprovalStep $approvalStep, ApprovalWorkflowService $workflow): RedirectResponse
    {
        try {
            $data = $request->validate([
                'signature_pin' => ['required', 'string', 'regex:/^\d{4,6}$/'],
                'comment' => ['nullable', 'string', 'max:2000'],
                'return_fragment' => ['nullable', 'string', Rule::in(['sed-approval-decision', 'sed-doc-approval-decision'])],
            ]);

            $workflow->reject($approvalStep, $request->user(), $data['signature_pin'], $data['comment'] ?? null);
        } catch (ValidationException $e) {
            return $this->redirectBackWithApprovalModal($request, $approvalStep, 'reject', $e);
        }

        return redirect()->back()->with('status', 'Документ отклонён по маршруту согласования.');
    }

    private function redirectBackWithApprovalModal(
        Request $request,
        ApprovalStep $approvalStep,
        string $action,
        ValidationException $e
    ): RedirectResponse {
        $fragment = $request->input('return_fragment');
        $response = redirect()
            ->back()
            ->withErrors($e->errors())
            ->withInput($request->except('signature_pin'))
            ->with('approval_modal', ['step_id' => $approvalStep->id, 'action' => $action]);

        if (is_string($fragment) && in_array($fragment, ['sed-approval-decision', 'sed-doc-approval-decision'], true)) {
            $response->withFragment($fragment);
        }

        return $response;
    }
}
