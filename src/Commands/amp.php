<?php

namespace Dwoodard\Laraamp\Commands;

use Illuminate\Console\Command;

class amp extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'amp:install {
    --force : Force the operation to run.
  }';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Command description';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    //
  }
}
