<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Simply call our master FIFA World Cup 2026 setup command
        $this->command->info('Running master QuinMariscal FIFA 2026 seeder...');
        Artisan::call('quinmariscal:seed-worldcup-2026', [], $this->command->getOutput());
    }
}
