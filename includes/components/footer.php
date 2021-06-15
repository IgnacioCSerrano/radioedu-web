    </div>

    <?php if ( isset($_SESSION['user']) ) : ?>

    <div class="footer">
        <p>Radio Educativa de la Consejería de Educación y Empleo de la Junta de Extremadura &copy; <?= date('Y'); ?></p>
    </div>

    <?php endif ?>

    <div id="bootstrapCssTest" class="collapse"></div>
    
    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <!-- jQuery fallback -->
    <script>
        window.jQuery || document.write('<?= '<script src="' . ROOT_FOLDER . '/static/js/lib/jquery-3.5.1.min.js"><\/script>' ?>');
    </script>
    <!--  Bootstrap JavaScript Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script>
    <!-- Bootstrap JavaScript Bundle fallback -->
    <script>
        typeof($.fn.modal) === 'undefined' && document.write('<?= '<script src="'. ROOT_FOLDER . '/static/bootstrap-4.6.0-dist/js/bootstrap.bundle.js"><\/script>' ?>');
    </script>
    <!-- Script de código JavaScript propio -->
    <script src="<?= ROOT_FOLDER . '/static/js/main.js' ?>"></script>
</body>

</html>