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

    public function test_failure_can_return_custom_status(): void
    {
        $tester = new CommandTester($this->command(new CustomStatusFailingBaseCommand));

        $status = $tester->execute([]);

        self::assertSame(E_ERROR, $status);
        self::assertStringContainsString('Broken', $tester->getDisplay());
    }

    public function test_option_as_int_returns_default_when_option_is_empty(): void
    {
        $tester = new CommandTester($this->command(new IntegerOptionBaseCommand));

        $status = $tester->execute(['--count' => '']);

        self::assertSame(BaseCommand::SUCCESS, $status);
        self::assertStringContainsString('5', $tester->getDisplay());
    }

    public function test_option_as_int_casts_without_minimum(): void
    {
        $tester = new CommandTester($this->command(new IntegerOptionWithoutMinimumBaseCommand));

        $status = $tester->execute(['--count' => '7']);

        self::assertSame(BaseCommand::SUCCESS, $status);
        self::assertStringContainsString('7', $tester->getDisplay());
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

    public function test_run_safely_returns_success_for_non_integer_result(): void
    {
        $tester = new CommandTester($this->command(new SafelySuccessfulBaseCommand));

        $status = $tester->execute([]);

        self::assertSame(BaseCommand::SUCCESS, $status);
    }

    public function test_run_safely_returns_integer_result(): void
    {
        $tester = new CommandTester($this->command(new SafelyIntegerBaseCommand));

        $status = $tester->execute([]);

        self::assertSame(3, $status);
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

final class CustomStatusFailingBaseCommand extends BaseCommand
{
    protected $signature = 'test:custom-status-failing-base-command';

    public function handle(): int
    {
        return $this->failure('Broken', E_ERROR);
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

final class IntegerOptionWithoutMinimumBaseCommand extends BaseCommand
{
    protected $signature = 'test:integer-option-without-minimum-base-command {--count=}';

    public function handle(): int
    {
        $this->line((string) $this->optionAsInt('count'));

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

final class SafelySuccessfulBaseCommand extends BaseCommand
{
    protected $signature = 'test:safely-successful-base-command';

    public function handle(): int
    {
        return $this->runSafely(static fn (): string => 'ok');
    }
}

final class SafelyIntegerBaseCommand extends BaseCommand
{
    protected $signature = 'test:safely-integer-base-command';

    public function handle(): int
    {
        return $this->runSafely(static fn (): int => 3);
    }
}
