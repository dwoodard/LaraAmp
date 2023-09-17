<?php

namespace Dwoodard\Laraamp\Console\Commands;

use Illuminate\Console\Command;

class AmpCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'amp:install
   
  ';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = ' Install AMP for Laravel ';

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
  }

  //install laradock and update .env files
  private function LaradockInstall(): void
  {
    $this->info('Installing Laradock');

    $this->info('- Clone laradock');
    if (!file_exists(base_path() . '/laradock')) {
      shell_exec("git clone https://github.com/Laradock/laradock.git");
      shell_exec("rm -rf laradock/.git");
      $this->info('   - removed laradock/.git');
      chdir(base_path() . '/laradock');
      copy('.env.example', '.env');
    } else {
      $this->info('   - laradock was already cloned');
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
      $this->info('   - laradock .env was already updated');
    }


    //update .env for laravel
    $filename = base_path() . '/.env';
    $env = file_get_contents($filename);
    $this->info("- Checking Laravel .env:");
    if (!str_contains($env, 'DB_HOST=mysql')) {
      $env = preg_replace("/DB_HOST=.*/", "DB_HOST=mysql", $env);
      $this->info('   - DB_HOST has been saved');
    }

    if (!str_contains($env, 'DB_DATABASE=default')) {
      $env = preg_replace("/DB_DATABASE=.*/", "DB_DATABASE=default", $env);
      $this->info('   - DB_DATABASE has been saved');
    }

    if (!str_contains($env, 'REDIS_HOST=redis')) {
      $env = preg_replace("/REDIS_HOST=.*/", "REDIS_HOST=redis", $env);
      $this->info('   - REDIS_HOST has been saved');
    }

    if (!str_contains($env, 'DB_USERNAME=root')) {
      $env = preg_replace("/DB_USERNAME=.*/", "DB_USERNAME=root", $env);
      $this->info('   - DB_USERNAME has been saved');
    }

    if (!str_contains($env, 'DB_PASSWORD=root')) {
      $env = preg_replace("/DB_PASSWORD=.*/", "DB_PASSWORD=root", $env);
      $this->info('   - DB_PASSWORD has been saved');
    }

    file_put_contents(base_path() . '/.env', $env);

    //Add envs
    if (!str_contains($env, 'QUEUE_HOST=beanstalkd')) {
      file_put_contents($filename, 'QUEUE_HOST=beanstalkd' . "\n", FILE_APPEND | LOCK_EX);
    }


    //Add data directory
    $filename = base_path() . '/data';
    if (!file_exists($filename)) {
      mkdir($filename, 0777, true);
      $this->info('- data directory added');
    } else {
      $this->info('- data directory exists');
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
      }
    }
  }



  //update composer.json
  private function updateComposerFile(): void
  {
    //read in composer.json
    $this->info("- Checking composer.json:");
    $composerString = file_get_contents(base_path('composer.json'));
    $composer = json_decode($composerString, true);
    $composer['autoload']['files'][] = "app/Helpers.php";

    //save composer.json
    file_put_contents(base_path('composer.json'), json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    $this->info('   - composer.json has been saved');

    $this->info('composer dump: ' . shell_exec("composer dump-autoload"));
  }
}
