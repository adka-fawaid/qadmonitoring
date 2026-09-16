<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class NormalizeQadDatabase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create qad_modules table (canonical module master)
        Schema::create('qad_modules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('normalized_name')->unique();
            $table->timestamps();
        });

        // Create qad_users from usr_qad
        Schema::create('qad_users', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->unique();
            $table->string('user_name');
            $table->enum('active', ['yes', 'no'])->default('yes');
            $table->date('last_logon')->nullable();
            $table->string('jenis_lisensi')->nullable();
            $table->integer('cc_kuota_lisensi')->nullable();
            $table->date('active_date')->nullable();
            $table->integer('code')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('active');
        });

        // Create qad_programs from modul_qad
        Schema::create('qad_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->nullable()->constrained('qad_modules');
            $table->string('program_code')->nullable();
            $table->string('kode')->nullable();
            $table->string('address')->nullable();
            $table->string('name');
            $table->enum('program_type', ['Entryan', 'Laporan'])->nullable();
            $table->timestamps();
            
            $table->index(['module_id', 'name']);
            $table->index('program_type');
        });

        // Create qad_activity_logs from history_logs (667K records)
        Schema::create('qad_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('qad_users');
            $table->foreignId('program_id')->nullable()->constrained('qad_programs');
            $table->string('cost_center')->nullable();
            $table->string('end_user')->nullable();
            $table->string('transaction_code')->nullable();
            $table->date('activity_date')->nullable();
            $table->decimal('activity_time', 10, 3)->nullable();
            $table->date('effective_date')->nullable();
            $table->string('last_activity')->nullable();
            $table->string('promise')->nullable();
            $table->string('activity_type')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('program_id');
            $table->index('activity_date');
            $table->index(['user_id', 'activity_date']);
            $table->index(['program_id', 'activity_date']);
        });

        // Create qad_user_sessions from user_activity
        Schema::create('qad_user_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('qad_users');
            $table->year('year')->nullable();
            $table->date('first_login_date')->nullable();
            $table->date('last_logout_date')->nullable();
            $table->integer('total_sessions')->nullable();
            $table->integer('days_active')->nullable();
            $table->decimal('avg_sess_per_day', 10, 2)->nullable();
            $table->integer('sess_at_peak')->nullable();
            $table->decimal('avg_hrs_per_wk', 10, 2)->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('year');
        });

        // Create qad_module_implementations for implementation reference
        Schema::create('qad_module_implementations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('qad_modules');
            $table->string('sub_module')->nullable();
            $table->string('implementation_status')->nullable();
            $table->boolean('implementation_possible')->nullable();
            $table->string('investment')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('module_id');
        });

        // Migrate data from old tables
        $this->migrateModules();
        $this->migrateUsers();
        $this->migratePrograms();
        $this->migrateActivityLogs();
        $this->migrateUserSessions();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('qad_module_implementations');
        Schema::dropIfExists('qad_user_sessions');
        Schema::dropIfExists('qad_activity_logs');
        Schema::dropIfExists('qad_programs');
        Schema::dropIfExists('qad_users');
        Schema::dropIfExists('qad_modules');
    }

    /**
     * Migrate modules from modul_qad
     */
    private function migrateModules()
    {
        // Extract unique modules from modul_qad
        $modules = DB::table('modul_qad')
            ->whereRaw("TRIM(`Modul`) <> ''")
            ->whereRaw("TRIM(`Modul`) <> '-'")
            ->distinct()
            ->pluck('Modul');

        $moduleMap = [
            'Sales' => 'Sales',
            'Procurement' => 'Procurement',
            'Procruement' => 'Procurement',
            'LPP' => 'LPP',
            'lpp' => 'LPP',
            'TI' => 'TI',
            'R&D' => 'R&D',
            'RnD' => 'R&D',
            'rnd' => 'R&D',
            'QC' => 'QC',
            'qc' => 'QC',
            'Manufacture' => 'Manufacture',
            'Warehouse' => 'Warehouse',
            'warehouse' => 'Warehouse',
            'Accounting' => 'Accounting',
            'Financial' => 'Financial',
        ];

        $inserted = [];
        foreach ($modules as $module) {
            $normalized = $moduleMap[trim($module)] ?? trim($module);
            
            if (!isset($inserted[$normalized])) {
                DB::table('qad_modules')->insert([
                    'name' => $normalized,
                    'normalized_name' => strtolower(str_replace('&', 'and', $normalized)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $inserted[$normalized] = true;
            }
        }
    }

    /**
     * Migrate users from usr_qad
     */
    private function migrateUsers()
    {
        // Get latest record for each user
        $users = DB::table('usr_qad')
            ->selectRaw('MAX(id) as id')
            ->groupBy('user_id')
            ->pluck('id');

        $userData = DB::table('usr_qad')
            ->whereIn('id', $users)
            ->get();

        foreach ($userData as $user) {
            DB::table('qad_users')->insert([
                'user_id' => $user->user_id,
                'user_name' => $user->user_name,
                'active' => strtolower($user->active) === 'yes' ? 'yes' : 'no',
                'last_logon' => $user->last_logon,
                'jenis_lisensi' => $user->jenis_lisensi,
                'cc_kuota_lisensi' => $user->cc_kuota_lisensi,
                'active_date' => $user->active_date,
                'code' => $user->code,
                'description' => $user->description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Migrate programs from modul_qad
     */
    private function migratePrograms()
    {
        $moduleMap = [
            'Sales' => 'Sales',
            'Procurement' => 'Procurement',
            'Procruement' => 'Procurement',
            'LPP' => 'LPP',
            'lpp' => 'LPP',
            'TI' => 'TI',
            'R&D' => 'R&D',
            'RnD' => 'R&D',
            'rnd' => 'R&D',
            'QC' => 'QC',
            'qc' => 'QC',
            'Manufacture' => 'Manufacture',
            'Warehouse' => 'Warehouse',
            'warehouse' => 'Warehouse',
            'Accounting' => 'Accounting',
            'Financial' => 'Financial',
        ];

        $programs = DB::table('modul_qad')->get();

        foreach ($programs as $program) {
            $moduleName = $moduleMap[trim($program->Modul)] ?? trim($program->Modul);
            $moduleId = DB::table('qad_modules')
                ->where('name', $moduleName)
                ->value('id');

            $programType = strtolower(trim($program->{'Identif Program'} ?? '')) === 'laporan' 
                ? 'Laporan' 
                : 'Entryan';

            DB::table('qad_programs')->insert([
                'module_id' => $moduleId,
                'program_code' => $program->{'kode program'},
                'kode' => $program->Kode,
                'address' => $program->alamat,
                'name' => $program->Nama,
                'program_type' => $programType,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Migrate activity logs from history_logs (667K records)
     */
    private function migrateActivityLogs()
    {
        $batchSize = 10000;
        $totalRecords = DB::table('history_logs')->count();

        for ($offset = 0; $offset < $totalRecords; $offset += $batchSize) {
            $records = DB::table('history_logs')
                ->offset($offset)
                ->limit($batchSize)
                ->get();

            $insertData = [];
            foreach ($records as $record) {
                // Find user_id foreign key
                $userId = DB::table('qad_users')
                    ->where('user_id', $record->user_id)
                    ->value('id');

                // Find program_id foreign key by address (program)
                $programId = DB::table('qad_programs')
                    ->where('address', $record->program)
                    ->value('id');

                $insertData[] = [
                    'user_id' => $userId,
                    'program_id' => $programId,
                    'cost_center' => $record->cc,
                    'end_user' => $record->end_user,
                    'transaction_code' => $record->trans,
                    'activity_date' => $record->date,
                    'activity_time' => $record->time,
                    'effective_date' => $record->eff_date,
                    'last_activity' => $record->last_activity,
                    'promise' => $record->promise,
                    'activity_type' => $record->type,
                    'status' => $record->status,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('qad_activity_logs')->insert($insertData);
        }
    }

    /**
     * Migrate user sessions from user_activity
     */
    private function migrateUserSessions()
    {
        $sessions = DB::table('user_activity')->get();

        foreach ($sessions as $session) {
            $userId = DB::table('qad_users')
                ->where('user_id', $session->user_id)
                ->value('id');

            if ($userId) {
                DB::table('qad_user_sessions')->insert([
                    'user_id' => $userId,
                    'year' => $session->tahun,
                    'first_login_date' => $session->first_login_date,
                    'last_logout_date' => $session->last_logout_date,
                    'total_sessions' => $session->total_sessions,
                    'days_active' => $session->days_active,
                    'avg_sess_per_day' => $session->avg_sess_per_day,
                    'sess_at_peak' => $session->sess_at_peak,
                    'avg_hrs_per_wk' => $session->avg_hrs_per_wk,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
