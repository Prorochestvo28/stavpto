@php
    $tid = $toolbarId ?? 'table';
    $formAction = $toolbarFormAction ?? url()->current();
    $fragmentTarget = $toolbarFragmentTarget ?? null;
@endphp
<form class="sed-toolbar" method="get" action="{{ $formAction }}" data-toolbar-auto @if($fragmentTarget) data-fragment-target="{{ $fragmentTarget }}" @endif>
    <div class="sed-toolbar__grow sed-field" style="margin:0;">
        <label class="visually-hidden" for="search-{{ $tid }}">Поиск</label>
        <input class="sed-input" type="search" id="search-{{ $tid }}" name="q" placeholder="Поиск…" value="{{ request('q') }}" autocomplete="off">
    </div>
    <div class="sed-toolbar__dates">
        <div class="sed-field sed-field--inline" style="margin:0;">
            <label for="date-from-{{ $tid }}">С даты</label>
            <input class="sed-input sed-input--date" type="date" id="date-from-{{ $tid }}" name="date_from" value="{{ request('date_from') }}">
        </div>
        <div class="sed-field sed-field--inline" style="margin:0;">
            <label for="date-to-{{ $tid }}">По дату</label>
            <input class="sed-input sed-input--date" type="date" id="date-to-{{ $tid }}" name="date_to" value="{{ request('date_to') }}">
        </div>
    </div>
    <div class="sed-field" style="margin:0;min-width:150px;">
        <label for="filter-{{ $tid }}">Фильтр</label>
        <select class="sed-select" id="filter-{{ $tid }}" name="filter">
            <option value="" @selected(request('filter') === null || request('filter') === '')>Все</option>
            <option value="active" @selected(request('filter') === 'active')>Активные</option>
            <option value="archive" @selected(request('filter') === 'archive')>Архив</option>
        </select>
    </div>
    @isset($toolbarActions)
        <div class="sed-toolbar__actions">
            {!! $toolbarActions !!}
        </div>
    @endisset
</form>
