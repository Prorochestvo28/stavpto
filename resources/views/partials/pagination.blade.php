<div class="sed-pagination" role="navigation" aria-label="Пагинация">
    <span>Показано {{ $from ?? '1' }}–{{ $to ?? '5' }} из {{ $total ?? '24' }}</span>
    <div class="sed-pagination__pages">
        <a class="sed-page-link" href="#" aria-disabled="true" onclick="return false;">«</a>
        <a class="sed-page-link sed-page-link--current" href="#" onclick="return false;">1</a>
        <a class="sed-page-link" href="#" onclick="return false;">2</a>
        <a class="sed-page-link" href="#" onclick="return false;">3</a>
        <a class="sed-page-link" href="#" onclick="return false;">»</a>
    </div>
</div>
