<?php if ( !empty($success) ) : ?>

<div class="container alert alert-success alert-dismissible fade show text-center" role="alert">
    <?php 
    foreach ($success as $s) {
        echo "$s<br>";
    } 
    ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<?php endif ?>