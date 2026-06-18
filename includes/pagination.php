<?php if (($meta['pages'] ?? 1) > 1): ?>
<nav class="mt-3">
    <ul class="pagination justify-content-center mb-0">
        <li class="page-item <?= $meta['page'] <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $meta['page'] - 1 ?>">Previous</a>
        </li>
        <?php for ($i = 1; $i <= $meta['pages']; $i++): ?>
        <li class="page-item <?= $i === $meta['page'] ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?= $meta['page'] >= $meta['pages'] ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $meta['page'] + 1 ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>
