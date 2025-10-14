<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\{BlogPost, Product, Page, FAQ};

class RebuildSearchIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:rebuild';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild Scout indexes for all searchable models';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $models = [
            BlogPost::class,
            Product::class,
            Page::class,
            FAQ::class,
        ];

        foreach ($models as $model) {
            $this->info("Importing {$model}");
            $this->call('scout:import', ['model' => $model]);
        }
        $this->info('Done.');
        return self::SUCCESS;
    }
}
