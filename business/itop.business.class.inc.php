<?php
// Copyright (C) 2010 Combodo SARL
//
//   This program is free software; you can redistribute it and/or modify
//   it under the terms of the GNU General Public License as published by
//   the Free Software Foundation; version 3 of the License.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of the GNU General Public License
//   along with this program; if not, write to the Free Software
//   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

/**
 * Persistent classes for a CMDB
 *
 * @author      Erwan Taloc <erwan.taloc@combodo.com>
 * @author      Romain Quetiez <romain.quetiez@combodo.com>
 * @author      Denis Flaven <denis.flaven@combodo.com>
 * @license     http://www.opensource.org/licenses/gpl-3.0.html LGPL
 */

require_once('../application/cmdbabstract.class.inc.php');
require_once('../application/template.class.inc.php');


/**
 * Possible values for the statuses of objects
 */
define('STANDARD_STATUSES', 'production,implementation,obsolete');

/**
 * Relation graphs
 */
MetaModel::RegisterRelation("impacts", array("description"=>"objects being functionaly impacted", "verb_down"=>"impacts", "verb_up"=>"is impacted by"));

////////////////////////////////////////////////////////////////////////////////////
/**
* An organization that owns some objects
*
* An organization "owns" some persons (its employees) but also some other objects
* (its assets) like buildings, computers, furniture...
* the services that they provides, the contracts/OLA they have signed as customer
* 
* Organization ownership might be used to manage the R/W access to the object
*/
////////////////////////////////////////////////////////////////////////////////////
class bizOrganization extends cmdbAbstractObject
{
	public static function Init()
	{
		global $oAllowedStatuses;
		$aParams = array
		(
			"category" => "bizmodel,searchable",
			"key_type" => "autoincrement",
			"key_label" => "id",
			"name_attcode" => "name",
			"state_attcode" => "",
			"reconc_keys" => array("name"),
			"db_table" => "organizations",
			"db_key_field" => "id",
			"db_finalclass_field" => "",
			"display_template" => "../business/templates/default.html",
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_AddAttribute(new AttributeString("name", array("allowed_values"=>null, "sql"=>"name", "default_value"=>"", "is_null_allowed"=>false, "depends_on"=>array() )));
		MetaModel::Init_AddAttribute(new AttributeString("code", array("allowed_values"=>null, "sql"=>"code", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array() )));
		MetaModel::Init_AddAttribute(new AttributeEnum("status", array("allowed_values"=>new ValueSetEnum(STANDARD_STATUSES), "sql"=>"status", "default_value"=>"implementation", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalKey("parent_id", array("targetclass"=>"bizOrganization", "allowed_values"=>null, "sql"=>"parent_id", "is_null_allowed"=>true, "on_target_delete"=>DEL_MANUAL, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalField("parent_name", array("allowed_values"=>null, "extkey_attcode"=> 'parent_id', "target_attcode"=>"name")));

		// Display lists
		MetaModel::Init_SetZListItems('details', array('name', 'code', 'status', 'parent_id')); // Attributes to be displayed for the complete details
		MetaModel::Init_SetZListItems('list', array('name', 'status', 'parent_id')); // Attributes to be displayed for a list
		// Search criteria
		MetaModel::Init_SetZListItems('standard_search', array('name', 'code', 'status')); // Criteria of the std search form
		MetaModel::Init_SetZListItems('advanced_search', array('name', 'code', 'status')); // Criteria of the advanced search form
	}
	
	public function Generate(cmdbDataGenerator $oGenerator)
	{
		//$this->SetKey($oGenerator->GetOrganizationCode());
		$this->Set('name', $oGenerator->GetOrganizationName());
		$this->Set('code', $oGenerator->GetOrganizationCode());
		$this->Set('status', 'implementation');
		$this->Set('parent_id', 1);

	}
}

////////////////////////////////////////////////////////////////////////////////////
/**
* Class of objects owned by some organization
*
* This is the root class of all the objects that can be "owned" by an organization
* 
* A Real Object
*   can be supported by Contacts, having a specific role (same contact with multiple roles?)
*   can be documented by Documents
*/
////////////////////////////////////////////////////////////////////////////////////
class logRealObject extends cmdbAbstractObject
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "bizmodel,searchable",
			"key_type" => "autoincrement",
			"key_label" => "id",
			"name_attcode" => "name",
			"state_attcode" => "",
			"reconc_keys" => array("name"),
			"db_table" => "objects",
			"db_key_field" => "id",
			"db_finalclass_field" => "obj_class",
			"display_template" => "../business/templates/default.html",
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_AddAttribute(new AttributeString("name", array("allowed_values"=>null, "sql"=>"name", "default_value"=>"", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeEnum("status", array("allowed_values"=>new ValueSetEnum('production,implementation,obsolete,off,left company,available'), "sql"=>"status", "default_value"=>"implementation", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalKey("org_id", array("targetclass"=>"bizOrganization", "allowed_values"=>null, "sql"=>"org_id", "is_null_allowed"=>false, "on_target_delete"=>DEL_MANUAL, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalField("org_name", array("allowed_values"=>null, "extkey_attcode"=> 'org_id', "target_attcode"=>"name")));

		// Display lists
		MetaModel::Init_SetZListItems('details', array('name', 'status', 'org_id')); // Attributes to be displayed for the complete details
		MetaModel::Init_SetZListItems('list', array('finalclass', 'name', 'status', 'org_id')); // Attributes to be displayed for a list
		// Search criteria
		MetaModel::Init_SetZListItems('standard_search', array('name', 'status')); // Criteria of the std search form
		MetaModel::Init_SetZListItems('advanced_search', array('name', 'status', 'org_id')); // Criteria of the advanced search form
	}
	
	public function Generate(cmdbDataGenerator $oGenerator)
	{
		$this->Set('org_id', $oGenerator->GetOrganizationId());
		$this->Set('name', "<overload in derived class>");
		$this->Set('status', $oGenerator->GenerateString("enum(implementation,production)"));
	}
}

////////////////////////////////////////////////////////////////////////////////////
/**
* Any kind of thing that can be contacted (person, team, hotline...)
* A contact can:
*   be linked to any Real Object with a role
*   be part of a GroupContact
*/
////////////////////////////////////////////////////////////////////////////////////
class bizContact extends logRealObject
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "bizmodel,searchable",
			"key_type" => "",
			"key_label" => "id",
			"name_attcode" => "name",
			"state_attcode" => "",
			"reconc_keys" => array("org_id", "name"), // inherited attributes
			"db_table" => "contacts",
			"db_key_field" => "id",
			"db_finalclass_field" => "",
			"display_template" => "../business/templates/default.html",
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_InheritAttributes();
		MetaModel::Init_AddAttribute(new AttributeEnum("status", array("allowed_values"=>new ValueSetEnum('off,left company,available'), "sql"=>"status", "default_value"=>"available", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalField("org_name", array("allowed_values"=>null, "extkey_attcode"=> 'org_id', "target_attcode"=>"name")));
		MetaModel::Init_AddAttribute(new AttributeEmailAddress("email", array("allowed_values"=>null, "sql"=>"email", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("phone", array("allowed_values"=>null, "sql"=>"telephone", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalKey("location_id", array("targetclass"=>"bizLocation", "allowed_values"=>new ValueSetObjects('SELECT bizLocation AS p WHERE p.org_id = :this->org_id'), "sql"=>"location_id", "is_null_allowed"=>true, "on_target_delete"=>DEL_MANUAL, "depends_on"=>array("org_id"))));
		MetaModel::Init_AddAttribute(new AttributeExternalField("location_name", array("allowed_values"=>null, "extkey_attcode"=> 'location_id', "target_attcode"=>"name")));

		// Display lists
		MetaModel::Init_SetZListItems('details', array('name', 'status', 'org_id', 'email', 'location_id', 'phone')); // Attributes to be displayed for the complete details
		MetaModel::Init_SetZListItems('list', array('finalclass', 'name', 'status', 'org_id', 'email', 'location_id', 'phone')); // Attributes to be displayed for a list
		// Search criteria
		MetaModel::Init_SetZListItems('standard_search', array('name', 'status', 'email', 'location_id', 'phone')); // Criteria of the std search form
		MetaModel::Init_SetZListItems('advanced_search', array('name', 'status', 'org_id')); // Criteria of the advanced search form
	}
	
	public function Generate(cmdbDataGenerator $oGenerator)
	{
		$this->Set('org_id', $oGenerator->GetOrganizationId());
		$this->Set('name', "<overload in derived classes>");
		$this->Set('email', "<overload in derived classes>");
		$this->Set('phone', $oGenerator->GenerateString("enum(+1,+33,+44,+49,+421)| |number(100-999)| |number(000-999)"));
		$this->Set('location_id', $oGenerator->GenerateKey("bizLocation", array('org_id' =>$oGenerator->GetOrganizationId() )));
	}
}

////////////////////////////////////////////////////////////////////////////////////
/**
* Physical person only  
*/
////////////////////////////////////////////////////////////////////////////////////
class bizPerson extends bizContact
{
	public static function Init()
	{
    $aParams = array
		(
			"category" => "bizmodel,searchable",
			"key_type" => "",
			"key_label" => "id",
			"name_attcode" => "name",
			"state_attcode" => "",
			"reconc_keys" => array("org_id", "first_name", "name"),  // comment en définir plusieurs
			// "reconc_keys" => array("org_id", "employee_number"), 
			"db_table" => "persons",   // Can it use the same physical DB table as any contact ?
			"db_key_field" => "id",
			"db_finalclass_field" => "",
			"display_template" => "../business/templates/person.html",
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_InheritAttributes();
		MetaModel::Init_AddAttribute(new AttributeString("first_name", array("allowed_values"=>null, "sql"=>"first_name", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("employee_number", array("allowed_values"=>null, "sql"=>"employee_number", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));

		// Display lists
		MetaModel::Init_SetZListItems('details', array('first_name', 'name', 'status', 'org_id', 'email', 'location_id', 'phone', 'employee_number')); // Attributes to be displayed for the complete details
		MetaModel::Init_SetZListItems('list', array('first_name', 'name', 'status', 'org_id', 'email', 'location_id', 'phone')); // Attributes to be displayed for a list
		// Search criteria
		MetaModel::Init_SetZListItems('standard_search', array('first_name', 'name', 'status', 'email', 'location_id', 'phone', 'employee_number')); // Criteria of the std search form
		MetaModel::Init_SetZListItems('advanced_search', array('first_name', 'name', 'status', 'email', 'location_id', 'phone', 'employee_number')); // Criteria of the advanced search form
	}

	public function Generate(cmdbDataGenerator $oGenerator)
	{
		parent::Generate($oGenerator);
		$this->Set('name', $oGenerator->GenerateLastName());
		$this->Set('first_name', $oGenerator->GenerateFirstName());
		$this->Set('email', $oGenerator->GenerateEmail($this->Get('first_name'), $this->Get('name')));
		$this->Set('phone', $oGenerator->GenerateString("enum(+1,+33,+44,+49,+421)| |number(100-999)| |number(000-999)"));
	}
}

////////////////////////////////////////////////////////////////////////////////////
/**
* A team is basically a contact which is also a group of contacts
* (and thus a team can contain other teams)
*/
////////////////////////////////////////////////////////////////////////////////////
class bizTeam extends bizContact
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "bizmodel,searchable",
			"key_type" => "",
			"key_label" => "id",
			"name_attcode" => "name",
			"state_attcode" => "",
			"reconc_keys" => array("org_id", "name"), // inherited attributes
			"db_table" => "teams",
			"db_key_field" => "id",
			"db_finalclass_field" => "",
			"display_template" => "../business/templates/team.html",
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_InheritAttributes();

		// Display lists
		MetaModel::Init_SetZListItems('details', array('name', 'status', 'org_id', 'email', 'location_id', 'phone')); // Attributes to be displayed for the complete details
		MetaModel::Init_SetZListItems('list', array('name', 'status', 'org_id', 'email', 'location_id', 'phone')); // Attributes to be displayed for a list
		// Search criteria
		MetaModel::Init_SetZListItems('standard_search', array('name', 'status', 'email', 'location_id', 'phone')); // Criteria of the std search form
		MetaModel::Init_SetZListItems('advanced_search', array('name', 'status', 'org_id')); // Criteria of the advanced search form
	}
}
////////////////////////////////////////////////////////////////////////////////////
/**
* n-n link between any Object and a contact
*/
////////////////////////////////////////////////////////////////////////////////////
class lnkContactTeam extends cmdbAbstractObject
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "bizmodel,searchable",
			"key_type" => "autoincrement",
			"key_label" => "link_id",
			"name_attcode" => "role",
			"state_attcode" => "",
			"reconc_keys" => array("contact_id", "team_name"),
			"db_table" => "teams_links",
			"db_key_field" => "link_id",
			"db_finalclass_field" => "",
			"display_template" => "../business/templates/default.html",
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_AddAttribute(new AttributeExternalKey("contact_id", array("targetclass"=>"bizPerson", "allowed_values"=>null, "sql"=>"contact_id", "is_null_allowed"=>false, "on_target_delete"=>DEL_AUTO, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalField("contact_name", array("allowed_values"=>null, "extkey_attcode"=> 'contact_id', "target_attcode"=>"name")));
		MetaModel::Init_AddAttribute(new AttributeExternalField("contact_phone", array("allowed_values"=>null, "extkey_attcode"=> 'contact_id', "target_attcode"=>"phone")));
		MetaModel::Init_AddAttribute(new AttributeExternalField("contact_email", array("allowed_values"=>null, "extkey_attcode"=> 'contact_id', "target_attcode"=>"email")));
		MetaModel::Init_AddAttribute(new AttributeExternalKey("team_id", array("targetclass"=>"bizTeam", "allowed_values"=>null, "sql"=>"team_id", "is_null_allowed"=>false, "on_target_delete"=>DEL_AUTO, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalField("team_name", array("allowed_values"=>null, "extkey_attcode"=> 'team_id', "target_attcode"=>"name")));
		MetaModel::Init_AddAttribute(new AttributeString("role", array("allowed_values"=>null, "sql"=>"role", "default_value"=>"", "is_null_allowed"=>false, "depends_on"=>array())));

		// Display lists
		MetaModel::Init_SetZListItems('details', array('contact_id', 'contact_phone', 'contact_email', 'team_id', 'role')); // Attributes to be displayed for the complete details
		MetaModel::Init_SetZListItems('list', array('contact_id', 'contact_phone', 'contact_email', 'team_id', 'role')); // Attributes to be displayed for a list
	}
}



////////////////////////////////////////////////////////////////////////////////////
/**
* An electronic document, with version tracking
*/
////////////////////////////////////////////////////////////////////////////////////
class bizDocument extends logRealObject
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "bizmodel,searchable",
			"key_type" => "",
			"key_label" => "id",
			"name_attcode" => "name",
			"state_attcode" => "",
			"reconc_keys" => array("org_id", "name"), // inherited attributes
			"db_table" => "documents",
			"db_key_field" => "id",
			"db_finalclass_field" => "",
			"display_template" => "../business/templates/document.html",
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_InheritAttributes();
		MetaModel::Init_AddAttribute(new AttributeEnum("status", array("allowed_values"=>new ValueSetEnum('production,implementation,obsolete'), "sql"=>"status", "default_value"=>"implementation", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalField("org_name", array("allowed_values"=>null, "extkey_attcode"=> 'org_id', "target_attcode"=>"name")));
		MetaModel::Init_AddAttribute(new AttributeEnum("type", array("allowed_values"=>new ValueSetEnum("documentation,contract,working instructions,network map,white paper,presentation,training"), "sql"=>"type", "default_value"=>"documentation", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeText("description", array("allowed_values"=>null, "sql"=>"description", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));

		MetaModel::Init_AddAttribute(new AttributeBlob("contents", array("depends_on"=>array())));

		MetaModel::Init_SetZListItems('details', array('name', 'status', 'org_id', 'type', 'description', 'contents')); // Attributes to be displayed for the complete details
		MetaModel::Init_SetZListItems('list', array('name', 'status', 'org_id', 'type', 'contents')); // Attributes to be displayed for a list
		// Search criteria
		MetaModel::Init_SetZListItems('standard_search', array('name', 'status', 'type')); // Criteria of the std search form
		MetaModel::Init_SetZListItems('advanced_search', array('name', 'status', 'type')); // Criteria of the advanced search form

	}

}

////////////////////////////////////////////////////////////////////////////////////
/**
* n-n link between any Object and a Document
*/
////////////////////////////////////////////////////////////////////////////////////
class lnkDocumentRealObject extends cmdbAbstractObject
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "bizmodel,searchable",
			"key_type" => "autoincrement",
			"key_label" => "link_id",
			"name_attcode" => "link_type",
			"state_attcode" => "",
			"reconc_keys" => array("doc_id", "object_name"),
			"db_table" => "documents_links",
			"db_key_field" => "link_id",
			"db_finalclass_field" => "",
			"display_template" => "../business/templates/default.html",
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_AddAttribute(new AttributeExternalKey("doc_id", array("targetclass"=>"bizDocument", "allowed_values"=>null, "sql"=>"doc_id", "is_null_allowed"=>false, "on_target_delete"=>DEL_AUTO, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalField("doc_name", array("allowed_values"=>null, "extkey_attcode"=> 'doc_id', "target_attcode"=>"name")));
		MetaModel::Init_AddAttribute(new AttributeExternalKey("object_id", array("targetclass"=>"logRealObject", "allowed_values"=>null, "sql"=>"object_id", "is_null_allowed"=>false, "on_target_delete"=>DEL_AUTO, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalField("object_name", array("allowed_values"=>null, "extkey_attcode"=> 'object_id', "target_attcode"=>"name")));
		MetaModel::Init_AddAttribute(new AttributeString("link_type", array("allowed_values"=>null, "sql"=>"link_type", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));

		// Display lists
		MetaModel::Init_SetZListItems('details', array('doc_id', 'object_id', 'link_type')); // Attributes to be displayed for the complete details
		MetaModel::Init_SetZListItems('list', array('doc_id', 'object_id', 'link_type')); // Attributes to be displayed for a list
	}
}




////////////////////////////////////////////////////////////////////////////////////
/**
* n-n link between any Object and a contact
*/
////////////////////////////////////////////////////////////////////////////////////
class lnkContactRealObject extends cmdbAbstractObject
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "bizmodel,searchable",
			"key_type" => "autoincrement",
			"key_label" => "link_id",
			"name_attcode" => "role",
			"state_attcode" => "",
			"reconc_keys" => array("contact_id", "object_name"),
			"db_table" => "contacts_links",
			"db_key_field" => "link_id",
			"db_finalclass_field" => "",
			"display_template" => "../business/templates/default.html",
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_AddAttribute(new AttributeExternalKey("contact_id", array("targetclass"=>"bizContact", "allowed_values"=>null, "sql"=>"contact_id", "is_null_allowed"=>false, "on_target_delete"=>DEL_AUTO, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalField("contact_name", array("allowed_values"=>null, "extkey_attcode"=> 'contact_id', "target_attcode"=>"name")));
		MetaModel::Init_AddAttribute(new AttributeExternalField("contact_phone", array("allowed_values"=>null, "extkey_attcode"=> 'contact_id', "target_attcode"=>"phone")));
		MetaModel::Init_AddAttribute(new AttributeExternalField("contact_email", array("allowed_values"=>null, "extkey_attcode"=> 'contact_id', "target_attcode"=>"email")));
		MetaModel::Init_AddAttribute(new AttributeExternalKey("object_id", array("targetclass"=>"logRealObject", "allowed_values"=>null, "sql"=>"object_id", "is_null_allowed"=>false, "on_target_delete"=>DEL_AUTO, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalField("object_name", array("allowed_values"=>null, "extkey_attcode"=> 'object_id', "target_attcode"=>"name")));
		MetaModel::Init_AddAttribute(new AttributeString("role", array("allowed_values"=>null, "sql"=>"role", "default_value"=>"", "is_null_allowed"=>false, "depends_on"=>array())));

		// Display lists
		MetaModel::Init_SetZListItems('details', array('contact_id', 'contact_phone', 'contact_email', 'object_id', 'role')); // Attributes to be displayed for the complete details
		MetaModel::Init_SetZListItems('list', array('contact_id', 'contact_phone', 'contact_email', 'object_id', 'role')); // Attributes to be displayed for a list
	}
}

////////////////////////////////////////////////////////////////////////////////////
/**
* Any Infrastructure object (bizLocation, bizDevice, bizApplication, bizCircuit, bizInterface)
* An infrastructure object:
*   can be covered by an OLA
*   can support the delivery of a Service
*   can be part of an GroupInfra
*/
////////////////////////////////////////////////////////////////////////////////////
abstract class logInfra extends logRealObject
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "bizmodel,searchable",
			"key_type" => "",
			"key_label" => "id",
			"name_attcode" => "name",
			"state_attcode" => "",
			"reconc_keys" => array("org_id", "name"), // inherited attributes
			"db_table" => "infra",
			"db_key_field" => "id",
			"db_finalclass_field" => "",
			"display_template" => "../business/templates/default.html",
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_InheritAttributes();
		MetaModel::Init_AddAttribute(new AttributeEnum("status", array("allowed_values"=>new ValueSetEnum('production,implementation,obsolete'), "sql"=>"status", "default_value"=>"implementation", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeEnum("severity", array("allowed_values"=>new ValueSetEnum("high,medium,low"), "sql"=>"severity", "default_value"=>"low", "is_null_allowed"=>false, "depends_on"=>array())));
	}
}

////////////////////////////////////////////////////////////////////////////////////
/**
* n-n link between any Object and a contact
*/
////////////////////////////////////////////////////////////////////////////////////
class lnkContactInfra extends cmdbAbstractObject
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "bizmodel,searchable",
			"key_type" => "autoincrement",
			"key_label" => "link_id",
			"name_attcode" => "role",
			"state_attcode" => "",
			"reconc_keys" => array("contact_id", "infra_id"),
			"db_table" => "contacts_infra_links",
			"db_key_field" => "link_id",
			"db_finalclass_field" => "",
			"display_template" => "../business/templates/default.html",
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_AddAttribute(new AttributeExternalKey("contact_id", array("targetclass"=>"bizContact", "allowed_values"=>null, "sql"=>"contact_id", "is_null_allowed"=>false, "on_target_delete"=>DEL_AUTO, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalField("contact_name", array("allowed_values"=>null, "extkey_attcode"=> 'contact_id', "target_attcode"=>"name")));
		MetaModel::Init_AddAttribute(new AttributeExternalField("contact_phone", array("allowed_values"=>null, "extkey_attcode"=> 'contact_id', "target_attcode"=>"phone")));
		MetaModel::Init_AddAttribute(new AttributeExternalField("contact_email", array("allowed_values"=>null, "extkey_attcode"=> 'contact_id', "target_attcode"=>"email")));
		MetaModel::Init_AddAttribute(new AttributeExternalKey("infra_id", array("targetclass"=>"logInfra", "allowed_values"=>null, "sql"=>"infra_id", "is_null_allowed"=>false, "on_target_delete"=>DEL_AUTO, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalField("infra_name", array("allowed_values"=>null, "extkey_attcode"=> 'infra_id', "target_attcode"=>"name")));
		MetaModel::Init_AddAttribute(new AttributeString("role", array("allowed_values"=>null, "sql"=>"role", "default_value"=>"", "is_null_allowed"=>false, "depends_on"=>array())));
		
		// Display lists
		MetaModel::Init_SetZListItems('details', array('contact_id', 'contact_phone', 'contact_email', 'infra_id', 'role')); // Attributes to be displayed for the complete details
		MetaModel::Init_SetZListItems('list', array('contact_id', 'contact_phone', 'contact_email', 'infra_id', 'role')); // Attributes to be displayed for a list
	}
}

////////////////////////////////////////////////////////////////////////////////////
/**
* bizLocation (Region, Country, City, Site, Building, Floor, Room, Rack,...)
* pourrait être mis en plusieurs sous objects, puisqu'une adresse sur region n'a pas trop de sens
* 
*/
////////////////////////////////////////////////////////////////////////////////////
class bizLocation extends logInfra
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "bizmodel,searchable",
			"key_type" => "",
			"key_label" => "id",
			"name_attcode" => "name",
			"state_attcode" => "",
			"reconc_keys" => array("org_id", "name"), // inherited attributes
			"db_table" => "location",
			"db_key_field" => "id",
			"db_finalclass_field" => "",
			"display_template" => "../business/templates/location.html",
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_InheritAttributes();
		MetaModel::Init_AddAttribute(new AttributeText("address", array("allowed_values"=>null, "sql"=>"address", "default_value"=>"", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("country", array("allowed_values"=>null, "sql"=>"country", "default_value"=>"", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalKey("parent_location_id", array("targetclass"=>"bizLocation", "allowed_values"=>null, "sql"=>"parent_location_id", "is_null_allowed"=>true, "on_target_delete"=>DEL_MANUAL, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalField("parent_location_name", array("allowed_values"=>null, "extkey_attcode"=> 'parent_location_id', "target_attcode"=>"name")));

		// Display lists
		MetaModel::Init_SetZListItems('details', array('name', 'status', 'org_id', 'address', 'country', 'parent_location_id')); // Attributes to be displayed for the complete details
		MetaModel::Init_SetZListItems('list', array('name', 'status', 'org_id', 'country')); // Attributes to be displayed for a list
		// Search criteria
		MetaModel::Init_SetZListItems('standard_search', array('name', 'status', 'country', 'parent_location_name')); // Criteria of the std search form
		MetaModel::Init_SetZListItems('advanced_search', array('name', 'status', 'address', 'country', 'parent_location_id', 'org_id')); // Criteria of the advanced search form
	}
	
	public function ComputeValues()
	{ 
  /*
		$this->Set("location_id", $this->GetKey());
		// Houston, I've got an issue, as this field is calculated, I should reload the object... ?
		$this->Set("location_name", "abc (to be finalized)");
  */
	}

	function DisplayDetails(WebPage $oPage)
	{
		parent::DisplayDetails($oPage);
/*
		parent::DisplayDetails($oPage);



		$oSearchFilter = new CMDBSearchFilter('bizServer');
		$oSearchFilter->AddCondition('location_id', $this->GetKey(), '=');
		$oSet = new CMDBObjectSet($oSearchFilter);
		$count = $oSet->Count();
		if ($count > 0)
		{
			$oPage->SetCurrentTab("Servers");
			$oPage->p("$count server(s) at this location:");
			$this->DisplaySet($oPage, $oSet);
		}
		$oSearchFilter = new CMDBSearchFilter('bizNetworkDevice');
		$oSearchFilter->AddCondition('location_id', $this->GetKey(), '=');
		$oSet = new CMDBObjectSet($oSearchFilter);
		$count = $oSet->Count();
		if ($count > 0)
		{
			$oPage->SetCurrentTab("Network Devices");
			$oPage->p("$count Network Device(s) at this location:");
			$this->DisplaySet($oPage, $oSet);
		}
		$oSearchFilter = new CMDBSearchFilter('bizPC');
		$oSearchFilter->AddCondition('location_id', $this->GetKey(), '=');
		$oSet = new CMDBObjectSet($oSearchFilter);
		$count = $oSet->Count();
		if ($count > 0)
		{
			$oPage->SetCurrentTab("PCs");
			$oPage->p("$count PC(s) at this location:");
			$this->DisplaySet($oPage, $oSet);
		}
		$oSearchFilter = new CMDBSearchFilter('bizPerson');
		$oSearchFilter->AddCondition('location_id', $this->GetKey(), '=');
		$oSet = new CMDBObjectSet($oSearchFilter);
		$count = $oSet->Count();
		if ($count > 0)
		{
			$oPage->SetCurrentTab("Contacts");
			$oPage->p("$count person(s) located to this location:");
			$this->DisplaySet($oPage, $oSet);
		}

		$oSearchFilter = new CMDBSearchFilter('lnkDocumentRealObject');
		$oSearchFilter->AddCondition('object_id', $this->GetKey(), '=');
		$oSet = new CMDBObjectSet($oSearchFilter);
		$count = $oSet->Count();
		if ($count > 0)
		{
			$oPage->SetCurrentTab("Details");
			$oPage->p("$count Document(s) linked to this location:");
			$this->DisplaySet($oPage, $oSet);
		}
*/
	
	}


	public function Generate(cmdbDataGenerator $oGenerator)
	{
		parent::Generate($oGenerator);
		$sLastName = $oGenerator->GenerateLastName();
		$sCityName = $oGenerator->GenerateCityName();
		$this->Set('name', $sCityName);
		$this->Set('country', $oGenerator->GenerateCountryName());
		$this->Set('address', $oGenerator->GenerateString("number(1-999)| |enum(rue,rue,rue,place,avenue,av.,route de)| |$sLastName| |number(0000-9999)|0 |$sCityName"));
		$this->Set('parent_location_id', 1);
	}
}

////////////////////////////////////////////////////////////////////////////////////
/**
* Circuit (one end only)
*/
////////////////////////////////////////////////////////////////////////////////////
class bizCircuit extends logInfra
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "bizmodel,searchable",
			"key_type" => "",
			"key_label" => "id",
			"name_attcode" => "name",
			"state_attcode" => "",
			"reconc_keys" => array("org_id", "provider_id", "carrier_ref", "name"), // inherited attributes
			"db_table" => "circuits",
			"db_key_field" => "id",
			"db_finalclass_field" => "",
			"display_template" => "../business/templates/circuit.html",
		);

		MetaModel::Init_Params($aParams);
		MetaModel::Init_InheritAttributes();
		MetaModel::Init_AddAttribute(new AttributeString("speed", array("allowed_values"=>null, "sql"=>"speed", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalKey("location1_id", array("targetclass"=>"bizLocation", "allowed_values"=>new ValueSetObjects('SELECT bizLocation AS p WHERE p.org_id = :this->org_id'), "sql"=>"location1_id", "is_null_allowed"=>false,"on_target_delete"=>DEL_MANUAL, "depends_on"=>array("org_id"))));
		MetaModel::Init_AddAttribute(new AttributeExternalField("location1_name", array("allowed_values"=>null, "extkey_attcode"=> 'location1_id', "target_attcode"=>"name")));
		MetaModel::Init_AddAttribute(new AttributeExternalKey("location2_id", array("targetclass"=>"bizLocation", "allowed_values"=>new ValueSetObjects('SELECT bizLocation AS p WHERE p.org_id = :this->org_id'), "sql"=>"location2_id", "is_null_allowed"=>false, "on_target_delete"=>DEL_MANUAL,"depends_on"=>array("org_id"))));
		MetaModel::Init_AddAttribute(new AttributeExternalField("location2_name", array("allowed_values"=>null, "extkey_attcode"=> 'location2_id', "target_attcode"=>"name")));

		MetaModel::Init_AddAttribute(new AttributeExternalKey("interface1_id", array("targetclass"=>"bizInterface", "allowed_values"=>new ValueSetObjects('SELECT bizInterface AS Intf JOIN bizDevice AS Dev ON Intf.device_id = Dev.id WHERE Intf.org_id = :this->org_id AND Dev.location_id = :this->location1_id'), "sql"=>"interface1_id", "is_null_allowed"=>false,"on_target_delete"=>DEL_MANUAL, "depends_on"=>array("org_id", "location1_id"))));
		MetaModel::Init_AddAttribute(new AttributeExternalField("interface1_name", array("allowed_values"=>null, "extkey_attcode"=> 'interface1_id', "target_attcode"=>"name")));
		MetaModel::Init_AddAttribute(new AttributeExternalField("device1_name", array("allowed_values"=>null, "extkey_attcode"=> 'interface1_id', "target_attcode"=>"device_name")));
	
		MetaModel::Init_AddAttribute(new AttributeExternalKey("interface2_id", array("targetclass"=>"bizInterface", "allowed_values"=>new ValueSetObjects('SELECT bizInterface AS Intf JOIN bizDevice AS Dev ON Intf.device_id = Dev.id WHERE Intf.org_id = :this->org_id AND Dev.location_id = :this->location2_id'), "sql"=>"interface2_id", "is_null_allowed"=>false,"on_target_delete"=>DEL_MANUAL, "depends_on"=>array("org_id", "location2_id"))));
		MetaModel::Init_AddAttribute(new AttributeExternalField("interface2_name", array("allowed_values"=>null, "extkey_attcode"=> 'interface2_id', "target_attcode"=>"name")));
		MetaModel::Init_AddAttribute(new AttributeExternalField("device2_name", array("allowed_values"=>null, "extkey_attcode"=> 'interface2_id', "target_attcode"=>"device_name")));

		MetaModel::Init_AddAttribute(new AttributeExternalKey("provider_id", array("targetclass"=>"bizOrganization", "allowed_values"=>null, "sql"=>"provider_id", "is_null_allowed"=>false,"on_target_delete"=>DEL_MANUAL, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalField("carrier_name", array("allowed_values"=>null, "extkey_attcode"=> 'provider_id', "target_attcode"=>"name")));
		MetaModel::Init_AddAttribute(new AttributeString("carrier_ref", array("allowed_values"=>null, "sql"=>"carrier_ref", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		
		// Display lists
		MetaModel::Init_SetZListItems('details', array('name', 'status', 'org_id', 'speed', 'location1_id','interface1_id','device1_name','location2_id','interface2_id','device2_name','provider_id', 'carrier_ref')); // Attributes to be displayed for the complete details
		MetaModel::Init_SetZListItems('list', array('name', 'status', 'org_id', 'provider_id', 'carrier_ref', 'speed')); // Attributes to be displayed for a list
		// Search criteria
		MetaModel::Init_SetZListItems('standard_search', array('name', 'status', 'location1_id','location2_id','carrier_ref', 'speed', 'provider_id')); // Criteria of the std search form
		MetaModel::Init_SetZListItems('advanced_search', array('name', 'status', 'location1_id','location2_id','carrier_ref', 'speed', 'provider_id')); // Criteria of the advanced search form
	}
	
	public function ComputeValues()
	{
/*
		$oLocatedObject = MetaModel::GetObject("Located Object", $this->Get("located_object_id"));

		$this->Set("location_id", $oLocatedObject->Get("location_id"));
		// Houston, I've got an issue, as this field is calculated, I should reload the object...
		$this->Set("location_name", "abc (to be finalized)");

		$this->Set("device_id", $oLocatedObject->Get("device_id"));
		// Houston, I've got an issue, as this field is calculated, I should reload the object...
		$this->Set("device_name", "abc (to be finalized)");

		$this->Set("interface_id", $oLocatedObject->Get("interface_id"));
		// Houston, I've got an issue, as this field is calculated, I should reload the object...
		$this->Set("interface_name", "abc (to be finalized)");
*/
	}
}

////////////////////////////////////////////////////////////////////////////////////
/**
* Any Device Network Interface 
*/
////////////////////////////////////////////////////////////////////////////////////
class bizInterface extends logInfra
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "bizmodel,searchable",
			"key_type" => "",
			"key_label" => "id",
			"name_attcode" => "name",
			"state_attcode" => "",
			"reconc_keys" => array("org_id", "device_id", "name"),
			"db_table" => "interfaces",
			"db_key_field" => "id",
			"db_finalclass_field" => "",
			"display_template" => "../business/templates/interface.html",
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_InheritAttributes();
		MetaModel::Init_AddAttribute(new AttributeExternalKey("device_id", array("targetclass"=>"bizDevice", "allowed_values"=>null, "sql"=>"device_id", "is_null_allowed"=>false, "on_target_delete"=>DEL_MANUAL, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalField("device_name", array("allowed_values"=>null, "extkey_attcode"=> 'device_id', "target_attcode"=>"name")));
		MetaModel::Init_AddAttribute(new AttributeExternalField("device_location_id", array("allowed_values"=>null, "extkey_attcode"=> 'device_id', "target_attcode"=>"location_id")));
		MetaModel::Init_AddAttribute(new AttributeExternalField("device_location_name", array("allowed_values"=>null, "extkey_attcode"=> 'device_id', "target_attcode"=>"location_name")));

		MetaModel::Init_AddAttribute(new AttributeEnum("logical_type", array("allowed_values"=>new ValueSetEnum("primary,secondary,backup,port,logical"), "sql"=>"logical_type", "default_value"=>"port", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeEnum("physical_type", array("allowed_values"=>new ValueSetEnum("ethernet,framerelay,atm,vlan"), "sql"=>"physical_type", "default_value"=>"ethernet", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("ip_address", array("allowed_values"=>null, "sql"=>"ip_address", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("mask", array("allowed_values"=>null, "sql"=>"mask", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("mac", array("allowed_values"=>null, "sql"=>"mac", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("speed", array("allowed_values"=>null, "sql"=>"speed", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeEnum("duplex", array("allowed_values"=>new ValueSetEnum("half,full,unknown"), "sql"=>"duplex", "default_value"=>"unknown", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalKey("if_connected_id", array("targetclass"=>"bizInterface", "allowed_values"=>null, "sql"=>"ext_if_id", "is_null_allowed"=>true, "on_target_delete"=>DEL_MANUAL, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalField("if_connected_name", array("allowed_values"=>null, "extkey_attcode"=> 'if_connected_id', "target_attcode"=>"name")));
		MetaModel::Init_AddAttribute(new AttributeExternalField("if_connected_device", array("allowed_values"=>null, "extkey_attcode"=> 'if_connected_id', "target_attcode"=>"device_name")));
    
		// Display lists
		MetaModel::Init_SetZListItems('details', array('name', 'status', 'org_id', 'device_id', 'device_location_id','severity','logical_type','physical_type','ip_address','mask','mac','speed','duplex','if_connected_name','if_connected_device')); // Attributes to be displayed for the complete details
		MetaModel::Init_SetZListItems('list', array('name', 'status', 'org_id', 'device_id','severity')); // Attributes to be displayed for a list
		// Search criteria
		MetaModel::Init_SetZListItems('standard_search', array('name', 'status', 'ip_address','mac','device_id')); // Criteria of the std search form
		MetaModel::Init_SetZListItems('advanced_search', array('name', 'status', 'device_id', 'org_id')); // Criteria of the advanced search form
	}

	function DisplayDetails(WebPage $oPage)
	{
		parent::DisplayDetails($oPage);
    /*
		$oSearchFilter = new CMDBSearchFilter('lnkInterfaces');
		$oSearchFilter->AddCondition('interface1_id', $this->GetKey(), '=');
		$oSet = new CMDBObjectSet($oSearchFilter);
		$count = $oSet->Count();
		if ($count > 0)
		{
			$oPage->SetCurrentTab("Connected interfaces");
			$oPage->p("$count interface(s) connected to this device:");
			$this->DisplaySet($oPage, $oSet);
		}
	*/
	}

	public function ComputeValues()
	{
	/*
		// my location is the location of my device
		$oDevice = MetaModel::GetObject("bizDevice", $this->Get("device_id"));
		$this->Set("location_id", $oDevice->Get("location_id"));
		// Houston, I've got an issue, as this field is calculated, I should reload the object...
		$this->Set("location_name", "abc (to be finalized)");

		// my device is given by my Creator

		// my interface is myself
		$this->Set("interface_id", $this->GetKey());
		// Houston, I've got an issue, as this field is calculated, I should reload the object...
		$this->Set("interface_name", "abc (to be finalized)");
	*/
  }
}

////////////////////////////////////////////////////////////////////////////////////
/**
* A subnet
*/
////////////////////////////////////////////////////////////////////////////////////
class bizSubnet extends logInfra
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "bizmodel,searchable",
			"key_type" => "",
			"key_label" => "id",
			"name_attcode" => "name",
			"state_attcode" => "",
			"reconc_keys" => array("org_id", "name"), // inherited attributes
			"db_table" => "subnets",
			"db_key_field" => "id",
			"db_finalclass_field" => "",
			"display_template" => "",
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_InheritAttributes();
		MetaModel::Init_AddAttribute(new AttributeIPAddress("ip", array("allowed_values"=>null, "sql"=>"ip", "default_value"=>"", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeIPAddress("mask", array("allowed_values"=>null, "sql"=>"mask", "default_value"=>"", "is_null_allowed"=>false, "depends_on"=>array())));

		// Display lists
		MetaModel::Init_SetZListItems('details', array('name', 'ip','mask')); // Attributes to be displayed for the complete details
		MetaModel::Init_SetZListItems('list', array('name', 'ip', 'mask')); // Attributes to be displayed for a list
		// Search criteria
		MetaModel::Init_SetZListItems('standard_search', array('name', 'ip','mask')); // Criteria of the std search form
		MetaModel::Init_SetZListItems('advanced_search', array('name', 'ip','mask')); // Criteria of the advanced search form
	}

	function DisplayBareRelations(WebPage $oPage)
	{
		parent::DisplayBareRelations($oPage);

		$oPage->SetCurrentTabContainer('Related Objects');

		$oPage->SetCurrentTab('IP Usage');

		$bit_ip = ip2long($this->Get('ip'));
		$bit_mask = ip2long($this->Get('mask'));

		$iIPMin = $bit_ip & $bit_mask;
		$iIPMax = ($bit_ip | (~$bit_mask)) - 1;

		$sIPMin = long2ip($iIPMin);
		$sIPMax = long2ip($iIPMax);

		$oPage->p("Interfaces having an IP in the range: <em>$sIPMin</em> to <em>$sIPMax</em>");
		
		$oIfSet = new CMDBObjectSet(DBObjectSearch::FromOQL("SELECT bizInterface AS if WHERE INET_ATON(if.ip_address) >= INET_ATON('$sIPMin') AND INET_ATON(if.ip_address) <= INET_ATON('$sIPMax')"));
		self::DisplaySet($oPage, $oIfSet);

		$iCountUsed = $oIfSet->Count();
		$iCountRange = $iIPMax - $iIPMin;
		$iFreeCount =  $iCountRange - $iCountUsed;

		$oPage->SetCurrentTab('Free IPs');
		$oPage->p("Free IPs: $iFreeCount");
		$oPage->p("Here is an extract of 10 free IP addresses");

		$aUsedIPs = $oIfSet->GetColumnAsArray('ip_address', false);
		$iAnIP = $iIPMin;
		$iFound = 0;
		while (($iFound < min($iFreeCount, 10)) && ($iAnIP <= $iIPMax))
		{
			$sAnIP = long2ip($iAnIP);
			if (!in_array($sAnIP, $aUsedIPs))
			{
				$iFound++;
				$oPage->p($sAnIP);
			}
			else
			{
			}
			$iAnIP++;
		}
	}
}

////////////////////////////////////////////////////////////////////////////////////
/**
* Any electronic device
*/
////////////////////////////////////////////////////////////////////////////////////
class bizDevice extends logInfra
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "bizmodel,searchable",
			"key_type" => "",
			"key_label" => "id",
			"name_attcode" => "name",
			"state_attcode" => "",
			"reconc_keys" => array("org_id", "name"), // inherited attributes
			"db_table" => "devices",
			"db_key_field" => "id",
			"db_finalclass_field" => "",
			"display_template" => "../business/templates/default.html",
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_InheritAttributes();
		MetaModel::Init_AddAttribute(new AttributeExternalKey("location_id", array("targetclass"=>"bizLocation", "allowed_values"=>new ValueSetObjects('SELECT bizLocation AS p WHERE p.org_id = :this->org_id'), "sql"=>"location_id", "is_null_allowed"=>false, "on_target_delete"=>DEL_MANUAL, "depends_on"=>array("org_id"))));
		MetaModel::Init_AddAttribute(new AttributeExternalField("location_name", array("allowed_values"=>null, "extkey_attcode"=> 'location_id', "target_attcode"=>"name")));
		MetaModel::Init_AddAttribute(new AttributeExternalField("country", array("allowed_values"=>null, "extkey_attcode"=> 'location_id', "target_attcode"=>"country")));
		MetaModel::Init_AddAttribute(new AttributeString("brand", array("allowed_values"=>null, "sql"=>"brand", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("model", array("allowed_values"=>null, "sql"=>"model", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("serial_number", array("allowed_values"=>null, "sql"=>"serial_number", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeIPAddress("mgmt_ip", array("allowed_values"=>null, "sql"=>"mgmt_ip", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
	}

	public static function GetRelationQueries($sRelCode)
	{
		switch ($sRelCode)
		{
		case "impacts":
			$aRels = array(
			);
			return array_merge($aRels, parent::GetRelationQueries($sRelCode));
		}
	}

	public function ComputeValues()
	{
	/*
		// my location is the location of my device (external field)
		$this->Set("location_id", $this->Get("device_location_id"));
		// Houston, I've got an issue, as this field is calculated, I should reload the object...
		$this->Set("location_name", "abc (to be finalized)");

		// my device is myself
		$this->Set("device_id", $this->GetKey());

		// my interface is "nothing"
		$this->Set("interface_id", null);
	*/
  }
}

////////////////////////////////////////////////////////////////////////////////////
/**
* A personal computer
*/
////////////////////////////////////////////////////////////////////////////////////
class bizPC extends bizDevice
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "bizmodel,searchable",
			"key_type" => "",
			"key_label" => "id",
			"name_attcode" => "name",
			"state_attcode" => "",
			"reconc_keys" => array("org_id", "name"), // inherited attributes
			"db_table" => "pcs",
			"db_key_field" => "id",
			"db_finalclass_field" => "",
			"display_template" => "../business/templates/pc.html",
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_InheritAttributes();
		MetaModel::Init_AddAttribute(new AttributeEnum("type", array("allowed_values"=>new ValueSetEnum("desktop PC,laptop"), "sql"=>"type", "default_value"=>"desktop PC", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("memory_size", array("allowed_values"=>null, "sql"=>"memory_size", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("cpu", array("allowed_values"=>null, "sql"=>"cpu_type", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("hdd_size", array("allowed_values"=>null, "sql"=>"hdd_size", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("os_family", array("allowed_values"=>null, "sql"=>"os_family", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("os_version", array("allowed_values"=>null, "sql"=>"os_version", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("shipment_number", array("allowed_values"=>null, "sql"=>"shipment_number", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("default_gateway", array("allowed_values"=>null, "sql"=>"default_gateway", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		
		// Display lists
		MetaModel::Init_SetZListItems('details', array('name', 'status','severity', 'org_id', 'location_id', 'brand', 'model','os_family','os_version','mgmt_ip','default_gateway','shipment_number','serial_number', 'type', 'cpu', 'memory_size', 'hdd_size')); // Attributes to be displayed for the complete details
		MetaModel::Init_SetZListItems('list', array('name', 'status', 'severity', 'org_id', 'location_id', 'brand', 'model', 'type')); // Attributes to be displayed for a list
		// Search criteria
		MetaModel::Init_SetZListItems('standard_search', array('name', 'status', 'severity','type', 'brand', 'model','os_family','mgmt_ip')); // Criteria of the std search form
		MetaModel::Init_SetZListItems('advanced_search', array('name', 'status', 'type', 'brand', 'model', 'cpu', 'memory_size', 'hdd_size')); // Criteria of the advanced search form
	}

	function DisplayDetails(WebPage $oPage)
	{
		parent::DisplayDetails($oPage);
		/*
		parent::DisplayDetails($oPage);
		$oSearchFilter = new CMDBSearchFilter('lnkContactRealObject');
		$oSearchFilter->AddCondition('object_id', $this->GetKey(), '=');
		$oSet = new CMDBObjectSet($oSearchFilter);
		$count = $oSet->Count();
		if ($count > 0)
		{
			$oPage->SetCurrentTab("Contacts");
			$oPage->p("$count contact(s) linked to this PC:");
			$this->DisplaySet($oPage, $oSet);
		}
		$oSearchFilter = new CMDBSearchFilter('bizInterface');
		$oSearchFilter->AddCondition('device_id', $this->GetKey(), '=');
		$oSet = new CMDBObjectSet($oSearchFilter);
		$count = $oSet->Count();
		if ($count > 0)
		{
			$oPage->SetCurrentTab("Interfaces");
			$oPage->p("$count interface(s) for this device:");
			$this->DisplaySet($oPage, $oSet);
		}
		*/
	}

	
	public function Generate(cmdbDataGenerator $oGenerator)
	{
		$this->Set('org_id', $oGenerator->GetOrganizationId());
		$this->Set('location_id', $oGenerator->GenerateKey("bizLocation", array('org_id' =>$oGenerator->GetOrganizationId() )));
		$this->Set('name', $oGenerator->GenerateString("enum(pc,pc,pc,pc,pc,win,redhat,linux,srv,workstation)|number(000-999)|.|domain()"));
		$this->Set('brand', $oGenerator->GenerateString("enum(Hewlett-Packard,Dell,Compaq,Siemens,Packard Bell,IBM,Gateway,Medion,Sony)"));
		$this->Set('model', $oGenerator->GenerateString("enum(Vectra,Deskpro,Dimension,Optiplex,Latitude,Precision,Vaio)"));
		$this->Set('serial_number', $oGenerator->GenerateString("enum(FR,US,TW,CH)|number(000000-999999)"));
		$this->Set('memory_size', $oGenerator->GenerateString("enum(128,256,384,512,768,1024,1536,2048)"));
		$this->Set('cpu', $oGenerator->GenerateString("enum(Pentium III,Pentium 4, Pentium M,Core Duo,Core 2 Duo,Celeron,Opteron,Thurion,Athlon)"));
		$this->Set('hdd_size', $oGenerator->GenerateString("enum(40,60,80,120,200,300)"));
	}
}

////////////////////////////////////////////////////////////////////////////////////
/**
* A server
*/
////////////////////////////////////////////////////////////////////////////////////
class bizServer extends bizDevice
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "bizmodel,searchable",
			"key_type" => "",
			"key_label" => "id",
			"name_attcode" => "name",
			//"state_attcode" => "status",
			"state_attcode" => "",
			"reconc_keys" => array("org_id", "name"), // inherited attributes
			"db_table" => "servers",
			"db_key_field" => "id",
			"db_finalclass_field" => "",
			"display_template" => "../business/templates/server.html",
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_InheritAttributes();
//		MetaModel::Init_AddAttribute(new AttributeEnum("status", array("allowed_values"=>new ValueSetEnum("InStore,Shipped,Plugged,ProductionCandidate,InProduction,BeingDeconfigured,Obsolete"), "sql"=>"status", "default_value"=>"InStore", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("memory_size", array("allowed_values"=>null, "sql"=>"memory_size", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("cpu", array("allowed_values"=>null, "sql"=>"cpu_type", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("number_of_cpus", array("allowed_values"=>null, "sql"=>"number_of_cpus", "default_value"=>"1", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("hdd_size", array("allowed_values"=>null, "sql"=>"hdd_size", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("hdd_free_size", array("allowed_values"=>null, "sql"=>"hdd_free_size", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("os_family", array("allowed_values"=>null, "sql"=>"os_family", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("os_version", array("allowed_values"=>null, "sql"=>"os_version", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("shipment_number", array("allowed_values"=>null, "sql"=>"shipment_number", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("default_gateway", array("allowed_values"=>null, "sql"=>"default_gateway", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
 
/*
		// Life cycle
		MetaModel::Init_DefineState("InStore", array("attribute_inherit"=>null,
												 "attribute_list"=>array()));
		MetaModel::Init_DefineState("Shipped", array("attribute_inherit"=>null,
												"attribute_list"=>array("location_id"=>OPT_ATT_MANDATORY,"serial_number"=>OPT_ATT_MANDATORY,"shipment_number"=>OPT_ATT_MANDATORY)));
		MetaModel::Init_DefineState("Plugged", array("attribute_inherit"=>null,
													"attribute_list"=>array("location_id"=>OPT_ATT_MANDATORY,"mgmt_ip"=>OPT_ATT_MANDATORY,"name"=>OPT_ATT_MANDATORY)));
		MetaModel::Init_DefineState("ProductionCandidate", array("attribute_inherit"=>null,
												"attribute_list"=>array()));
		MetaModel::Init_DefineState("InProduction", array("attribute_inherit"=>null,
												"attribute_list"=>array()));
		MetaModel::Init_DefineState("BeingDeconfigured", array("attribute_inherit"=>null,
												"attribute_list"=>array()));
		MetaModel::Init_DefineState("Obsolete", array("attribute_inherit"=>null,
												"attribute_list"=>array()));

		MetaModel::Init_DefineStimulus(new StimulusUserAction("ev_store", array())); // "Store this server / This server is moved to storage"
		MetaModel::Init_DefineStimulus(new StimulusUserAction("ev_ship", array())); // "Ship this server / This server is shipped to futur location"
		MetaModel::Init_DefineStimulus(new StimulusUserAction("ev_plug", array())); // "Plug this server / The server is pluuged on the network"
		MetaModel::Init_DefineStimulus(new StimulusUserAction("ev_configuration_finished", array())); // "Configuration finished / The device is ready to move to production evaluation"
		MetaModel::Init_DefineStimulus(new StimulusUserAction("ev_val_failed", array())); // "Review configuration / The configuration for this server is not completed"
		MetaModel::Init_DefineStimulus(new StimulusUserAction("ev_mtp", array())); // "Move to Production / The server is moved to production"
		MetaModel::Init_DefineStimulus(new StimulusUserAction("ev_decommission", array())); // "Decommission / The server is being decommissioned"
		MetaModel::Init_DefineStimulus(new StimulusUserAction("ev_obsolete", array())); // "Obsolete / The server is no more used"
		MetaModel::Init_DefineStimulus(new StimulusUserAction("ev_recycle", array())); // "Recycle this server / The server is move back to deconfiguration"

		MetaModel::Init_DefineTransition("InStore", "ev_ship", array("target_state"=>"Shipped", "actions"=>array(), "user_restriction"=>null));
		MetaModel::Init_DefineTransition("InStore", "ev_plug", array("target_state"=>"Plugged", "actions"=>array(), "user_restriction"=>null));
		MetaModel::Init_DefineTransition("Shipped", "ev_store", array("target_state"=>"InStore", "actions"=>array(), "user_restriction"=>null));
		MetaModel::Init_DefineTransition("Shipped", "ev_plug", array("target_state"=>"Plugged", "actions"=>array(), "user_restriction"=>null));
		MetaModel::Init_DefineTransition("Plugged", "ev_ship", array("target_state"=>"Shipped", "actions"=>array(), "user_restriction"=>null));
		MetaModel::Init_DefineTransition("Plugged", "ev_store", array("target_state"=>"InStore", "actions"=>array(), "user_restriction"=>null));
		MetaModel::Init_DefineTransition("Plugged", "ev_configuration_finished", array("target_state"=>"ProductionCandidate", "actions"=>array(), "user_restriction"=>null));
		MetaModel::Init_DefineTransition("ProductionCandidate", "ev_val_failed", array("target_state"=>"Plugged", "actions"=>array(), "user_restriction"=>null));
		MetaModel::Init_DefineTransition("ProductionCandidate", "ev_mtp", array("target_state"=>"InProduction", "actions"=>array(), "user_restriction"=>null));
		MetaModel::Init_DefineTransition("InProduction", "ev_obsolete", array("target_state"=>"Obsolete", "actions"=>array(), "user_restriction"=>null));
		MetaModel::Init_DefineTransition("InProduction", "ev_decommission", array("target_state"=>"BeingDeconfigured", "actions"=>array(), "user_restriction"=>null));
		MetaModel::Init_DefineTransition("BeingDeconfigured", "ev_ship", array("target_state"=>"Shipped", "actions"=>array(), "user_restriction"=>null));
		MetaModel::Init_DefineTransition("BeingDeconfigured", "ev_plug", array("target_state"=>"Plugged", "actions"=>array(), "user_restriction"=>null));
		MetaModel::Init_DefineTransition("BeingDeconfigured", "ev_store", array("target_state"=>"InStore", "actions"=>array(), "user_restriction"=>null));
		MetaModel::Init_DefineTransition("BeingDeconfigured", "ev_obsolete", array("target_state"=>"Obsolete", "actions"=>array(), "user_restriction"=>null));
		MetaModel::Init_DefineTransition("Obsolete", "ev_recycle", array("target_state"=>"BeingDeconfigured", "actions"=>array(), "user_restriction"=>null));
*/
	
		// Display lists

  		MetaModel::Init_SetZListItems('details', array('name', 'status', 'mgmt_ip','default_gateway', 'severity','org_id', 'location_id', 'brand', 'model', 'os_family', 'os_version','serial_number','shipment_number', 'cpu', 'number_of_cpus', 'memory_size', 'hdd_size', 'hdd_free_size')); // Attributes to be displayed for the complete details
		MetaModel::Init_SetZListItems('list', array('name', 'status','severity', 'org_id', 'location_id', 'brand', 'model', 'os_family', 'os_version')); // Attributes to be displayed for a list
		// Search criteria
		MetaModel::Init_SetZListItems('standard_search', array('name', 'status','severity', 'brand', 'model', 'os_family', 'location_id')); // Criteria of the std search form
		MetaModel::Init_SetZListItems('advanced_search', array('name', 'status','brand', 'model', 'os_family', 'os_version', 'location_id', 'cpu', 'number_of_cpus', 'memory_size', 'hdd_size', 'hdd_free_size')); // Criteria of the advanced search form
	}
	
	function DisplayDetails(WebPage $oPage)
	{
		parent::DisplayDetails($oPage);
		/*
		parent::DisplayDetails($oPage);
		$oSearchFilter = new CMDBSearchFilter('lnkContactRealObject');
		$oSearchFilter->AddCondition('object_id', $this->GetKey(), '=');
		$oSet = new CMDBObjectSet($oSearchFilter);
		$count = $oSet->Count();
		if ($count > 0)
		{
			$oPage->SetCurrentTab("Contacts");
			$oPage->p("$count contact(s) for this server:");
			$this->DisplaySet($oPage, $oSet);
		}
		$oSearchFilter = new CMDBSearchFilter('bizInterface');
		$oSearchFilter->AddCondition('device_id', $this->GetKey(), '=');
		$oSet = new CMDBObjectSet($oSearchFilter);
		$count = $oSet->Count();
		if ($count > 0)
		{
			$oPage->SetCurrentTab("Interfaces");
			$oPage->p("$count interface(s) for this server:");
			$this->DisplaySet($oPage, $oSet);
		}
		$oSearchFilter = new CMDBSearchFilter('Application');
		$oSearchFilter->AddCondition('infra_id', $this->GetKey(), '=');
		$oSet = new CMDBObjectSet($oSearchFilter);
		$count = $oSet->Count();
		if ($count > 0)
		{
			$oPage->SetCurrentTab("Installed applications");
			$oPage->p("$count application(s) installed on this server:");
			$this->DisplaySet($oPage, $oSet);
		}
		$oSearchFilter = new CMDBSearchFilter('bizPatch');
		$oSearchFilter->AddCondition('infra_id', $this->GetKey(), '=');
		$oSet = new CMDBObjectSet($oSearchFilter);
		$count = $oSet->Count();
		if ($count > 0)
		{
			$oPage->SetCurrentTab("Installed patches");
			$oPage->p("$count patch(s) installed on this server:");
			$this->DisplaySet($oPage, $oSet);
		}
		*/


	}


	public function Generate(cmdbDataGenerator $oGenerator)
	{
		$this->Set('org_id', $oGenerator->GetOrganizationId());
		$this->Set('location_id', $oGenerator->GenerateKey("bizLocation", array('org_id' =>$oGenerator->GetOrganizationId() )));
		$this->Set('name', $oGenerator->GenerateString("enum(pc,pc,pc,pc,pc,win,redhat,linux,srv,workstation)|number(000-999)|.|domain()"));
		$this->Set('brand', $oGenerator->GenerateString("enum(Hewlett-Packard,Dell,Compaq,Siemens,Packard Bell,IBM,Gateway,Medion,Sony)"));
		$this->Set('model', $oGenerator->GenerateString("enum(Vectra,Deskpro,Dimension,Optiplex,Latitude,Precision,Vaio)"));
		$this->Set('serial_number', $oGenerator->GenerateString("enum(FR,US,TW,CH)|number(000000-999999)"));
		$this->Set('memory_size', $oGenerator->GenerateString("enum(512,1024,2048,4096,2048,4096,8192,8192,8192,16384,32768)"));
		$this->Set('cpu', $oGenerator->GenerateString("enum(Pentium III,Pentium 4,Pentium M,Core Duo,Core 2 Duo,Celeron,Opteron,Thurion,Athlon)"));
		$this->Set('number_of_cpu', $oGenerator->GenerateString("enum(1,1,2,2,2,2,2,4,4,8)"));
		$this->Set('hdd_size', $oGenerator->GenerateString("enum(500,1024,500,1024,500,1024,2048)"));
		$this->Set('hdd_free_size', $this->Get('hdd_size')*$oGenerator->GenerateString("number(20-80)"));
		$this->Set('os_family', $oGenerator->GenerateString("enum(Windows,Windows,Windows,Linux,Windows,Linux,Windows,Linux,Linux,HP-UX,Solaris,AIX)"));
		$this->Set('os_version', $oGenerator->GenerateString("enum(XP,XP,XP,RH EL 4,RH EL 5,SuSE 10.3,SuSE 10.4,11.11,11.11i)"));
	}
}

////////////////////////////////////////////////////////////////////////////////////
/**
* A network device
*/
////////////////////////////////////////////////////////////////////////////////////
class bizNetworkDevice extends bizDevice
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "bizmodel,searchable",
			"key_type" => "",
			"key_label" => "id",
			"name_attcode" => "name",
			"state_attcode" => "",
			"reconc_keys" => array("org_id", "name"), // inherited attributes
			"db_table" => "network_devices",
			"db_key_field" => "id",
			"db_finalclass_field" => "",
			"display_template" => "../business/templates/network.device.html",
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_InheritAttributes();
		MetaModel::Init_AddAttribute(new AttributeEnum("type", array("allowed_values"=>new ValueSetEnum("switch,router,firewall,load balancer,hub,WAN accelerator"), "sql"=>"type", "default_value"=>"switch", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("default_gateway", array("allowed_values"=>null, "sql"=>"default_gateway", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("ios_version", array("allowed_values"=>null, "sql"=>"ios_version", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("memory", array("allowed_values"=>null, "sql"=>"memory", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));

		MetaModel::Init_AddAttribute(new AttributeString("snmp_read", array("allowed_values"=>null, "sql"=>"snmp_read", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("snmp_write", array("allowed_values"=>null, "sql"=>"snmp_write", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));

		// Display lists
		MetaModel::Init_SetZListItems('details', array('name', 'status','severity','org_id', 'location_id', 'brand','model','type','mgmt_ip','default_gateway','serial_number','ios_version','memory','snmp_read','snmp_write')); // Attributes to be displayed for the complete details
		MetaModel::Init_SetZListItems('list', array('name', 'status','org_id','brand','model','type','mgmt_ip')); // Attributes to be displayed for a list
		
		// Search criteria
		MetaModel::Init_SetZListItems('standard_search', array('name', 'status', 'location_id', 'brand','model','type','mgmt_ip')); // Criteria of the std search form
		MetaModel::Init_SetZListItems('advanced_search', array('name', 'status', 'org_id', 'location_id', 'brand','model','type','mgmt_ip','serial_number','ios_version','snmp_read','snmp_write')); // Criteria of the advanced search form


	}

	public function Generate(cmdbDataGenerator $oGenerator)
	{
		$this->Set('org_id', $oGenerator->GetOrganizationId());
		$this->Set('location_id', $oGenerator->GenerateKey("bizLocation", array('org_id' =>$oGenerator->GetOrganizationId() )));
		$this->Set('name', $oGenerator->GenerateString("enum(sw,swi,switch,rout,rtr,gw)|number(000-999)|.|domain()"));
		$this->Set('brand', $oGenerator->GenerateString("enum(Hewlett-Packard,Cisco,3Com,Avaya,Alcatel,Cabletron,Extrem Networks,Juniper,Netgear,Synopitcs,Xylan)"));
		$this->Set('model', $oGenerator->GenerateString("enum(Procurve ,Catalyst ,Multiswitch ,C)|enum(25,26,36,40,65)|enum(00,09,10,50)"));
		$this->Set('serial_number', $oGenerator->GenerateString("enum(FAA,AGA,PAD,COB,DFE)|number(0000-9999)|enum(M,X,L)"));
		$this->Set('ip_address', $oGenerator->GenerateString("number(10-248)|.|number(1-254)|.|number(1-254)|.|number(1-254)"));
		$this->Set('ios_version', $oGenerator->GenerateString("enum(9,10,12)|.|enum(0,1,2)|enum(,,,,XP,.5.1)"));
		$this->Set('snmp_read', $oGenerator->GenerateString("enum(Ew,+0,**,Ps)|number(00-99)|enum(+,=,],;, )|enum(Aze,Vbn,Bbn,+9+,-9-,#)"));
		$this->Set('snmp_write', $oGenerator->GenerateString("enum(M3,l3,$,*,Zz,Ks,jh)|number(00-99)|enum(A*e,V%n,Bbn,+,-,#)|number(0-9)"));
	}
}

////////////////////////////////////////////////////////////////////////////////////
/**
* A "Solution"
*/
////////////////////////////////////////////////////////////////////////////////////
class bizInfraGroup extends logInfra
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "bizmodel,searchable",
			"key_type" => "",
			"key_label" => "id",
			"name_attcode" => "name",
			"state_attcode" => "",
			"reconc_keys" => array("org_id", "name"), // inherited attributes
			"db_table" => "infra_group",
			"db_key_field" => "id",
			"db_finalclass_field" => "",
			"display_template" => "../business/templates/group.html",
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_InheritAttributes();
		MetaModel::Init_AddAttribute(new AttributeEnum("type", array("allowed_values"=>new ValueSetEnum("Monitoring,Reporting,list"), "sql"=>"type", "default_value"=>"list", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("description", array("allowed_values"=>null, "sql"=>"description", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalKey("parent_group_id", array("targetclass"=>"bizInfraGroup", "allowed_values"=>null, "sql"=>"parent_group_id", "is_null_allowed"=>true, "on_target_delete"=>DEL_MANUAL, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalField("parent_group_name", array("allowed_values"=>null, "extkey_attcode"=> 'parent_group_id', "target_attcode"=>"name")));

		// Display lists
		MetaModel::Init_SetZListItems('details', array('name', 'status', 'org_id', 'type', 'description','parent_group_id')); // Attributes to be displayed for a list
		MetaModel::Init_SetZListItems('list', array('name', 'status', 'org_id', 'type', 'description')); // Attributes to be displayed for a list
		// Search criteria
		MetaModel::Init_SetZListItems('standard_search', array('name', 'status', 'type')); // Criteria of the std search form
		MetaModel::Init_SetZListItems('advanced_search', array('name', 'status', 'type', 'description', 'org_id')); // Criteria of the advanced search form
	}

	function DisplayDetails(WebPage $oPage)
	{
		parent::DisplayDetails($oPage);
	/*
  	$oSearchFilter = new CMDBSearchFilter('lnkInfraGrouping');
		$oSearchFilter->AddCondition('infra_group_id', $this->GetKey(), '=');
		$oSet = new CMDBObjectSet($oSearchFilter);
		$count = $oSet->Count();
		if ($count > 0)
		{
   			$oPage->SetCurrentTab("RelatedInfrastructure");
			$oPage->p("Infrastructure Link to this group:");
			$this->DisplaySet($oPage, $oSet);
		}
		$oSearchFilter = new CMDBSearchFilter('lnkContactRealObject');
		$oSearchFilter->AddCondition('object_id', $this->GetKey(), '=');
		$oSet = new CMDBObjectSet($oSearchFilter);
		$count = $oSet->Count();
		if ($count > 0)
		{
   			$oPage->SetCurrentTab("TeamLinks");
			$oPage->p("People concerned by this group:");
			$this->DisplaySet($oPage, $oSet);
		}
*/
	}



	
	public function Generate(cmdbDataGenerator $oGenerator)
	{
		$this->Set('org_id', $oGenerator->GetOrganizationId());
		$this->Set('name', $oGenerator->GenerateString("enum(ov_nnm_,ovpi_,vitalnet_,datacenter_,web_farm_)|number(000-999)"));
		$this->Set('type', $oGenerator->GenerateString("enum(Application,Infrastructure)"));
	}	
}
////////////////////////////////////////////////////////////////////////////////////
//**
//* An application is an instance of a software install on a PC or Server
//* 
////////////////////////////////////////////////////////////////////////////////////
class bizApplication extends logInfra
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "bizmodel,searchable",
			"key_type" => "",
			"key_label" => "id",
			"name_attcode" => "name",
			"state_attcode" => "",
			"reconc_keys" => array("device_id", "name"), // inherited attributes
			"db_table" => "applications",
			"db_key_field" => "id",
			"db_finalclass_field" => "",
			"display_template" => "../business/templates/application.html",
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_InheritAttributes();
		MetaModel::Init_AddAttribute(new AttributeExternalKey("device_id", array("targetclass"=>"bizDevice", "jointype"=> '', "allowed_values"=>new ValueSetObjects('SELECT bizDevice AS p WHERE p.org_id = :this->org_id'), "sql"=>"device_id", "is_null_allowed"=>false, "on_target_delete"=>DEL_MANUAL, "depends_on"=>array("org_id"))));
		MetaModel::Init_AddAttribute(new AttributeExternalField("device_name", array("allowed_values"=>null, "extkey_attcode"=> 'device_id', "target_attcode"=>"name")));
		MetaModel::Init_AddAttribute(new AttributeDateTime("install_date", array("allowed_values"=>null, "sql"=>"install_date", "default_value"=>"", "is_null_allowed"=>false, "depends_on"=>array())));

		MetaModel::Init_AddAttribute(new AttributeString("version", array("allowed_values"=>null, "sql"=>"version", "default_value"=>"undefined", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("function", array("allowed_values"=>null, "sql"=>"function", "default_value"=>"", "is_null_allowed"=>false, "depends_on"=>array())));

		// Display lists
		MetaModel::Init_SetZListItems('details', array('name','device_id','org_id','status','install_date', 'version','function')); // Attributes to be displayed for the complete details
		MetaModel::Init_SetZListItems('list', array('name','device_id', 'version', 'function')); // Attributes to be displayed for a list
		// Search criteria
		MetaModel::Init_SetZListItems('standard_search', array('name', 'device_id','version','function')); // Criteria of the std search form
		MetaModel::Init_SetZListItems('advanced_search', array('name', 'device_id','version','function')); // Criteria of the advanced search form

	}

	public static function GetRelationQueries($sRelCode)
	{
		switch ($sRelCode)
		{
		case "impacts":
			$aRels = array(
			);
			return array_merge($aRels, parent::GetRelationQueries($sRelCode));
		}
	}

	function DisplayDetails(WebPage $oPage)
	{
		parent::DisplayDetails($oPage);
	/*
  	$oSearchFilter = new CMDBSearchFilter('lnkClientServer');
		$oSearchFilter->AddCondition('server_id', $this->GetKey(), '=');
		$oSet = new CMDBObjectSet($oSearchFilter);
		$count = $oSet->Count();
		if ($count > 0)
		{
   			$oPage->SetCurrentTab("Connected clients");
			$oPage->p("Client applications impacted when down:");
			$this->DisplaySet($oPage, $oSet);
		}
*/
	}

}

////////////////////////////////////////////////////////////////////////////////////
/**
* n-n link between any Infra and a Group
*/
////////////////////////////////////////////////////////////////////////////////////
class lnkInfraGrouping extends cmdbAbstractObject
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "bizmodel,searchable",
			"key_type" => "autoincrement",
			"key_label" => "link_id",
			"name_attcode" => "impact", 
			"state_attcode" => "",
			"reconc_keys" => array(""),
			"db_table" => "infra_grouping",
			"db_key_field" => "link_id",
			"db_finalclass_field" => "",
			"display_template" => "../business/templates/default.html",
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_AddAttribute(new AttributeExternalKey("infra_id", array("targetclass"=>"logInfra", "jointype"=> '', "allowed_values"=>null, "sql"=>"infra_id", "is_null_allowed"=>false, "on_target_delete"=>DEL_AUTO, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalField("infra_name", array("allowed_values"=>null, "extkey_attcode"=> 'infra_id', "target_attcode"=>"name")));
		MetaModel::Init_AddAttribute(new AttributeExternalField("infra_status", array("allowed_values"=>null, "extkey_attcode"=> 'infra_id', "target_attcode"=>"status")));
		MetaModel::Init_AddAttribute(new AttributeExternalKey("infra_group_id", array("targetclass"=>"bizInfraGroup", "jointype"=> '', "allowed_values"=>null, "sql"=>"infra_group_id", "is_null_allowed"=>false, "on_target_delete"=>DEL_AUTO, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalField("group_name", array("allowed_values"=>null, "extkey_attcode"=> 'infra_group_id', "target_attcode"=>"name")));
		MetaModel::Init_AddAttribute(new AttributeString("impact", array("allowed_values"=>null, "sql"=>"impact", "default_value"=>"none", "is_null_allowed"=>true, "depends_on"=>array())));
		// impact should modelized: enum (eg: if the group si dead when infra is dead)
		
		// Display lists
		MetaModel::Init_SetZListItems('details', array('infra_id','infra_status', 'impact', 'infra_group_id')); // Attributes to be displayed for a list
		MetaModel::Init_SetZListItems('list', array('infra_id','infra_status', 'impact', 'infra_group_id')); // Attributes to be displayed for a list
		// Search criteria
		MetaModel::Init_SetZListItems('standard_search', array('infra_id', 'infra_group_id', 'impact')); // Criteria of the std search form
		MetaModel::Init_SetZListItems('advanced_search', array('infra_id', 'infra_group_id', 'impact')); // Criteria of the advanced search form
	}
	
	public function Generate(cmdbDataGenerator $oGenerator)
	{
		$this->Set('infra_id', $oGenerator->GenerateKey("logInfra", array('org_id' =>$oGenerator->GetOrganizationId() )));
		$this->Set('infra_group_id', $oGenerator->GenerateKey("bizInfraGroup", array('org_id' =>$oGenerator->GetOrganizationId() )));
		$this->Set('impact', $oGenerator->GenerateString("enum(none,mandatory,partial)"));
	}

}






////////////////////////////////////////////////////////////////////////////////////
/**
* n-n link between two applications, one is the server side and the scond one the client*/
////////////////////////////////////////////////////////////////////////////////////
class lnkClientServer extends logRealObject
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "bizmodel,searchable",
			"key_type" => "autoincrement",
			"key_label" => "link_id",
			"name_attcode" => "relation",  // ????
			"state_attcode" => "",
			"reconc_keys" => array("relation"),  // ????
			"db_table" => "clientserver_links",
			"db_key_field" => "link_id",
			"db_finalclass_field" => "",
			"display_template" => "../business/templates/default.html",
	
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_AddAttribute(new AttributeEnum("status", array("allowed_values"=>new ValueSetEnum('production,implementation,obsolete'), "sql"=>"status", "default_value"=>"production", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalKey("client_id", array("targetclass"=>"bizApplication", "jointype"=> '', "allowed_values"=>null, "sql"=>"client_id", "is_null_allowed"=>false, "on_target_delete"=>DEL_AUTO, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalField("client_name", array("allowed_values"=>null, "extkey_attcode"=> 'client_id', "target_attcode"=>"name")));
		MetaModel::Init_AddAttribute(new AttributeExternalKey("server_id", array("targetclass"=>"bizApplication", "jointype"=> '', "allowed_values"=>null, "sql"=>"server_id", "is_null_allowed"=>false, "on_target_delete"=>DEL_AUTO, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalField("server_name", array("allowed_values"=>null, "extkey_attcode"=> 'server_id', "target_attcode"=>"name")));
		MetaModel::Init_AddAttribute(new AttributeString("relation", array("allowed_values"=>null, "sql"=>"relation", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		
		// Display lists
		MetaModel::Init_SetZListItems('details', array('client_id', 'server_id', 'relation')); // Attributes to be displayed for a list
		MetaModel::Init_SetZListItems('list', array('client_id', 'server_id', 'relation')); // Attributes to be displayed for a list
		// Search criteria
		MetaModel::Init_SetZListItems('standard_search', array('client_id', 'server_id')); // Criteria of the std search form
		MetaModel::Init_SetZListItems('advanced_search', array('client_id', 'server_id')); // Criteria of the advanced search form
	}


}

////////////////////////////////////////////////////////////////////////////////////
//**
//* A patch is an application or OS fixe for an infrastructure
//* 
////////////////////////////////////////////////////////////////////////////////////
class bizPatch extends logRealObject
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "bizmodel,searchable",
			"key_type" => "",
			"key_label" => "id",
			"name_attcode" => "name",
			"state_attcode" => "",
			"reconc_keys" => array("device_id", "name"), // inherited attributes
			"db_table" => "patches",
			"db_key_field" => "id",
			"db_finalclass_field" => "",
			"display_template" => "../business/templates/default.html",
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_InheritAttributes();
		MetaModel::Init_AddAttribute(new AttributeEnum("status", array("allowed_values"=>new ValueSetEnum('production,obsolete'), "sql"=>"status", "default_value"=>"production", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalKey("device_id", array("targetclass"=>"bizDevice", "jointype"=> '', "allowed_values"=>new ValueSetObjects('SELECT bizDevice AS p WHERE p.org_id = :this->org_id'), "sql"=>"device_id", "is_null_allowed"=>false, "on_target_delete"=>DEL_MANUAL, "depends_on"=>array("org_id"))));
		MetaModel::Init_AddAttribute(new AttributeExternalField("device_name", array("allowed_values"=>null, "extkey_attcode"=> 'device_id', "target_attcode"=>"name")));
   		MetaModel::Init_AddAttribute(new AttributeDateTime("install_date", array("allowed_values"=>null, "sql"=>"install_date", "default_value"=>"", "is_null_allowed"=>false, "depends_on"=>array())));
		
		MetaModel::Init_AddAttribute(new AttributeText("description", array("allowed_values"=>null, "sql"=>"description", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("patch_type", array("allowed_values"=>new ValueSetEnum("OS,Application"), "sql"=>"patch_type", "default_value"=>"OS", "is_null_allowed"=>false, "depends_on"=>array())));

		// Display lists
		MetaModel::Init_SetZListItems('details', array('name','device_id', 'install_date', 'patch_type','description')); // Attributes to be displayed for the complete details
		MetaModel::Init_SetZListItems('list', array('name','device_id', 'patch_type','install_date')); // Attributes to be displayed for a list
		// Search criteria
		MetaModel::Init_SetZListItems('standard_search', array('name', 'device_id','patch_type')); // Criteria of the std search form
		MetaModel::Init_SetZListItems('advanced_search', array('name', 'device_id','patch_type')); // Criteria of the advanced search form

	}
}

/*** Insert here all modules requires for ITOP application  ***/

require_once('incidentMgmt.business.php');
require_once('ServiceMgmt.business.php');
require_once('ChangeMgmt.business.php');
require_once('KEDB.business.php');
require_once('ServiceDesk.business.php');
?>
