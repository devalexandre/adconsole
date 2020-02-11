<?php
/**
 * Created by PhpStorm.
 * User: Indev
 * Date: 19/11/18
 * Time: 15:40
 */

namespace App\Command;


use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Init  extends Command
{

    private $filename = 'migration';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {


        $this->setName('init')
            ->setDescription('Initialize the application for Phinx')
            ->addOption('--format', '-f', InputArgument::OPTIONAL, 'What format should we use to initialize?', 'php')
            ->addArgument('path', InputArgument::OPTIONAL, 'Which path should we initialize for AdConsole?','app/config')
            ->setHelp(sprintf(
                '%sInitializes the application for AdConsole%s',
                PHP_EOL,
                PHP_EOL
            ));
    }

    /**
     * Initializes the application.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input  Interface implemented by all input classes.
     * @param \Symfony\Component\Console\Output\OutputInterface $output Interface implemented by all output classes.
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $this->resolvePath($input);
        $format = strtolower('php');
        $this->writeConfig($path, $format);
        $this->makedirs($output);

        $output->writeln("<info>created</info> {$path}");

    }


    /**
     * Return valid $path for Phinx's config file.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input Interface implemented by all input classes.
     *
     * @return string
     */
    protected function resolvePath(InputInterface $input)
    {
        // get the migration path from the config
        $path = $input->getArgument('path');

        $format = strtolower($input->getOption('format'));
        if (!in_array($format, ['php'])) {
            throw new InvalidArgumentException(sprintf(
                'Invalid format "%s". Format must be either php.',
                $format
            ));
        }



        // Fallback
        if (!$path) {
            $path = getcwd() . DIRECTORY_SEPARATOR . $this->filename. '.' . $format;


        }

        // Adding file name if necessary
        if (is_dir($path)) {
            $path .= DIRECTORY_SEPARATOR . $this->filename . '.' . $format;
        }

        // Check if path is available
        $dirname = dirname($path);
        if (is_dir($dirname) && !is_file($path)) {
            return $path;
        }

        // Path is valid, but file already exists
        if (is_file($path)) {
            throw new InvalidArgumentException(sprintf(
                'Config file "%s" already exists.',
                $path
            ));
        }

        // Dir is invalid
        throw new InvalidArgumentException(sprintf(
            'Invalid path "%s" for config file.',
            $path
        ));
    }

    /**
     * Writes Phinx's config in provided $path
     *
     * @param string $path   Config file's path.
     * @param string $format Format to use for config file
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @return void
     */
    protected function writeConfig($path, $format = 'php')
    {



        // Check if dir is writable
        $dirname = dirname($path);
        if (!is_writable($dirname)) {
            throw new InvalidArgumentException(sprintf(
                'The directory "%s" is not writable',
                $dirname
            ));
        }

        // load the config template
        if (is_dir(dirname(__FILE__,2).'/templates/migrations')) {
            $contents = file_get_contents(dirname(__FILE__,2).'/templates/migrations/phinx.php.dist');
        }




        if (file_put_contents($path, $contents) === false) {
            throw new RuntimeException(sprintf(
                'The file "%s" could not be written to',
                $path
            ));
        }
    }

    protected function makedirs(OutputInterface $output){



        if(!file_exists('app/database/db/migrations')){
            mkdir('app/database/db/migrations',0777,true);
            $output->writeln("<info>created</info> app/database/db/migrations");
        }

        if(!file_exists('app/database/db/seeds')){
            mkdir('app/database/db/seeds',0777,true);
            $output->writeln("<info>created</info> app/database/db/seeds");
        }

        if(!file_exists('app/config/adconfig.ini')){
            $contents = file_get_contents(dirname(__FILE__,2).'/templates/adconfig.ini.dist');

            if (file_put_contents('app/config/adconfig.ini', $contents) === false) {
                throw new RuntimeException(sprintf(
                    'The file "%s" could not be written to',
                    'app/config/adconfig.ini'
                ));
            }

            $output->writeln("<info>created</info> app/config/adconfig.ini");
        }
    }


}