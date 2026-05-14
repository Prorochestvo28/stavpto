@extends('layouts.app')

@section('title', 'Документы — СЭД СТАВ')

@section('content')
<div class="sed-page-header">
    <h1>Документы</h1>
    <p>Папки (категории), загрузка и список документов.</p>
</div>

@if (session('status'))
    <p class="sed-muted" style="margin:0 0 0.75rem;">{{ session('status') }}</p>
@endif

@if ($errors->any())
    <div class="sed-card" style="margin-bottom:1rem;border-color:#e0b4b4;">
        <ul class="sed-muted" style="margin:0;padding-left:1.25rem;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="sed-card">
    @include('partials.table-toolbar', [
        'toolbarId' => 'documents',
        'toolbarFormAction' => isset($category) ? route('categories.show', $category) : route('documents.index'),
        'toolbarActions' => view('partials.toolbar-documents-actions')->render(),
        'toolbarFragmentTarget' => '#sed-documents-fragment',
    ])

    <div id="sed-documents-fragment">
        @include('documents.partials.list-fragment')
    </div>
</div>
@endsection
