<?php

namespace LebedevSoft\AmoSync\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmoCustomFields extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function getFields($table)
    {
        switch ($table) {
			case "amo_companies":
            case "companies":
                return $this->where("entity_type", "companies")->whereNotNull("db_name")->whereNull("status")->get();
            case "amo_contacts":
            case "contacts":
                return $this->where("entity_type", "contacts")->whereNotNull("db_name")->whereNull("status")->get();
            case "amo_leads":
            case "leads":
                return $this->where("entity_type", "leads")->whereNotNull("db_name")->whereNull("status")->get();
            default:
                return null;
        }
    }

    public function listFields($entity)
    {
        $where_params = [
            ["entity_type", $entity],
            ["status", "added"]
        ];
        $cfs = $this->where($where_params)->get();
        $cfs_list = null;
        foreach ($cfs as $cf) {
            if (!empty($cf["db_name"])) {
                $cfs_list[$cf["id"]] = $cf["db_name"];
            }
        }
        return $cfs_list;
    }
}
