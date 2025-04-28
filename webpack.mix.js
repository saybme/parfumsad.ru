const mix = require('laravel-mix');

mix.options({
    processCssUrls: false,
})

mix
mix.sass('./src/css/app.scss', './www/themes/tmp/assets/css')
.js('./src/js/app.js', './www/themes/tmp/assets/js').sourceMaps()
.postCss('./src/css/main.css', './www/themes/tmp/assets/css',[
    require('tailwindcss'),
]);

// mix.webpackConfig({
//     stats: {
//         children: true,
//     },
// });

//mix.copyDirectory('./src/fonts', './www/themes/tmp/assets/fonts');