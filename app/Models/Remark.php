<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Remark extends Model
{
    protected $table = 'remarks';
    protected $primaryKey = 'remark_id';

    protected $fillable = [
        'interaction_id',
        'remark_text',
        'interaction_mode',
        'meeting_duration',
        'outcome',
        'added_by',
        'added_by_name',
    ];

    // Relationships
    public function interaction()
    {
        return $this->belongsTo(InteractionHistory::class, 'interaction_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(VmsUser::class, 'added_by');
    }

    public function isInitialRemark()
    {
        return $this->remark_text === 'NA';
    }
}
