<?php

namespace LebedevSoft\AmoSync\Http\Controllers;

use LebedevSoft\AmoCRM\Libs\AmoCRM;
use LebedevSoft\AmoSync\Models\AmoCustomFields;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AmoMigrateController extends Controller
{
    private $amo;

    public function __construct()
    {
        $app_id = config("amo.app_id");
        $this->amo = new AmoCRM($app_id);
    }

    public
    function cfsMigrations($entity)
    {
        $cf_table = new AmoCustomFields();
        $cfs_conf = $cf_table->getFields($entity);
        if (!empty($cfs_conf)) {
            $table_name = null;
            switch ($entity) {
				case "companies":
                    $table_name = "amo_companies";
                    break;
                case "contacts":
                    $table_name = "amo_contacts";
                    break;
                case "leads":
                    $table_name = "amo_leads";
                    break;
            }
            Schema::table($table_name, function (Blueprint $table) {
                $cf_table = new AmoCustomFields();
                $cfs_conf = $cf_table->getFields($table->getTable());
                foreach ($cfs_conf as $cf) {
                    if (!empty($cf["db_name"])) {
                        switch ($cf["db_type"]) {
                            case "integer":
                                $table->bigInteger($cf["db_name"])->nullable()->comment($cf["name"]);
                                break;
                            case "boolean":
                                $table->boolean($cf["db_name"])->nullable()->comment($cf["name"]);
                                break;
                            case "date":
                                $table->date($cf["db_name"])->nullable()->comment($cf["name"]);
                                break;
                            case "timestamp":
                                $table->timestamp($cf["db_name"])->nullable()->comment($cf["name"]);
                                break;
                            default:
                                $table->mediumText($cf["db_name"])->nullable()->comment($cf["name"]);
                                break;
                        }
                        $cf_table->where("id", $cf["id"])->update(["status" => "added"]);
                    }
                }
            });
        }
    }

    public
    function loadCustomFields($entity)
    {
        $cfs = $this->amo->getCustomFields($entity);
        $cf_list = null;
        foreach ($cfs as $cf) {
            $new_cf = [
                "id" => $cf["id"],
                "name" => $cf["name"],
                "type" => $cf["type"],
                "code" => empty($cf["code"]) ? null : $cf["code"],
                "entity_type" => $cf["entity_type"],
                "db_type" => $this->getFieldType($cf["type"]),
            ];
            if (!empty($cf["code"])) {
                $new_cf["db_name"] = strtolower($cf["code"]);
                if (in_array($new_cf["db_name"], ["phone", "email", "position", "im", "user_agreement"])) {
                    $new_cf["status"] = "added";
                }
            }
            AmoCustomFields::updateOrInsert(["id" => $cf["id"]], $new_cf);
            $cf_list[] = $new_cf;
        }
    }

    public function getFieldType($amo_type)
    {
        switch ($amo_type) {
            case "numeric":
                return "integer";
            case "checkbox":
            case "radiobutton":
                return "boolean";
            case "date":
                return "date";
            case "date_time":
                return "timestamp";
            default:
                return "mediumtext";
        }
    }
}
