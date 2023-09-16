<?php

namespace Dwoodard\Laraamp\Console\Commands;

use Illuminate\Console\Command;

class AmpCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   I want some thing like amp:install or amp:setup

   php artisan amp:install
   php artisan amp:setup
   */
  protected $signature = 'amp:install
  {--setup : Run the setup procedure}
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
    //show that it works
    $this->info('Hello from Laraamp!');
  }
}
