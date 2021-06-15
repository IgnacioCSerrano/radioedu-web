<?php
$start = max(3, (intval( ($page - 1) / Constants::PAG_INTERV ) * Constants::PAG_INTERV) + 1);
$end = min($totaPages - 1, $start + Constants::PAG_INTERV);
?>

<nav aria-label="Paginación">
    <ul class="pagination d-flex justify-content-center">
        <?php if ($page > 1) : ?>
            <li class="page-item mr-3 <?= $page == 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $page == 1 ? '#' : $baseUrl . '&' . Constants::PARAM_PAGE . '=' . $page - 1 ?>" aria-label="Anterior">
                    <span aria-hidden="true">&laquo;</span>
                    <span class="sr-only">Anterior</span>
                </a>
            </li>
        <?php endif; ?>

        <li class="page-item <?= $page == 1 ? 'active' : '' ?>"><a href="<?= $baseUrl ?>" class="page-link">1</a></li>

        <?php if ($page > Constants::PAG_INTERV) : ?>
            <li class="page-item mx-2 pt-1">&hellip; </li>
        <?php endif; ?>
        
        <?php
            for ($i = $start - 1; $i <= $end; $i++) {
                echo '<li class="page-item ' . ($i == $page 
                    ? 'active' : '') . '"><a class="page-link" href="' . $baseUrl . '&' . Constants::PARAM_PAGE . '=' . $i . '">' . $i . '</a></li>';
            }
        ?>

        <?php if ($page < ( floor($totaPages / Constants::PAG_INTERV) * (Constants::PAG_INTERV) + 1) ) : ?>
            <li class="page-item mx-2 pt-1">&hellip;</li>
        <?php endif; ?>

        <li class="page-item <?= $page == $totaPages ? 'active' : '' ?>"><a href="<?= $baseUrl . '&' . Constants::PARAM_PAGE . '=' . $totaPages ?>" class="page-link"><?= $totaPages ?></a></li>
        
        <?php if ($page < $totaPages) : ?>
            <li class="page-item ml-3 <?= $page == $totaPages ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $page == $totaPages ? '#' : $baseUrl . '&' . Constants::PARAM_PAGE . '=' . $page + 1 ?>" aria-label="Siguiente">
                    <span aria-hidden="true">&raquo;</span>
                    <span class="sr-only">Siguiente</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>