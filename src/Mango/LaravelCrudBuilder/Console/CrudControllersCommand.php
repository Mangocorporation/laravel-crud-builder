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

    private $crudBuilder;

    public function __construct(CrudBuilder $crudBuilder)
    {
        parent::__construct();
        $this->crudBuilder = $crudBuilder;
    }

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

        if ($this->crudBuilder->copyFile($file, $newFile)) {
            $this->output->error("failed to create {$file}");
            die;
        }
    }

    protected function replaceVars()
    {
        $vars = array('%%openPHP%%', '%%modelName%%', '%%controllerName%%', '%%controllerNameLower%%',
            '%%varNamePlural%%', '%%varNameSingular%%');

        $contents = array('<?php', $this->modelName, $this->controllerName, $this->controllerNameLower,
            $this->varNamePlural, $this->varNameSingular);

        $this->crudBuilder->replaceVars($vars, $contents, $this->path);
    }

    protected function getArguments()
    {
        return array(array('model', InputArgument::REQUIRED, 'Model name'));
    }
}