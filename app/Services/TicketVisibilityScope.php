<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class TicketVisibilityScope
{
    public static function apply(Builder $query, User $user): Builder
    {
        if ($user->hasAnyRole(['super_admin', 'helpdesk', 'manager_it'])) {
            return $query;
        }

        if ($user->hasRole('pegawai')) {
            return $query->where('reporter_id', $user->id);
        }

        if ($user->hasRole('ketua_tim_kerja')) {
            if ($user->work_unit_id === null) {
                return $query->where('reporter_id', $user->id);
            }

            return $query->whereIn('reporter_id', function ($subQuery) use ($user): void {
                $subQuery->select('id')
                    ->from('users')
                    ->where('work_unit_id', $user->work_unit_id);
            });
        }

        if ($user->hasRole('teknisi')) {
            return $query->where('handler_id', $user->id);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function for(User $user): Builder
    {
        return self::apply(Ticket::query(), $user);
    }
}
