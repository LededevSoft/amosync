<?php

namespace Adminka\AmoSync\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmoLeadCompanies extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function updateSyncStatus($lead_id)
    {
        $this->where("lead_id", $lead_id)->update(["is_sync" => false]);
    }

    public function removeNotSync()
    {
        $this->where("is_sync", false)->delete();
    }
}
