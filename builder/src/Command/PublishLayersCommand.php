<?php

namespace App\Command;

use App\Traits\ConfigTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PublishLayersCommand extends Command
{
    use ConfigTrait;

    protected static $defaultName = 'app:layers:publish';
    private array $regions;

    public function __construct(array $regions)
    {
        parent::__construct();
        $this->regions = $regions;
    }

    protected function configure()
    {
        $this
            ->addOption(
                'dont-update-config',
                null,
                InputOption::VALUE_NONE
            )
            ->addOption(
                'region',
                null,
                InputOption::VALUE_REQUIRED
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $selectedRegion = $input->getOption('region');
        $exitCode = 0;
        foreach ($this->regions as $region) {
            if ($selectedRegion && $selectedRegion !== $region) {
                $output->writeln("Skipping region '${region}'");
                continue;
            }
            chdir($this->getRootDir());

            $name = $this->getJson()['awsLayerName'];

            foreach ($this->getVersions() as $version) {
                $out = [];
                exec(
                    "aws lambda --region ${region} publish-layer-version --layer-name ${name}-${version} --zip-file fileb://./export/layer-php-imagick-${version}.zip",
                    $out,
                    $exitCode
                );

                foreach ($out as $line) {
                    $output->writeln($line);
                }

                if ($exitCode !== 0) {
                    break;
                }

                $result = json_decode(implode(PHP_EOL, $out), true);
                $layerVersion = $result['Version'] ?? null;
                if ($layerVersion === null) {
                    $output->writeln('Could not get version from the AWS output');
                    return 1;
                }

                passthru(
                    "aws lambda --region ${region} add-layer-version-permission --layer-name ${name}-${version} --statement-id layer-imagick-${version} --version-number ${layerVersion} --principal '*' --action lambda:GetLayerVersion",
                    $exitCode
                );
                if ($exitCode !== 0) {
                    break;
                }

                if (!$input->getOption('dont-update-config')) {
                    $newConfig = $this->getJson();
                    $newConfig[$version][$region] = $layerVersion;

                    file_put_contents($this->getConfigPath(), json_encode($newConfig, JSON_PRETTY_PRINT));
                }
            }
        }

        return $exitCode;
    }
}
