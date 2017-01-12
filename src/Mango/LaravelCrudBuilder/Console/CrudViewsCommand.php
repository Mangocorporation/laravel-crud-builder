<?php namespace Mango\LaravelCrudBuilder\Console;

use File;
use Illuminate\Console\Command;

class CrudViewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mango:views
                            {model : The model name.}';

    protected $description = 'Create Views\'s CRUD';

    protected $crudName;
    protected $crudNameCap;
    protected $crudNameCapSingular;
    protected $crudNameSingular;
    protected $crudNamePlural;
    protected $modelName;
    protected $viewName;
    protected $path;
    protected $tableName;

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

        $this->copyLayout();

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

        $this->path = config('view.paths')[0] . '/' . str_plural($this->viewName) . '/';

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

    protected function copyLayout()
    {
        $layout = $file = __DIR__ . '/../publish/mango.blade.php';
        $newLayout = base_path('resources/views/layouts/mango.blade.php');

        if (!File::isFile($newLayout)) {
            if ($this->output->confirm('Do you want to add a Twitter Bootstrap layout?')) {
                $this->crudBuilder->copyFile($layout, $newLayout);
            }
        }
    }

    protected function mountViews()
    {
        if ($this->output->confirm('Generate index view?')) {
            $this->buildIndexView();
        }

        if ($this->output->confirm('Generate create view?')) {
            $this->buildCreateView();
        }

        if ($this->output->confirm('Generate edit view?')) {
            $this->buildEditView();
        }

        if ($this->output->confirm('Generate show view?')) {
            $this->buildShowView();
        }

        $this->output->success("All views created at {$this->path}");
    }

    protected function buildIndexView()
    {
        $header = '';
        $content = '';

        foreach ($this->modelFields as $f) {
            $header .= "<th>{$f['name']}</th>\n";
            $content .= '<td>{{$' . $this->crudNameSingular . '->' . $f['name'] . '}}</td>' . "\n";
        }

        $this->buildIndexViewFile($header, $content);
    }

    protected function buildIndexViewFile($header, $content)
    {
        $view = $this->stubs()['index'];
        $newFile = $this->path . 'index.blade.php';
        if ($this->crudBuilder->copyFile($view, $newFile)) {
            $this->output->error("failed to copy {$view}");
        } else {
            $vars = array('%%varName%%', '%%pluralVar%%', '%%varNameSingular%%', '%%fieldsTitle%%', '%%fieldsColumn%%');
            $contents = array($this->crudNamePlural, $this->crudNamePlural, $this->crudNameSingular, $header, $content);
            $this->crudBuilder->replaceVars($vars, $contents, $newFile);
        }
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

    protected function buildCreateView()
    {
        $fields = '';
        foreach ($this->modelFields as $f) {
            $fields .= $this->mountField($f['html'], $f['name']);
        }

        $this->buildCreateViewFile($fields);
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

    protected function buildCreateViewFile($fields)
    {
        $view = $this->stubs()['create'];
        $newFile = $this->path . 'create.blade.php';
        if ($this->crudBuilder->copyFile($view, $newFile)) {
            $this->output->error("failed to copy {$view}");
        } else {
            $vars = array('%%pluralVar%%', '%%crudNameCapSingular%%', '%%fields%%');
            $contents = array($this->crudNamePlural, $this->crudNameCapSingular, $fields);
            $this->crudBuilder->replaceVars($vars, $contents, $newFile);
        }
    }

    protected function buildEditView()
    {
        $fields = '';
        foreach ($this->modelFields as $f) {
            $fields .= $this->mountField($f['html'], $f['name'], '$' . $this->crudNamePlural . '->' . $f['name'] . '', $f['pk']);
        }

        $this->buildEditViewFile($fields);
    }

    protected function buildEditViewFile($fields)
    {
        $view = $this->stubs()['edit'];
        $newFile = $this->path . 'edit.blade.php';
        if ($this->crudBuilder->copyFile($view, $newFile)) {
            $this->output->error("failed to copy {$view}");
        } else {
            $vars = array('%%pluralVar%%', '%%varNameSingular%%', '%%crudNameCapSingular%%', '%%fields%%');
            $contents = array($this->crudNamePlural, $this->crudNameSingular, $this->crudNameCapSingular, $fields);
            $this->crudBuilder->replaceVars($vars, $contents, $newFile);
        }
    }

    protected function buildShowView()
    {
        $fields = '';
        foreach ($this->modelFields as $f) {
            $fields .= "<tr>\n";
            $fields .= "<td><strong>{$f['name']}</strong></td>\n";
            $fields .= '<td>{{$' . $this->crudNameSingular . '->' . $f['name'] . '}}</td>' . "\n";
            $fields .= "</tr>\n";
        }

        $this->buildShowViewFile($fields);
    }

    protected function buildShowViewFile($fields)
    {
        $view = $this->stubs()['show'];
        $newFile = $this->path . 'show.blade.php';
        if ($this->crudBuilder->copyFile($view, $newFile)) {
            $this->output->error("failed to copy {$view}");
        } else {
            $vars = array('%%pluralVar%%', '%%fields%%');
            $contents = array($this->crudNamePlural, $fields);
            $this->crudBuilder->replaceVars($vars, $contents, $newFile);
        }
    }

    protected function getArguments()
    {
        return array(array('model', InputArgument::REQUIRED, 'Model name'));
    }

    protected function getNameInput()
    {
        return str_replace('/', '\\', $this->argument('name'));
    }
}