<?php

namespace App\Console\Commands\Generator;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

#[Signature('make:service {name}')]
#[Description('Create a new service class')]
class ServiceCommand extends Command
{
    protected Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
    }

    public function handle(): int
    {
        $name = $this->argument('name');

        $name = str_replace('\\', '/', $name);
        $name = str_replace('Service', '', $name) . 'Service';

        $path = app_path('Services/' . $name . '.php');

        if ($this->filesystem->exists($path)) {
            $this->components->error('Service [%s.php] already exists.', $name);

            return Command::FAILURE;
        }

        $this->makeDirectory($path);

        $stub = $this->buildClass($name);

        $this->filesystem->put($path, $stub);

        $this->components->info(sprintf('Service [%s.php] created successfully.', $name));

        return Command::SUCCESS;
    }

    protected function makeDirectory(string $path): void
    {
        $directory = dirname($path);

        if (!$this->filesystem->isDirectory($directory)) {
            $this->filesystem->makeDirectory($directory, 0755, true, true);
        }
    }

    protected function buildClass(string $name): string
    {
        $namespace = 'App\\Services';
        $extends = 'Service';

        $className = class_basename($name);

        $subNamespace = trim(dirname(str_replace('\\', '/', $name)), '.');
        if ($subNamespace) {
            $namespace .= '\\' . str_replace('/', '\\', $subNamespace);
            $extends = 'Service';
        }

        return sprintf(
            "<?php\n\nnamespace %s;\n\nuse App\\Services\\Service;\n\nclass %s extends %s\n{\n    \n}\n",
            $namespace,
            $className,
            $extends
        );
    }
}
