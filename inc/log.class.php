<?php

/*
   ------------------------------------------------------------------------
   Plugin Monitoring for GLPI
   Copyright (C) 2011-2012 by the Plugin Monitoring for GLPI Development Team.

   https://forge.indepnet.net/projects/monitoring/
   ------------------------------------------------------------------------

   LICENSE

   This file is part of Plugin Monitoring project.

   Plugin Monitoring for GLPI is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   Plugin Monitoring for GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with Behaviors. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Plugin Monitoring for GLPI
   @author    David Durieux
   @co-author 
   @comment   
   @copyright Copyright (c) 2011-2012 Plugin Monitoring for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/monitoring/
   @since     2011
 
   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMonitoringLog extends CommonDBTM {
   

   /**
   * Get name of this type
   *
   *@return text name of this type by language of the user connected
   *
   **/
   static function getTypeName($nb=0) {
      global $LANG;

      return __('Logs', 'monitoring');
   }



   static function canCreate() {
      return haveRight('computer', 'w');
   }


   
   static function canView() {
      return haveRight('computer', 'r');
   }


   
   static function cronCleanlogs() {
      global $DB;

      $pmLog      = new PluginMonitoringLog();
      $pmConfig   = new PluginMonitoringConfig();
      
      $id_restart = 0;
      $a_restarts = $this->find("`action`='restart'", "`id` DESC", 1);
      if (count($a_restarts) > 0) {
         $a_restart = current($a_restarts);
         $id_restart = $a_restart['id'];
      }
      $pmConfig->getFromDB(1);
      $secs = $pmConfig->fields['logretention'] * DAY_TIMESTAMP;
      $query = "DELETE FROM `".$pmLog->getTable()."`
         WHERE UNIX_TIMESTAMP(date_mod) < UNIX_TIMESTAMP()-$secs";
      if ($id_restart > 0) {
         $query .= " AND `id` < '".$id_restart."'";
      }
      $DB->query($query);
      return true;
   }

}

?>