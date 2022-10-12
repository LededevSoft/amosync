<?php

namespace LebedevSoft\AmoSync\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmoCompanyTags extends Model
{
    use HasFactory;

    public $timestamps = false;

    public static function updateSyncStatus($company_id)
    {
        static::where("company_id", $company_id)->update(["is_sync" => false]);
    }

    public static function removeNotSync()
    {
        static::where("is_sync", false)->delete();
    }
}
