<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MigrateActivityAndSessionData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Migrate activity logs using optimized SQL
        $this->migrateActivityLogsSql();
        
        // Migrate user sessions from user_activity
        $this->migrateUserSessions();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Keep data safe - manual cleanup only
    }

    /**
     * Migrate activity logs using raw SQL for better performance
     */
    private function migrateActivityLogsSql()
    {
        // Use INSERT ... SELECT for efficient bulk insert
        $sql = "
            INSERT INTO qad_activity_logs 
            (user_id, program_id, cost_center, end_user, transaction_code, activity_date, activity_time, 
             effective_date, last_activity, promise, activity_type, status, created_at, updated_at)
            SELECT 
                qu.id,
                qp.id,
                hl.cc,
                hl.end_user,
                hl.trans,
                hl.date,
                hl.time,
                hl.eff_date,
                hl.last_activity,
                hl.promise,
                hl.type,
                hl.status,
                NOW(),
                NOW()
            FROM history_logs hl
            LEFT JOIN qad_users qu ON qu.user_id = hl.user_id
            LEFT JOIN qad_programs qp ON qp.address = hl.program
            WHERE qu.id IS NOT NULL AND qp.id IS NOT NULL
        ";
        
        DB::statement($sql);
    }

    /**
     * Migrate user sessions from user_activity
     */
    private function migrateUserSessions()
    {
        $sql = "
            INSERT INTO qad_user_sessions 
            (user_id, year, first_login_date, last_logout_date, total_sessions, days_active, 
             avg_sess_per_day, sess_at_peak, avg_hrs_per_wk, created_at, updated_at)
            SELECT 
                qu.id,
                ua.tahun,
                ua.first_login_date,
                ua.last_logout_date,
                ua.total_sessions,
                ua.days_active,
                ua.avg_sess_per_day,
                ua.sess_at_peak,
                ua.avg_hrs_per_wk,
                NOW(),
                NOW()
            FROM user_activity ua
            LEFT JOIN qad_users qu ON qu.user_id = ua.user_id
            WHERE qu.id IS NOT NULL
        ";
        
        DB::statement($sql);
    }
}
