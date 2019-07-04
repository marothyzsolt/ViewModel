<?php


namespace MarothyZsolt\ViewModel\Console;


use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class ViewModelComponentMakeCommand extends GeneratorCommand
{
    protected $name = 'make:view-model-component';
    protected $description = 'Create a new ViewModel Component class';
    protected $type = 'Component';

    public function handle()
    {
        if (parent::handle() === false) {
            if (! $this->option('force')) {
                return;
            }
        }
    }
    protected function getStub()
    {
        return __DIR__.'/../../stubs/DummyViewModelComponent.stub';
    }
    protected function getDefaultNamespace($rootNamespace)
    {
        if ($this->isCustomNamespace()) {
            return $rootNamespace;
        }
        if($this->option('base'))
            return $rootNamespace.'\ViewModels\Components\Base';
        return $rootNamespace.'\ViewModels\Components';
    }
    protected function getOptions(): array
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the view-model already exists'],
        ];
    }
    protected function isCustomNamespace(): bool
    {
        return Str::contains($this->argument('name'), '/');
    }
}
