<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $table = 'tags';

    protected $fillable = [
        'name',
        'color',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all visitors associated with this tag
     */
    public function visitors(): BelongsToMany
    {
        return $this->belongsToMany(Visitor::class, 'visitor_tags', 'tag_id', 'visitor_id');
    }

    /**
     * Scope to get only active tags
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the tag's display color
     */
    public function getDisplayColorAttribute(): string
    {
        return $this->color ?? '#007bff';
    }

    /**
     * Get tag usage count
     */
    public function getUsageCountAttribute(): int
    {
        return $this->visitors()->count();
    }

    /**
     * Check if tag can be deleted (no associated visitors)
     */
    public function canBeDeleted(): bool
    {
        return $this->visitors()->count() === 0;
    }
}
