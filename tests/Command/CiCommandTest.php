<?php
declare(strict_types=1);

namespace Danger\Command;

use Danger\ConfigLoader;
use Danger\Platform\Github\Github;
use Danger\Platform\PlatformDetector;
use Danger\Renderer\HTMLRenderer;
use Danger\Runner;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @internal
 */
class CiCommandTest extends TestCase
{
    public function testValid(): void
    {
        $platform = $this->createMock(Github::class);

        $detector = $this->createMock(PlatformDetector::class);
        $detector->method('detect')->willReturn($platform);

        $output = new BufferedOutput();

        $cmd = new CiCommand($detector, new ConfigLoader(), new Runner(), new HTMLRenderer());
        $returnCode = $cmd->run(new ArgvInput(['danger', '--config=' . dirname(__DIR__) . '/configs/empty.php']), $output);

        static::assertSame(0, $returnCode);
        static::assertStringContainsString('Looks good!', $output->fetch());
    }

    public function testNotValid(): void
    {
        $platform = $this->createMock(Github::class);
        $platform->method('post')->willReturn('http://danger.local/test');

        $detector = $this->createMock(PlatformDetector::class);
        $detector->method('detect')->willReturn($platform);
        $output = new BufferedOutput();

        $cmd = new CiCommand($detector, new ConfigLoader(), new Runner(), new HTMLRenderer());
        $returnCode = $cmd->run(new ArgvInput(['danger', '--config=' . dirname(__DIR__) . '/configs/all.php']), $output);

        static::assertSame(-1, $returnCode);
        static::assertStringContainsString('The comment has been created at http://danger.local/test', $output->fetch());
    }
}