<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TimeEntry extends Model
{
    protected $fillable = ['user_id','date','start_time','end_time','duration_hours','notes'];
    protected $casts = ['date' => 'date'];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function createForUser(array $data)
    {
        $data['user_id'] = Auth::id();
        if(isset($data['start_time']) && isset($data['end_time'])) {
        
            $start = \Carbon\Carbon::createFromFormat('H:i', $data['start_time']);
            $end   = \Carbon\Carbon::createFromFormat('H:i', $data['end_time']);

            if ($end->lessThan($start)) {
                $end->addDay(); // caso “turno che supera la mezzanotte”
            }

            $minutes = $end->diffInMinutes($start);
            $data['duration_hours'] = round($minutes / 60, 2);
        }
        
        return static::create($data);
    }
}
