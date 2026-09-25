<?php

namespace App\Models;

use Eloquent as Model;
use App\Traits\DeleteRecord;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;


class Document extends Model implements HasMedia
{
    //Use File Upload Traits
    use InteractsWithMedia;
    use DeleteRecord;

    public $table = 'documents';
    public $fillable = [
        'name',
        'type',
        'date',
    ];
    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [];
}
