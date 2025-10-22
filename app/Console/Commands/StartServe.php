<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class StartServe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'start:serve';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the Laravel development server on port 8070';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Laravel server at http://localhost:8070');

        $process = new Process(['php', 'artisan', 'serve', '--port=8070']);
        $process->setTimeout(null);
        $process->setTty(Process::isTtySupported());
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        return 0;
    }
}
