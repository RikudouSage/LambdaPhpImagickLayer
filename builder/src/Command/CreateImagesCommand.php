<?php


namespace App\Command;

use App\Traits\ConfigTrait;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateImagesCommand extends Command
{
    use ConfigTrait;

    protected static $defaultName = 'app:images:create';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $exitCode = 0;
        chdir($this->getRootDir());
        foreach ($this->getVersions() as $version) {
            $command = sprintf(
                'docker build -t rikudousage/layer-php-imagick-%1$s --build-arg PHP_VERSION=%1$s --build-arg PHP_EXTENSION_DIR=%2$s .',
                $version,
                $this->getExtensionDir($version)
            );

            passthru($command, $exitCode);
            if ($exitCode !== 0) {
                break;
            }
        }

        return $exitCode;
    }

    private function getExtensionDir(string $version): string
    {
        switch ($version) {
            case '72':
                return '20170718';
            case '73':
                return '20180731';
            case '74':
                return '20190902';
            default:
                throw new InvalidArgumentException("Unsupported version '${version}'");
        }
    }
}
