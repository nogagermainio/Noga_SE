<?php declare(strict_types=1);
namespace Noga\Core;

class NgManager
{
    private static ?self $instance = null;
    private array $data = [];

    private string $regInclude =
        '/@include\(["\'](.+?)["\']\)/';

    private string $regType =
        '/^(string|int|bool|array|json)\s+(\w+)\s*=\s*([\s\S]*?)(?=^\s*(?:string|int|bool|array|json)\s+\w+\s*=|\z)/m';

    private function __construct(string $file)
    {
        $this->data = $this->loadFile($file);
    }

    public static function getInstance(?string $file = null): NgManager|null
    {
        if (!static::$instance) {
            if ($file == null) {
                throw new \RuntimeException("NGConfig non initialisé".$file);
            }
            static::$instance = new static($file);
        }
        return static::$instance;
    }

    // ---------------------------------
    // Chargement complet du fichier
    // ---------------------------------
    private function loadFile(string $file): array
    {
        if (!file_exists($file)) {
            throw new \RuntimeException("Fichier introuvable : $file");
        }

        $content = file_get_contents($file);
        $content = preg_replace('/^\s*#.*$/m', '', $content);
        // gestion des @include()
        $content = preg_replace_callback(
            $this->regInclude,
            function ($m) use ($file) {
                $included = dirname($file) . '/' . $m[1];
                return file_get_contents($included);
            },
            $content
        );

        preg_match_all($this->regType, $content, $matches, PREG_SET_ORDER);

        $result = [];

        foreach ($matches as $m) {
            [, $type, $key, $raw] = $m;

            if (\array_key_exists($key, $result)) {
                throw new \RuntimeException("Clé dupliquée : $key");
            }

            $result[$key] = $this->parseByType($type, trim($raw));
        }

        return $result;
    }

    // ---------------------------------
    // Parsing par type
    // ---------------------------------
    private function parseByType(string $type, string $value): mixed
    {
        return match ($type) {
            'string' => $this->parseString($value),
            'int'    => $this->parseInt($value),
            'bool'   => $this->parseBool($value),
            'array'  => $this->parseArray($value),
            'json'   => $this->parseJson($value),
            default  => throw new \RuntimeException("Type inconnu : $type"),
        };
    }

    private function parseString(string $v): string
    {
        if (!preg_match('/^".*"$/s', $v)) {
            throw new \RuntimeException("String invalide : $v");
        }
        return trim($v, '"');
    }

    private function parseInt(string $v): int
    {
        if (!ctype_digit(trim($v))) {
            throw new \RuntimeException("Int invalide : $v");
        }
        return (int)$v;
    }

    private function parseBool(string $v): bool
    {
        return match (strtolower($v)) {
            'true'  => true,
            'false' => false,
            default => throw new \RuntimeException("Bool invalide : $v"),
        };
    }

    private function parseArray(string $v): array
    {
        if (!str_starts_with($v, '[') || !str_ends_with($v, ']')) {
            throw new \RuntimeException("Array invalide");
        }

        $body = trim(substr($v, 1, -1));
        $lines = array_filter(array_map('trim', explode("\n", $body)));

        $result = [];

        foreach ($lines as $line) {
            $line = rtrim($line, ',');
            if (!str_contains($line, '=>')) continue;

            [$k, $val] = array_map('trim', explode('=>', $line, 2));
            $result[$k] = $this->inferValue($val);
        }

        return $result;
    }

    private function parseJson(string $v): array
    {
        return json_decode($v, true, 512, JSON_THROW_ON_ERROR);
    }

    private function inferValue(string $v): mixed
    {
        if (preg_match('/^".*"$/', $v)) return trim($v, '"');
        if (is_numeric($v)) return (int) $v;
        if (strtolower($v) === 'true') return true;
        if (strtolower($v) === 'false') return false;
        return $v;
    }

    // ---------------------------------
    // API publique
    // ---------------------------------
    public function getParam(string $key, mixed $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->data;
    }

    private function __clone() {}
    public function __wakeup() {}
}
