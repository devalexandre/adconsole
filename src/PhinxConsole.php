<?php
/**
 * Created by PhpStorm.
 * User: Indev
 * Date: 17/11/18
 * Time: 14:18
 */

namespace App\Command;


use Symfony\Component\Console\Application;
use Phinx\Console\Command;
use App\Command\CreateModelsCommand;
use App\Command\CreateFormsCommand;
use App\Command\CreateBootstrapFormCommand;

class PhinxConsole extends Application
{

    public function __construct($version = null)
    {


        if ($version === null) {
            $composerConfig = json_decode(file_get_contents(dirname(__FILE__,2).'/composer.json'));
            $version = $composerConfig->version;
        }

        parent::__construct('AdConsole  - https://github.com/devalexandre/adconsole.', $version);
        $this->addCommands([
            new Init(),
            new Create(),
            new Migrate(),
            new Rollback(),
            new Status(),
            new Breakpoint(),
            new Test(),
            new SeedCreate(),
            new SeedRun(),
            new CreateModelsCommand(),
            new Rest(),
            new RestService(),
            new CreateFormsCommand(),
            new CreateBootstrapFormCommand()
        ]);

    }



}