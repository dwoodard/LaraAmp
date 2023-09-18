<?php

namespace Dwoodard\Laraamp\Console\Commands;

use Illuminate\Console\Command;

require __DIR__ . '../../../helpers.php';


class AmpCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'amp:install';

  /**
   * The console command description.
   *
   * @var string
   */


  protected $description = ' LaraAMP is a Laravel package that will run a command that will ask you what you want to install. (Laradock, Stubs, Composer.json) ';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    // Composer Dump Autoload
    $this->info('composer dump: ' . shell_exec("composer dump-autoload"));

    // Install Laradock
    $this->LaradockInstall();

    // Update Composer File
    $this->updateComposerFile();

    // Install Stubs
    $this->installStubs();
  }

  //install laradock and update .env files
  private function LaradockInstall()
  {

    $installLaradock = $this->choice("Would you like to install Laradock?", ['yes', 'no'], 'yes');

    //installLaradock
    if ($installLaradock == 'no') {
      $this->info('Laradock will not be installed');
      $this->newLine(3);
      return;
    }



    $this->info('Installing Laradock');

    $this->info('- Clone laradock');
    if (!file_exists(base_path() . '/laradock')) {
      shell_exec("git clone https://github.com/Laradock/laradock.git");
      shell_exec("rm -rf laradock/.git");
      $this->info('   - removed laradock/.git');
      chdir(base_path() . '/laradock');
      copy('.env.example', '.env');
    } else {
      $this->warn('   - laradock was already cloned');
    }


    //update .env for laradock
    $laradockEnv = file_get_contents(base_path() . '/laradock/.env');
    if (!str_contains($laradockEnv, 'DATA_PATH_HOST=../data')) {
      $laradockEnv = preg_replace("/DATA_PATH_HOST=.*/", "DATA_PATH_HOST=../data", $laradockEnv);
      $php_version = '7.4'; # $this->anticipate('What version of php (8.0 - 7.4 - 7.3)', ['8.0', '7.4', '7.3'], '7.4');
      $laradockEnv = preg_replace("/PHP_VERSION=.*/", "PHP_VERSION=" . $php_version, $laradockEnv);
      file_put_contents(base_path() . '/laradock/.env', $laradockEnv);
      $this->info('   - laradock .env was updated');
    } else {
      $this->warn('   - laradock .env was already updated');
    }


    //update .env for laravel
    $filename = base_path() . '/.env';
    $env = file_get_contents($filename);


    $this->info("- Checking Laravel .env:");


    if (!str_contains($env, 'DB_HOST=mysql')) {
      $env = preg_replace("/DB_HOST=.*/", "DB_HOST=mysql", $env);
      $this->info('   - DB_HOST has been saved');
    } else {
      $this->warn('   - DB_HOST was already updated');
    }

    if (!str_contains($env, 'DB_DATABASE=default')) {
      $env = preg_replace("/DB_DATABASE=.*/", "DB_DATABASE=default", $env);
      $this->info('   - DB_DATABASE has been saved');
    } else {
      $this->warn('   - DB_DATABASE was already updated');
    }

    if (!str_contains($env, 'REDIS_HOST=redis')) {
      $env = preg_replace("/REDIS_HOST=.*/", "REDIS_HOST=redis", $env);
      $this->info('   - REDIS_HOST has been saved');
    } else {
      $this->warn('   - REDIS_HOST was already updated');
    }

    if (!str_contains($env, 'DB_USERNAME=root')) {
      $env = preg_replace("/DB_USERNAME=.*/", "DB_USERNAME=root", $env);
      $this->info('   - DB_USERNAME has been saved');
    } else {
      $this->warn('   - DB_USERNAME was already updated');
    }

    if (!str_contains($env, 'DB_PASSWORD=root')) {
      $env = preg_replace("/DB_PASSWORD=.*/", "DB_PASSWORD=root", $env);
      $this->info('   - DB_PASSWORD has been saved');
    } else {
      $this->warn('   - DB_PASSWORD was already updated');
    }

    file_put_contents(base_path() . '/.env', $env);

    //Add envs
    if (!str_contains($env, 'QUEUE_HOST=beanstalkd')) {
      file_put_contents($filename, 'QUEUE_HOST=beanstalkd' . "\n", FILE_APPEND | LOCK_EX);
    } else {
      $this->warn('   - QUEUE_HOST was already updated');
    }


    //Add data directory
    $this->info("- Data directory");

    $filename = base_path() . '/data';
    if (!file_exists($filename)) {
      mkdir($filename, 0777, true);
      $this->info('- data directory added');
    } else {
      $this->warn('  - data directory exists');
    }

    //add .gitignore items
    $this->info("- Check git " . base_path() . "/.gitignore");
    $gitignore = file_get_contents(base_path() . '/.gitignore');
    $gitignoreChecks = [
      '/data/',
      '!/laradock/.env',
    ];

    foreach ($gitignoreChecks as $check) {
      if (!str_contains($gitignore, $check)) {
        file_put_contents(base_path() . '/.gitignore', $check . "\n", FILE_APPEND | LOCK_EX);
      } else {
        $this->warn('   - ' . $check . ' was already updated');
      }
    }

    $this->newLine(3);
  }

  //update composer.json
  private function updateComposerFile()
  {
    //read in composer.json
    $this->info("- Checking composer.json:");
    $composerString = file_get_contents(base_path('composer.json'));
    $composer = json_decode($composerString, true);

    // if composer doesn't have the files key, add it
    // check if app/Helpers.php is allready there otherwise add it
    if (!isset($composer['autoload']['files']) || !in_array('app/Helpers.php', $composer['autoload']['files'])) {
      $composer['autoload']['files'][] = 'app/Helpers.php';
      $this->info('   - app/Helpers.php has been added to composer.json');
    } else {
      $this->warn('   - app/Helpers.php was already added to composer.json');
    }

    //save composer.json
    file_put_contents(base_path('composer.json'), json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $this->info('composer dump: ' . shell_exec("composer dump-autoload"));
    $this->newLine(3);
  }

  //stub files
  private function installStubs()
  {
    // get current path and go up two level to get the project root to the src directory
    $srcDirectory = dirname(__DIR__, 2); //src directory

    //dd path of project root, create a string of the path;

    $this->info(dirname(__DIR__, 2));

    $installs = $this->choice('What stubs would you like to install? (ex. 0,1,2)', ['common', 'livewire', 'vue'], "0,1", 3, true);

    //write out the stubs to install, $install is an array so put it to a string
    $this->info('Installing Stubs');



    $overwrite = $this->confirm('Stubs will be overwritten if they already exist', true);
    if ($overwrite) {
      $this->info('   - Stubs will be over write what is already there');


      // if common is selected, copy the common directory to the root of the project
      if (in_array('common', $installs)) {
        $this->info('   - common stubs will be installed');
        recursiveCopy($srcDirectory . '/stubs/common', base_path(''));
      }

      // if livewire is selected, copy the livewire directory to the root of the project
      if (in_array('livewire', $installs)) {
        $this->info('   - livewire stubs will be installed');
        recursiveCopy($srcDirectory . '/stubs/livewire', base_path(''));
      }

      // if vue is selected, copy the vue directory to the root of the project
      if (in_array('vue', $installs)) {
        $this->info('   - vue stubs will be installed');
        recursiveCopy($srcDirectory . '/stubs/vue', base_path(''));
      }
    } else {
      $this->warn('   - Stubs will not be overwritten');
    }

    $this->info('Installing Stubs');



    // all common files will be copied to the root of the project
    // it assumes that the root of the project is the parent directory of this package

    // so if any files in the common directory already exist, they will be overwritten

    //so if i had composer.json in the root of my project, it would be overwritten by the composer.json in the common directory 

    recursiveCopy($srcDirectory . '/stubs/common', base_path(''));
    $this->newLine(3);
  }
}
