<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php page_title(); ?></title>
        <?php template_assets(); ?>
    </head>
    <body>
        <template plunc-name="app">
            <main plunc-component="App"></main>
        </template>
        <template plunc-name="App">
            <?php template_content(); ?>
        </template>
    </body>
</html>