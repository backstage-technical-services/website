<?php

namespace Package\WebDevTools\Laravel\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::directive('HelpDoc', function ($path) {
            return "<?php echo \Markdown::convertToHtml(file_get_contents(base_path('resources/documentation/' . str_replace('.', '/', {$path}) . '.md'))); ?>";
        });
        Blade::directive('InputClass', function ($name) {
            return "<?php echo isset(\$errors) && \$errors->any() ? (\$errors->default->has({$name}) ? 'has-error' : 'has-success') : ''; ?>";
        });
        Blade::directive('InputError', function ($name) {
            return "<?php echo isset(\$errors) && \$errors->any() && \$errors->default->has({$name}) ? ('<div class=\"invalid-feedback help-block\">' . \$errors->default->first({$name}) . '</div>'): ''; ?>";
        });
        Blade::directive('Paginator', function ($arguments) {
            [$name, $style] = array_pad($this->getDirectiveArguments($arguments), 2, null);
            return "<?php echo get_class({$name}) == 'Illuminate\Pagination\LengthAwarePaginator' ? {$name}->render('pagination::" . ($style ?: 'default') . "') : ''; ?>";
        });
        Blade::directive('ContentWidth', function () {
            return "<?php echo isset(\$__env) && !empty(trim(\$__env->yieldContent('content-width'))) ? ('w-' . \$__env->yieldContent('content-width')) : ''; ?>";
        });
        Blade::directive('Script', function ($arguments) {
            $arguments = $this->getDirectiveArguments($arguments);
            [$url, $mix] = array_pad($arguments, 2, false);
            return "<?php echo '<script src=\"" . asset($mix ? mix($url) : $url) . "\"></script>'; ?>";
        });
        Blade::directive('Stylesheet', function ($arguments) {
            $arguments = $this->getDirectiveArguments($arguments);
            [$url, $mix] = array_pad($arguments, 2, false);
            return "<?php echo '<link rel=\"stylesheet\" href=\"" . asset($mix ? mix($url) : $url) . "\">'; ?>";
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Convert a string of arguments to an array.
     *
     * @param string $arguments
     *
     * @return array
     */
    private function getDirectiveArguments($arguments)
    {
        return explode(',', str_replace(['(', ')', ' ', "'"], '', $arguments));
    }
}
