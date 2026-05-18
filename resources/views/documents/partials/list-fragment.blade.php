@php
    $listBaseUrl = \Illuminate\Support\Str::before(request()->fullUrl(), '#');
@endphp
@if(!empty($breadcrumbs) && count($breadcrumbs) > 0)
    <p class="sed-flash" style="margin:0 0 0.75rem;">
        <a href="{{ route('documents.index') }}">Корень</a>
        @foreach($breadcrumbs as $bc)
            / <a href="{{ route('categories.show', $bc) }}">{{ $bc->name }}</a>
        @endforeach
    </p>
@endif

<div class="sed-table-wrap">
    <table class="sed-table">
        <thead>
            <tr>
                <th class="sed-table__icon-col" scope="col"><span class="visually-hidden">Тип</span></th>
                <th>Название</th>
                <th>Статус</th>
                <th>Изменён</th>
                <th>Последний изменивший</th>
                <th style="text-align:right;">Действия</th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $c)
                <tr>
                    <td class="sed-table__icon-col">
                        <img class="sed-file-icon" src="{{ \App\Support\FileIcon::urlForFolder() }}" width="24" height="24" alt="" role="presentation">
                    </td>
                    <td><a href="{{ route('categories.show', $c) }}">{{ $c->name }}</a></td>
                    <td>—</td>
                    <td>{{ $c->updated_at?->format('d.m.Y') }}</td>
                    <td>—</td>
                    <td style="text-align:right;">
                        <div class="sed-folder-actions">
                            <a class="sed-btn sed-btn--ghost sed-btn--sm" href="{{ route('categories.show', $c) }}">Открыть</a>
                            <a class="sed-btn sed-btn--ghost sed-btn--sm" href="{{ $listBaseUrl }}#sed-rename-{{ $c->id }}">Изменить</a>
                            <form method="post" action="{{ route('categories.destroy', $c) }}" class="sed-folder-actions__form" data-sed-confirm="Удалить эту папку? Вложенные папки перейдут в корень, документы останутся без папки.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="sed-btn sed-btn--danger sed-btn--sm">Удалить</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
            @endforelse

            @forelse($documents as $doc)
                <tr>
                    <td class="sed-table__icon-col">
                        <img class="sed-file-icon" src="{{ \App\Support\FileIcon::urlForDocument($doc->latestVersion?->file_name) }}" width="24" height="24" alt="" role="presentation">
                    </td>
                    <td><a href="{{ route('documents.show', $doc) }}">{{ $doc->name }}</a></td>
                    <td><span class="{{ $doc->statusBadgeClass() }}">{{ $doc->statusLabel() }}</span></td>
                    <td>{{ $doc->updated_at?->format('d.m.Y') }}</td>
                    <td>{{ $doc->lastEditor?->displayName() ?? '—' }}</td>
                    <td style="text-align:right;">
                        <a class="sed-btn sed-btn--ghost sed-btn--sm" href="{{ route('documents.show', $doc) }}#sed-edit-document">Изменить</a>
                        <a class="sed-btn sed-btn--ghost sed-btn--sm" href="{{ route('documents.download', $doc) }}">Скачать</a>
                        <form action="{{ route('documents.destroy', $doc) }}" method="post" style="display:inline;" data-sed-confirm="Удалить документ?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="sed-btn sed-btn--danger sed-btn--sm">Удалить</button>
                        </form>
                    </td>
                </tr>
            @empty
                @if($categories->isEmpty())
                    <tr>
                        <td colspan="6">Пока пусто. Создайте папку или загрузите документ.</td>
                    </tr>
                @endif
            @endforelse
        </tbody>
    </table>
</div>

@foreach ($categories as $c)
    <div id="sed-rename-{{ $c->id }}" class="sed-target-modal" role="dialog" aria-modal="true" aria-labelledby="sed-rename-title-{{ $c->id }}">
        <a href="{{ $listBaseUrl }}" class="sed-target-modal__scrim" aria-label="Закрыть"></a>
        <div class="sed-modal-panel sed-modal-panel--center sed-target-modal__inner">
            <div class="sed-modal-panel__head">
                <h2 id="sed-rename-title-{{ $c->id }}" class="sed-modal-panel__title">Переименовать папку</h2>
                <a href="{{ $listBaseUrl }}" class="sed-modal-close" title="Закрыть" aria-label="Закрыть">&times;</a>
            </div>
            <form class="sed-modal-panel__body" method="post" action="{{ route('categories.update', $c) }}">
                @csrf
                @method('PUT')
                <div class="sed-field" style="margin-bottom:0;">
                    <label for="sed-rename-input-{{ $c->id }}">Название <span class="sed-req" aria-hidden="true">*</span></label>
                    <input class="sed-input" type="text" id="sed-rename-input-{{ $c->id }}" name="name" value="{{ $c->name }}" required maxlength="255" placeholder="Название папки">
                </div>
                <div class="sed-modal-panel__foot sed-modal-panel__foot--form">
                    <a href="{{ $listBaseUrl }}" class="sed-btn sed-btn--ghost sed-btn--sm">Отмена</a>
                    <button type="submit" class="sed-btn sed-btn--primary sed-btn--sm">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
@endforeach

@if($documents->hasPages())
<div class="sed-pagination" role="navigation" aria-label="Пагинация">
    <span>Показано {{ $documents->firstItem() ?? 0 }}–{{ $documents->lastItem() ?? 0 }} из {{ $documents->total() }}</span>
    <div class="sed-pagination__pages">
        @if($documents->onFirstPage())
            <span class="sed-page-link" aria-disabled="true">«</span>
        @else
            <a class="sed-page-link" href="{{ $documents->previousPageUrl() }}">«</a>
        @endif

        <span class="sed-page-link sed-page-link--current">{{ $documents->currentPage() }}</span>

        @if($documents->hasMorePages())
            <a class="sed-page-link" href="{{ $documents->nextPageUrl() }}">»</a>
        @else
            <span class="sed-page-link" aria-disabled="true">»</span>
        @endif
    </div>
</div>
@endif
