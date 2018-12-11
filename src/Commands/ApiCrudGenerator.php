<?php

namespace AndreaCivita\ApiCrudGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ApiCrudGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:crud
    {name : Class (singular) for example User}
    {--table=default : Table name (plural) for example users | Default is generated-plural}
    {--timestamps=false : Table name (plural) for example users | Default is generated-plural}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create CRUD operations';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $name = $this->argument('name');
        $table = $this->option('table');
        $timestamps = $this->option('timestamps');


        $this->controller($name);
        $this->model($name, $table, $timestamps);
        $this->request($name);
        $this->routes($name, $table);
    }

    /**
     * Get the file from the stub
     * @param $type
     * @return bool|string
     */
    protected function getStub($type)
    {
        return file_get_contents(resource_path("stubs/$type.stub"));
    }


    /**
     * Generate model class from stubs
     * @param $name string name of model class
     * @param $table string name of DB table
     * @param $timestamps boolean set timestamps true | false
     */
    protected function model($name, $table, $timestamps)
    {
        $table === "default" ? $table = strtolower(str_plural($name)) : null;
        $timeDeclaration = 'public $timestamps = false;';
        if ($timestamps == "true")
            $timeDeclaration = 'public $timestamps = true;';
        $modelTemplate = str_replace(
            [
                '{{modelName}}',
                '{{tableDeclaration}}',
                '{{timestamps}}'
            ],
            [
                $name,
                $table,
                $timeDeclaration,
            ],
            $this->getStub('Model')
        );

        if (!file_exists($path = app_path('/Model')))
            mkdir($path, 0777, true);


        file_put_contents(app_path("Model/{$name}.php"), $modelTemplate);
    }

    /**
     * Create controller from controller.stub
     * @param $name
     */
    protected function controller($name)
    {
        $controllerTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}'
            ],
            [
                $name,
                strtolower(str_plural($name)),
                strtolower($name)
            ],
            $this->getStub('Controller')
        );

        file_put_contents(app_path("/Http/Controllers/{$name}Controller.php"), $controllerTemplate);
    }

    /**
     * Generate Request from request.stub
     * @param $name
     */
    protected function request($name)
    {
        $requestTemplate = str_replace(
            ['{{modelName}}'],
            [$name],
            $this->getStub('Request')
        );

        if (!file_exists($path = app_path('/Http/Requests')))
            mkdir($path, 0777, true);

        file_put_contents(app_path("/Http/Requests/{$name}Request.php"), $requestTemplate);
    }

    /**
     * Generate routes
     * @param $name
     */
    public function routes($name, $table)
    {
        $table === "default" ? $table = strtolower(str_plural($name)) : null;
        $requestTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}'
            ],
            [
                $name,
                $table,
                strtolower($name)
            ],
            $this->getStub('Routes')
        );
        File::append(base_path('routes/api.php'), $requestTemplate);
    }
}
