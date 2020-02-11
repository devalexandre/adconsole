<?php
/**
 * Created by PhpStorm.
 * User: indev
 * Date: 23/11/2018
 * Time: 11:29
 */

namespace App\Command;



use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Breakpoint extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->addOption('--environment', '-e', InputOption::VALUE_REQUIRED, 'The target environment.');

        $this->setName('breakpoint')
            ->setDescription('Manage breakpoints')
            ->addOption('--target', '-t', InputOption::VALUE_REQUIRED, 'The version number to set or clear a breakpoint against')
            ->addOption('--remove-all', '-r', InputOption::VALUE_NONE, 'Remove all breakpoints')
            ->setHelp(
                <<<EOT
The <info>breakpoint</info> command allows you to set or clear a breakpoint against a specific target to inhibit rollbacks beyond a certain target.
If no target is supplied then the most recent migration will be used.
You cannot specify un-migrated targets

<info>phinx breakpoint -e development</info>
<info>phinx breakpoint -e development -t 20110103081132</info>
<info>phinx breakpoint -e development -r</info>
EOT
            );
    }

    /**
     * Toggle the breakpoint.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->bootstrap($input, $output);

        $environment = $input->getOption('environment');
        $version = $input->getOption('target');
        $removeAll = $input->getOption('remove-all');

        if ($environment === null) {
            $environment = $this->getConfig()->getDefaultEnvironment();
            $output->writeln('<comment>warning</comment> no environment specified, defaulting to: ' . $environment);
        } else {
            $output->writeln('<info>using environment</info> ' . $environment);
        }

        if ($version && $removeAll) {
            throw new \InvalidArgumentException('Cannot toggle a breakpoint and remove all breakpoints at the same time.');
        }

        // Remove all breakpoints
        if ($removeAll) {
            $this->getManager()->removeBreakpoints($environment);
        } else {
            // Toggle the breakpoint.
            $this->getManager()->toggleBreakpoint($environment, $version);
        }
    }
}
