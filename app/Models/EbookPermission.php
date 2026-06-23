<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EbookPermission extends Model
{
    protected $fillable = [
        'ebook_id',
        'role'
    ];

    public function ebook()
    {
        return $this->belongsTo(Ebook::class, 'ebook_id');
    }
}