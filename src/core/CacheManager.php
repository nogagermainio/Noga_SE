<?php declare(strict_types=1);
namespace Noga\Core;

use Generator;
use Noga\Noga;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Throwable;

class CacheManager
{
    protected string|array $key;
    protected mixed $data      = null;
    private ?string $signature = null;
    private string $path;
    private ?string $debug = null;
    private ?int $delay    = null;
    private ?string $basePath;

    public function __construct(string | array $key = "")
    {
        $this->basePath = Noga::get("cache_path") ?: __DIR__ . "/../../src/cache";
        $this->path      = $this->basePath;
        $this->key       = $key;
        $this->data      = null;
        $this->debug     = "";
        $this->signature = null;
        if (
            ! is_dir($this->path) &&
            ! mkdir($this->path, 0755, true)
        ) {
            throw new RuntimeException("cache directory creation failed");
        }
    }

    public function dir(string $dir): CacheManager
    {
        $clone = clone $this;

        $dir         = trim($dir, "/");
        $clone->path = "{$clone->basePath}/{$dir}";

           if (
        ! is_dir($clone->path) &&
        ! mkdir($clone->path, 0755, true)
    ) {
        throw new RuntimeException("cache directory creation failed");
    }

        return $clone;
    }

    private function buildKey(): string
    {
        return is_array($this->key)
            ? hash('xxh128', serialize($this->key))
            : hash('xxh128', $this->key);
    }

    public function getPath(): string
    {
        return "{$this->path}" . \DIRECTORY_SEPARATOR  . $this->buildKey() . ".php";
    }

    public function has(): bool
    {
        return file_exists($this->getPath());
    }

    public static function key(string | array $key): CacheManager
    {
        return new static($key);
    }

    public function data(mixed $data): CacheManager
    {
        $clone       = clone $this;
        $clone->data = $data;

        return $clone;
    }

    public function put(): CacheManager
    {
        $clone = clone $this;

        if ($clone->data === null) {
            throw new RuntimeException("no data found");
        }

        $tmp = $clone->path . DIRECTORY_SEPARATOR .
        bin2hex(random_bytes(16)) . '.tmp';

        $content = "<?php" . PHP_EOL
        . "return " . var_export($clone->mergeData(), true) . ";";

        if (file_put_contents($tmp, $content, LOCK_EX) === false) {
            throw new RuntimeException("tmp write failed");
        }

        if (! rename($tmp, $clone->getPath())) {
            throw new RuntimeException("cache write failed");
        }

        $clone->debug =
        "the cache has been created\n"
        . "cache key : " . (is_array($clone->key) ? implode(',', $clone->key) : $clone->key) . "\n"
        . "cache fileName : " . $clone->buildKey() . ".php\n"
        . "path : " . $clone->getPath() . "\n"
        . "content : " . json_encode($clone->mergeData(), JSON_PRETTY_PRINT);

        return $clone;
    }

    public function delay(int $delay):CacheManager{
        $clone = clone $this;

        $clone->delay = $delay;
        return $clone;
    }

    public function get(): mixed
    {
        $path = $this->getPath();
        try {

            return file_exists($path) ? require $path : null;

        } catch (Throwable $e) {
            $this->debug = $e->getMessage();
            return null;
        }

    }

    public function getData(): array
    {
        $data = $this->get();
        return isset($data['data']) ? $data['data'] : [];
    }

    public function getSignature(): string
    {
        $data = $this->get();
        return isset($data['signature']) ? $data['signature'] : "";
    }

    public static function getAll(string $dir): Generator
    {
        $instance = new static();
        $dir = trim($dir, "/");

        $path = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $instance->basePath . $dir,
                \FilesystemIterator::SKIP_DOTS
            )
        );

        foreach ($path as $file) {

            if (! $file->isFile()) {
                continue;
            }

            if ($file->getExtension() !== 'php') {
                continue;
            }

            $realPath = $file->getRealPath();
            try {
                yield require $realPath;

            } catch (Throwable $e) {
                continue;
            }

        }

    }

    public function signature(array $data): CacheManager
    {
        $clone            = clone $this;
        $clone->signature = $clone->generateSignature($data);
        return $clone;
    }

    public function hasValidSignature(array $data)
    {
        $signature = $this->generateSignature($data);
        $cache     = $this->get();

        return (is_array($cache) && isset($cache['signature']) && $cache['signature'] === $signature);
    }

    private function mergeData(): array
    {
        return [
            "delay"     => $this->delay,
            "signature" => $this->signature,
            "data"      => $this->data,
        ];
    }

    public function delete(): static
    {

        if ($this->has()) {
            @unlink($this->getPath());

             $this->debug =
                "the cache has been deleted\n"
                . "cache key : " . (is_array($this->key) ? implode(',', $this->key) : $this->key) . "\n"
                . "cache fileName : " . $this->buildKey() . ".php\n"
                . "path : " . $this->getPath();
        }else{
            $this->debug = "key {$this->key} : introuvable ! \n {$this->path}";
        }

        return $this;
    }

    public static function clearAll(?string $dir = null): static
    {
        $instance = new static();

        $d = !\is_null($dir) ? "/$dir" : ""; 

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $instance->path.$d,
                \FilesystemIterator::SKIP_DOTS
            )
        );

        $count = 0;

        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                @unlink($file->getRealPath());
                $count++;
            }
        }

        $instance->debug =
        "all cache has been deleted\n"
        . "path : " . $instance->path.$d . "\n "
        . "number of cache : " . $count;

        return $instance;
    }

    private function generateSignature(array $data): string
    {
        return md5(\serialize($data));
    }

    public function debug(): ?string
    {
        return $this->debug;
    }

}
