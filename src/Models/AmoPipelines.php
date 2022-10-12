<?php

namespace LebedevSoft\AmoSync\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmoPipelines extends Model
{
    use HasFactory;

    public $timestamps = false;

    public static function getPipelines()
    {
        $res = [];
        $pipelines = static::all()->toArray();
        if (!empty($pipelines)) {
            foreach ($pipelines as $pipeline) {
                $res[$pipeline["id"]] = $pipeline["name"];
            }
        }
        return $res;
    }
}
