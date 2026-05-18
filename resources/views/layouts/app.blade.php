<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'СЭД СТАВ')</title>
    <link rel="stylesheet" href="{{ asset('css/sed.css') }}">
</head>
<body>
    <input type="checkbox" id="sed-modal-upload" class="sed-modal-state">
    <input type="checkbox" id="sed-modal-folder" class="sed-modal-state">
    <div class="sed-app">
        <aside class="sed-sidebar">
            <div class="sed-sidebar__brand">
                <a href="{{ route('documents.index') }}" title="На главную — СЭД СТАВ">
                    <img src="{{ asset('img/logo.svg') }}" alt="СЭД СТАВ" width="119" height="48">
                </a>
            </div>
            <nav class="sed-sidebar__nav" aria-label="Основное меню">
                <a class="sed-nav-link @if(request()->routeIs('documents.*') || request()->routeIs('categories.*')) sed-nav-link--active @endif" href="{{ route('documents.index') }}">Документы</a>
                <a class="sed-nav-link @if(request()->routeIs('approvals.*')) sed-nav-link--active @endif" href="{{ route('approvals.index') }}">Согласования</a>
                @if(auth()->user()->isAdmin() || auth()->user()->isDepartmentHead())
                    <a class="sed-nav-link @if(request()->routeIs('admin.users.*')) sed-nav-link--active @endif" href="{{ route('admin.users.index') }}">Пользователи</a>
                @endif
                @if(auth()->user()->isAdmin())
                    <a class="sed-nav-link @if(request()->routeIs('admin.departments.*')) sed-nav-link--active @endif" href="{{ route('admin.departments.index') }}">Отделы</a>
                @endif
            </nav>
            <div class="sed-sidebar__footer">
                <a href="{{ route('settings.index') }}" class="sed-settings-btn">
                    <img src="{{ asset('img/settings.svg') }}" alt="" width="24" height="24">
                    Настройки
                </a>
                <div class="sed-user-row">
                    <div class="sed-user-row__info">
                        <div class="sed-user-row__name">{{ auth()->user()->displayName() }}</div>
                        <div class="sed-user-row__hint">{{ auth()->user()->email }}</div>
                    </div>
                    <form method="post" action="{{ route('logout') }}" style="margin:0;" data-sed-confirm="Выйти из учётной записи?">
                        @csrf
                        <button type="submit" class="sed-btn sed-btn--ghost sed-btn--sm">Выход</button>
                    </form>
                </div>
            </div>
        </aside>
        <main class="sed-main">
            @yield('content')
        </main>
    </div>
    @include('partials.modals')
    @include('partials.confirm-modal')
    @include('partials.toasts')
    @yield('scripts')
    <script>
    (function () {
        function buildUrlFromForm(form) {
            var u = new URL(form.action, window.location.origin);
            var fd = new FormData(form);
            fd.forEach(function (val, key) {
                u.searchParams.set(key, String(val));
            });
            return u;
        }

        function loadListFragment(urlString, fragmentSelector) {
            var el = document.querySelector(fragmentSelector);
            if (!el) {
                return Promise.reject();
            }
            el.setAttribute('aria-busy', 'true');
            return fetch(urlString, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'text/html'
                },
                credentials: 'same-origin'
            })
                .then(function (r) {
                    if (!r.ok) {
                        throw new Error('bad status');
                    }
                    return r.text();
                })
                .then(function (html) {
                    el.innerHTML = html;
                    var u = new URL(urlString, window.location.origin);
                    history.replaceState(null, '', u.pathname + u.search);
                })
                .finally(function () {
                    el.removeAttribute('aria-busy');
                });
        }

        document.addEventListener('click', function (e) {
            var a = e.target.closest('#sed-documents-fragment .sed-pagination a.sed-page-link[href]');
            if (!a) {
                return;
            }
            e.preventDefault();
            loadListFragment(a.href, '#sed-documents-fragment').catch(function () {});
        });

        document.querySelectorAll('form.sed-toolbar[data-toolbar-auto]').forEach(function (form) {
            var fragmentSel = form.getAttribute('data-fragment-target');
            var search = form.querySelector('input[name="q"]');
            var dates = form.querySelectorAll('input[name="date_from"], input[name="date_to"]');
            var filter = form.querySelector('select[name="filter"]');
            var debounceMs = 400;
            var timer;

            form.addEventListener('submit', function (e) {
                if (!fragmentSel) {
                    return;
                }
                e.preventDefault();
                loadListFragment(buildUrlFromForm(form).toString(), fragmentSel).catch(function () {});
            });

            function submitForm() {
                if (fragmentSel) {
                    loadListFragment(buildUrlFromForm(form).toString(), fragmentSel).catch(function () {});
                    return;
                }
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            }

            function scheduleSubmit() {
                clearTimeout(timer);
                timer = setTimeout(function () {
                    timer = null;
                    submitForm();
                }, debounceMs);
            }

            if (search) {
                search.addEventListener('input', scheduleSubmit);
                search.addEventListener('change', submitForm);
                search.addEventListener('blur', function () {
                    if (timer) {
                        clearTimeout(timer);
                        timer = null;
                        submitForm();
                    }
                });
            }

            dates.forEach(function (el) {
                el.addEventListener('change', submitForm);
            });

            if (filter) {
                filter.addEventListener('change', submitForm);
            }
        });
    })();
    </script>
</body>
</html>
