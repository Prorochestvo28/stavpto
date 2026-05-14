<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DocumentCommentController extends Controller
{
    public function store(Request $request, Document $document): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:10000'],
        ]);

        DocumentComment::query()->create([
            'document_id' => $document->id,
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        return redirect()
            ->route('documents.show', $document)
            ->with('comment_status', 'Комментарий добавлен.');
    }
}
