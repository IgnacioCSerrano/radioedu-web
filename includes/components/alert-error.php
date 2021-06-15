<?php if ( !empty($errors) ) : ?>

<div class="container alert alert-danger alert-dismissible fade show text-center" role="alert">
    <?php 
    foreach ($errors as $e) {
        echo "$e<br>";
    } 
    ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<?php endif ?>