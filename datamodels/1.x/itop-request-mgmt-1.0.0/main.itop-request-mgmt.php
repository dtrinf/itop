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

define('PORTAL_SERVICECATEGORY_QUERY', 'SELECT Service AS s JOIN SLA AS sla ON sla.service_id=s.id JOIN lnkContractToSLA AS ln ON ln.sla_id=sla.id JOIN CustomerContract AS cc ON ln.contract_id=cc.id WHERE cc.org_id = :org_id');
define('PORTAL_SERVICE_SUBCATEGORY_QUERY', 'SELECT ServiceSubcategory WHERE service_id = :svc_id');

define('PORTAL_VALIDATE_SERVICECATEGORY_QUERY', 'SELECT Service AS s JOIN SLA AS sla ON sla.service_id=s.id JOIN lnkContractToSLA AS ln ON ln.sla_id=sla.id JOIN CustomerContract AS cc ON ln.contract_id=cc.id WHERE cc.org_id = :org_id AND s.id = :id');
define('PORTAL_VALIDATE_SERVICESUBCATEGORY_QUERY', 'SELECT ServiceSubcategory AS Sub JOIN Service AS Svc ON Sub.service_id = Svc.id WHERE Sub.id=:id');

define('PORTAL_ALL_PARAMS', 'from_service_id,org_id,caller_id,service_id,servicesubcategory_id,title,description,impact,urgency,workgroup_id,moreinfo,caller_id,start_date,end_date,duration,impact_duration');

define('PORTAL_ATTCODE_LOG', 'ticket_log');
define('PORTAL_ATTCODE_COMMENT', 'user_commment');
define('PORTAL_REQUEST_FORM_ATTRIBUTES', 'title,description,impact,urgency,workgroup_id');

define('PORTAL_ATTCODE_TYPE', ''); // optional if the type has to be set
define('PORTAL_SET_TYPE_FROM', ''); // The attribute to get the type from (Subcategory)

?>
