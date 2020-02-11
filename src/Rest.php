<?php
/**
 * Created by PhpStorm.
 * User: indev
 * Date: 23/11/2018
 * Time: 12:31
 */

namespace App\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Rest extends Command
{

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('rest:install')

            // the short description shown while running "php bin/console list"
            ->setDescription('install rest with JWT auth')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to install rest with JWT auth')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        if(!file_exists('rest.php')){
            $contents = file_get_contents(dirname(__FILE__,2).'/templates/rest/rest.php.dist');

            if (file_put_contents('rest.php', $contents) === false) {
                throw new RuntimeException(sprintf(
                    'The file "%s"  already exists',
                    'rest.php'
                ));
            }

            if(!file_exists('app/config/adconfig.ini')){
                $contents = file_get_contents(dirname(__FILE__,2).'/templates/adconfig.ini.dist');
                $contents .= file_get_contents(dirname(__FILE__,2).'/templates/rest/rest.ini.dist');
                if (file_put_contents('app/config/adconfig.ini', $contents) === false) {
                    throw new RuntimeException(sprintf(
                        'The file "%s" could not be written to',
                        'app/config/adconfig.ini'
                    ));
                }

                $output->writeln("<info>created</info> app/config/adconfig.ini");
            }else{
                $contents = file_get_contents('app/config/adconfig.ini');
                $contents .= file_get_contents(dirname(__FILE__,2).'/templates/rest/rest.ini.dist');
                if (file_put_contents('app/config/adconfig.ini', $contents) === false) {
                    throw new RuntimeException(sprintf(
                        'The file "%s" could not be written to',
                        'app/config/adconfig.ini'
                    ));
                }
            }


            $output->writeln("<info>created</info> rest.php");
        }



    }
}