<?php

namespace LebedevSoft\AmoSync\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmoLeadTags extends Model
{
    use HasFactory;

    public $timestamps = false;

    public static function updateSyncStatus($lead_id)
    {
        static::where("lead_id", $lead_id)->update(["is_sync" => false]);
    }

    public static function removeNotSync()
    {
        static::where("is_sync", false)->delete();
    }
}
