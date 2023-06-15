<?php

namespace Tki\Providers;

use Tki\Helpers\LocalisationScript;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Symfony\Component\Finder\SplFileInfo;

class LocalisationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->resolved('blade.compiler')) {
            $this->registerDirective($this->app['blade.compiler']);
        } else {
            $this->app->afterResolving('blade.compiler', function (BladeCompiler $bladeCompiler) {
                $this->registerDirective($bladeCompiler);
            });
        }
    }

    protected function registerDirective(BladeCompiler $bladeCompiler)
    {
        $bladeCompiler->directive('localisation', function () {
            $translation = $this->translations(app()->getLocale());
            $output = new LocalisationScript($translation);

            return (string) $output;
        });
    }

    protected function translations(string $locale): array
    {
        $translationFiles = File::files(base_path("resources/lang/{$locale}"));

        return collect($translationFiles)
            ->map(function (SplFileInfo $file) {
                $ext = $file->getExtension();
                if ($ext === 'json') {
                    $data = json_decode(File::get($file));
                } else if ($ext === 'php') {
                    $data = require($file);
                } else {
                    $data = [];
                }

                return [$file->getFilenameWithoutExtension() => $data];
            })
            ->collapse()
            ->toArray();
    }
}
