<?php

namespace Adminka\AmoSync\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmoContactTags extends Model
{
    use HasFactory;

    public $timestamps = false;

    public static function updateSyncStatus($contact_id)
    {
        static::where("contact_id", $contact_id)->update(["is_sync" => false]);
    }

    public static function removeNotSync()
    {
        static::where("is_sync", false)->delete();
    }
}
