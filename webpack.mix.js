const mix = require('laravel-mix');
const path = require('path'); // Added missing path import

mix.js('resources/js/admin.js', 'assets/js/admin.js')
   .vue({ version: 3 }) // Ensure Vue 3 compatibility
   .sass('resources/scss/admin/admin.scss', 'assets/css/admin.css')
//    .copy('resources/images', 'assets/images')
   .sourceMaps();

// Explicitly configure Webpack resolve.extensions and add alias for admin folder
mix.webpackConfig({
    resolve: {
        extensions: ['.js', '.vue', '.json'], // Updated extensions for better compatibility
        alias: {
            '@': path.resolve(__dirname, 'resources/js')
        }
    }
});