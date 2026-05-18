@php
    $viewerId = $viewerId ?? 'sed-pdf-viewer';
@endphp
<div id="{{ $viewerId }}" class="sed-pdf-viewer" data-pdf-url="{{ $previewUrl }}">
    <div class="sed-pdf-toolbar">
        <button type="button" class="sed-btn sed-btn--sm sed-btn--ghost" data-pdf-prev disabled>← Предыдущая</button>
        <span class="sed-pdf-page-num"><span data-pdf-current>1</span> / <span data-pdf-total>—</span></span>
        <button type="button" class="sed-btn sed-btn--sm sed-btn--ghost" data-pdf-next disabled>Следующая →</button>
    </div>
    <div class="sed-pdf-canvas-wrap">
        <canvas class="sed-pdf-canvas" data-pdf-canvas></canvas>
    </div>
    <p class="sed-pdf-error" style="margin:0;padding:0.75rem 1rem;display:none;color:#a33;" data-pdf-error></p>
</div>
<script type="module">
(async () => {
    const root = document.getElementById(@json($viewerId));
    if (!root) return;
    const url = root.dataset.pdfUrl;
    const canvas = root.querySelector('[data-pdf-canvas]');
    const errEl = root.querySelector('[data-pdf-error]');
    const btnPrev = root.querySelector('[data-pdf-prev]');
    const btnNext = root.querySelector('[data-pdf-next]');
    const elCur = root.querySelector('[data-pdf-current]');
    const elTot = root.querySelector('[data-pdf-total]');
    const wrap = root.querySelector('.sed-pdf-canvas-wrap');
    if (!url || !canvas) return;

    const showErr = (msg) => {
        errEl.textContent = msg;
        errEl.style.display = 'block';
    };

    try {
        const pdfjsLib = await import('https://cdn.jsdelivr.net/npm/pdfjs-dist@4.10.38/build/pdf.min.mjs');
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@4.10.38/build/pdf.worker.min.mjs';

        const loadingTask = pdfjsLib.getDocument({ url, withCredentials: true });
        const pdf = await loadingTask.promise;
        const numPages = pdf.numPages;
        elTot.textContent = String(numPages);

        let pageNum = 1;
        const ctx = canvas.getContext('2d');

        const renderPage = async () => {
            const page = await pdf.getPage(pageNum);
            const base = page.getViewport({ scale: 1 });
            const maxW = Math.max(200, (wrap?.clientWidth ?? 800) - 24);
            const scale = Math.min(maxW / base.width, 2.25);
            const viewport = page.getViewport({ scale });
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            await page.render({ canvasContext: ctx, viewport }).promise;
            elCur.textContent = String(pageNum);
            btnPrev.disabled = pageNum <= 1;
            btnNext.disabled = pageNum >= numPages;
        };

        btnPrev.addEventListener('click', () => {
            if (pageNum <= 1) return;
            pageNum -= 1;
            renderPage();
        });
        btnNext.addEventListener('click', () => {
            if (pageNum >= numPages) return;
            pageNum += 1;
            renderPage();
        });
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => void renderPage(), 150);
        });

        await renderPage();
    } catch (e) {
        console.error(e);
        showErr('Не удалось загрузить PDF для предпросмотра. Попробуйте скачать файл.');
    }
})();
</script>

