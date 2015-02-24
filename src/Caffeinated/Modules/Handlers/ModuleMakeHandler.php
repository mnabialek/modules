<?php
namespace Caffeinated\Modules\Handlers;

use Caffeinated\Modules\Modules;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class ModuleMakeHandler
{
    /**
     * @var Console
     */
    protected $console;

    /**
     * @var array $folders Module folders to be created.
     */
    protected $folders
        = [
            //'Console/',
            'Database/',
            'Database/Migrations/',
            'Database/Seeds',
            'Http/',
            'Http/Controllers/',
            //'Http/Middleware/',
            'Http/Requests/',
            'Providers/',
            //'Resources/',
            //'Resources/Lang/',
            //'Resources/Views/',
        ];

    /**
     * @var array $files Module files to be created.
     */
    protected $files
        = [
            'Database/Seeds/{{name}}DatabaseSeeder.php',
            'Http/routes.php',
            'Providers/{{name}}ServiceProvider.php',
            'Providers/RouteServiceProvider.php',
            'module.json',
            'Models/{{name}}.php',
            'Repositories/{{name}}Repository.php',
            'Http/Controllers/{{name}}Controller.php',
            'Http//Requests/{{name}}Request.php'
        ];

    /**
     * @var array $stubs Module stubs used to populate defined files.
     */
    protected $stubs
        = [
            'seeder.stub',
            'routes.stub',
            'moduleserviceprovider.stub',
            'routeserviceprovider.stub',
            'module.stub',
            'model.stub',
            'repository.stub',
            'controller.stub',
            'request.stub',
        ];

    /**
     * @var \Caffeinated\Modules\Modules
     */
    protected $module;

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $finder;

    /**
     * @var string
     */
    protected $slug;

    /**
     * @var string
     */
    protected $name;

    /**
     * Constructor method.
     *
     * @param \Caffeinated\Modules\Modules      $module
     * @param \Illuminate\Filesystem\Filesystem $finder
     */
    public function __construct(Modules $module, Filesystem $finder)
    {
        $this->module = $module;
        $this->finder = $finder;
    }

    /**
     * Fire off the handler.
     *
     * @param  \Caffeinated\Modules\Console\ModuleMakeCommand $console
     * @param  string                                         $slug
     *
     * @return bool
     */
    public function fire(Command $console, $slug)
    {
        $this->console = $console;
        $this->slug = $slug;
        $this->name = Str::studly($slug);

        if ($this->module->exists($this->slug)) {
            $console->comment('Module [{$this->name}] already exists.');

            return false;
        }

        $this->generate($console);
    }

    /**
     * Generate module folders and files.
     *
     * @param  \Caffeinated\Modules\Console\ModuleMakeCommand $console
     *
     * @return boolean
     */
    public function generate(Command $console)
    {
        $this->generateFolders();

        $this->generateFiles();

        $this->generateGitkeep();

        $console->info("Module [{$this->name}] has been created successfully.");

        return true;
    }

    /**
     * Generate defined module folders.
     *
     * @return void
     */
    protected function generateFolders()
    {
        if (!$this->finder->isDirectory($this->module->getPath())) {
            $this->finder->makeDirectory($this->module->getPath());
        }

        $this->finder->makeDirectory($this->getModulePath($this->slug, true));

        foreach ($this->folders as $folder) {
            $this->finder->makeDirectory($this->getModulePath($this->slug)
                . $folder);
        }
    }

    /**
     * Generate defined module files.
     *
     * @return void
     */
    protected function generateFiles()
    {
        foreach ($this->files as $key => $file) {
            $file = $this->formatContent($file);

            $this->makeFile($key, $file);
        }
    }

    /**
     *  Generate .gitkeep files into empty generated folders
     *
     * @param string $directory
     */
    protected function generateGitkeep($directory = null)
    {
        $module_path = $this->getModulePath($this->slug);

        foreach ($this->folders as $folder) {
            if (empty($this->finder->files($folder))
                && empty($this->finder->files($folder))
            ) {
                $this->finder->put($module_path . $folder . '.gitkeep', '');
            }
        }
    }

    /**
     * Create module file.
     *
     * @param  int    $key
     * @param  string $file
     *
     * @return int
     */
    protected function makeFile($key, $file)
    {
        return $this->finder->put($this->getDestinationFile($file),
            $this->getStubContent($key));
    }

    /**
     * Get the path to the module.
     *
     * @param  string $slug
     *
     * @return string
     */
    protected function getModulePath($slug = null, $allowNotExists = false)
    {
        if ($slug) {
            return $this->module->getModulePath($slug, $allowNotExists);
        }

        return $this->module->getPath();
    }

    /**
     * Get destination file.
     *
     * @param  string $file
     *
     * @return string
     */
    protected function getDestinationFile($file)
    {
        return $this->getModulePath($this->slug) . $this->formatContent($file);
    }

    /**
     * Get stub content by key.
     *
     * @param  int $key
     *
     * @return string
     */
    protected function getStubContent($key)
    {
        return $this->formatContent($this->finder->get(__DIR__
            . '/../Console/stubs/' . $this->stubs[$key]));
    }

    /**
     * Replace placeholder text with correct values.
     *
     * @return string
     */
    protected function formatContent($content)
    {
        return str_replace(
            [
                '{{slug}}',
                '{{name}}',
                '{{namespace}}',
                '{{smallname}}',
                '{{className}}',
                '{{moduleName}}',
                '{{snake_case}}'
            ],
            [
                $this->slug,
                $this->name,
                $this->modules->getNamespace(),
                strtolower($this->name),
                $this->name . 'Request',
                $this->name,
                snake_case($this->name)
            ],
            $content
        );
    }
}
