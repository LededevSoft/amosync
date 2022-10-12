<?php

namespace LebedevSoft\AmoSync\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmoCompanyContacts extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function updateSyncStatus($company_id)
    {
        $this->where("company_id", $company_id)->update(["is_sync" => false]);
    }

    public function removeNotSync()
    {
        $this->where("is_sync", false)->delete();
    }
}
