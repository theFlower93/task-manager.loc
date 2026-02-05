<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'user_id',
    ];

    protected $casts = [
        'due_date'   => 'date',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'status'   => 'pending',
        'priority' => 'medium',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Скоупы для фильтрации
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    public function scopeDueThisWeek($query)
    {
        return $query->whereBetween('due_date', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast()
            && $this->status !== 'completed';
    }

    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }
}
