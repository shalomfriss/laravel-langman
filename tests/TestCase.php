<?php

abstract class TestCase extends Orchestra\Testbench\TestCase
{
    protected $consoleOutput;

    protected function getPackageProviders($app)
    {
        return [\Themsaid\Langman\LangmanServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('langman.path', __DIR__.'/temp');
        $app['config']->set('view.paths', [__DIR__.'/views_temp']);
    }

    public function setUp() : void
    {
        parent::setUp();
        $this->withoutMockingConsoleOutput();

        exec('rm -rf '.__DIR__.'/temp/*');
        exec('rm -rf '.__DIR__.'/views_temp/*');
    }

    public function tearDown() : void
    {
        parent::tearDown();

        exec('rm -rf '.__DIR__.'/views_temp/*');

        $this->consoleOutput = '';
    }

    public function createTempFiles($files = [])
    {
        foreach ($files as $dir => $dirFiles) {
            mkdir(__DIR__.'/temp/'.$dir);

            foreach ($dirFiles as $file => $content) {
                if (is_array($content) && $file!== "-json") {
                    mkdir(__DIR__.'/temp/'.$dir.'/'.$file);

                    foreach ($content as $subDir => $subContent) {
                        mkdir(__DIR__.'/temp/vendor/'.$file.'/'.$subDir);
                        foreach ($subContent as $subFile => $subsubContent) {
                            file_put_contents(__DIR__.'/temp/'.$dir.'/'.$file.'/'.$subDir.'/'.$subFile.'.php', $subsubContent);
                        }
                    }
                } else {
                    if ($file == "-json") {
                        file_put_contents(__DIR__.'/temp/'.$dir.'.json', json_encode($content, JSON_PRETTY_PRINT));
                    } else {
                        file_put_contents(__DIR__.'/temp/'.$dir.'/'.$file.'.php', $content);
                    }
                }
            }
        }
    }

    public function resolveApplicationConsoleKernel($app)
    {
        $app->singleton('artisan', function ($app) {
            return new \Illuminate\Console\Application($app, $app['events'], $app->version());
        });

        $app->singleton('Illuminate\Contracts\Console\Kernel', Kernel::class);
    }

    public function artisan($command, $parameters = [])
    {
        parent::artisan($command, array_merge($parameters, ['--no-interaction' => true]));
    }

    public function consoleOutput()
    {
        return $this->consoleOutput ?: $this->consoleOutput = $this->app[Kernel::class]->output();
    }
}
