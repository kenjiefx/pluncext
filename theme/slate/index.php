<html>
    <head>
        <title><?php page_title(); ?></title>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/gh/kenjiefx/plunc/dist/plunc.0.8.4.min.js"></script>
        <?php template_assets(); ?>
    </head>
    <body>
        <app plunc-app="app" class="width-24 height-24"></app>
        <template plunc-name="app">
            <?php template_content(); ?>
        </template>
        <?php Kenjiefx\Pluncext\API\Component::export(); ?>
    </body>
</html>