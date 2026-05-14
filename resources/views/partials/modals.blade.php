@php
    $departments = \App\Models\Department::query()->orderBy('name')->get();
    $routeCategory = request()->route('category');
    $currentCategoryId = null;
    if ($routeCategory instanceof \App\Models\Category) {
        $currentCategoryId = $routeCategory->id;
    } elseif (request()->filled('category')) {
        $currentCategoryId = (int) request('category');
    }
@endphp

<div class="sed-modal-stack sed-modal-stack--folder" aria-hidden="true">
    <label for="sed-modal-folder" class="sed-modal-scrim sed-modal-scrim--dim"></label>
    <div class="sed-modal-panel sed-modal-panel--center" role="dialog" aria-modal="true" aria-labelledby="sed-folder-title">
        <div class="sed-modal-panel__head">
            <h2 id="sed-folder-title" class="sed-modal-panel__title">Новая папка</h2>
            <label for="sed-modal-folder" class="sed-modal-close" title="Закрыть" aria-label="Закрыть">&times;</label>
        </div>
        <form class="sed-modal-panel__body" action="{{ route('categories.store') }}" method="post">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $currentCategoryId }}">
            <div class="sed-field" style="margin-bottom:0;">
                <label for="sed-folder-name">Название папки <span class="sed-req" aria-hidden="true">*</span></label>
                <input class="sed-input" type="text" id="sed-folder-name" name="name" required maxlength="255" placeholder="Например: Договоры 2026">
            </div>
            <div class="sed-modal-panel__foot sed-modal-panel__foot--form">
                <label for="sed-modal-folder" class="sed-btn sed-btn--ghost sed-btn--sm">Отмена</label>
                <button type="submit" class="sed-btn sed-btn--primary sed-btn--sm">Создать</button>
            </div>
        </form>
    </div>
</div>

<div class="sed-modal-stack sed-modal-stack--upload" aria-hidden="true">
    <label for="sed-modal-upload" class="sed-modal-scrim sed-modal-scrim--dim"></label>
    <div class="sed-modal-panel sed-modal-panel--center" role="dialog" aria-modal="true" aria-labelledby="sed-upload-title">
        <div class="sed-modal-panel__head">
            <h2 id="sed-upload-title" class="sed-modal-panel__title">Новый документ</h2>
            <label for="sed-modal-upload" class="sed-modal-close" title="Закрыть" aria-label="Закрыть">&times;</label>
        </div>
        <form class="sed-modal-panel__body" action="{{ route('documents.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="category_id" value="{{ $currentCategoryId }}">
            <div class="sed-field">
                <label for="sed-upload-name">Название документа <span class="sed-req" aria-hidden="true">*</span></label>
                <input class="sed-input" type="text" id="sed-upload-name" name="name" required maxlength="255" placeholder="Например: Договор поставки №12">
            </div>
            <div class="sed-field">
                <label for="sed-upload-file">Файл <span class="sed-req" aria-hidden="true">*</span></label>
                <input class="sed-input sed-input--file" type="file" id="sed-upload-file" name="file" required
                    accept=".pdf,.doc,.docx,.txt,.xlsx,.xls,.ppt,.pptx,.odt,.ods,.rtf,.csv,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,text/plain,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel">
            </div>
            <fieldset class="sed-fieldset">
                <legend class="sed-fieldset__legend">Виден отделам <span class="sed-req" aria-hidden="true">*</span></legend>
                <div class="sed-dept-checkboxes">
                    @foreach($departments as $d)
                        <label class="sed-check-row">
                            <input type="checkbox" name="departments[]" value="{{ $d->id }}" @checked(in_array((string) $d->id, array_map('strval', old('departments', [])), true))>
                            <span>{{ $d->name }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <div class="sed-field" style="margin-bottom:0;">
                <label for="sed-upload-desc">Описание</label>
                <textarea class="sed-textarea" id="sed-upload-desc" name="description" rows="3" placeholder="Необязательно"></textarea>
            </div>
            <div class="sed-modal-panel__foot sed-modal-panel__foot--form">
                <label for="sed-modal-upload" class="sed-btn sed-btn--ghost sed-btn--sm">Отмена</label>
                <button type="submit" class="sed-btn sed-btn--primary sed-btn--sm">Добавить</button>
            </div>
        </form>
    </div>
</div>
