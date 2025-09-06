<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CleanDuplicateMuzakkiSeeder extends Seeder
{
    /**
     * Run the database seeds to clean up duplicate Muzakki entries.
     */
    public function run(): void
    {
        // Get all duplicate NIKs
        $duplicates = DB::table('muzakki')
            ->select('nik', DB::raw('COUNT(*) as count'))
            ->groupBy('nik')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            // Get all entries with this NIK
            $entries = DB::table('muzakki')
                ->where('nik', $duplicate->nik)
                ->orderBy('created_at')
                ->get();

            // Keep the first entry and delete the rest
            $firstEntry = $entries->first();
            foreach ($entries->skip(1) as $entry) {
                DB::table('muzakki')->where('id', $entry->id)->delete();
            }
        }

        $this->command->info('Duplicate Muzakki entries cleaned up successfully.');
    }
}