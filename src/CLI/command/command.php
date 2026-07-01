<?php declare(strict_types=1);
namespace Noga\CLI\Command;

use Noga\Core\CacheManager;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Noga\CLI\Services\Render;

class Command
{
    private array $dir = [];
    private array $class = [];
    private array $command = [];
    private ?CacheManager $cache = null;

    private static ?self $instance = null;

    public function __construct(array|string|null $dir = null)
    {
        $this->cache = CacheManager::key("commands")->dir("command");
            $this->setDirs($dir);

        $this->handle();
    }

    public static function get(string|array|null $dir = null): self
    {
        $dirs = [];
            $dirs = $dir;

        if(self::$instance === null){
            self::$instance = new self($dirs);
        }
        return self::$instance;
    }

    public function dir(string|array|null $dir = null): static
    {
            $this->setDirs($dir);

        return $this;
    }

    private function setDirs(string|array|null $dir): array
    {
        $dirs = self::normalizeDirs($dir);

        if(\is_array($dirs)){
             $this->dir = $dirs;
        }else{
            $this->dir[] = $dirs;
        }
       
        return $this->dir;
    }

    private static function normalizeDirs(string|array|null $dir)
    {
        $dirs = [];

        if (is_array($dir)) {
            foreach ($dir as $path) {
                $dirs[] = __DIR__ . "/../../../".trim($path, '/');
            }
        } else if(\is_null($dir)) {

            $dirs = __DIR__ . "/../../../";

        }else{
            $dirs[] = __DIR__ . "/../../../".trim($dir,"/");
        }

        return $dirs;
    }

    public function handle()
    {
        $this->command = [];
        $this->class = [];

        if (!is_array($this->dir)) {
          
        }

        foreach ($this->dir as $dir) {

            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $dir,
                    \FilesystemIterator::SKIP_DOTS
                )
            );

            foreach ($files as $f) {

                if (!$f->isFile() || $f->getExtension() !== 'php') {
                    continue;
                }
                $basePath = \dirname(__DIR__,3);

                $file = trim(str_replace(
                    [$basePath, ".php"],
                    ['', ''],
                    $f->getRealPath()
                ),"\\");

               

                $getname = explode('\\', $file);
                $name = ucfirst(end($getname));

                $class = ucwords($file, '\\');
               
                if (in_array($name, ['Command', 'Kernel'])) {
                    continue;
                }

                $this->class[] = $class;
                $this->command[$name] = $class;
                 
            }       

        }
         return $this;
    }

    public function put(): void
    {
        $cache = $this->cache->data($this->command)->put();

        echo color(
            "Command cache generated (" . count($this->command) . " commands)\n in {$cache->getPath()}",
            'green'
        );

        exit(0);
    }

    public function show(): void
    {
        $cache = $this->cache->get();

        if (!is_array($cache) || !isset($cache['data']) || !is_array($cache['data'])) {
            error("no command available !");
            exit(1);
        }

        Render::data($cache['data'])->array();
    }

    public function clear(): void
    {
        if ($this->cache->has()) {
            $this->cache->delete();
            \success("cache deleted !");
            exit(0);
        }

        error("cache not found !");
        exit(1);
    }

    public function list():array
    {
        $command = [];
        $comm = $this->cache->get();

        if(!empty($comm)){
            $command = $comm['data'];
        }else{
               $command = $this->command;
        }

        return $command;
    }

    public function command(string $key = ""): void
    {
        $cache = $this->cache->get();

        if (is_array($cache) && isset($cache['data'])) {
            $this->command = $cache['data'];
        }

        $com = isset($this->command[$key])
            ? "$key => {$this->command[$key]}"
            : "Command not Found $key";

        \success($com);
    }

    public function add(string $key, string $command): static
    {
        $this->command[$key] = $command;

        return $this;
    }
}