<?php



namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class SpiceHarvesterRecord extends Model
{
    use SoftDeletes;

    protected $softDelete = true;
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'identifier',
        'type',
    ];

    public function harvest()
    {
        return $this->belongsTo('App\SpiceHarvesterHarvest', 'harvest_id');
    }

    public function item()
    {
        return $this->belongsTo('App\Item', 'item_id');
    }
}
