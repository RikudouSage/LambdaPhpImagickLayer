<?php


namespace App\Command;

use App\Traits\ConfigTrait;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateImagesCommand extends Command
{
    use ConfigTrait;

    protected static $defaultName = 'app:images:create';
    private string $imageMagickVersion;

    public function __construct(string $imageMagickVersion)
    {
        $this->imageMagickVersion = $imageMagickVersion;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption(
                'magick-version',
                null,
                InputOption::VALUE_REQUIRED,
                'The ImageMagick version',
                $this->imageMagickVersion
            )
            ->addOption(
                'php-version',
                null,
                InputOption::VALUE_REQUIRED,
                'The php version to build'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $imageMagickVersion = $input->getOption('magick-version');
        $majorVersion = substr($imageMagickVersion, 0, 1);

        $phpVersion = $input->getOption('php-version');

        $exitCode = 0;
        chdir($this->getRootDir());
        foreach ($this->getVersions() as $version) {
            if ($phpVersion && (string) $version !== $phpVersion) {
                $output->writeln("Skipping php version '${version}'");
                continue;
            }
            $command = sprintf(
                'docker build -t rikudousage/layer-php-imagick-%1$s --build-arg PHP_VERSION=%1$s \
                 --build-arg PHP_EXTENSION_DIR=%2$s --build-arg IMAGE_MAGICK_VERSION=%3$s --build-arg IMAGE_MAGICK_MAJOR_VERSION=%4$s .',
                $version,
                $this->getExtensionDir($version),
                $imageMagickVersion,
                $majorVersion
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
