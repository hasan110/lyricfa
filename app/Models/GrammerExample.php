<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrammerExample extends Model
{
    use HasFactory;
    protected $table = 'grammer_examples';
    protected $guarded = [];

    public function grammer_explanation()
    {
        return $this->belongsTo(GrammerExplanation::class, 'grammer_explanation_id');
    }
}
