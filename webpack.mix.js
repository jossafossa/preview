const mix = require('laravel-mix');

mix.sass('style.scss', 'style.css')
.browserSync({
    proxy: 'http://www.preview.test',
    host: "www.preview.test",
    port: 3000,
    open: 'external',
    files: [
        'style.css',
        'index.html'
    ]
});