const {mix} = require('laravel-mix');

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

mix
// Copy vendor files
    .copy('vendor/components/jquery/jquery.min.js', 'resources/assets/js/vendors/jquery.js')
    .copy('vendor/twbs/bootstrap/dist/js/bootstrap.min.js', 'resources/assets/js/vendors/bootstrap.js')
    .copy('vendor/twbs/bootstrap/dist/css/bootstrap.min.css', 'resources/assets/css/vendors/bootstrap.css')
    .copy('vendor/twbs/bootstrap/dist/css/bootstrap-theme.min.css', 'resources/assets/css/vendors/bootstrap-theme.css')
    .copy('vendor/moment/moment/min/moment.min.js', 'resources/assets/js/vendors/moment.js')
    .copy('vendor/select2/select2/dist/js/select2.min.js', 'resources/assets/js/vendors/select2.js')
    .copy('vendor/select2/select2/dist/css/select2.min.css', 'resources/assets/css/vendors/select2.css')
    .copy('vendor/eonasdan/bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js', 'resources/assets/js/vendors/datetimepicker.js')
    .copy('vendor/eonasdan/bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css', 'resources/assets/css/vendors/datetimepicker.css')
    // .copy('vendor/fortawesome/font-awesome/scss', 'resources/assets/sass/vendors/font-awesome')
    // .sass('resources/assets/sass/vendors/font-awesome/font-awesome.scss', '../resources/assets/css/vendors/font-awesome.css')
    // .copy('node_modules/lightbox2/src/css/lightbox.css', 'resources/assets/css/vendors/lightbox.css')
    .copy('node_modules/lightbox2/dist/images', 'public/images/lightbox')

    // Combine the vendor files
    .combine([
        'resources/assets/js/vendors/jquery.js',
        'resources/assets/js/vendors/moment.js',
        'resources/assets/js/vendors/bootstrap.js',
        'resources/assets/js/vendors/bootstrap-markdown.js',
        'node_modules/simplemde/dist/simplemde.min.js',
        'node_modules/js-cookie/src/js.cookie.js',
        'node_modules/lightbox2/dist/js/lightbox.min.js',
        'node_modules/clipboard/dist/clipboard.min.js',
        'node_modules/js-modals/dist/modal.min.js',
        'resources/assets/js/vendors/datetimepicker.js',
        'vendor/bnjns/laravel-searchtools/src/resources/assets/js/search_tools.min.js',
        'vendor/bnjns/laravel-notifications/src/resources/assets/js/notifications.min.js',
        'resources/assets/js/app/DatetimePicker.js',
        'vendor/bnjns/web-dev-tools/js/plugins/ApplySelect2/enable.js',
        'vendor/bnjns/web-dev-tools/js/plugins/CookieAcceptance/enable.js',
        'vendor/bnjns/web-dev-tools/js/plugins/DisableButtonOnClick/enable.js',
        'vendor/bnjns/web-dev-tools/js/plugins/EditableFields/enable.js',
        'vendor/bnjns/web-dev-tools/js/plugins/OtherInput/enable.js',
        'vendor/bnjns/web-dev-tools/js/plugins/SimpleMDE/enable.js',
        'vendor/bnjns/web-dev-tools/js/plugins/ToggleVisibility/enable.js',
    ], 'public/js/vendors.js')
    .combine([
        'resources/assets/css/reset.css',
        'resources/assets/css/vendors/bootstrap.css',
        'resources/assets/css/vendors/bootstrap-theme.css',
        'node_modules/simplemde/dist/simplemde.min.css',
        'resources/assets/css/vendors/select2.css',
        'node_modules/select2-bootstrap-theme/dist/select2-bootstrap.min.css',
        'resources/assets/css/vendors/font-awesome.css',
        'resources/assets/css/vendors/datetimepicker.css',
        'resources/assets/css/vendors/lightbox.css',
        'vendor/bnjns/laravel-notifications/src/resources/assets/css/notifications.min.css',
    ], 'public/css/vendors.css')

    // Process SCSS files
    .sass('resources/assets/sass/structure/main.scss', 'public/css/structure.main.css')
    .sass('resources/assets/sass/structure/error.scss', 'public/css/structure.error.css')
    .sass('resources/assets/sass/general/general.scss', 'public/css/general.css')
    .sass('resources/assets/sass/partials/partials.scss', 'public/css/content.css')

    // Process JS files
    .combine([
        'vendor/bnjns/web-dev-tools/js/misc.js',
        'vendor/bnjns/web-dev-tools/js/ajax.js',
        'resources/assets/js/app/ajax_toggle.js',
        'resources/assets/js/app/clipboard.js',
        'resources/assets/js/app/cookies.js',
        'vendor/bnjns/web-dev-tools/js/forms.js',
        'vendor/bnjns/web-dev-tools/js/modals.js',
        'vendor/bnjns/web-dev-tools/js/plugins/ApplyDatetimePicker/auto-init.js',
        'vendor/bnjns/web-dev-tools/js/plugins/ApplySelect2/auto-init.js',
        'vendor/bnjns/web-dev-tools/js/plugins/DisableButtonOnClick/auto-init.js',
        'vendor/bnjns/web-dev-tools/js/plugins/EditableFields/auto-init.js',
        'vendor/bnjns/web-dev-tools/js/plugins/OtherInput/auto-init.js',
        'vendor/bnjns/web-dev-tools/js/plugins/SimpleMDE/auto-init.js',
        'vendor/bnjns/web-dev-tools/js/plugins/ToggleVisibility/auto-init.js',
        'node_modules/js-tabify/dist/tabify.min.js',
    ], 'public/js/app.js')
    .js('resources/assets/js/partials/events.view.js', 'public/js/partials/events.view.js')
    .js('resources/assets/js/partials/events.diary.js', 'public/js/partials/events.diary.js')
    .js('resources/assets/js/partials/resources.index.js', 'public/js/partials/resources.index.js')
    .js('resources/assets/js/partials/resources.form.js', 'public/js/partials/resources.form.js');