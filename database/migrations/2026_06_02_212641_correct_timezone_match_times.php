<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Correct Match Times
        $jsonPath = database_path('data/wc2026.json');
        if (file_exists($jsonPath)) {
            $wcData = json_decode(file_get_contents($jsonPath), true);
            $matchesList = $wcData['matches'] ?? [];
            
            foreach ($matchesList as $index => $matchData) {
                $matchNumber = $index + 1;
                $date = $matchData['date'];
                $timeString = $matchData['time'];
                $parsedDateTime = null;

                if (preg_match('/(\d{2}:\d{2})\s+UTC([+-]\d+)/', $timeString, $matchesOffset)) {
                    $time = $matchesOffset[1];
                    $offset = intval($matchesOffset[2]);
                    $sign = $offset >= 0 ? '+' : '-';
                    $absOffset = abs($offset);
                    $offsetStr = sprintf('%s%02d:00', $sign, $absOffset);
                    $parsedDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i P', $date . ' ' . $time . ' ' . $offsetStr);
                } else {
                    $parsedDateTime = \Carbon\Carbon::parse($date . ' ' . $timeString);
                }

                // Explicitly set the timezone to America/Guatemala
                $parsedDateTime->setTimezone('America/Guatemala');

                DB::table('matches')
                    ->where('match_number', $matchNumber)
                    ->update([
                        'starts_at' => $parsedDateTime->format('Y-m-d H:i:s')
                    ]);
            }
        }

        // 2. Correct Pools & Tournaments times
        DB::table('tournaments')->update([
            'starts_at' => '2026-06-11 12:00:00',
            'ends_at' => '2026-07-19 18:00:00',
        ]);

        DB::table('pools')->update([
            'starts_at' => '2026-06-11 12:00:00',
            'ends_at' => '2026-07-19 18:00:00',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data correction migration - nothing to do on rollback
    }
};
