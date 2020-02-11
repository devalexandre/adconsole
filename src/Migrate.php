<?php
/**
 * Created by PhpStorm.
 * User: indev
 * Date: 23/11/2018
 * Time: 11:24
 */

namespace App\Command;



use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Migrate extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->addOption('--environment', '-e', InputOption::VALUE_REQUIRED, 'The target environment');

        $this->setName('migration:run')
            ->setDescription('Migrate the database')
            ->addOption('--target', '-t', InputOption::VALUE_REQUIRED, 'The version number to migrate to')
            ->addOption('--date', '-d', InputOption::VALUE_REQUIRED, 'The date to migrate to')
            ->addOption('--dry-run', '-x', InputOption::VALUE_NONE, 'Dump query to standard output instead of executing it')
            ->addOption('--fake', null, InputOption::VALUE_NONE, "Mark any migrations selected as run, but don't actually execute them")
            ->setHelp(
                <<<EOT
The <info>migrate</info> command runs all available migrations, optionally up to a specific version

<info>phinx migrate -e development</info>
<info>phinx migrate -e development -t 20110103081132</info>
<info>phinx migrate -e development -d 20110103</info>
<info>phinx migrate -e development -v</info>

EOT
            );
    }

    /**
     * Migrate the database.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int integer 0 on success, or an error code.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->bootstrap($input, $output);

        $version = $input->getOption('target');
        $environment = $input->getOption('environment');
        $date = $input->getOption('date');
        $fake = (bool)$input->getOption('fake');

        if ($environment === null) {
            $environment = $this->getConfig()->getDefaultEnvironment();
            $output->writeln('<comment>warning</comment> no environment specified, defaulting to: ' . $environment);
        } else {
            $output->writeln('<info>using environment</info> ' . $environment);
        }

        $envOptions = $this->getConfig()->getEnvironment($environment);
        if (isset($envOptions['adapter'])) {
            $output->writeln('<info>using adapter</info> ' . $envOptions['adapter']);
        }

        if (isset($envOptions['wrapper'])) {
            $output->writeln('<info>using wrapper</info> ' . $envOptions['wrapper']);
        }

        if (isset($envOptions['name'])) {
            $output->writeln('<info>using database</info> ' . $envOptions['name']);
        } else {
            $output->writeln('<error>Could not determine database name! Please specify a database name in your config file.</error>');

            return 1;
        }

        if (isset($envOptions['table_prefix'])) {
            $output->writeln('<info>using table prefix</info> ' . $envOptions['table_prefix']);
        }
        if (isset($envOptions['table_suffix'])) {
            $output->writeln('<info>using table suffix</info> ' . $envOptions['table_suffix']);
        }

        if ($fake) {
            $output->writeln('<comment>warning</comment> performing fake migrations');
        }

        // run the migrations
        $start = microtime(true);
        if ($date !== null) {
            $this->getManager()->migrateToDateTime($environment, new \DateTime($date), $fake);
        } else {
            $this->getManager()->migrate($environment, $version, $fake);
        }
        $end = microtime(true);

        $output->writeln('');
        $output->writeln('<comment>All Done. Took ' . sprintf('%.4fs', $end - $start) . '</comment>');

        return 0;
    }
}
