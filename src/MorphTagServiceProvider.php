<?php

namespace WalkerChiu\MorphTag;

use Illuminate\Support\ServiceProvider;

class MorphTagServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfig();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config files
        $this->publishes([
           __DIR__ .'/config/morph-tag.php' => config_path('wk-morph-tag.php'),
        ], 'config');

        // Publish migration files
        $from = __DIR__ .'/database/migrations/';
        $to   = database_path('migrations') .'/';
        $this->publishes([
            $from .'create_wk_morph_tag_table.php'
                => $to .date('Y_m_d_His', time()) .'_create_wk_morph_tag_table.php',
        ], 'migrations');

        $this->loadTranslationsFrom(__DIR__.'/translations', 'php-morph-tag');
        $this->publishes([
            __DIR__.'/translations' => resource_path('lang/vendor/php-morph-tag'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                config('wk-morph-tag.command.cleaner')
            ]);
        }

        config('wk-core.class.morph-tag.tag')::observe(config('wk-core.class.morph-tag.tagObserver'));
        config('wk-core.class.morph-tag.tagLang')::observe(config('wk-core.class.morph-tag.tagLangObserver'));
    }

    /**
     * Merges user's and package's configs.
     *
     * @return void
     */
    private function mergeConfig()
    {
        if (!config()->has('wk-morph-tag')) {
            $this->mergeConfigFrom(
                __DIR__ .'/config/morph-tag.php', 'wk-morph-tag'
            );
        }

        $this->mergeConfigFrom(
            __DIR__ .'/config/morph-tag.php', 'morph-tag'
        );
    }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param String  $path
     * @param String  $key
     * @return void
     */
    protected function mergeConfigFrom($path, $key)
    {
        if (
            !(
                $this->app instanceof CachesConfiguration
                && $this->app->configurationIsCached()
            )
        ) {
            $config = $this->app->make('config');
            $content = $config->get($key, []);

            $config->set($key, array_merge(
                require $path, $content
            ));
        }
    }
}
