<?php

namespace CHStudio\LaravelTransclude\Providers;

use CHStudio\LaravelTransclude\Compilers\TranscludeCompiler;
use Illuminate\Support\ServiceProvider;

class TranscludeServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $compiler = new TranscludeCompiler;
        $this->app['view']->share($compiler->name, $compiler);

        \Blade::directive('transclude', [$compiler, 'compileTransclude']);
        \Blade::directive('endtransclude', [$compiler, 'compileEndTransclude']);
        \Blade::directive('transcluded', [$compiler, 'compileTranscluded']);
    }
}
