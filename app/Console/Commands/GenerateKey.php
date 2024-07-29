<?php

namespace App\Console\Commands;

use App\Models\Key;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class GenerateKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:key';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Token Key';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::beginTransaction();

        try {
            if (Key::count() == 0) {
                Key::create([
                    'key' => bin2hex(random_bytes(16))
                ]);
            } else {
                Key::first()->update([
                    'key' => bin2hex(random_bytes(16))
                ]);
            }

            DB::commit();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->error($th->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
