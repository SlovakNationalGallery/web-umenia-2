<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemTranslation extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'author',
        'title',
        'description',
        'work_type',
        'work_level',
        'topic',
        'subject',
        'measurement',
        'dating',
        'medium',
        'technique',
        'inscription',
        'place',
        'state_edition',
        'gallery',
        'relationship_type',
        'related_work',
        'gallery_collection',
    ];

    public function getRelationshipTypeAttribute($value) {
        if ($value) {
            return $value;
        }

        return trans('dielo.item_relationship_type', [], null, $this->locale);
    }
}
