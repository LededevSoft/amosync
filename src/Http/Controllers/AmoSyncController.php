<?php

namespace Adminka\AmoSync\Http\Controllers;

use Adminka\AmoCRM\Libs\AmoCRM;
use Adminka\AmoCRM\Models\AmoLogs;
use Adminka\AmoSync\Models\AmoCalls;
use Adminka\AmoSync\Models\AmoNotes;
use Adminka\AmoSync\Models\AmoEmails;
use Adminka\AmoSync\Models\AmoChats;
use Adminka\AmoSync\Models\AmoCompanyContacts;
use Adminka\AmoSync\Models\AmoCompanies;
use Adminka\AmoSync\Models\AmoCompanyTags;
use Adminka\AmoSync\Models\AmoContacts;
use Adminka\AmoSync\Models\AmoContactTags;
use Adminka\AmoSync\Models\AmoCustomFields;
use Adminka\AmoSync\Models\AmoGroups;
use Adminka\AmoSync\Models\AmoLeadCompanies;
use Adminka\AmoSync\Models\AmoLeadContacts;
use Adminka\AmoSync\Models\AmoLeads;
use Adminka\AmoSync\Models\AmoLeadTags;
use Adminka\AmoSync\Models\AmoPipelines;
use Adminka\AmoSync\Models\AmoPipelineStatuses;
use Adminka\AmoSync\Models\AmoStatusHistories;
use Adminka\AmoSync\Models\AmoTaskDeadlines;
use Adminka\AmoSync\Models\AmoTags;
use Adminka\AmoSync\Models\AmoTasks;
use Adminka\AmoSync\Models\AmoTaskTypes;
use Adminka\AmoSync\Models\AmoUsers;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AmoSyncController extends Controller
{
    private $amo;

    public function __construct()
    {
        $app_id = config("amo.app_id");
        $this->amo = new AmoCRM($app_id);
    }

    public
    function loadAmoLeads($date_range = null)
    {
        print_r("Start amocrm leads synchronization\n");
        $db_leads = new AmoLeads();
        $db_lead_contacts = new AmoLeadContacts();
		$db_lead_companies = new AmoLeadCompanies();
        $cfs_config = new AmoCustomFields();
        $cfs_config = $cfs_config->listFields("leads");
        $params = [
            "limit" => 250,
        ];
        if ($date_range) {
            $params["filter"] = [
                "updated_at" => $date_range
            ];
        }
        $get = true;
        $page = 0;
        $total_leads = null;
        while ($get) {
            $params["page"] = $page;
            $amo_leads = $this->amo->getLeads(null, $params);
            if (!empty($amo_leads["_embedded"]["leads"])) {
                $total_leads += sizeof($amo_leads["_embedded"]["leads"]);
                foreach ($amo_leads["_embedded"]["leads"] as $lead) {
//                    dd($lead);
                    $tmp_lead = [
                        "name" => $lead["name"],
                        "price" => $lead["price"],
                        "responsible_user_id" => empty($lead["responsible_user_id"]) ? null : $lead["responsible_user_id"],
                        "group_id" => empty($lead["group_id"]) ? null : $lead["group_id"],
                        "status_id" => empty($lead["status_id"]) ? null : $lead["status_id"],
                        "pipeline_id" => empty($lead["pipeline_id"]) ? null : $lead["pipeline_id"],
                        "loss_reason_id" => empty($lead["status_id"]) ? null : $lead["status_id"],
                        "created_by" => empty($lead["created_by"]) ? null : $lead["created_by"],
                        "updated_by" => empty($lead["updated_by"]) ? null : $lead["updated_by"],
                        "created_at" => empty($lead["created_at"]) ? null : (($lead["created_at"] < 0) ? null : date("Y-m-d H:i:s", $lead["created_at"])),
                        "updated_at" => empty($lead["updated_at"]) ? null : (($lead["updated_at"] < 0) ? null : date("Y-m-d H:i:s", $lead["updated_at"])),
                        "closed_at" => empty($lead["closed_at"]) ? null : (($lead["closed_at"] < 0) ? null : date("Y-m-d H:i:s", $lead["closed_at"])),
                        "closest_task_at" => empty($lead["closest_task_at"]) ? null : (($lead["closest_task_at"] < 0) ? null : date("Y-m-d H:i:s", $lead["closest_task_at"])),
                        "is_deleted" => empty($lead["is_deleted"]) ? null : $lead["is_deleted"],
                        "score" => empty($lead["score"]) ? null : $lead["score"],
                        "account_id" => empty($lead["account_id"]) ? null : $lead["account_id"],
                        "loss_reason" => empty($lead["_embedded"]["loss_reason"]) ? null : $lead["_embedded"]["loss_reason"][0]["name"]
                    ];
                    if (!empty($lead["custom_fields_values"])) {
                        foreach ($lead["custom_fields_values"] as $cf) {
                            if (isset($cfs_config[$cf["field_id"]])) {
                                $tmp_lead[$cfs_config[$cf["field_id"]]] = $this->getCustomFieldValue($cf);
                            }
                        }
                    }
                    $db_leads->updateOrInsert(["id" => $lead["id"]], $tmp_lead);
					
                    if (!empty($lead["_embedded"]["contacts"])) {
                        $db_lead_contacts->updateSyncStatus($lead["id"]);
                        foreach ($lead["_embedded"]["contacts"] as $contact) {
                            $find_param = [
                                "lead_id" => $lead["id"],
                                "contact_id" => $contact["id"]
                            ];
                            $update_param = [
                                "is_main" => $contact["is_main"],
                                "is_sync" => true
                            ];
                            $db_lead_contacts->updateOrInsert($find_param, $update_param);
                        }
                    }
					if (!empty($lead["_embedded"]["companies"])) {
                        $db_lead_companies->updateSyncStatus($lead["id"]);
                        foreach ($lead["_embedded"]["companies"] as $company) {
                            $find_param = [
                                "lead_id" => $lead["id"],
                                "company_id" => $company["id"]
                            ];
							$update_param = [
                                "is_sync" => true
                            ];
                            $db_lead_companies->updateOrInsert($find_param, $update_param);
                        }
                    }
                    if (!empty($lead["_embedded"]["tags"])) {
                        AmoLeadTags::updateSyncStatus($lead["id"]);
                        foreach ($lead["_embedded"]["tags"] as $tag) {
                            $find_param = [
                                "lead_id" => $lead["id"],
                                "tag_id" => $tag["id"]
                            ];
                            $update_param = [
                                "tag_name" => $tag["name"],
                                "is_sync" => true
                            ];
                            AmoLeadTags::updateOrInsert($find_param, $update_param);
                        }
                    }
                }
            }
            print_r("Page - $page, leads number - $total_leads\n");
            if (isset($amo_leads["_links"]["next"])) {
                $page++;
            } else {
                $get = false;
            }
        }
        $db_lead_contacts->removeNotSync(); // Зачистка таблиц с привязкой контактов у сделок
		$db_lead_companies->removeNotSync(); // Зачистка таблиц с привязкой компаний у сделок
        print_r("End amocrm leads synchronization\n");
    }

    public function loadDeletedLeads($date_range = null)
    {
        print_r("Start amocrm deleted leads synchronization\n");
        $db_leads = new AmoLeads();
        $db_lead_contacts = new AmoLeadContacts();
		$db_lead_companies = new AmoLeadCompanies();
        $params = [
            "limit" => 250,
        ];
        if ($date_range) {
            $params["filter"] = [
                "updated_at" => $date_range
            ];
        }
        $get = true;
        $page = 0;
        $total_leads = null;
        while ($get) {
            $params["page"] = $page;
            $amo_leads = $this->amo->getLeads(null, $params, true);
            if (!empty($amo_leads["_embedded"]["leads"])) {
                $total_leads += sizeof($amo_leads["_embedded"]["leads"]);
                foreach ($amo_leads["_embedded"]["leads"] as $lead) {
                    $tmp_lead = [
                        "updated_by" => empty($lead["updated_by"]) ? null : $lead["updated_by"],
                        "updated_at" => empty($lead["updated_at"]) ? null : (($lead["updated_at"] < 0) ? null : date("Y-m-d H:i:s", $lead["updated_at"])),
                        "is_deleted" => empty($lead["is_deleted"]) ? null : $lead["is_deleted"],
                        "account_id" => empty($lead["account_id"]) ? null : $lead["account_id"],
                    ];
                    $db_leads->updateOrInsert(["id" => $lead["id"]], $tmp_lead);
					if (!empty($lead["_embedded"]["companies"])) {
                        $db_lead_companies->updateSyncStatus($lead["id"]);
                        foreach ($lead["_embedded"]["companies"] as $company) {
                            $find_param = [
                                "lead_id" => $lead["id"],
                                "company_id" => $company["id"]
                            ];
                            $update_param = [
                                "is_sync" => true
                            ];
                            $db_lead_contacts->updateOrInsert($find_param, $update_param);
                        }
                    }
                    if (!empty($lead["_embedded"]["contacts"])) {
                        $db_lead_contacts->updateSyncStatus($lead["id"]);
                        foreach ($lead["_embedded"]["contacts"] as $contact) {
                            $find_param = [
                                "lead_id" => $lead["id"],
                                "contact_id" => $contact["id"]
                            ];
                            $update_param = [
                                "is_main" => $contact["is_main"],
                                "is_sync" => true
                            ];
                            $db_lead_contacts->updateOrInsert($find_param, $update_param);
                        }
                    }
                }
            }
            print_r("Page - $page, leads number - $total_leads\n");
            if (isset($amo_leads["_links"]["next"])) {
                $page++;
            } else {
                $get = false;
            }
        }
        $db_lead_contacts->removeNotSync(); // Зачистка таблиці з прив'язкою контактів до сделок
		$db_lead_companies->removeNotSync(); // Зачистка таблиц с привязкой компаний у сделок
        AmoLeadTags::removeNotSync();
        print_r("End amocrm deleted leads synchronization\n");
    }
	
    public
    function loadAmoCompanies($date_range = null)
    {
        print_r("Start amocrm companies synchronization\n");
        $db_companies = new AmoCompanies();
		$db_company_contacts = new AmoCompanyContacts();
        $cfs_config = new AmoCustomFields();
        $cfs_config = $cfs_config->listFields("companies");
        $params = [
            "limit" => 250,
        ];
        if ($date_range) {
            $params["filter"] = [
                "updated_at" => $date_range
            ];
        }
        $get = true;
        $page = 0;
        $total_companies = null;
        while ($get) {
            $params["page"] = $page;
            $amo_companies = $this->amo->getCompanies(null, $params);
            if (!empty($amo_companies["_embedded"]["companies"])) {
                $total_companies += sizeof($amo_companies["_embedded"]["companies"]);
                foreach ($amo_companies["_embedded"]["companies"] as $company) {
                    $tmp_company = [
                        "name" => $company["name"],
                        "responsible_user_id" => empty($company["responsible_user_id"]) ? null : $company["responsible_user_id"],
                        "group_id" => empty($company["group_id"]) ? null : $company["group_id"],
                        "created_by" => empty($company["created_by"]) ? null : $company["created_by"],
                        "updated_by" => empty($company["updated_by"]) ? null : $company["updated_by"],
                        "created_at" => empty($company["created_at"]) ? null : (($company["created_at"] < 0) ? null : date("Y-m-d H:i:s", $company["created_at"])),
                        "updated_at" => empty($company["updated_at"]) ? null : (($company["updated_at"] < 0) ? null : date("Y-m-d H:i:s", $company["updated_at"])),
                        "closest_task_at" => empty($company["closest_task_at"]) ? null : (($company["closest_task_at"] < 0) ? null : date("Y-m-d H:i:s", $company["closest_task_at"])),
                        "is_deleted" => empty($company["is_deleted"]) ? null : $company["is_deleted"],
                    ];
                    if (!empty($company["custom_fields_values"])) {
                        foreach ($company["custom_fields_values"] as $cf) {
                            if (isset($cfs_config[$cf["field_id"]])) {
                                $tmp_company[$cfs_config[$cf["field_id"]]] = $this->getCustomFieldValue($cf);
                            }
                        }
                    }
                    $db_companies->updateOrInsert(["id" => $company["id"]], $tmp_company);
					
					if (!empty($company["_embedded"]["contacts"])) {
                        $db_company_contacts->updateSyncStatus($company["id"]);
                        foreach ($company["_embedded"]["contacts"] as $contact) {
                            $find_param = [
                                "company_id" => $company["id"],
                                "contact_id" => $contact["id"]
                            ];
                            $update_param = [
                                "is_sync" => true
                            ];
                            $db_company_contacts->updateOrInsert($find_param, $update_param);
                        }
                    }
                    if (!empty($company["_embedded"]["tags"])) {
                        AmoCompanyTags::updateSyncStatus($company["id"]);
                        foreach ($company["_embedded"]["tags"] as $tag) {
                            $find_param = [
                                "company_id" => $company["id"],
                                "tag_id" => $tag["id"]
                            ];
                            $update_param = [
                                "tag_name" => $tag["name"],
                                "is_sync" => true
                            ];
                            AmoCompanyTags::updateOrInsert($find_param, $update_param);
                        }
                    }
                }
            }
            print_r("Page - $page, companies number - $total_companies\n");
            if (isset($amo_companies["_links"]["next"])) {
                $page++;
            } else {
                //print_r($amo_companies);
                $get = false;
            }
        }
        AmoCompanyTags::removeNotSync();
        print_r("End amocrm companies synchronization\n");
    }
	
    public
    function loadAmoContacts($date_range = null)
    {
        print_r("Start amocrm contacts synchronization\n");
        $db_contacts = new AmoContacts();
        $cfs_config = new AmoCustomFields();
        $cfs_config = $cfs_config->listFields("contacts");
        $params = [
            "limit" => 250,
        ];
        if ($date_range) {
            $params["filter"] = [
                "updated_at" => $date_range
            ];
        }
        $get = true;
        $page = 0;
        $total_contacts = null;
        while ($get) {
            $params["page"] = $page;
            $amo_contacts = $this->amo->getContacts(null, $params);
            if (!empty($amo_contacts["_embedded"]["contacts"])) {
                $total_contacts += sizeof($amo_contacts["_embedded"]["contacts"]);
                foreach ($amo_contacts["_embedded"]["contacts"] as $contact) {
                    $tmp_contact = [
                        "name" => $contact["name"],
                        "first_name" => empty($contact["first_name"]) ? null : $contact["first_name"],
                        "last_name" => empty($contact["last_name"]) ? null : $contact["last_name"],
                        "responsible_user_id" => empty($contact["responsible_user_id"]) ? null : $contact["responsible_user_id"],
                        "group_id" => empty($contact["group_id"]) ? null : $contact["group_id"],
                        "created_by" => empty($contact["created_by"]) ? null : $contact["created_by"],
                        "updated_by" => empty($contact["updated_by"]) ? null : $contact["updated_by"],
                        "created_at" => empty($contact["created_at"]) ? null : (($contact["created_at"] < 0) ? null : date("Y-m-d H:i:s", $contact["created_at"])),
                        "updated_at" => empty($contact["updated_at"]) ? null : (($contact["updated_at"] < 0) ? null : date("Y-m-d H:i:s", $contact["updated_at"])),
                        "closest_task_at" => empty($contact["closest_task_at"]) ? null : (($contact["closest_task_at"] < 0) ? null : date("Y-m-d H:i:s", $contact["closest_task_at"])),
                        "is_deleted" => empty($contact["is_deleted"]) ? null : $contact["is_deleted"],
                    ];
                    if (!empty($contact["custom_fields_values"])) {
                        foreach ($contact["custom_fields_values"] as $cf) {
                            if (isset($cfs_config[$cf["field_id"]])) {
                                $tmp_contact[$cfs_config[$cf["field_id"]]] = $this->getCustomFieldValue($cf);
                            }
                        }
                    }
                    $db_contacts->updateOrInsert(["id" => $contact["id"]], $tmp_contact);
                    if (!empty($contact["_embedded"]["tags"])) {
                        AmoContactTags::updateSyncStatus($contact["id"]);
                        foreach ($contact["_embedded"]["tags"] as $tag) {
                            $find_param = [
                                "contact_id" => $contact["id"],
                                "tag_id" => $tag["id"]
                            ];
                            $update_param = [
                                "tag_name" => $tag["name"],
                                "is_sync" => true
                            ];
                            AmoContactTags::updateOrInsert($find_param, $update_param);
                        }
                    }
                }
            }
            print_r("Page - $page, contacts number - $total_contacts\n");
            if (isset($amo_contacts["_links"]["next"])) {
                $page++;
            } else {
                //print_r($amo_contacts);
                $get = false;
            }
        }
        AmoContactTags::removeNotSync();
        print_r("End amocrm contacts synchronization\n");
    }

    public
    function loadAmoCalls($date_range = null)
    {
        print_r("Start amocrm calls synchronization\n");
        $db_calls = new AmoCalls();
        $call_types = ["call_in", "call_out"];
        foreach ($call_types as $call_type) {
            $call_params = [
                "limit" => 250,
                "filter" => [
                    "note_type" => $call_type
                ]
            ];
            if ($date_range) {
                $call_params["filter"]["updated_at"] = $date_range;
            }
            $get = true;
            $page = 0;
            $total_calls = 0;
            while ($get) {
                $call_params["page"] = $page;
                $amo_calls = $this->amo->getNotes("contacts", $call_params);
                if (!empty($amo_calls)) {
                    $call_list = null;
                    $total_calls += sizeof($amo_calls["_embedded"]["notes"]);
                    foreach ($amo_calls["_embedded"]["notes"] as $call) {
                        $tmp_call = [
                            "id" => $call["id"],
                            "entity_id" => $call["entity_id"],
                            "created_by" => empty($call["created_by"]) ? null : $call["created_by"],
                            "updated_by" => empty($call["updated_by"]) ? null : $call["updated_by"],
                            "created_at" => empty($call["created_at"]) ? null : (($call["created_at"] < 0) ? null : date("Y-m-d H:i:s", $call["created_at"])),
                            "updated_at" => empty($call["updated_at"]) ? null : (($call["updated_at"] < 0) ? null : date("Y-m-d H:i:s", $call["updated_at"])),
                            "responsible_user_id" => empty($call["responsible_user_id"]) ? null : $call["responsible_user_id"],
                            "group_id" => empty($call["group_id"]) ? null : $call["group_id"],
                            "note_type" => empty($call["note_type"]) ? null : $call["note_type"],
                            "account_id" => empty($call["account_id"]) ? null : $call["account_id"],
                            "uniq" => empty($call["params"]["uniq"]) ? null : $call["params"]["uniq"],
                            "duration" => empty($call["params"]["duration"]) ? null : $call["params"]["duration"],
                            "source" => empty($call["params"]["source"]) ? null : $call["params"]["source"],
                            "link" => empty($call["params"]["link"]) ? null : $call["params"]["link"],
                            "phone" => empty($call["params"]["phone"]) ? null : $call["params"]["phone"],
                            "call_result" => empty($call["params"]["call_result"]) ? null : $call["params"]["call_result"],
                            "call_status" => empty($call["params"]["call_status"]) ? null : $call["params"]["call_status"]
                        ];
                        $call_list[] = $tmp_call;
                    }
                    $db_calls->upsert($call_list, ['id'], ['entity_id', 'created_by', 'updated_by', 'created_at',
                        'updated_at', 'responsible_user_id', 'group_id', 'note_type', 'account_id', 'uniq', 'duration',
                        'source', 'link', 'phone', 'call_result', 'call_status']);
                }
                print_r("Page - $page, calls number - $total_calls\n");
                if (isset($amo_calls["_links"]["next"])) {
                    $page++;
                } else {
                    $get = false;
                }
            }
        }
        print_r("End amocrm calls synchronization\n");
    }
	
	public
    function loadAmoNotesLeads($date_range = null)
    {
        print_r("Start amocrm notes leads synchronization\n");
        $db_notes = new AmoNotes();
        $note_types = ["common"];
        foreach ($note_types as $note_type) {
            $note_params = [
                "limit" => 250,
                "filter" => [
                    "note_type" => $note_type
                ]
            ];
            if ($date_range) {
                $note_params["filter"]["updated_at"] = $date_range;
            }
            $get = true;
            $page = 0;
            $total_notes = 0;
            while ($get) {
                $note_params["page"] = $page;
                $amo_notes = $this->amo->getNotes("leads", $note_params);
                if (!empty($amo_notes)) {
                    $note_list = null;
                    $total_notes += sizeof($amo_notes["_embedded"]["notes"]);
                    foreach ($amo_notes["_embedded"]["notes"] as $note) {
                        $tmp_note = [
                            "id" => $note["id"],
                            "entity_id" => $note["entity_id"],
							"entity_type" => 'lead',
                            "created_by" => empty($note["created_by"]) ? null : $note["created_by"],
                            "updated_by" => empty($note["updated_by"]) ? null : $note["updated_by"],
                            "created_at" => empty($note["created_at"]) ? null : (($note["created_at"] < 0) ? null : date("Y-m-d H:i:s", $note["created_at"])),
                            "updated_at" => empty($note["updated_at"]) ? null : (($note["updated_at"] < 0) ? null : date("Y-m-d H:i:s", $note["updated_at"])),
                            "responsible_user_id" => empty($note["responsible_user_id"]) ? null : $note["responsible_user_id"],
                            "group_id" => empty($note["group_id"]) ? null : $note["group_id"],
                            "note_type" => empty($note["note_type"]) ? null : $note["note_type"],
                            "account_id" => empty($note["account_id"]) ? null : $note["account_id"],
                            "text" => empty($note["params"]["text"]) ? null : $note["params"]["text"]
                        ];
                        $note_list[] = $tmp_note;
                    }
                    $db_notes->upsert($note_list, ['id'], ['entity_id', 'entity_type', 'created_by', 'updated_by', 'created_at',
                        'updated_at', 'responsible_user_id', 'group_id', 'note_type', 'account_id', 'text']);
                }
                print_r("Page - $page, notes leads number - $total_notes\n");
                if (isset($amo_notes["_links"]["next"])) {
                    $page++;
                } else {
                    $get = false;
                }
            }
        }
        print_r("End amocrm notes leads synchronization\n");
    }
	
 	public
    function loadAmoEmailsLeads($date_range = null)
    {
        print_r("Start amocrm emails leads synchronization\n");
        $db_emails = new AmoEmails();
        $email_types = ["amomail_message"];
        foreach ($email_types as $email_type) {
            $email_params = [
                "limit" => 250,
                "filter" => [
                    "note_type" => $email_type
                ]
            ];
            if ($date_range) {
                $email_params["filter"]["updated_at"] = $date_range;
            }
            $get = true;
            $page = 0;
            $total_emails = 0;
            while ($get) {
                $email_params["page"] = $page;
                $amo_emails = $this->amo->getNotes("leads", $email_params);
                if (!empty($amo_emails)) {
                    $email_list = null;
                    $total_emails += sizeof($amo_emails["_embedded"]["notes"]);
                    foreach ($amo_emails["_embedded"]["notes"] as $email) {
                        $tmp_email = [
                            "id" => $email["id"],
                            "entity_id" => $email["entity_id"],
							"entity_type" => 'lead',
                            "created_by" => empty($email["created_by"]) ? null : $email["created_by"],
                            "updated_by" => empty($email["updated_by"]) ? null : $email["updated_by"],
                            "created_at" => empty($email["created_at"]) ? null : (($email["created_at"] < 0) ? null : date("Y-m-d H:i:s", $email["created_at"])),
                            "updated_at" => empty($note["updated_at"]) ? null : (($email["updated_at"] < 0) ? null : date("Y-m-d H:i:s", $email["updated_at"])),
                            "responsible_user_id" => empty($email["responsible_user_id"]) ? null : $email["responsible_user_id"],
                            "group_id" => empty($email["group_id"]) ? null : $email["group_id"],
                            "note_type" => empty($email["note_type"]) ? null : $email["note_type"],
                            "account_id" => empty($email["account_id"]) ? null : $email["account_id"],
                            "income" => empty($email["params"]["income"]) ? null : $email["params"]["income"],
							"from" => empty($email["params"]["from"]["email"]) ? null : $email["params"]["from"]["email"],
							"to" => empty($email["params"]["to"]["email"]) ? null : $email["params"]["to"]["email"],
							"subject" => empty($email["params"]["subject"]) ? null : $email["params"]["subject"],
							"content_summary" => empty($email["params"]["content_summary"]) ? null : $email["params"]["content_summary"],
							"delivery_status" => empty($email["params"]["delivery"]["status"]) ? null : $email["params"]["delivery"]["status"]
                        ];
                        $email_list[] = $tmp_email;
                    }
                    $db_emails->upsert($email_list, ['id'], ['entity_id', 'entity_type', 'created_by', 'updated_by', 'created_at',
                        'updated_at', 'responsible_user_id', 'group_id', 'note_type', 'account_id', 'income',
						'from', 'to', 'subject', 'delivery_status']);
                }
                print_r("Page - $page, emails leads number - $total_emails\n");
                if (isset($amo_emails["_links"]["next"])) {
                    $page++;
                } else {
                    $get = false;
                }
            }
        }
        print_r("End amocrm emails leads synchronization\n");
    }
	
  	public
    function loadAmoEmailsContacts($date_range = null)
    {
        print_r("Start amocrm emails contacts synchronization\n");
        $db_emails = new AmoEmails();
        $email_types = ["amomail_message"];
        foreach ($email_types as $email_type) {
            $email_params = [
                "limit" => 250,
                "filter" => [
                    "note_type" => $email_type
                ]
            ];
            if ($date_range) {
                $email_params["filter"]["updated_at"] = $date_range;
            }
            $get = true;
            $page = 0;
            $total_emails = 0;
            while ($get) {
                $email_params["page"] = $page;
                $amo_emails = $this->amo->getNotes("contacts", $email_params);
                if (!empty($amo_emails)) {
                    $email_list = null;
                    $total_emails += sizeof($amo_emails["_embedded"]["notes"]);
                    foreach ($amo_emails["_embedded"]["notes"] as $email) {
                        $tmp_email = [
                            "id" => $email["id"],
                            "entity_id" => $email["entity_id"],
							"entity_type" => 'contact',
                            "created_by" => empty($email["created_by"]) ? null : $email["created_by"],
                            "updated_by" => empty($email["updated_by"]) ? null : $email["updated_by"],
                            "created_at" => empty($email["created_at"]) ? null : (($email["created_at"] < 0) ? null : date("Y-m-d H:i:s", $email["created_at"])),
                            "updated_at" => empty($note["updated_at"]) ? null : (($email["updated_at"] < 0) ? null : date("Y-m-d H:i:s", $email["updated_at"])),
                            "responsible_user_id" => empty($email["responsible_user_id"]) ? null : $email["responsible_user_id"],
                            "group_id" => empty($email["group_id"]) ? null : $email["group_id"],
                            "note_type" => empty($email["note_type"]) ? null : $email["note_type"],
                            "account_id" => empty($email["account_id"]) ? null : $email["account_id"],
                            "income" => empty($email["params"]["income"]) ? null : $email["params"]["income"],
							"from" => empty($email["params"]["from"]["email"]) ? null : $email["params"]["from"]["email"],
							"to" => empty($email["params"]["to"]["email"]) ? null : $email["params"]["to"]["email"],
							"subject" => empty($email["params"]["subject"]) ? null : $email["params"]["subject"],
							"content_summary" => empty($email["params"]["content_summary"]) ? null : $email["params"]["content_summary"],
							"delivery_status" => empty($email["params"]["delivery"]["status"]) ? null : $email["params"]["delivery"]["status"]
                        ];
                        $email_list[] = $tmp_email;
                    }
                    $db_emails->upsert($email_list, ['id'], ['entity_id', 'entity_type', 'created_by', 'updated_by', 'created_at',
                        'updated_at', 'responsible_user_id', 'group_id', 'note_type', 'account_id', 'income',
						'from', 'to', 'subject', 'delivery_status']);
                }
                print_r("Page - $page, emails contacts number - $total_emails\n");
                if (isset($amo_emails["_links"]["next"])) {
                    $page++;
                } else {
                    $get = false;
                }
            }
        }
        print_r("End amocrm emails contacts synchronization\n");
    }
 
    public
    function loadAmoEmailsCompanies($date_range = null)
    {
        print_r("Start amocrm emails companies synchronization\n");
        $db_emails = new AmoEmails();
        $email_types = ["amomail_message"];
        foreach ($email_types as $email_type) {
            $email_params = [
                "limit" => 250,
                "filter" => [
                    "note_type" => $email_type
                ]
            ];
            if ($date_range) {
                $email_params["filter"]["updated_at"] = $date_range;
            }
            $get = true;
            $page = 0;
            $total_emails = 0;
            while ($get) {
                $email_params["page"] = $page;
                $amo_emails = $this->amo->getNotes("companies", $email_params);
                if (!empty($amo_emails)) {
                    $email_list = null;
                    $total_emails += sizeof($amo_emails["_embedded"]["notes"]);
                    foreach ($amo_emails["_embedded"]["notes"] as $email) {
                        $tmp_email = [
                            "id" => $email["id"],
                            "entity_id" => $email["entity_id"],
							"entity_type" => 'contact',
                            "created_by" => empty($email["created_by"]) ? null : $email["created_by"],
                            "updated_by" => empty($email["updated_by"]) ? null : $email["updated_by"],
                            "created_at" => empty($email["created_at"]) ? null : (($email["created_at"] < 0) ? null : date("Y-m-d H:i:s", $email["created_at"])),
                            "updated_at" => empty($note["updated_at"]) ? null : (($email["updated_at"] < 0) ? null : date("Y-m-d H:i:s", $email["updated_at"])),
                            "responsible_user_id" => empty($email["responsible_user_id"]) ? null : $email["responsible_user_id"],
                            "group_id" => empty($email["group_id"]) ? null : $email["group_id"],
                            "note_type" => empty($email["note_type"]) ? null : $email["note_type"],
                            "account_id" => empty($email["account_id"]) ? null : $email["account_id"],
                            "income" => empty($email["params"]["income"]) ? null : $email["params"]["income"],
							"from" => empty($email["params"]["from"]["email"]) ? null : $email["params"]["from"]["email"],
							"to" => empty($email["params"]["to"]["email"]) ? null : $email["params"]["to"]["email"],
							"subject" => empty($email["params"]["subject"]) ? null : $email["params"]["subject"],
							"content_summary" => empty($email["params"]["content_summary"]) ? null : $email["params"]["content_summary"],
							"delivery_status" => empty($email["params"]["delivery"]["status"]) ? null : $email["params"]["delivery"]["status"]
                        ];
                        $email_list[] = $tmp_email;
                    }
                    $db_emails->upsert($email_list, ['id'], ['entity_id', 'entity_type', 'created_by', 'updated_by', 'created_at',
                        'updated_at', 'responsible_user_id', 'group_id', 'note_type', 'account_id', 'income',
						'from', 'to', 'subject', 'delivery_status']);
                }
                print_r("Page - $page, emails companies number - $total_emails\n");
                if (isset($amo_emails["_links"]["next"])) {
                    $page++;
                } else {
                    $get = false;
                }
            }
        }
        print_r("End amocrm emails companies synchronization\n");
    }
  public	
  function loadAmoNotesCompanies($date_range = null)
    {
        print_r("Start amocrm notes companies synchronization\n");
        $db_notes = new AmoNotes();
        $note_types = ["common"];
        foreach ($note_types as $note_type) {
            $note_params = [
                "limit" => 250,
                "filter" => [
                    "note_type" => $note_type
                ]
            ];
            if ($date_range) {
                $note_params["filter"]["updated_at"] = $date_range;
            }
            $get = true;
            $page = 0;
            $total_notes = 0;
            while ($get) {
                $note_params["page"] = $page;
                $amo_notes = $this->amo->getNotes("companies", $note_params);
                if (!empty($amo_notes)) {
                    $note_list = null;
                    $total_notes += sizeof($amo_notes["_embedded"]["notes"]);
                    foreach ($amo_notes["_embedded"]["notes"] as $note) {
                        $tmp_note = [
                            "id" => $note["id"],
                            "entity_id" => $note["entity_id"],
							"entity_type" => 'contact',
                            "created_by" => empty($note["created_by"]) ? null : $note["created_by"],
                            "updated_by" => empty($note["updated_by"]) ? null : $note["updated_by"],
                            "created_at" => empty($note["created_at"]) ? null : (($note["created_at"] < 0) ? null : date("Y-m-d H:i:s", $note["created_at"])),
                            "updated_at" => empty($note["updated_at"]) ? null : (($note["updated_at"] < 0) ? null : date("Y-m-d H:i:s", $note["updated_at"])),
                            "responsible_user_id" => empty($note["responsible_user_id"]) ? null : $note["responsible_user_id"],
                            "group_id" => empty($note["group_id"]) ? null : $note["group_id"],
                            "note_type" => empty($note["note_type"]) ? null : $note["note_type"],
                            "account_id" => empty($note["account_id"]) ? null : $note["account_id"],
                            "text" => empty($note["params"]["text"]) ? null : $note["params"]["text"]
                        ];
                        $note_list[] = $tmp_note;
                    }
                    $db_notes->upsert($note_list, ['id'], ['entity_id', 'entity_type', 'created_by', 'updated_by', 'created_at',
                        'updated_at', 'responsible_user_id', 'group_id', 'note_type', 'account_id', 'text']);
                }
                print_r("Page - $page, notes companies number - $total_notes\n");
                if (isset($amo_notes["_links"]["next"])) {
                    $page++;
                } else {
                    $get = false;
                }
            }
        }
        print_r("End amocrm notes companies synchronization\n");
    }
 public	
 function loadAmoNotesContacts($date_range = null)
    {
        print_r("Start amocrm notes contacts synchronization\n");
        $db_notes = new AmoNotes();
        $note_types = ["common"];
        foreach ($note_types as $note_type) {
            $note_params = [
                "limit" => 250,
                "filter" => [
                    "note_type" => $note_type
                ]
            ];
            if ($date_range) {
                $note_params["filter"]["updated_at"] = $date_range;
            }
            $get = true;
            $page = 0;
            $total_notes = 0;
            while ($get) {
                $note_params["page"] = $page;
                $amo_notes = $this->amo->getNotes("contacts", $note_params);
                if (!empty($amo_notes)) {
                    $note_list = null;
                    $total_notes += sizeof($amo_notes["_embedded"]["notes"]);
                    foreach ($amo_notes["_embedded"]["notes"] as $note) {
                        $tmp_note = [
                            "id" => $note["id"],
                            "entity_id" => $note["entity_id"],
							"entity_type" => 'contact',
                            "created_by" => empty($note["created_by"]) ? null : $note["created_by"],
                            "updated_by" => empty($note["updated_by"]) ? null : $note["updated_by"],
                            "created_at" => empty($note["created_at"]) ? null : (($note["created_at"] < 0) ? null : date("Y-m-d H:i:s", $note["created_at"])),
                            "updated_at" => empty($note["updated_at"]) ? null : (($note["updated_at"] < 0) ? null : date("Y-m-d H:i:s", $note["updated_at"])),
                            "responsible_user_id" => empty($note["responsible_user_id"]) ? null : $note["responsible_user_id"],
                            "group_id" => empty($note["group_id"]) ? null : $note["group_id"],
                            "note_type" => empty($note["note_type"]) ? null : $note["note_type"],
                            "account_id" => empty($note["account_id"]) ? null : $note["account_id"],
                            "text" => empty($note["params"]["text"]) ? null : $note["params"]["text"]
                        ];
                        $note_list[] = $tmp_note;
                    }
                    $db_notes->upsert($note_list, ['id'], ['entity_id', 'entity_type', 'created_by', 'updated_by', 'created_at',
                        'updated_at', 'responsible_user_id', 'group_id', 'note_type', 'account_id', 'text']);
                }
                print_r("Page - $page, notes contacts number - $total_notes\n");
                if (isset($amo_notes["_links"]["next"])) {
                    $page++;
                } else {
                    $get = false;
                }
            }
        }
        print_r("End amocrm notes contacts synchronization\n");
    }
	public
    function loadAmoChats($date_range = null)
    {
        print_r("Start amocrm chats synchronization\n");
		$chat_types = ["incoming_chat_message", "outgoing_chat_message"];
        $chat_params = [
            "limit" => 100,
            "filter" => [
                "type" => $chat_types
            ]
        ];
        if ($date_range) {
            $chat_params["filter"]["created_at"] = $date_range;
        }

        $db_chats = new AmoChats();
        $get = true;
        $page = 0;
        $total_chats = 0;
        while ($get) {
            $chat_params["page"] = $page;
            $amo_chats = $this->amo->getEvents($chat_params);
            if (!empty($amo_chats["_embedded"]["events"])) {
                $chats_list = null;
                $total_chats += sizeof($amo_chats["_embedded"]["events"]);
                foreach ($amo_chats["_embedded"]["events"] as $chat) {
                    $tmp_chat = [
                        "id" => $chat["id"],
						"entity_id" => $chat["entity_id"],
						"type" => $chat["type"],                       
						"entity_type" => $chat["entity_type"],
                        "created_by" => empty($chat["created_by"]) ? null : $chat["created_by"],
                        "created_at" => empty($chat["created_at"]) ? null : (($chat["created_at"] < 0) ? null : date("Y-m-d H:i:s", $chat["created_at"])),
                        "account_id" => empty($chat["account_id"]) ? null : $chat["account_id"],						
                        "message_id" => empty($chat["value_after"][0]["message"]["id"]) ? null : $chat["value_after"][0]["message"]["id"]

                    ];
                    $chats_list[] = $tmp_chat;
                    $db_chats->upsert($chats_list, ['id'], ['entity_id', 'type','entity_type', 'created_by', 'created_at', 'account_id', 'message_id']);
                }
            }
            print_r("Page - $page, chats number - $total_chats\n");
            if (isset($amo_chats["_embedded"]["events"])) {
                $page++;
            } else {
                $get = false;
            }
        }
        print_r("End amocrm chats synchronization\n");
    }
	public
    function loadAmoTaskDeadlines($date_range = null)
    {
        print_r("Start amocrm task deadline changed synchronization\n");
        $event_params = [
            "limit" => 100,
            "filter" => [
                "type" => "task_deadline_changed"
            ]
        ];
        if ($date_range) {
            $event_params["filter"]["created_at"] = $date_range;
        }

        $db_events = new AmoTaskDeadlines();
        $get = true;
        $page = 0;
        $total_events = 0;
        while ($get) {
            $event_params["page"] = $page;
            $amo_events = $this->amo->getEvents($event_params);
            if (!empty($amo_events["_embedded"]["events"])) {
                $events_list = null;
                $total_events += sizeof($amo_events["_embedded"]["events"]);
                foreach ($amo_events["_embedded"]["events"] as $event) {
                    $tmp_event = [
                        "id" => $event["id"],
                        "entity_id" => $event["entity_id"],
                        "created_by" => empty($event["created_by"]) ? null : $event["created_by"],
                        "created_at" => empty($event["created_at"]) ? null : (($event["created_at"] < 0) ? null : date("Y-m-d H:i:s", $event["created_at"])),
                        "account_id" => empty($event["account_id"]) ? null : $event["account_id"],
                        "new_task_deadline_at" => empty($event["value_after"][0]["task_deadline"]["timestamp"]) ? null : (($event["value_after"][0]["task_deadline"]["timestamp"] < 0) ? null : date("Y-m-d H:i:s", $event["value_after"][0]["task_deadline"]["timestamp"])),
                        "old_task_deadline_at" => empty($event["value_before"][0]["task_deadline"]["timestamp"]) ? null : (($event["value_before"][0]["task_deadline"]["timestamp"] < 0) ? null : date("Y-m-d H:i:s", $event["value_before"][0]["task_deadline"]["timestamp"]))
                    ];

                    $events_list[] = $tmp_event;
                    $db_events->upsert($events_list, ['id'], ['entity_id', 'created_by', 'created_at', 'account_id',
                        'new_task_deadline_at', 'old_task_deadline_at']);
                }
            }
            print_r("Page - $page, events number - $total_events\n");
            if (isset($amo_events["_embedded"]["events"])) {
                $page++;
            } else {
                $get = false;
            }
        }
        print_r("End amocrm task deadline changed synchronization\n");
    }
    public
    function loadAmoStatusHistory($date_range = null)
    {
        print_r("Start amocrm status history synchronization\n");
        $event_params = [
            "limit" => 100,
            "filter" => [
                "type" => "lead_status_changed"
            ]
        ];
        if ($date_range) {
            $event_params["filter"]["created_at"] = $date_range;
        }
        $pipelines = AmoPipelines::getPipelines();
        $statuses = AmoPipelineStatuses::getStatuses();
        $db_events = new AmoStatusHistories();
        $get = true;
        $page = 0;
        $total_events = 0;
        while ($get) {
            $event_params["page"] = $page;
            $amo_events = $this->amo->getEvents($event_params);
            if (!empty($amo_events["_embedded"]["events"])) {
                $events_list = null;
                $total_events += sizeof($amo_events["_embedded"]["events"]);
                foreach ($amo_events["_embedded"]["events"] as $event) {
                    $tmp_event = [
                        "id" => $event["id"],
                        "entity_id" => $event["entity_id"],
                        "created_by" => empty($event["created_by"]) ? null : $event["created_by"],
                        "created_at" => empty($event["created_at"]) ? null : (($event["created_at"] < 0) ? null : date("Y-m-d H:i:s", $event["created_at"])),
                        "account_id" => empty($event["account_id"]) ? null : $event["account_id"],
                        "new_status_id" => empty($event["value_after"][0]["lead_status"]["id"]) ? null : $event["value_after"][0]["lead_status"]["id"],
                        "new_pipeline_id" => empty($event["value_after"][0]["lead_status"]["pipeline_id"]) ? null : $event["value_after"][0]["lead_status"]["pipeline_id"],
                        "old_status_id" => empty($event["value_before"][0]["lead_status"]["id"]) ? null : $event["value_before"][0]["lead_status"]["id"],
                        "old_pipeline_id" => empty($event["value_before"][0]["lead_status"]["pipeline_id"]) ? null : $event["value_before"][0]["lead_status"]["pipeline_id"],
                        "new_status" => null,
                        "new_pipeline" => null,
                        "old_status" => null,
                        "old_pipeline" => null

                    ];
                    if ((!empty($tmp_event["new_status_id"])) && (!empty($tmp_event["new_pipeline_id"]))) {
                        $tmp_event["new_status"] = !empty($statuses) ? ($statuses[$tmp_event["new_pipeline_id"]][$tmp_event["new_status_id"]] ?? null) : null;
                        $tmp_event["new_pipeline"] = !empty($pipelines) ? ($pipelines[$tmp_event["new_pipeline_id"]] ?? null) : null;
                    }
                    if ((!empty($tmp_event["old_status_id"])) && (!empty($tmp_event["old_pipeline_id"]))) {
                        $tmp_event["old_status"] = !empty($statuses) ? ($statuses[$tmp_event["old_pipeline_id"]][$tmp_event["old_status_id"]] ?? null) : null;
                        $tmp_event["old_pipeline"] = !empty($pipelines) ? ($pipelines[$tmp_event["old_pipeline_id"]] ?? null) : null;
                    }
                    $events_list[] = $tmp_event;
                    $db_events->upsert($events_list, ['id'], ['entity_id', 'created_by', 'created_at', 'account_id',
                        'new_status_id', 'new_pipeline_id', 'old_status_id', 'old_pipeline_id', 'new_status', 'new_pipeline', 'old_status', 'old_pipeline']);
                }
            }
            print_r("Page - $page, events number - $total_events\n");
            if (isset($amo_events["_embedded"]["events"])) {
                $page++;
            } else {
                $get = false;
            }
        }
        print_r("End amocrm status history synchronization\n");
    }

    public
    function loadAmoTags()
    {
        print_r("Start amocrm tags synchronization\n");
        $db_tags = new AmoTags();
        $tag_leads = $this->amo->getTags("leads");
        $db_tags->upsert($tag_leads, ['id'], ['name','color']);
        $tag_contacts = $this->amo->getTags("contacts");
        $db_tags->upsert($tag_contacts, ['id'], ['name','color']);
		$tag_companies = $this->amo->getTags("companies");
        $db_tags->upsert($tag_companies, ['id'], ['name','color']);
        print_r("End amocrm tags synchronization\n");
    }

    public
    function loadAmoTasks($date_range = null)
    {
        print_r("Start amocrm tasks synchronization\n");
        $db_groups = new AmoTasks();
        $total_tasks = null;
        $get = true;
        $page = 0;
        $task_params = [
            "limit" => 250,
        ];
        if ($date_range) {
            $task_params["filter"]["updated_at"] = $date_range;
        }
        while ($get) {
            $task_params["page"] = $page;
            $amo_tasks = $this->amo->getTasks($task_params);
            if (!empty($amo_tasks["_embedded"]["tasks"])) {
                $total_tasks += sizeof($amo_tasks["_embedded"]["tasks"]);
                $task_list = null;
                foreach ($amo_tasks["_embedded"]["tasks"] as $task) {
                    $tmp_task = [
                        "id" => $task["id"],
                        "created_by" => empty($task["created_by"]) ? null : $task["created_by"],
                        "updated_by" => empty($task["updated_by"]) ? null : $task["updated_by"],
                        "created_at" => empty($task["created_at"]) ? null : date("Y-m-d H:i:s",$task["created_at"]),
                        "updated_at" => empty($task["updated_at"]) ? null : date("Y-m-d H:i:s",$task["updated_at"]),
                        "responsible_user_id" => empty($task["responsible_user_id"]) ? null : $task["responsible_user_id"],
                        "group_id" => empty($task["group_id"]) ? null : $task["group_id"],
                        "entity_id" => empty($task["entity_id"]) ? null : $task["entity_id"],
                        "entity_type" => empty($task["entity_type"]) ? null : $task["entity_type"],
                        "duration" => empty($task["duration"]) ? null : $task["duration"],
                        "is_completed" => empty($task["is_completed"]) ? null : $task["is_completed"],
                        "task_type_id" => empty($task["task_type_id"]) ? null : $task["task_type_id"],
                        "text" => empty($task["text"]) ? null : $task["text"],
                        "result" => empty($task["result"]) ? null : $task["result"]["text"],
                        "complete_till" => empty($task["complete_till"]) ? null : date("Y-m-d H:i:s",$task["complete_till"]),
                        "account_id" => empty($task["account_id"]) ? null : $task["account_id"],
                    ];
                    $task_list[] = $tmp_task;
                }
                $db_groups->upsert($task_list, ['id'], ['created_by', 'updated_by', 'created_at',
                    'updated_at', 'responsible_user_id', 'group_id', 'entity_id', 'entity_type', 'duration', 'is_completed',
                    'task_type_id', 'text', 'result', 'complete_till', 'account_id']);
            }
            print_r("Page - $page, tasks number - $total_tasks\n");
            if (isset($amo_tasks["_links"]["next"])) {
                $page++;
            } else {
                $get = false;
            }
        }
        print_r("End amocrm tasks synchronization\n");
    }

    public
    function loadAmoPipelines()
    {
        print_r("Start amocrm pipeline synchronization\n");
        $db_pipelines = new AmoPipelines();
        $db_statuses = new AmoPipelineStatuses();
        $amo_pipelines = $this->amo->getPipelines();
        $pipelines_params = null;
        foreach ($amo_pipelines as $pipeline) {
            $last_sort = null;
            foreach ($pipeline["_embedded"]["statuses"] as $status) {
                $find_params = [
                    "status_id" => $status["id"],
                    "pipeline_id" => $status["pipeline_id"]
                ];
                $pipelines_stat = [
                    "name" => $status["name"],
                    "is_sync" => true
                ];
                if (($status["id"] != 142) && ($status["id"] != 143)) {
                    $pipelines_stat["sort"] = $status["sort"] / 10;
                    $last_sort = $status["sort"] / 10;
                } elseif ($status["id"] == 142) {
                    $pipelines_stat["sort"] = $last_sort + 1;
                } elseif ($status["id"] == 143) {
                    $pipelines_stat["sort"] = $last_sort + 2;
                }
                $db_statuses->updateOrInsert($find_params, $pipelines_stat);
            }
            $pipelines_param = [
                "id" => $pipeline["id"],
                "name" => $pipeline["name"],
                "is_sync" => true
            ];
            $pipelines_params[] = $pipelines_param;
        }
        $db_pipelines->upsert($pipelines_params, ['id'], ['name', 'is_sync']);
        print_r("End amocrm pipeline synchronization\n");
    }

    public
    function loadTaskTypes()
    {
        print_r("Start amocrm task types synchronization\n");
        $task_types = $this->amo->accountInfo("users_groups,task_types");
        $task_type_list = null;
        foreach ($task_types["_embedded"]["task_types"] as $task_type) {
            $task_type_list[] = [
                "id" => $task_type["id"],
                "name" => $task_type["name"],
                "is_sync" => true
            ];
        }
        $db_groups = new AmoTaskTypes();
        $db_groups->upsert($task_type_list, ['id'], ['name', 'is_sync']);
        print_r("End amocrm task types synchronization\n");
    }

    public
    function loadUserGroups()
    {
        print_r("Start amocrm user groups synchronization\n");
        $amo_groups = $this->amo->accountInfo("users_groups");
        $group_list = null;
        foreach ($amo_groups["_embedded"]["users_groups"] as $group) {
            $group_list[] = [
                "id" => $group["id"],
                "name" => $group["name"],
                "is_sync" => true
            ];
        }
        if (!empty($group_list)) {
            $db_groups = new AmoGroups();
            $db_groups->upsert($group_list, ['id'], ['name', 'is_sync']);
        }
        print_r("End amocrm user groups synchronization\n");
    }

    public
    function loadAmoUsers()
    {
        print_r("Start amocrm users synchronization\n");
        $amo_users = $this->amo->getUsers();
        $user_list = null;
        foreach ($amo_users as $user) {
            $tmp_user = [
                "id" => $user["id"],
                "name" => $user["name"],
                "email" => $user["email"],
                "group_id" => null,
                "group_name" => null,
                "is_sync" => true,
                "is_active" => $user["rights"]["is_active"] ?? false
            ];
            if (!empty($user["_embedded"]["groups"])) {
                $tmp_user["group_id"] = $user["_embedded"]["groups"][0]["id"];
                $tmp_user["group_name"] = $user["_embedded"]["groups"][0]["name"];
            } else {
                $tmp_user["group_id"] = 0;
                $tmp_user["group_name"] = "Отдел продаж";
            }
            $user_list[] = $tmp_user;
        }
        $db_users = new AmoUsers();
        $db_users->upsert($user_list, ['id'], ['name', 'email', 'group_id', 'group_name', 'is_sync', 'is_active']);
        print_r("End amocrm users synchronization\n");
    }

    public function getCustomFieldValue($cf)
    {
        switch ($cf["field_type"]) {
            case "date":
                return date("Y-m-d", $cf["values"][0]["value"]);
            case "date_time":
                return date("Y-m-d H:i:s", $cf["values"][0]["value"]);
            case "multiselect":
            case "multitext":
                $cf_val = null;
                foreach ($cf["values"] as $val) {
                    if (empty($cf_val)) {
                        $cf_val = ($cf["field_code"] == "PHONE") ? preg_replace('/[^0-9]/', '', $val["value"]) : $val["value"];
                    } else {
                        $cf_val .= ", " . ($cf["field_code"] == "PHONE") ? preg_replace('/[^0-9]/', '', $val["value"]) : $val["value"];
                    }
                }
                return $cf_val;
            default:
                if (!empty($cf["values"][0]["value"])) {
                    return $cf["values"][0]["value"];
                } else {
                    return null;
                }
        }
    }
	 public
    function deleteAmoLogs()
    {	
        print_r("Start delete amocrm logs\n");
		//AmoLogs::whereNotNull('id')->delete();
        $db_logs = new AmoLogs("amo_lib");		
        $db_logs->whereNotNull('id')->delete();
        print_r("End delete amocrm logs\n");
    }
}
