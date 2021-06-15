<?php
require_once '../includes/classes/Constants.php';
require_once Constants::INC_UTIL_CLASS;

$util = new Util();
?>

<!-- “Colorlib Error 404 V20 Template” by rokr is licensed under CC BY 3.0 (https://colorlib.com/wp/template/colorlib-error-404-20/) -->

<?php $util->includeWithVariables(Constants::INC_HEADER, array('title' => 'Error 403')) ?>

<body>
    <div class="error-template">
        <div>
            <div>
                <h1>403</h1>
            </div>
            <h2>¡Acceso prohibido!</h2> 
            <p>Lo sentimos, no tiene permiso para acceder a esta página</p>
            <a href="<?= ROOT_FOLDER ?>">Volver a inicio</a>
        </div>
    </div>
</body>

</html>