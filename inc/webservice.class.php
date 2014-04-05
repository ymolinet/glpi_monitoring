<?php

/*
   ------------------------------------------------------------------------
   Plugin Monitoring for GLPI
   Copyright (C) 2011-2014 by the Plugin Monitoring for GLPI Development Team.

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
   along with Monitoring. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Plugin Monitoring for GLPI
   @author    David Durieux
   @co-author
   @comment
   @copyright Copyright (c) 2011-2014 Plugin Monitoring for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/monitoring/
   @since     2011

   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

class PluginMonitoringWebservice {


   static function methodShinkenGetConffiles($params, $protocol) {

      if (isset ($params['help'])) {
         return array('file'  => 'config filename to get : commands.cfg, hosts.cfg',
                      'help'    => 'bool,optional');
      }

      if (!isset($params['tag'])) {
         $params['tag'] = '';
      }

      ini_set("max_execution_time", "0");
      ini_set("memory_limit", "-1");

      $pmShinken = new PluginMonitoringShinken();
      switch ($params['file']) {

         case 'commands.cfg':
            $array = $pmShinken->generateCommandsCfg(1);
            return array($array[0]=>$array[1]);
            break;

         case 'hosts.cfg':
            $array = $pmShinken->generateHostsCfg(1, $params['tag']);
            return array($array[0]=>$array[1]);
            break;

         case 'hostgroups.cfg':
            $array = $pmShinken->generateHostgroupsCfg(1, $params['tag']);
            return array($array[0]=>$array[1]);
            break;

         case 'contacts.cfg':
            $array = $pmShinken->generateContactsCfg(1);
            return array($array[0]=>$array[1]);
            break;

         case 'timeperiods.cfg':
            $array = $pmShinken->generateTimeperiodsCfg(1);
            return array($array[0]=>$array[1]);
            break;

         case 'services.cfg':
            $array = $pmShinken->generateServicesCfg(1, $params['tag']);
            return array($array[0]=>$array[1]);
            break;

         case 'templates.cfg':
            $array = $pmShinken->generateTemplatesCfg(1, $params['tag']);
            return array($array[0]=>$array[1]);
            break;

         case 'all':
            $output = array();
            $array = $pmShinken->generateCommandsCfg(1);
            $output[$array[0]] = $array[1];
            $array = $pmShinken->generateHostsCfg(1, $params['tag']);
            $output[$array[0]] = $array[1];
            $array = $pmShinken->generateHostgroupsCfg(1, $params['tag']);
            $output[$array[0]] = $array[1];
            $array = $pmShinken->generateContactsCfg(1);
            $output[$array[0]] = $array[1];
            $array = $pmShinken->generateTimeperiodsCfg(1);
            $output[$array[0]] = $array[1];
            $array = $pmShinken->generateTemplatesCfg(1, $params['tag']);
            $output[$array[0]] = $array[1];
            $array = $pmShinken->generateServicesCfg(1, $params['tag']);
            $output[$array[0]] = $array[1];
            return $output;
            break;

      }
   }



   static function methodShinkenCommands($params, $protocol) {

      $pmShinken = new PluginMonitoringShinken();
      $array = $pmShinken->generateCommandsCfg();
      return $array;
   }



   static function methodShinkenHosts($params, $protocol) {
      if (!isset($params['tag'])) {
         $params['tag'] = '';
      }

      // Update ip with Tag
      if (isset($_SERVER['REMOTE_ADDR'])) {
         $pmTag = new PluginMonitoringTag();
         $pmTag->setIP($params['tag'], $_SERVER['REMOTE_ADDR']);
      }

      $pmShinken = new PluginMonitoringShinken();
      $array = $pmShinken->generateHostsCfg(0, $params['tag']);
      return $array;
   }



   static function methodShinkenHostgroups($params, $protocol) {

      if (!isset($params['tag'])) {
         $params['tag'] = '';
      }

      $pmShinken = new PluginMonitoringShinken();
      $array = $pmShinken->generateHostgroupsCfg(0, $params['tag']);
      return $array;
   }



   static function methodShinkenServices($params, $protocol) {

      if (!isset($params['tag'])) {
         $params['tag'] = '';
      }

      $pmShinken = new PluginMonitoringShinken();
      $array = $pmShinken->generateServicesCfg(0, $params['tag']);
      return $array;
   }



   static function methodShinkenTemplates($params, $protocol) {

      if (!isset($params['tag'])) {
         $params['tag'] = '';
      }

      $pmShinken = new PluginMonitoringShinken();
      $array = $pmShinken->generateTemplatesCfg(0, $params['tag']);
      return $array;
   }



   static function methodShinkenContacts($params, $protocol) {

      $pmShinken = new PluginMonitoringShinken();
      $array = $pmShinken->generateContactsCfg();
      return $array;
   }



   static function methodShinkenTimeperiods($params, $protocol) {

      $pmShinken = new PluginMonitoringShinken();
      $array = $pmShinken->generateTimeperiodsCfg();
      return $array;
   }



   static function methodDashboard($params, $protocol) {
      $response = array();

      if (!isset($params['view'])) {
         return $response;
      }

      // Toolbox::logInFile("pm-ws", "methodDashboard, view : ".$params['view']."\n");
      $pm = new PluginMonitoringDisplay();
      if ($params['view'] == 'Hosts') {
         return $pm->displayHostsCounters(0);
      } else {
         return $pm->displayCounters($params['view'], 0);
      }
   }



   static function methodGetServicesList($params, $protocol) {
      $array = PluginMonitoringWebservice::getServicesList($params['statetype'], $params['view']);

      return $array;
   }
   static function getServicesList($statetype, $view) {
      global $DB;

      $services = array();

      if ($view == 'Ressources') {

         switch ($statetype) {

            case "ok":
               $query = "SELECT * FROM `glpi_plugin_monitoring_services`
                  LEFT JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`
                     ON `plugin_monitoring_componentscatalogs_hosts_id`=
                        `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`
                  WHERE (`state`='OK' OR `state`='UP') AND `state_type`='HARD'";
               $result = $DB->query($query);
               while ($data=$DB->fetch_array($result)) {
                  $itemtype = $data['itemtype'];
                  $item = new $itemtype();
                  $item->getFromDB($data['items_id']);

                  $services[] = "(".$itemtype.") ".$item->getName()."\n=> ".$data['name'];
               }
               break;

            case "warning":
               $query = "SELECT * FROM `glpi_plugin_monitoring_services`
                  LEFT JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`
                     ON `plugin_monitoring_componentscatalogs_hosts_id`=
                        `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`
                  WHERE (`state`='WARNING' OR `state`='UNKNOWN' OR `state`='RECOVERY' OR `state`='FLAPPING' OR `state` IS NULL)
                    AND `state_type`='HARD'";
               $result = $DB->query($query);
               while ($data=$DB->fetch_array($result)) {
                  $itemtype = $data['itemtype'];
                  $item = new $itemtype();
                  $item->getFromDB($data['items_id']);

                  $services[] = "(".$itemtype.") ".$item->getName()."\n=> ".$data['name'];
               }
               break;

            case "critical":
               $query = "SELECT * FROM `glpi_plugin_monitoring_services`
                  LEFT JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`
                     ON `plugin_monitoring_componentscatalogs_hosts_id`=
                        `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`
                  WHERE (`state`='DOWN' OR `state`='UNREACHABLE' OR `state`='CRITICAL' OR `state`='DOWNTIME')
                    AND `state_type`='HARD'";
               $result = $DB->query($query);
               while ($data=$DB->fetch_array($result)) {
                  $itemtype = $data['itemtype'];
                  $item = new $itemtype();
                  $item->getFromDB($data['items_id']);

                  $services[] = "(".$itemtype.") ".$item->getName()."\n=> ".$data['name'];
               }
               break;
         }

      } else if ($view == 'Componentscatalog') {
         $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();
         $queryCat = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs`";
         $resultCat = $DB->query($queryCat);
         while ($data=$DB->fetch_array($resultCat)) {

            $query = "SELECT * FROM `".$pmComponentscatalog_Host->getTable()."`
               WHERE `plugin_monitoring_componentscalalog_id`='".$data['id']."'";
            $result = $DB->query($query);
            $state = array();
            $state['ok'] = 0;
            $state['warning'] = 0;
            $state['critical'] = 0;
            while ($dataComponentscatalog_Host=$DB->fetch_array($result)) {

               $state['ok'] += countElementsInTable("glpi_plugin_monitoring_services",
                       "(`state`='OK' OR `state`='UP') AND `state_type`='HARD'
                          AND `plugin_monitoring_componentscatalogs_hosts_id`='".$dataComponentscatalog_Host['id']."'");


               $state['warning'] += countElementsInTable("glpi_plugin_monitoring_services",
                       "(`state`='WARNING' OR `state`='UNKNOWN' OR `state`='RECOVERY' OR `state`='FLAPPING' OR `state` IS NULL)
                          AND `state_type`='HARD'
                          AND `plugin_monitoring_componentscatalogs_hosts_id`='".$dataComponentscatalog_Host['id']."'");

               $state['critical'] += countElementsInTable("glpi_plugin_monitoring_services",
                       "(`state`='DOWN' OR `state`='UNREACHABLE' OR `state`='CRITICAL' OR `state`='DOWNTIME')
                          AND `state_type`='HARD'
                          AND `plugin_monitoring_componentscatalogs_hosts_id`='".$dataComponentscatalog_Host['id']."'");

            }
            if ($state['critical'] > 0) {
               if ($statetype == 'critical') {
                  $services[] = "(Catalog) ".$data['name'];
               }
            } else if ($state['warning'] > 0) {
               if ($statetype == 'warning') {
                  $services[] = "(Catalog) ".$data['name'];
               }
            } else if ($state['ok'] > 0) {
               if ($statetype == 'ok') {
                  $services[] = "(Catalog) ".$data['name'];
               }
            }
         }
      } else if ($view == 'Businessrules') {

      }
      return $services;
   }


   static function methodGetHostsStates($params, $protocol) {
      return PluginMonitoringWebservice::getHostsStates(isset($params['filter']) ? $params['filter'] : '');
   }
   static function getHostsStates($filter) {
      global $DB;

      $hosts = array();

      $query = "
         SELECT
            `glpi_entities`.`name` AS entity_name,
            CONCAT_WS('', `glpi_computers`.`name`, `glpi_printers`.`name`, `glpi_networkequipments`.`name`) AS host_name,
            `glpi_computers`.*,
            `glpi_plugin_monitoring_hosts`.*
         FROM `glpi_plugin_monitoring_hosts`
         LEFT JOIN `glpi_computers`
            ON `glpi_plugin_monitoring_hosts`.`items_id` = `glpi_computers`.`id` AND `glpi_plugin_monitoring_hosts`.`itemtype`='Computer'
         LEFT JOIN `glpi_printers`
            ON `glpi_plugin_monitoring_hosts`.`items_id` = `glpi_printers`.`id` AND `glpi_plugin_monitoring_hosts`.`itemtype`='Printer'
         LEFT JOIN `glpi_networkequipments`
            ON `glpi_plugin_monitoring_hosts`.`items_id` = `glpi_networkequipments`.`id` AND `glpi_plugin_monitoring_hosts`.`itemtype`='NetworkEquipment'
         LEFT JOIN `glpi_entities`
            ON CONCAT_WS('', `glpi_computers`.`entities_id`, `glpi_printers`.`entities_id`, `glpi_networkequipments`.`entities_id`) = `glpi_entities`.`id`
      ";
      $query .= "WHERE `glpi_computers`.`name` <> '' AND `glpi_entities`.`id` IN (".$_SESSION['glpiactiveentities_string'].")";
      if (! empty($filter)) {
         $query .= " AND $filter";
      }
//      $query .= " ORDER BY entity_name ASC, host_name ASC;";
	  $query .= " ORDER BY entity_name ASC, FIELD(`glpi_plugin_monitoring_hosts`.`state`,'DOWN','PENDING','UNKNOWN','UNREACHABLE','UP');";
      // Toolbox::logInFile("pm-ws", "getHostsStates, query : $query\n");
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $host = array();
         foreach ($data as $key=>$value) {
            if (is_string($key)) {
               $host[$key] = $value;
            }
         }
         $hosts[] = $host;
      }

      return $hosts;
   }


   static function methodGetServicesStates($params, $protocol) {
      return PluginMonitoringWebservice::getServicesStates(isset($params['filter']) ? $params['filter'] : '');
   }
   static function getServicesStates($filter) {
      global $DB;

      $hosts = array();

      $query = "
         SELECT
            `glpi_entities`.`name` AS entity_name,
            CONCAT_WS('', `glpi_computers`.`name`, `glpi_printers`.`name`, `glpi_networkequipments`.`name`) AS host_name,
            `glpi_computers`.*,
            `glpi_plugin_monitoring_hosts`.*,
            `glpi_plugin_monitoring_services`.*,
            `glpi_plugin_monitoring_componentscatalogs_hosts`.*,
            `glpi_plugin_monitoring_components`.*
         FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
         INNER JOIN `glpi_plugin_monitoring_services`
            ON (`glpi_plugin_monitoring_services`.`plugin_monitoring_componentscatalogs_hosts_id` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`)
         INNER JOIN `glpi_plugin_monitoring_hosts`
            ON `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_plugin_monitoring_hosts`.`items_id` AND `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype` = `glpi_plugin_monitoring_hosts`.`itemtype`
         INNER JOIN `glpi_plugin_monitoring_componentscatalogs`
            ON `plugin_monitoring_componentscalalog_id` = `glpi_plugin_monitoring_componentscatalogs`.`id`
         INNER JOIN `glpi_plugin_monitoring_components`
            ON (`glpi_plugin_monitoring_services`.`plugin_monitoring_components_id` = `glpi_plugin_monitoring_components`.`id`)
         INNER JOIN `glpi_entities`
            ON (`glpi_plugin_monitoring_services`.`entities_id` = `glpi_entities`.`id`)
         LEFT JOIN `glpi_computers`
            ON `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_computers`.`id` AND `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype`='Computer'
         LEFT JOIN `glpi_printers`
            ON `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_printers`.`id` AND `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype`='Printer'
         LEFT JOIN `glpi_networkequipments`
            ON `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_networkequipments`.`id` AND `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype`='NetworkEquipment'
      ";
      $query .= "WHERE `glpi_entities`.`id` IN (".$_SESSION['glpiactiveentities_string'].")";
      if (! empty($filter)) {
         $query .= " AND $filter";
      }
//      $query .= " ORDER BY host_name ASC;";
	  $query .= " ORDER BY FIELD(`glpi_plugin_monitoring_services`.`state`,'CRITICAL','PENDING','UNKNOWN','WARNING','OK');";
      Toolbox::logInFile("pm-ws", "getHostsStates, query : $query\n");
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $host = array();
         foreach ($data as $key=>$value) {
            if (is_string($key)) {
               $host[$key] = $value;
            }
         }
         $hosts[] = $host;
      }

      return $hosts;
   }


   static function methodGetDailyCounters($params, $protocol) {
      if (isset($params['last'])) {
         return PluginMonitoringWebservice::getLastCountersPerHost(isset($params['filter']) ? $params['filter'] : '');
      }
      return PluginMonitoringWebservice::getDailyCounters(isset($params['filter']) ? $params['filter'] : '', isset($params['limit']) ? $params['limit'] : '100');
   }
   static function getLastCountersPerHost($filter) {
      global $DB;

      $rows = array();

      $query = "
         SELECT tmp.* FROM ( SELECT * FROM `glpi_plugin_monitoring_hostdailycounters` ORDER BY DATE(DAY) DESC ) tmp
         LEFT JOIN `glpi_computers`
          ON `tmp`.`hostname` = `glpi_computers`.`name`
         LEFT JOIN `glpi_entities`
          ON (`glpi_computers`.`entities_id` = `glpi_entities`.`id`)
      ";
      $query .= "WHERE `glpi_entities`.`id` IN (".$_SESSION['glpiactiveentities_string'].")";
      if (! empty($filter)) {
         $query .= " AND $filter";
      }
      $query .= "
         GROUP BY hostname
         ORDER BY hostname
      ";
      // Toolbox::logInFile("pm-ws", "getDailyCounters, query : $query\n");
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $row = array();
         foreach ($data as $key=>$value) {
            if (is_string($key)) {
               $row[$key] = $value;
            }
         }
         $rows[] = $row;
      }

      return $rows;
   }
   static function getLastCountersPerDay($filter) {
      global $DB;

      $rows = array();

      $query = "
         SELECT tmp.* FROM ( SELECT * FROM `glpi_plugin_monitoring_hostdailycounters` ORDER BY DATE(DAY) DESC ) tmp
         LEFT JOIN `glpi_computers`
          ON `tmp`.`hostname` = `glpi_computers`.`name`
         LEFT JOIN `glpi_entities`
          ON (`glpi_computers`.`entities_id` = `glpi_entities`.`id`)
      ";
      $query .= "WHERE `glpi_entities`.`id` IN (".$_SESSION['glpiactiveentities_string'].")";
      if (! empty($filter)) {
         $query .= " AND $filter";
      }
      $query .= "
         GROUP BY hostname
         ORDER BY hostname
      ";
      Toolbox::logInFile("pm-ws", "getDailyCounters, query : $query\n");
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $row = array();
         foreach ($data as $key=>$value) {
            if (is_string($key)) {
               $row[$key] = $value;
            }
         }
         $rows[] = $row;
      }

      return $rows;
   }
   static function getDailyCounters($filter, $limit) {
      global $DB;

      $rows = array();

      $query = "
         SELECT
            `glpi_entities`.`name` AS entity_name,
            `glpi_plugin_monitoring_hostdailycounters`.*
         FROM `glpi_plugin_monitoring_hostdailycounters`
         LEFT JOIN `glpi_computers`
            ON `glpi_plugin_monitoring_hostdailycounters`.`hostname` = `glpi_computers`.`name`
         LEFT JOIN `glpi_entities`
            ON (`glpi_computers`.`entities_id` = `glpi_entities`.`id`)
      ";
      $query .= "WHERE `glpi_entities`.`id` IN (".$_SESSION['glpiactiveentities_string'].")";
      if (! empty($filter)) {
         $query .= " AND $filter";
      }
      $query .= " ORDER BY date(day) DESC LIMIT $limit;";
      // Toolbox::logInFile("pm-ws", "getDailyCounters, query : $query\n");
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $row = array();
         foreach ($data as $key=>$value) {
            if (is_string($key)) {
               $row[$key] = $value;
            }
         }
         $rows[] = $row;
      }

      return $rows;
   }
}

?>
