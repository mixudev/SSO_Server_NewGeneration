<?php

namespace App\Domain\Sessions\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class SessionInspector
{
    /** @return LengthAwarePaginator<int, object> */
    public function paginate(): LengthAwarePaginator
    {
        if (config('session.driver') !== 'database') {
            return new LengthAwarePaginator([], 0, 20);
        }

        $table = config('session.table', 'sessions');
        if (! DB::getSchemaBuilder()->hasTable($table)) {
            return new LengthAwarePaginator([], 0, 20);
        }

        $expiredBefore = now()->subMinutes((int) config('session.lifetime', 120))->timestamp;

        return DB::table($table)
            ->join('users', 'users.id', '=', $table.'.user_id')
            ->whereNotNull($table.'.user_id')
            ->where($table.'.last_activity', '>=', $expiredBefore)
            ->select([
                $table.'.id',
                $table.'.ip_address',
                $table.'.user_agent',
                $table.'.last_activity',
                'users.name as user_name',
                'users.email as user_email',
            ])
            ->orderByDesc($table.'.last_activity')
            ->orderBy($table.'.id')
            ->paginate(20);
    }
}
