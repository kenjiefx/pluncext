<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script type="text/javascript" src="https://cdn.jsdelivr.net/gh/kenjiefx/plunc/dist/plunc.0.8.4.min.js"></script>
        <title><?php page_title(); ?></title>
        <?php template_assets(); ?>
    </head>
    <body>
        <app plunc-app="app" class="width-24 height-24"></app>
        <template plunc-name="app">
            <main plunc-component="App"></main>
        </template>
        <template plunc-name="App">
            <?php template_content(); ?>
        </template>
    </body>
</html>