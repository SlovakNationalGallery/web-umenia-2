<?php



namespace App;

use Illuminate\Database\Eloquent\Model;

class SpiceHarvesterHarvest extends Model
{

    const STATUS_QUEUED      = 'queued';
    const STATUS_IN_PROGRESS = 'in progress';
    const STATUS_COMPLETED   = 'completed';
    const STATUS_ERROR       = 'error';
    const STATUS_DELETED     = 'deleted';
    const STATUS_KILLED      = 'killed';

    public static $types = ['item' => 'Dielo', 'author' => 'Autorita'];

    protected $appends = array('from');
    public static $datum;
    public static $cron_statuses = ['manual' => 'Manual', 'daily' => 'Daily', 'weekly' => 'Weekly'];
    // public static $cron_status;

    public static $rules = array(
        'base_url' => 'required',
        'metadata_prefix' => 'required',
        );

    public function records()
    {
        return $this->hasMany('App\SpiceHarvesterRecord', 'harvest_id');
    }

    public function collection()
    {
        return $this->belongsTo('App\Collection');
    }

    public function getFromAttribute()
    {
        return $this->start_from;
    }

    public function getStatusClassAttribute()
    {
        switch ($this->status) {
            case self::STATUS_COMPLETED:
                return 'success';
                break;

            case self::STATUS_IN_PROGRESS:
            case self::STATUS_QUEUED:
                return 'warning';
                break;

            case self::STATUS_ERROR:
                return 'danger';
                break;
        }

        return 'default';
    }


}
