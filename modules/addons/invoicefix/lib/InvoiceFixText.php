<?php

declare(strict_types=1);

namespace InvoiceFix\Module;

use RuntimeException;
use Throwable;
use WHMCS\Database\Capsule;

if (!defined('WHMCS')) {
    exit('This file cannot be accessed directly');
}

final class InvoiceFixText
{
    public const OVERRIDE_SETTING = 'text_overrides_json';

    /** @var array<string,string>|null */
    private static ?array $defaults = null;

    /** @var array<string,array<string,mixed>>|null */
    private static ?array $meta = null;

    /** @var array<string,string>|null */
    private static ?array $overrides = null;

    /**
     * Return translated/default text with optional placeholder replacement.
     *
     * @param array<string,string|int|float> $replacements
     */
    public static function get(string $key, array $replacements = []): string
    {
        $defaults = self::defaults();
        $overrides = self::overrides();
        $value = array_key_exists($key, $overrides) ? $overrides[$key] : ($defaults[$key] ?? $key);

        if ($replacements === []) {
            return $value;
        }

        $replace = [];
        foreach ($replacements as $name => $replacement) {
            $replace['{' . $name . '}'] = (string) $replacement;
        }

        return strtr($value, $replace);
    }

    /**
     * @return array<string,array{value:string,default:string,group:string,label:string,type:string,rows:int,help:string}>
     */
    public static function editorFields(): array
    {
        $fields = [];
        $defaults = self::defaults();
        $meta = self::meta();
        $overrides = self::overrides();

        foreach ($meta as $key => $definition) {
            if (!array_key_exists($key, $defaults)) {
                continue;
            }

            $fields[$key] = [
                'value' => array_key_exists($key, $overrides) ? $overrides[$key] : $defaults[$key],
                'default' => $defaults[$key],
                'group' => (string) ($definition['group'] ?? 'Other'),
                'label' => (string) ($definition['label'] ?? $key),
                'type' => (string) ($definition['type'] ?? 'text'),
                'rows' => max(2, min(10, (int) ($definition['rows'] ?? 3))),
                'help' => (string) ($definition['help'] ?? ''),
            ];
        }

        return $fields;
    }

    /**
     * @param array<string,mixed> $submitted
     */
    public static function saveOverrides(array $submitted): int
    {
        $defaults = self::defaults();
        $editable = self::meta();
        $overrides = [];

        foreach ($editable as $key => $_definition) {
            if (!array_key_exists($key, $defaults) || !array_key_exists($key, $submitted)) {
                continue;
            }

            $value = str_replace(["\r\n", "\r"], "\n", (string) $submitted[$key]);
            if (strlen($value) > 10000) {
                throw new RuntimeException(self::get('error_text_too_long', ['key' => $key]));
            }

            if ($value !== $defaults[$key]) {
                $overrides[$key] = $value;
            }
        }

        $encodedLength = strlen((string) json_encode($overrides, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        if ($encodedLength > 60000) {
            throw new RuntimeException(self::get('error_text_total_too_long'));
        }

        self::writeOverrides($overrides);
        self::$overrides = $overrides;

        return count($overrides);
    }

    public static function resetOverrides(): void
    {
        self::writeOverrides([]);
        self::$overrides = [];
    }

    /**
     * @param string[] $keys
     * @return array<string,string>
     */
    public static function subset(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = self::get($key);
        }

        return $result;
    }

    /** @return array<string,string> */
    private static function defaults(): array
    {
        self::loadLanguageFile();
        return self::$defaults ?? [];
    }

    /** @return array<string,array<string,mixed>> */
    private static function meta(): array
    {
        self::loadLanguageFile();
        return self::$meta ?? [];
    }

    private static function loadLanguageFile(): void
    {
        if (self::$defaults !== null && self::$meta !== null) {
            return;
        }

        $_ADDONLANG = [];
        $_ADDONLANG_META = [];
        $path = dirname(__DIR__) . '/lang/english.php';
        if (!is_file($path)) {
            throw new RuntimeException('InvoiceFix language file is missing.');
        }

        include $path;

        self::$defaults = [];
        foreach ($_ADDONLANG as $key => $value) {
            self::$defaults[(string) $key] = (string) $value;
        }

        self::$meta = is_array($_ADDONLANG_META) ? $_ADDONLANG_META : [];
    }

    /** @return array<string,string> */
    private static function overrides(): array
    {
        if (self::$overrides !== null) {
            return self::$overrides;
        }

        try {
            $json = Capsule::table('tbladdonmodules')
                ->where('module', InvoiceFixService::MODULE)
                ->where('setting', self::OVERRIDE_SETTING)
                ->value('value');

            if ($json === null || trim((string) $json) === '') {
                self::$overrides = [];
                return self::$overrides;
            }

            $decoded = json_decode((string) $json, true);
            if (!is_array($decoded)) {
                self::$overrides = [];
                return self::$overrides;
            }

            $defaults = self::defaults();
            self::$overrides = [];
            foreach ($decoded as $key => $value) {
                if (array_key_exists((string) $key, $defaults) && is_scalar($value)) {
                    self::$overrides[(string) $key] = (string) $value;
                }
            }
        } catch (Throwable $e) {
            self::$overrides = [];
        }

        return self::$overrides;
    }

    /** @param array<string,string> $overrides */
    private static function writeOverrides(array $overrides): void
    {
        $json = json_encode($overrides, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new RuntimeException(self::get('error_text_encode'));
        }

        Capsule::table('tbladdonmodules')->updateOrInsert(
            [
                'module' => InvoiceFixService::MODULE,
                'setting' => self::OVERRIDE_SETTING,
            ],
            ['value' => $json]
        );
    }
}
