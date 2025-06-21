<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

     protected $fillable = [
        'auditable_id',
        'auditable_type',
        'user_id',
        'action',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

     protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Get the user that owns the audit log.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Get the auditable model.
     */
    public function auditable()
    {
        return $this->morphTo();
    }
    /**
     * Scope a query to only include logs for a specific user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
    /**
     * Scope a query to only include logs for a specific auditable model.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $auditableType
     * @param int $auditableId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForAuditable($query, $auditableType, $auditableId)
    {
        return $query->where('auditable_type', $auditableType)
                     ->where('auditable_id', $auditableId);
    }
    /**
     * Scope a query to only include logs for a specific action.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $action
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForAction($query, $action)   
    {
        return $query->where('action', $action);
    }
    /**
     * Scope a query to only include logs within a specific date range.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithinDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }


}
