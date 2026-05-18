@extends('layouts.app')

@section('title', 'Документы — СЭД СТАВ')

@section('content')
<div class="sed-page-header">
    <h1>Документы</h1>
</div>

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
