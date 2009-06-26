<?php

/**
 * A change as requested/validated at once by user, may groups many atomic changes 
 *
 * @package     iTopORM
 * @author      Romain Quetiez <romainquetiez@yahoo.fr>
 * @author      Denis Flaven <denisflave@free.fr>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.itop.com
 * @since       1.0
 * @version     1.1.1.1 $
 */
class CMDBChange extends DBObject
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "core/cmdb",
			"name" => "change",
			"description" => "Changes tracking",
			"key_type" => "autoincrement",
			"key_label" => "",
			"name_attcode" => "date",
			"state_attcode" => "",
			"reconc_keys" => array(),
			"db_table" => "priv_change",
			"db_key_field" => "id",
			"db_finalclass_field" => "",
		);
		MetaModel::Init_Params($aParams);
		//MetaModel::Init_InheritAttributes();
		MetaModel::Init_AddAttribute(new AttributeDate("date", array("label"=>"date", "description"=>"date and time at which the changes have been recorded", "allowed_values"=>null, "sql"=>"date", "default_value"=>"", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("userinfo", array("label"=>"misc. info", "description"=>"caller's defined information", "allowed_values"=>null, "sql"=>"userinfo", "default_value"=>null, "is_null_allowed"=>true, "depends_on"=>array())));

		//MetaModel::Init_InheritFilters();
		MetaModel::Init_AddFilterFromAttribute("date");
		MetaModel::Init_AddFilterFromAttribute("userinfo");
	}

}

?>
