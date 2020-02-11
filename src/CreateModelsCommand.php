<?php
/**
 * Created by PhpStorm.
 * User: Indev
 * Date: 16/11/18
 * Time: 09:52
 */

namespace App\Command;

use PDO;
use Phinx\Config\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class CreateModelsCommand extends Command
{

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:model:create')

            // the short description shown while running "php bin/console list"
            ->setDescription('create a new model')
            ->addArgument('name',null,'Name for Model')
          //  ->addArgument('fields',null,'List of fiends')
            ->addOption('fields','f',InputOption::VALUE_OPTIONAL,'add fields')
            ->addOption('primary-key','k',InputOption::VALUE_OPTIONAL,'primary key')
            ->addOption('assosiacao','s',InputOption::VALUE_OPTIONAL,'add assosiacao')
            ->addOption('composition','c',InputOption::VALUE_OPTIONAL,'add composition')
            ->addOption('aggregate','a',InputOption::VALUE_OPTIONAL,'add agreggation')
            ->addOption('pivot',null,InputOption::VALUE_OPTIONAL,'pivot record')
            ->addOption('idpolicy','i',InputOption::VALUE_OPTIONAL,'IDPOLICY')
            ->addOption('path','p',InputOption::VALUE_OPTIONAL,'path for model')
            ->addOption('rest','r',InputOption::VALUE_OPTIONAL,'çreate rest service')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to create a model')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $fields = $input->getOption('fields');
        $key = $input->getOption('primary-key');
        $idpolicy = $input->getOption('idpolicy');
        $ipatch =  $input->getOption('path');
        $assosiacao =  $input->getOption('assosiacao');
        $composition =  $input->getOption('composition');
        $aggregate = $input->getOption('aggregate');
        $pivot = $input->getOption('pivot');
        $rest = $input->getOption('rest');

        if(!empty($rest)){
            $this->createRestService($name,$output);
        }

        $pivot = (empty($pivot)?ucfirst($name).ucfirst($aggregate):$pivot);

        $key = (empty($key)?'id':$key);
        $idpolicy = (empty($idpolicy)?'max':$idpolicy);



        if(empty($ipatch))
            $ipatch = 'app/model';

        $out = $ipatch.'/'.$name.'.class.php';

        $patch = dirname( __FILE__,2);

        $patch = realpath($patch);


        $file = file_get_contents($patch.'/templates/models/HRecord.php');
        $file = str_replace('$name',$name,$file);
        $file = str_replace('$idpolicy',$idpolicy,$file);
        $file = str_replace('$key',$key,$file);



        $lines = ''; // atributos
        $asslines = ''; // associacao
        $comline = '';
        $linegetandset = '';
        $methodsFile = '';


// isere as associacoes



        $lines .= $this->addAttribute($fields);



// associacao
if(!empty($assosiacao)) {
    if ($this->strCharFind(',', $assosiacao)) {

        $assosiacao = is_array($assosiacao) ? $assosiacao : explode(',', $assosiacao);
        $lines .= $this->addAttribute($assosiacao, 'id');
        foreach ($assosiacao as $ass) {
            $asslines .= "private $".strtolower($ass)."; \n\t";

        }

    } else {
        $asslines .= "private $".strtolower($assosiacao)."; \n\t";
        $lines .= $this->addAttribute(strtolower($assosiacao), 'id');
    }


    if (!empty($assosiacao)) {
        $assfile = file_get_contents($patch . '/templates/models/getset.php');
        $assosiacao = is_array($assosiacao) ? $assosiacao : explode(',', $assosiacao);

        // insere os get and Set
        if ($this->strCharFind(',', $assosiacao) || is_array($assosiacao)) {

            foreach ($assosiacao as $ass) {
                $nameclass = ucfirst($ass);

                $novo = str_replace('$name', $ass, $assfile);
                $novo = str_replace('$class', $nameclass, $novo);
                $novo = str_replace('$pai', $name, $novo);

                $linegetandset .= "$novo \n\t";
            }
        } else {
            $nameclass = ucfirst($assosiacao);
            $novo = str_replace('$name', $assosiacao, $assfile);
            $novo = str_replace('$class', $nameclass, $novo);
            $novo = str_replace('$pai', $name, $novo);

            $linegetandset .= "$novo \n\t";
        }

    }
}


        // composicao
        if(!empty($composition)) {
            if ($this->strCharFind(',', $composition)) {

                $composition = is_array($composition) ? $composition : explode(',', $composition);

                foreach ($composition as $ass) {

                     $asslines .= "private $".$ass."; \n\t";

                }

            } else {
               
                 $asslines .= "private $".$composition."; \n\t";

            }

            if (!empty($composition)) {

                $comfile = file_get_contents($patch . '/templates/models/composition.php');
                $methodsFile = file_get_contents($patch . '/templates/models/methods.php');

                $composition = is_array($composition) ? $composition : explode(',', $composition);
                $load = '';
                $save = '';
                $delete = '';

                // insere os get and Set
                if ($this->strCharFind(',', $composition) || is_array($composition)) {

                    foreach ($composition as $com) {
                        $nameclass = ucfirst($com);

                        $novo = str_replace('$name', $com, $comfile);
                        $novo = str_replace('$class', $nameclass, $novo);
                        $novo = str_replace('$pai', $name, $novo);

                        $comline .= "$novo \n\t";

                        $load .= $this->loadcomposition($com,$patch,$name);
                        $save .= $this->savecomposition($com,$patch,$name);
                        $delete .= $this->deletecomposition($com,$patch,$name);

                    }
                } else {
                    $nameclass = ucfirst($composition);
                    $novo = str_replace('$name', $assosiacao, $assfile);
                    $novo = str_replace('$class', $nameclass, $novo);
                    $novo = str_replace('$pai', $name, $novo);

                    $comline .= "$novo \n\t";


                    $load = $this->loadcomposition($composition,$patch,$name);
                    $save = $this->savecomposition($composition,$patch,$name);
                    $delete = $this->deletecomposition($composition,$patch,$name);


                }

                // implementar metodos

                $methodsFile = str_replace('$loadComposite',$load,$methodsFile);
                $methodsFile = str_replace('$saveComposite',$save,$methodsFile);
                $methodsFile = str_replace('$deleteComposite',$delete,$methodsFile);
            }

        }else{
            $methodsFile = empty($methodsFile)?file_get_contents($patch . '/templates/models/methods.php'):$methodsFile;



            $methodsFile = str_replace('$loadComposite','',$methodsFile);
            $methodsFile = str_replace('$saveComposite','',$methodsFile);
            $methodsFile = str_replace('$deleteComposite','',$methodsFile);
        }

        // agreggation

        // composicao
        if(!empty($aggregate)) {
            if ($this->strCharFind(',', $aggregate)) {

                $aggregate = is_array($aggregate) ? $aggregate : explode(',', $aggregate);

                foreach ($aggregate as $ass) {
                     $asslines .= "private $".$ass."; \n\t";
                  

                }

            } else {
                 $asslines .= "private $".$aggregate."; \n\t";
             

            }

            if (!empty($aggregate)) {

                $comfile = file_get_contents($patch . '/templates/models/composition.php');

                $methodsFile = empty($methodsFile)?file_get_contents($patch . '/templates/models/methods.php'):$methodsFile;

                $aggregate = is_array($aggregate) ? $aggregate : explode(',', $aggregate);
                $load = '';
                $save = '';
                $delete = '';

                // insere os get and Set
                if ($this->strCharFind(',', $aggregate) || is_array($aggregate)) {

                    foreach ($aggregate as $com) {
                        $nameclass = ucfirst($com);

                        $novo = str_replace('$name', $com, $comfile);
                        $novo = str_replace('$class', $nameclass, $novo);
                        $novo = str_replace('$pai', $name, $novo);

                        $comline .= "$novo \n\t";

                        $load .= $this->loadAggregate($name,$patch,$com,$pivot);
                        $save .= $this->saveAggregate($name,$patch,$com,$pivot);
                        $delete .= $this->deletecomposition($pivot,$patch,$name);

                    }
                } else {
                    $nameclass = ucfirst($composition);
                    $novo = str_replace('$name', $assosiacao, $assfile);
                    $novo = str_replace('$class', $nameclass, $novo);
                    $novo = str_replace('$pai', $name, $novo);

                    $comline .= "$novo \n\t";


                    $load = $this->loadAggregate($name,$patch,$aggregate,$pivot);
                    $save = $this->savecomposition($name,$patch,$aggregate,$pivot);
                    $delete = $this->deleteaggregate($pivot,$patch,$name);


                }

                // implementar metodos

                $methodsFile = str_replace('$loadAggregate',$load,$methodsFile);
                $methodsFile = str_replace('$saveAggregate',$save,$methodsFile);
                $methodsFile = str_replace('$deleteAggregate',$delete,$methodsFile);
            }

            /// limpa
        }else{
            $methodsFile = str_replace('$loadAggregate','',$methodsFile);
            $methodsFile = str_replace('$saveAggregate','',$methodsFile);
            $methodsFile = str_replace('$deleteAggregate','',$methodsFile);
        }


        if(empty($aggregate) && empty($composition))
            $methodsFile = '';

        $file = str_replace('$assosiacao',$asslines,$file);
        $file = str_replace('$fields',$lines,$file);
        $file = str_replace('$getset',$linegetandset,$file);
        $file = str_replace('$composition',$comline,$file);
        $file = str_replace('$methods',$methodsFile,$file);


        file_put_contents($out,$file);

        $output->writeln("<info>created</info>  {$out}");

        }


        private function addAttribute($fields,$prefix = ''){
            $lines = '';
            $fields = is_array($fields)?$fields:explode(',', $fields);


            if($this->strCharFind(',', $fields) || is_array($fields)) {
                // insere os compos

                foreach ($fields as $field) {
                    if(!empty($prefix))
                          $field = strtolower($field).'_'.$prefix;

                    $lines .= "parent::addAttribute('$field'); \n\t";


                }

            }else{
                if(!empty($prefix))
                    $fields = strtolower($fields).'_'.$prefix;

                $lines .= "parent::addAttribute('$fields'); \n\t";
            }
            return $lines;
        }

        private function loadcomposition($name,$patch,$pai){

            $load = file_get_contents($patch.'/templates/models/metods/loadComposite.php');

            $nameclass = ucfirst($name);

            $novo = str_replace('$name', $name, $load);
            $novo = str_replace('$class', $nameclass, $novo);
            $novo = str_replace('$pai', strtolower($pai), $novo);


           return "$novo \n\t";

        }

    private function savecomposition($name,$patch,$pai){


        $save = file_get_contents($patch.'/templates/models/metods/saveComposite.php');

        $nameclass = ucfirst($name);

        $novo = str_replace('$name', $name, $save);
        $novo = str_replace('$class', $nameclass, $novo);
        $novo = str_replace('$pai', strtolower($pai), $novo);


        return "$novo \n\t";

    }

    private function deletecomposition($name,$patch,$pai){


        $delete = file_get_contents($patch.'/templates/models/metods/deleteComposite.php');

        $nameclass = ucfirst($name);

        $novo = str_replace('$name', $name, $delete);
        $novo = str_replace('$class', $nameclass, $novo);
        $novo = str_replace('$pai', strtolower($pai), $novo);


        return   "$novo \n\t";

    }


    private function loadAggregate($name,$patch,$pai,$pivot){

        $load = file_get_contents($patch.'/templates/models/metods/loadAggregate.php');

        $nameclass = ucfirst($pai);

        $novo = str_replace('$name', strtolower($name), $load);
        $novo = str_replace('$class', $nameclass, $novo);
        $novo = str_replace('$pai', strtolower($pai), $novo);
        $novo = str_replace('$pivot', $pivot, $novo);


        return "$novo \n\t";

    }

    private function saveAggregate($name,$patch,$pai,$pivot){


        $save = file_get_contents($patch.'/templates/models/metods/saveAggregate.php');

        $nameclass = ucfirst($name);

        $novo = str_replace('$name', strtolower($name), $save);
        $novo = str_replace('$class', $nameclass, $novo);
        $novo = str_replace('$pai', strtolower($pai), $novo);
        $novo = str_replace('$pivot', $pivot, $novo);


        return "$novo \n\t";

    }


    private function deleteaggregate($pivot,$patch,$pai){


        $delete = file_get_contents($patch.'/templates/models/metods/deleteComposite.php');


        $novo = str_replace('$class', $pivot, $delete);
        $novo = str_replace('$pai', $pai, $novo);


        return   "$novo \n\t";

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

    private function createRestService($name,OutputInterface $output)
    {



        if (!file_exists('app/service/' . $name . '.class.php')) {
            $contents = file_get_contents(dirname(__FILE__, 2) . '/templates/rest/service.php.dist');

            $contents = str_replace('$class',$name.'Service',$contents);
            $contents = str_replace('$model',$name,$contents);
            $contents = str_replace('$database','adconfig',$contents);


            if (file_put_contents('app/service/' . $name . 'class.php', $contents) === false) {
                throw new RuntimeException(sprintf(
                    'The file "%s"  already exists',
                    'app/service/' . $name . 'class.php'
                ));
            }

            $output->writeln("<info>created</info> 'app/service/' . $name . 'class.php'");
        }


    }

    private function getConn(){
    try {


        $contents =  Config::fromPhp('app/config/migration.php');

       $dev =  $contents->getDefaultEnvironment();

        $dados =  $contents->getEnvironment($dev);


        $conn = $this->openArray($dados);

            return $conn;

        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }
    }

    public  function openArray($db)
    {
        try{
        // read the database properties
        $user  = isset($db['user']) ? $db['user'] : NULL;
        $pass  = isset($db['pass']) ? $db['pass'] : NULL;
        $name  = isset($db['name']) ? $db['name'] : NULL;
        $host  = isset($db['host']) ? $db['host'] : NULL;
        $type  = isset($db['adapter']) ? $db['adapter'] : NULL;
        $port  = isset($db['port']) ? $db['port'] : NULL;
        $char  = isset($db['char']) ? $db['char'] : NULL;
        $flow  = isset($db['flow']) ? $db['flow'] : NULL;
        $fkey  = isset($db['fkey']) ? $db['fkey'] : NULL;
        $type  = strtolower($type);

        // each database driver has a different instantiation process
        switch ($type)
        {
            case 'pgsql':
                $port = $port ? $port : '5432';
                $conn = new PDO("pgsql:dbname={$name};user={$user}; password={$pass};host=$host;port={$port}");
                if(!empty($char))
                {
                    $conn->exec("SET CLIENT_ENCODING TO '{$char}';");
                }
                break;
            case 'mysql':
                $port = $port ? $port : '3306';
                if ($char == 'ISO')
                {
                    $conn = new PDO("mysql:host={$host};port={$port};dbname={$name}", $user, $pass);
                }
                else
                {
                    $conn = new PDO("mysql:host={$host};port={$port};dbname={$name}", $user, $pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
                }
                break;
            case 'sqlite':
                $conn = new PDO("sqlite:{$name}");
                if (is_null($fkey) OR $fkey == '1')
                {
                    $conn->query('PRAGMA foreign_keys = ON'); // referential integrity must be enabled
                }
                break;
            case 'ibase':
            case 'fbird':
                $db_string = empty($port) ? "{$host}:{$name}" : "{$host}/{$port}:{$name}";
                $charset = $char ? ";charset={$char}" : '';
                $conn = new PDO("firebird:dbname={$db_string}{$charset}", $user, $pass);
                break;
            case 'oracle':
                $port    = $port ? $port : '1521';
                $charset = $char ? ";charset={$char}" : '';
                $tns     = isset($db['tns']) ? $db['tns'] : NULL;

                if ($tns)
                {
                    $conn = new PDO("oci:dbname={$tns}{$charset}", $user, $pass);
                }
                else
                {
                    $conn = new PDO("oci:dbname={$host}:{$port}/{$name}{$charset}", $user, $pass);
                }

                if (isset($db['date']))
                {
                    $date = $db['date'];
                    $conn->query("ALTER SESSION SET NLS_DATE_FORMAT = '{$date}'");
                }
                if (isset($db['time']))
                {
                    $time = $db['time'];
                    $conn->query("ALTER SESSION SET NLS_TIMESTAMP_FORMAT = '{$time}'");
                }
                if (isset($db['nsep']))
                {
                    $nsep = $db['nsep'];
                    $conn->query("ALTER SESSION SET NLS_NUMERIC_CHARACTERS = '{$nsep}'");
                }
                break;
            case 'mssql':
                if (OS == 'WIN')
                {
                    if ($port)
                    {
                        $conn = new PDO("sqlsrv:Server={$host},{$port};Database={$name}", $user, $pass);
                    }
                    else
                    {
                        $conn = new PDO("sqlsrv:Server={$host};Database={$name}", $user, $pass);
                    }
                }
                else
                {
                    if ($port)
                    {
                        $conn = new PDO("dblib:host={$host}:{$port};dbname={$name}", $user, $pass);
                    }
                    else
                    {
                        $conn = new PDO("dblib:host={$host};dbname={$name}", $user, $pass);
                    }
                }
                break;
            case 'dblib':
                if ($port)
                {
                    $conn = new PDO("dblib:host={$host}:{$port};dbname={$name}", $user, $pass);
                }
                else
                {
                    $conn = new PDO("dblib:host={$host};dbname={$name}", $user, $pass);
                }
                break;
            case 'sqlsrv':
                if ($port)
                {
                    $conn = new PDO("sqlsrv:Server={$host},{$port};Database={$name}", $user, $pass);
                }
                else
                {
                    $conn = new PDO("sqlsrv:Server={$host};Database={$name}", $user, $pass);
                }
                break;
            default:
                throw new Exception(AdiantiCoreTranslator::translate('Driver not found') . ': ' . $type);
                break;
        }

        // define wich way will be used to report errors (EXCEPTION)
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($flow == '1')
        {
            $conn->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
        }

        // return the PDO object
        return $conn;

        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }
    }
}