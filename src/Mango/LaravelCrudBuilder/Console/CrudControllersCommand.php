<?php namespace Mango\LaravelCrudBuilder\Console;

use File;
use Illuminate\Console\Command;

class CrudControllersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mango:controller
                            {model : The model name. (plural)}';

    protected $description = 'Create Controller\'s CRUD';

    protected $modelName;
    protected $controllerName;
    protected $controllerNameLower;
    protected $varNamePlural;
    protected $varNameSingular;

    protected $path;

    public function handle()
    {
        $this->setConfig();

        $this->replaceVars();

        $this->output->success("Your {$this->controllerName} controller is created at {$this->path}");
    }

    protected function setConfig()
    {
        $this->modelName = $this->argument('model');
        $this->controllerName = str_plural($this->argument('model'));
        $this->controllerNameLower = strtolower(str_plural($this->argument('model')));
        $this->varNameSingular = strtolower($this->modelName);
        $this->varNamePlural = strtolower($this->controllerName);

        $this->path = app_path('Http/Controllers') . '/' . $this->controllerName . 'Controller.php';
        if (File::isFile($this->path)) {
            if ($this->output->confirm('This controller already exists. Do you want to overwrite it?')) {
                $this->createFile();
            } else {
                $this->output->note('Action canceled.');
                die;
            }
        } else {
            $this->createFile();
        }
    }

    protected function createFile()
    {
        $file = __DIR__ . '/stubs/controllers/controller.php.stub';
        $newFile = $this->path;

        if (!File::copy($file, $newFile)) {
            $this->output->error("failed to create {$file}");
            die;
        }
    }

    protected function replaceVars()
    {
        File::put($this->path, str_replace('%%openPHP%%', '<?php', File::get($this->path)));
        File::put($this->path, str_replace('%%modelName%%', $this->modelName, File::get($this->path)));
        File::put($this->path, str_replace('%%controllerName%%', $this->controllerName, File::get($this->path)));
        File::put($this->path, str_replace('%%controllerNameLower%%', $this->controllerNameLower, File::get($this->path)));
        File::put($this->path, str_replace('%%varNamePlural%%', $this->varNamePlural, File::get($this->path)));
        File::put($this->path, str_replace('%%varNameSingular%%', $this->varNameSingular, File::get($this->path)));
    }


}