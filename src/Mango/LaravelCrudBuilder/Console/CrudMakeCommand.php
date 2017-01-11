<?php namespace Mango\LaravelCrudBuilder\Console;

use File;
use Illuminate\Console\Command;

class CrudMakeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mango:views
                            {model : The model name. (plural)}';

    protected $description = 'Create CRUD views';

    protected $crudName;
    protected $crudNameCap;
    protected $crudNameSingular;
    protected $crudNamePlural;
    protected $modelName;
    protected $viewName;
    protected $path;

    protected $modelFields = array();
    /**
     *  HTML fields type by MySQL type.
     *
     * @var array
     */
    protected $htmlFields = [
        'string' => 'text',
        'char' => 'text',
        'varchar' => 'text',
        'text' => 'textarea',
        'mediumtext' => 'textarea',
        'longtext' => 'textarea',
        'json' => 'textarea',
        'jsonb' => 'textarea',
        'binary' => 'textarea',
        'password' => 'password',
        'email' => 'email',
        'number' => 'number',
        'integer' => 'number',
        'bigint' => 'number',
        'mediumint' => 'number',
        'tinyint' => 'number',
        'smallint' => 'number',
        'decimal' => 'number',
        'double' => 'number',
        'float' => 'number',
        'date' => 'date',
        'datetime' => 'datetime-local',
        'timestamp' => 'datetime-local',
        'time' => 'time',
        'boolean' => 'radio',
        'enum' => 'select',
        'hidden' => 'hidden'
    ];
    protected $crudNameCapSingular;
    private $crudBuilder;

    public function __construct(CrudBuilder $crudBuilder)
    {
        parent::__construct();
        $this->crudBuilder = $crudBuilder;
    }

    public function handle()
    {
        $this->setConfig();

        $this->getFields();

        $this->mountViews();

    }

    protected function setConfig()
    {
        $this->crudName = strtolower($this->argument('model'));
        $this->crudNameCap = ucwords($this->crudName);
        $this->crudNameCapSingular = str_singular($this->crudNameCap);
        $this->crudNameSingular = str_singular($this->crudName);
        $this->crudNamePlural = str_plural($this->crudName);
        $this->modelName = str_singular($this->argument('model'));
        $this->viewName = snake_case($this->argument('model'), '-');
        $this->tableName = snake_case($this->argument('model'), '-');

        $this->path = config('view.paths')[0] . '/' . $this->viewName . '/';

        if (!File::isDirectory($this->path)) {
            File::makeDirectory($this->path, 0755, true);
        }
    }

    protected function getFields()
    {
        $query = 'SELECT COLUMN_NAME, DATA_TYPE, COLUMN_KEY FROM information_schema.columns WHERE TABLE_NAME="' . strtolower($this->crudNamePlural) . '" AND TABLE_SCHEMA="' . env('DB_DATABASE') . '";';
        $columns = \DB::select(\DB::raw($query));

        if (!count($columns)) {
            $this->output->error('This model/table has no fields.');
            die;
        }

        foreach ($columns as $c) {
            if ($c->DATA_TYPE != 'timestamp') {
                $data = array(
                    'name' => $c->COLUMN_NAME,
                    'type' => $c->DATA_TYPE,
                    'pk' => ($c->COLUMN_KEY == 'PRI') ? true : false,
                );
                array_push($this->modelFields, $this->getColumnType($data));
            }
        }
    }

    protected function getColumnType($data)
    {
        if ($data['type'] == 'int' && $data['pk']) {
            $data['html'] = 'hidden';
        } else {
            $data['html'] = (isset($this->htmlFields[$data['type']])) ? $this->htmlFields[$data['type']] : 'text';
        }

        return $data;
    }

    protected function mountViews()
    {
        if ($this->output->confirm('Create index view?')) {
            $this->createIndexView();
        }

        if ($this->output->confirm('Create show view?')) {
            $this->createShowView();
        }

        if ($this->output->confirm('Create edit view?')) {
            $this->createEditView();
        }

        if ($this->output->confirm('Create create view?')) {
            $this->createCreateView();
        }

        $this->output->success("All views created at {$this->path}");
    }

    protected function createShowView()
    {
        $fields = '';
        foreach ($this->modelFields as $f) {
            $fields .= "<tr>\n";
            $fields .= "<td><strong>{$f['name']}</strong></td>\n";
            $fields .= '<td>{{$' . $this->crudNameSingular . '->' . $f['name'] . '}}</td>'. "\n";
            $fields .= "</tr>\n";
        }

        $view = $this->stubs()['show'];

        $newFile = $this->path . 'show.blade.php';
        if (!File::copy($view, $newFile)) {
            $this->output->error("failed to copy {$view}");
        } else {
            File::put($newFile, str_replace('%%pluralVar%%', $this->crudNamePlural, File::get($newFile)));
            File::put($newFile, str_replace('%%fields%%', $fields, File::get($newFile)));
        }
    }

    protected function createCreateView()
    {
        $fields = '';
        foreach ($this->modelFields as $f) {
            $fields .= $this->mountField($f['html'], $f['name']);
        }

        $view = $this->stubs()['create'];

        $newFile = $this->path . 'create.blade.php';
        if (!File::copy($view, $newFile)) {
            $this->output->error("failed to copy {$view}");
        } else {
            File::put($newFile, str_replace('%%pluralVar%%', $this->crudNamePlural, File::get($newFile)));
            File::put($newFile, str_replace('%%crudNameCapSingular%%', $this->crudNameCapSingular, File::get($newFile)));
            File::put($newFile, str_replace('%%fields%%', $fields, File::get($newFile)));
        }
    }

    private function mountField($type, $name, $pk = false)
    {
        $file = __DIR__ . '/stubs/fields/' . $type . '.blade.php.stub';
        $string = str_replace('%%name%%', $name, File::get($file));
        $string = str_replace('%%label%%', ucwords($name), $string);

        $value = 'null';
        if ($pk) {
            $value = '$' . $this->crudNameSingular . '->' . $name;
        }
        $string = str_replace('%%value%%', $value, $string);


        $string = $string . "\n";

        return $string;
    }

    protected function stubs()
    {
        return array(
            'edit' => __DIR__ . '/stubs/views/form.blade.php.stub',
            'create' => __DIR__ . '/stubs/views/create.blade.php.stub',
            'index' => __DIR__ . '/stubs/views/index.blade.php.stub',
            'show' => __DIR__ . '/stubs/views/show.blade.php.stub',
        );
    }

    protected function createEditView()
    {
        $fields = '';
        foreach ($this->modelFields as $f) {
            $fields .= $this->mountField($f['html'], $f['name'], '$' . $this->crudNamePlural . '->' . $f['name'] . '', $f['pk']);
        }

        $view = $this->stubs()['edit'];

        $newFile = $this->path . 'edit.blade.php';
        if (!File::copy($view, $newFile)) {
            $this->output->error("failed to copy {$view}");
        } else {
            File::put($newFile, str_replace('%%pluralVar%%', $this->crudNamePlural, File::get($newFile)));
            File::put($newFile, str_replace('%%varNameSingular%%', $this->crudNameSingular, File::get($newFile)));
            File::put($newFile, str_replace('%%crudNameCapSingular%%', $this->crudNameCapSingular, File::get($newFile)));
            File::put($newFile, str_replace('%%fields%%', $fields, File::get($newFile)));
        }
    }

    protected function createIndexView()
    {
        $header = '';
        $content = '';

        foreach ($this->modelFields as $f) {
            $header .= "<th>{$f['name']}</th>\n";
            $content .= '<td>{{$' . $this->crudNameSingular . '->' . $f['name'] . '}}</td>' . "\n";
        }

        $view = $this->stubs()['index'];

        $newFile = $this->path . 'index.blade.php';
        if (!File::copy($view, $newFile)) {
            $this->output->error("failed to copy {$view}");
        } else {
            File::put($newFile, str_replace('%%varName%%', $this->crudNamePlural, File::get($newFile)));
            File::put($newFile, str_replace('%%pluralVar%%', $this->crudNamePlural, File::get($newFile)));
            File::put($newFile, str_replace('%%varNameSingular%%', $this->crudNameSingular, File::get($newFile)));
            File::put($newFile, str_replace('%%fieldsTitle%%', $header, File::get($newFile)));
            File::put($newFile, str_replace('%%fieldsColumn%%', $content, File::get($newFile)));
        }
    }

    protected function getArguments()
    {
        return array(
            array('model', InputArgument::REQUIRED, 'Model name')
        );
    }

    protected function getNameInput()
    {
        return str_replace('/', '\\', $this->argument('name'));
    }
}