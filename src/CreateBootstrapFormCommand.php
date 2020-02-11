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

class CreateBootstrapFormCommand extends Command
{

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('form:create:bootstrap')
            ->addArgument('name',null,'Name for Service')
            ->addOption('model','m',InputOption::VALUE_REQUIRED,'ACTIVE_RECORD used in service ')
            ->addOption('database','d',InputOption::VALUE_REQUIRED,'DataBase used in service ','adconfig')
            ->addOption('fields','f',InputOption::VALUE_OPTIONAL,'add fields')
            // the short description shown while running "php bin/console list"
            ->setDescription('generate BootFormBuilder class')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command you can generate BootFormBuilder class');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $name = ucfirst($input->getArgument('name'));
        $model = $input->getOption('model');
        $database = $input->getOption('database');
        $fields = $input->getOption('fields');

        $lines = $this->addAttribute($fields);

        $lform = $this->addAttributeForm($fields);


    

        if(empty($model))
            $model= ucfirst($name);

        if (!file_exists('app/control/' . $name . '.class.php')) {
            $contents = file_get_contents(dirname(__FILE__, 2) . '/templates/forms/BootstrapFormBuilder.php.dist');

            $contents = str_replace('$name',$name,$contents);
            $contents = str_replace('$database',$database,$contents);
            $contents = str_replace('$class',$name,$contents);
            $contents = str_replace('$class',$name,$contents);
            $contents = str_replace('$model',$model,$contents);
            $contents = str_replace('$fields',$lines,$contents);
            $contents = str_replace('$lform',$lform,$contents);


            if (file_put_contents('app/control/' . $name . '.class.php', $contents) === false) {
                throw new RuntimeException(sprintf(
                    'The file "%s"  already exists',
                    'app/control/' . $name . 'class.php'
                ));
            }

            $output->writeln("<info>created</info> app/control/{$name}.class.php'");
        }else{
             $output->writeln("<info>created</info> app/control/{$name}.class.php já existe");
     
        }


    }

    private function addAttribute($fields){
            $lines = '';
            $fields = is_array($fields)?$fields:explode(',', $fields);


            if($this->strCharFind(',', $fields) || is_array($fields)) {
                // insere os compos

                foreach ($fields as $field) {
                  $campo = explode(':',$field);

                  $name = $campo[0];
                  $type = $campo[1];

                    $lines .= "$$name = new $type('$name'); \n\t";


                }

            }
            
            return $lines;
        }

        private function addAttributeForm($fields){
            $lines = '';
            $fields = is_array($fields)?$fields:explode(',', $fields);


            if($this->strCharFind(',', $fields) || is_array($fields)) {
                // insere os compos

                foreach ($fields as $field) {
                  $campo = explode(':',$field);
                  $name = $campo[0];

                    $lines .= '$this->form'."->addFields( [new TLabel('$name')], [$$name] ); \n\t";


                }

            }
            
            return $lines;
        }

        private function strCharFind($needle,$haystack){
        $return = FALSE;

        if(!is_array($haystack)) {
            $arr = str_split($haystack, 1);
            foreach ($arr as $value) {
                if ($value == strtolower($needle) || $value == strtoupper($needle)) {
                    $return = TRUE;
                }
            }
        }
        return $return;
    }
}
