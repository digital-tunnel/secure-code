<?php

declare(strict_types=1);

namespace DigitalTunnel\SecureCode\Console;

use DigitalTunnel\SecureCode\Enums\Charset;
use DigitalTunnel\SecureCode\SecureCode;
use Illuminate\Console\Command;

class GenerateCommand extends Command
{
    protected $signature = 'secure-code:generate
        {--length=8 : Code length}
        {--charset=Alphanumeric : Character set}
        {--count=1 : Number of codes to generate}
        {--prefix= : Prefix to prepend}
        {--suffix= : Suffix to append}
        {--separator= : Separator character}
        {--every=4 : Separator interval}
        {--preset= : Use a preset (pin, otp, voucher, serial, api-key, token, invite)}
        {--pattern= : Pattern string (A=letter, 9=digit, X=hex, *=any)}
        {--upper : Force uppercase}
        {--lower : Force lowercase}
        {--exclude-similar : Exclude similar characters (0, O, 1, I, l)}
        {--checksum : Append Luhn check digit}
        {--json : Output as JSON}
        {--csv : Output as CSV}';

    protected $description = 'Generate cryptographically secure random codes';

    public function handle(): int
    {
        $pattern = $this->option('pattern');

        if ($pattern) {
            return $this->handlePattern($pattern);
        }

        $presetName = $this->option('preset');

        if ($presetName) {
            return $this->handlePreset($presetName);
        }

        return $this->handleBuilder();
    }

    private function handlePattern(string $pattern): int
    {
        $count = (int) $this->option('count');
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $codes[] = SecureCode::pattern($pattern)->generate();
        }

        $this->outputCodes($codes);

        return self::SUCCESS;
    }

    private function handlePreset(string $presetName): int
    {
        $builder = SecureCode::preset($presetName);
        $count = (int) $this->option('count');

        if ($count > 1) {
            $builder = $builder->count($count);
        }

        $result = $builder->generate();
        $this->outputCodes(is_array($result) ? $result : [$result]);

        return self::SUCCESS;
    }

    private function handleBuilder(): int
    {
        $builder = SecureCode::length((int) $this->option('length'));

        $charsetName = $this->option('charset');
        foreach (Charset::cases() as $case) {
            if (strcasecmp($case->name, $charsetName) === 0) {
                $builder = $builder->charset($case);
                break;
            }
        }

        if ($this->option('prefix')) {
            $builder = $builder->prefix($this->option('prefix'));
        }

        if ($this->option('suffix')) {
            $builder = $builder->suffix($this->option('suffix'));
        }

        if ($this->option('separator')) {
            $builder = $builder->separator($this->option('separator'), (int) $this->option('every'));
        }

        if ($this->option('upper')) {
            $builder = $builder->uppercase();
        }

        if ($this->option('lower')) {
            $builder = $builder->lowercase();
        }

        if ($this->option('exclude-similar')) {
            $builder = $builder->excludeSimilar();
        }

        if ($this->option('checksum')) {
            $builder = $builder->withChecksum();
        }

        $count = (int) $this->option('count');
        if ($count > 1) {
            $builder = $builder->count($count);
        }

        $result = $builder->generate();
        $this->outputCodes(is_array($result) ? $result : [$result]);

        return self::SUCCESS;
    }

    private function outputCodes(array $codes): void
    {
        if ($this->option('json')) {
            $this->line(json_encode($codes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return;
        }

        if ($this->option('csv')) {
            $this->line('code');
            foreach ($codes as $code) {
                $this->line($code);
            }

            return;
        }

        if (count($codes) === 1) {
            $this->info($codes[0]);

            return;
        }

        $this->table(['#', 'Code'], array_map(
            fn (string $code, int $i) => [$i + 1, $code],
            $codes,
            array_keys($codes),
        ));
    }
}
