<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiGeneration extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'id',
        'outfitId',
        'prompt',
        'modelUsed',
        'generatedImagePath',
        'status',
        'errorMessage',
        'generationTime',
        'createdAt',
    ];

    public function outfit()
    {
        return $this->belongsTo(Outfit::class, 'outfitId');
    }
}