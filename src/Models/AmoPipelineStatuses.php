<?php

namespace LebedevSoft\AmoSync\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmoPipelineStatuses extends Model
{
    use HasFactory;

    public $timestamps = false;

    public static function getStatuses()
    {
        $res = [];
        $rows = static::get()->groupBy("pipeline_id")->toArray();
        if (!empty($rows)) {
            foreach ($rows as $pipeline => $statuses) {
                foreach ($statuses as $status) {
                    $res[$pipeline][$status["status_id"]] = $status["name"];
                }
            }
        }
        return $res;
    }
}
