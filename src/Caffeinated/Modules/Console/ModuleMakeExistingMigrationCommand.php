<?php
namespace Caffeinated\Modules\Console;

use Caffeinated\Modules\Handlers\ModuleMakeMigrationHandler;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class ModuleMakeExistingMigrationCommand extends Command
{
	/**
	 * @var string $name The console command name.
	 */
	protected $name = 'module:make-existing-migration';

	/**
	 * @var string $description The console command description.
	 */
	protected $description = 'Create migration file for old-system table';

	/**
	 * @var \Caffeinated\Modules\Handlers\ModuleMakeMigrationHandler
	 */
	protected $handler;

	/**
	 * Create a new command instance.
	 *
	 * @param \Caffeinated\Modules\Handlers\ModuleMakeMigrationHandler $handler
	 */
	public function __construct(ModuleMakeMigrationHandler $handler)
	{
		parent::__construct();

		$this->handler = $handler;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		return $this->handler->fire($this, $this->argument('module'), $this->argument('table'));
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['module', InputArgument::REQUIRED, 'Module slug.'],
			['table', InputArgument::REQUIRED, 'Table name.']
		];
	}
}
