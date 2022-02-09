const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js').version();
mix.js('resources/js/public/app.js', 'public/js/public.js').version();
mix.sass('resources/sass/app.scss', 'public/css').version();
mix.copy('resources/css/styles.css', 'public/css/styles.css').minify('public/css/styles.css').version();
mix.version([
    'public/icofont/icofont.min.css',
    'public/assets/logo.png',
    'public/assets/action-email.png',
    'public/assets/action-phone.png',
    'public/assets/action-whatsapp.png',
    'public/assets/contact-cellphone.png',
    'public/assets/contact-email.png',
    'public/assets/contact-phone.png',
    'public/assets/contact-phone1.png',
    'public/assets/contact-phone2.png',
    'public/assets/contact-web.png',
    'public/assets/social-facebook.png',
    'public/assets/social-instagram.png',
    'public/assets/social-linkedin.png',
    'public/assets/social-twitter.png',
    'public/assets/social-youtube.png',
]);
