<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        
        // Get all existing unique indexes (except PRIMARY) based on database driver
        $indexNames = $this->getExistingUniqueIndexes($driver);
        
        // Delete all existing unique indexes (except PRIMARY) to start fresh
        Schema::table('attendances', function (Blueprint $table) use ($indexNames) {
            foreach ($indexNames as $name) {
                if ($name !== 'attendance_student_date_session_unique') {
                    try {
                        if ($this->indexExists('attendances', $name)) {
                            $table->dropUnique($name);
                        }
                    } catch (\Exception $e) {
                        // Ignore if already deleted
                    }
                }
            }
        });
        
        // Check if our new index exists
        $newIndexExists = $this->indexExists('attendances', 'attendance_student_date_session_unique');
        
        if (!$newIndexExists) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->unique(
                    ['student_school_year_id', 'date', 'session_start'],
                    'attendance_student_date_session_unique'
                );
            });
        }
    }
    
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            try {
                $table->dropUnique('attendance_student_date_session_unique');
            } catch (\Exception $e) {
                // Ignore if index doesn't exist
            }
            
            $table->unique(['student_school_year_id', 'date'], 'attendance_unique');
        });
    }
    
    /**
     * Get existing unique index names based on database driver
     */
    private function getExistingUniqueIndexes(string $driver): array
    {
        $tableName = 'attendances';
        
        if ($driver === 'pgsql') {
            // PostgreSQL query to get unique indexes
            $indexes = DB::select("
                SELECT DISTINCT indexname
                FROM pg_indexes
                WHERE schemaname = 'public'
                  AND tablename = ?
                  AND indexname NOT LIKE '%_pkey'
            ", [$tableName]);
            
            return collect($indexes)->pluck('indexname')->toArray();
        } elseif ($driver === 'mysql') {
            // MySQL query
            $indexes = DB::select("
                SELECT DISTINCT index_name
                FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                  AND table_name = ?
                  AND non_unique = 0
                  AND index_name != 'PRIMARY'
            ", [$tableName]);
            
            return collect($indexes)->pluck('index_name')->toArray();
        } elseif ($driver === 'sqlite') {
            // SQLite - simpler approach, just try to drop what we know
            return $this->getSqliteIndexes($tableName);
        }
        
        return [];
    }
    
    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $tableName, string $indexName): bool
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'pgsql') {
            $result = DB::select("
                SELECT COUNT(*) as count
                FROM pg_indexes
                WHERE schemaname = 'public'
                  AND tablename = ?
                  AND indexname = ?
            ", [$tableName, $indexName]);
            
            return (int) $result[0]->count > 0;
            
        } elseif ($driver === 'mysql') {
            $result = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                  AND table_name = ?
                  AND index_name = ?
            ", [$tableName, $indexName]);
            
            return (int) $result[0]->count > 0;
            
        } elseif ($driver === 'sqlite') {
            // SQLite: check via PRAGMA
            $result = DB::select("PRAGMA index_list({$tableName})");
            return collect($result)->contains('name', $indexName);
        }
        
        return false;
    }
    
    /**
     * Get indexes for SQLite (simplified approach)
     */
    private function getSqliteIndexes(string $tableName): array
    {
        $result = DB::select("PRAGMA index_list({$tableName})");
        return collect($result)
            ->where('unique', 1)
            ->pluck('name')
            ->reject(fn($name) => $name === 'sqlite_autoindex_attendances_1')
            ->toArray();
    }
};