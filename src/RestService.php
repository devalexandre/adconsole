<?php
/**
 * Created by PhpStorm.
 * User: indev
 * Date: 23/11/2018
 * Time: 13:56
 */

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
class RestService extends Command
{

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('rest:service:create')
            ->addArgument('name',null,'Name for Service')
            ->addOption('model','m',InputOption::VALUE_OPTIONAL,'ACTIVE_RECORD used in service ')
            ->addOption('database','d',InputOption::VALUE_OPTIONAL,'DataBase used in service ','adconfig')

            // the short description shown while running "php bin/console list"
            ->setDescription('generate service class')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command you can generate service class');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $name = $input->getArgument('name');
        $model = $input->getOption('model');
        $database = $input->getOption('database');

        if(empty($model))
            $model= ucfirst($name);

        if (!file_exists('app/service/' . $name . '.class.php')) {
            $contents = file_get_contents(dirname(__FILE__, 2) . '/templates/rest/service.php.dist');

            $contents = str_replace('$class',$name.'Service',$contents);
            $contents = str_replace('$model',$model,$contents);
            $contents = str_replace('$database',$database,$contents);


            if (file_put_contents('app/service/' . $name . '.class.php', $contents) === false) {
                throw new RuntimeException(sprintf(
                    'The file "%s"  already exists',
                    'app/service/' . $name . 'class.php'
                ));
            }

            $output->writeln("<info>created</info> app/service/{$name}.class.php'");
        }


    }
}
