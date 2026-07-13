<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit\Bases;

use Curicows\LaravelCommon\Bases\BaseCommand;
use Curicows\LaravelCommon\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(BaseCommand::class)]
class BaseCommandTest extends TestCase
{
    public function test_success_outputs_message_and_returns_success(): void
    {
        $tester = new CommandTester($this->command(new SuccessfulBaseCommand));

        $status = $tester->execute([]);

        self::assertSame(BaseCommand::SUCCESS, $status);
        self::assertStringContainsString('Done', $tester->getDisplay());
    }

    public function test_failure_outputs_message_and_returns_failure(): void
    {
        $tester = new CommandTester($this->command(new FailingBaseCommand));

        $status = $tester->execute([]);

        self::assertSame(BaseCommand::FAILURE, $status);
        self::assertStringContainsString('Broken', $tester->getDisplay());
    }

    public function test_option_as_int_clamps_to_minimum(): void
    {
        $tester = new CommandTester($this->command(new IntegerOptionBaseCommand));

        $status = $tester->execute(['--count' => '0']);

        self::assertSame(BaseCommand::SUCCESS, $status);
        self::assertStringContainsString('1', $tester->getDisplay());
    }

    public function test_run_safely_reports_failure(): void
    {
        $tester = new CommandTester($this->command(new SafelyFailingBaseCommand));

        $status = $tester->execute([]);

        self::assertSame(BaseCommand::FAILURE, $status);
        self::assertStringContainsString('Unexpected failure', $tester->getDisplay());
    }

    private function command(BaseCommand $command): BaseCommand
    {
        $command->setLaravel($this->app);

        return $command;
    }
}

final class SuccessfulBaseCommand extends BaseCommand
{
    protected $signature = 'test:successful-base-command';

    public function handle(): int
    {
        return $this->success('Done');
    }
}

final class FailingBaseCommand extends BaseCommand
{
    protected $signature = 'test:failing-base-command';

    public function handle(): int
    {
        return $this->failure('Broken');
    }
}

final class IntegerOptionBaseCommand extends BaseCommand
{
    protected $signature = 'test:integer-option-base-command {--count=}';

    public function handle(): int
    {
        $this->line((string) $this->optionAsInt('count', default: 5, minimum: 1));

        return self::SUCCESS;
    }
}

final class SafelyFailingBaseCommand extends BaseCommand
{
    protected $signature = 'test:safely-failing-base-command';

    public function handle(): int
    {
        return $this->runSafely(static function (): void {
            throw new RuntimeException('Unexpected failure');
        });
    }
}
