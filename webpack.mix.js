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
    .options({autoprefixer: false})
    // Copy vendor files
    // .copy('vendor/fortawesome/font-awesome/scss', 'resources/assets/sass/vendors/font-awesome')
    // .sass('resources/assets/sass/vendors/font-awesome/font-awesome.scss', '../resources/assets/css/vendors/font-awesome.css')
    // .copy('node_modules/lightbox2/src/css/lightbox.css', 'resources/assets/css/vendors/lightbox.css')
    .copy('node_modules/lightbox2/dist/images', 'public/images/lightbox')

    // Combine the vendor files
    .combine([
        'node_modules/jquery/dist/jquery.min.js',
        'node_modules/moment/min/moment-with-locales.min.js',
        'node_modules/bootstrap/dist/js/bootstrap.min.js',
        'node_modules/bootstrap-markdown/js/bootstrap-markdown.js',
        'node_modules/select2/dist/js/select2.full.min.js',
        'node_modules/simplemde/dist/simplemde.min.js',
        'node_modules/js-cookie/src/js.cookie.js',
        'node_modules/lightbox2/dist/js/lightbox.min.js',
        'node_modules/clipboard/dist/clipboard.min.js',
        'node_modules/js-modals/dist/modal.min.js',
        'vendor/eonasdan/bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',
        'packages/SearchTools/resources/assets/js/search_tools.min.js',
        'packages/Notifications/resources/assets/js/notifications.min.js',
        'resources/assets/js/app/DatetimePicker.js',
        'packages/WebDevTools/assets/js/plugins/ApplySelect2/enable.js',
        'packages/WebDevTools/assets/js/plugins/CookieAcceptance/enable.js',
        'packages/WebDevTools/assets/js/plugins/DisableButtonOnClick/enable.js',
        'packages/WebDevTools/assets/js/plugins/EditableFields/enable.js',
        'packages/WebDevTools/assets/js/plugins/OtherInput/enable.js',
        'packages/WebDevTools/assets/js/plugins/SimpleMDE/enable.js',
        'packages/WebDevTools/assets/js/plugins/ToggleVisibility/enable.js',
    ], 'public/js/vendors.js')
    .combine([
        'resources/assets/css/reset.css',
        'node_modules/bootstrap/dist/css/bootstrap.min.css',
        'node_modules/bootstrap/dist/css/bootstrap-theme.min.css',
        'node_modules/simplemde/dist/simplemde.min.css',
        'node_modules/select2/dist/css/select2.min.css',
        'node_modules/select2-bootstrap-theme/dist/select2-bootstrap.min.css',
        'resources/assets/css/vendors/font-awesome.css',
        'vendor/eonasdan/bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css',
        'resources/assets/css/vendors/lightbox.css',
        'packages/Notifications/resources/assets/css/notifications.min.css',
    ], 'public/css/vendors.css')

    // Process SCSS files
    .sass('resources/assets/sass/structure/main.scss', 'public/css/structure.main.css')
    .sass('resources/assets/sass/structure/error.scss', 'public/css/structure.error.css')
    .sass('resources/assets/sass/general/general.scss', 'public/css/general.css')
    .sass('resources/assets/sass/partials/partials.scss', 'public/css/content.css')

    // Process JS files
    .combine([
        'packages/WebDevTools/assets/js/misc.js',
        'packages/WebDevTools/assets/js/ajax.js',
        'resources/assets/js/app/ajax_toggle.js',
        'resources/assets/js/app/clipboard.js',
        'resources/assets/js/app/cookies.js',
        'packages/WebDevTools/assets/js/forms.js',
        'packages/WebDevTools/assets/js/modals.js',
        'packages/WebDevTools/assets/js/plugins/ApplyDatetimePicker/auto-init.js',
        'packages/WebDevTools/assets/js/plugins/ApplySelect2/auto-init.js',
        'packages/WebDevTools/assets/js/plugins/DisableButtonOnClick/auto-init.js',
        'packages/WebDevTools/assets/js/plugins/EditableFields/auto-init.js',
        'packages/WebDevTools/assets/js/plugins/OtherInput/auto-init.js',
        'packages/WebDevTools/assets/js/plugins/SimpleMDE/auto-init.js',
        'packages/WebDevTools/assets/js/plugins/ToggleVisibility/auto-init.js',
        'node_modules/js-tabify/dist/tabify.min.js',
    ], 'public/js/app.js')
    .js('resources/assets/js/partials/events.view.js', 'public/js/partials/events.view.js')
    .js('resources/assets/js/partials/events.diary.js', 'public/js/partials/events.diary.js')
    .js('resources/assets/js/partials/resources.index.js', 'public/js/partials/resources.index.js')
    .js('resources/assets/js/partials/resources.form.js', 'public/js/partials/resources.form.js');