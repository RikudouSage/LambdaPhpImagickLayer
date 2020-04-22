<?php

namespace App\Command;

use App\Traits\ConfigTrait;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateLayersCommand extends Command
{
    use ConfigTrait;

    protected static $defaultName = 'app:layers:create';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $exitCode = 0;
        try {
            chdir($this->getRootDir());
            $commands = [
                'rm -rf export/tmp',
                'rm -rf export/*.zip',
                'mkdir -p export/tmp'
            ];

            foreach ($commands as $command) {
                passthru($command, $exitCode);
                $this->checkExitCode($exitCode);
            }

            foreach ($this->getVersions() as $version) {
                chdir($this->getRootDir() . '/export/tmp');

                passthru(
                    "docker run --rm --entrypoint \"tar\" rikudousage/layer-php-imagick-${version} -ch -C /opt . | tar -x",
                    $exitCode
                );
                $this->checkExitCode($exitCode);

                passthru(
                    "zip --quiet -X --recurse-paths ../layer-php-imagick-${version}.zip .",
                    $exitCode
                );
                $this->checkExitCode($exitCode);

                chdir($this->getRootDir());

                $output->writeln("Created zip file for v${version}");
            }

            passthru('rm -rf export/tmp', $exitCode);
            $this->checkExitCode($exitCode);

            return 0;
        } catch (RuntimeException $e) {
            return $exitCode;
        }
    }

    private function checkExitCode(int $exitCode): void
    {
        if ($exitCode !== 0) {
            throw new RuntimeException();
        }
    }
}
