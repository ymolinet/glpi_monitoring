<?php

/*
   ----------------------------------------------------------------------
   Monitoring plugin for GLPI
   Copyright (C) 2010-2011 by the GLPI plugin monitoring Team.

   https://forge.indepnet.net/projects/monitoring/
   ----------------------------------------------------------------------

   LICENSE

   This file is part of Monitoring plugin for GLPI.

   Monitoring plugin for GLPI is free software: you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation, either version 2 of the License, or
   any later version.

   Monitoring plugin for GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with Monitoring plugin for GLPI.  If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------
   Original Author of file: David DURIEUX
   Co-authors of file:
   Purpose of file:
   ----------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMonitoringComponentscatalog_rule extends CommonDBTM {
   

   /**
   * Get name of this type
   *
   *@return text name of this type by language of the user connected
   *
   **/
   static function getTypeName() {
      global $LANG;

      return "Rules";
   }



   function canCreate() {
      return true;
   }


   
   function canView() {
      return true;
   }


   
   function canCancel() {
      return true;
   }


   
   function canUndo() {
      return true;
   }


   
   function canValidate() {
      return true;
   }

   

   function showRules($componentscatalogs_id) {
      global $DB,$CFG_GLPI,$LANG;

      $this->addRule($componentscatalogs_id);

      $rand = mt_rand();

      $query = "SELECT * FROM `".$this->getTable()."`
         WHERE `plugin_monitoring_componentscalalog_id`='".$componentscatalogs_id."'";
      $result = $DB->query($query);
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";
      echo "<th>";
      echo $LANG['plugin_monitoring']['rule'][0];
      echo "</th>";
      echo "</tr>";
      echo "</table>";

      echo "<table class='tab_cadre_fixe'>";     
      
      echo "<tr>";
      echo "<th width='10'>&nbsp;</th>";
      echo "<th>".$LANG['common'][17]."</th>";
      echo "<th>".$LANG['entity'][0]."</th>";
      echo "<th>".$LANG['common'][16]."</th>";
      echo "</tr>";
      
      while ($data=$DB->fetch_array($result)) {
         echo "<tr>";
         echo "<td>";
         echo "<input type='checkbox' name='item[".$data["id"]."]' value='1'>";
         echo "</td>";
         echo "<td class='center'>";
         echo $data['itemtype'];
         echo "</td>";
         echo "<td class='center'>";

         echo "</td>";
         echo "<td class='center'>";
         echo $data['name'];
         echo "</td>";
         echo "</tr>";
      }
      
      echo "</table>";

      return true;
   }
   
   
   
   function addRule($componentscatalogs_id) {
      
      $param = array();
      if (isset($_SESSION['plugin_monitoring_rules'])) {
         $param = $_SESSION['plugin_monitoring_rules'];
      }
      $_GET = $param;
      if (isset($_SESSION['plugin_monitoring_rules_REQUEST_URI'])) {
         $_SERVER['REQUEST_URI'] = $_SESSION['plugin_monitoring_rules_REQUEST_URI'];
      }
      $itemtype = 'Computer';
      if (isset($_SESSION['plugin_monitoring_rules']['itemtype'])) {
         $itemtype = $_SESSION['plugin_monitoring_rules']['itemtype'];
      }
      Search::manageGetValues($itemtype);
      $this->showGenericSearch($itemtype, $param);
      
   }
   

   
   /*
    * Use when add a rule, caclculate for all items in GLPI DB
    */
   function getItemsDynamicly() {
      
   }
   
   
   
   /*
    * When add or update an item (Computer, ...), check if 
    * a rule verify it
    */
   static function isThisItemCheckRule($parm) {
      global $DB;
      
      $itemtype = get_class($parm);
      $items_id = $parm->fields['id'];
      
      $a_find = array();
      $pmComponentscatalog_rule = new PluginMonitoringComponentscatalog_rule();

      $query = "SELECT * FROM `".$pmComponentscatalog_rule->getTable()."`
         WHERE `itemtype`='".$itemtype."'";
      $result = $DB->query($query);
      $get_tmp = array();
      if (isset($_GET)) {
          $get_tmp = $_GET;  
      }
      while ($data=$DB->fetch_array($result)) {
         if (isset($_SESSION["glpisearchcount"][$data['itemtype']])) {
            unset($_SESSION["glpisearchcount"][$data['itemtype']]);
         }
         if (isset($_SESSION["glpisearchcount2"][$data['itemtype']])) {
            unset($_SESSION["glpisearchcount2"][$data['itemtype']]);
         }

         $_GET = importArrayFromDB($data['condition']);
         
         $_GET["glpisearchcount"] = count($_GET['field']);
         if (isset($_GET['field2'])) {
            $_GET["glpisearchcount2"] = count($_GET['field2']);
         }
         
         Search::manageGetValues($data['itemtype']);

         $resultr = $pmComponentscatalog_rule->constructSQL($itemtype, 
                                        $_GET,
                                        $items_id);
         if ($DB->numrows($resultr) > 0) {
            $a_find[$data['plugin_monitoring_componentscalalog_id']] = 1;
         } else {
            if (!isset($a_find[$data['plugin_monitoring_componentscalalog_id']])) {
               $a_find[$data['plugin_monitoring_componentscalalog_id']] = 0;
            }
         }
      }
      if (count($get_tmp) > 0) {
         $_GET = $get_tmp; 
      }
      $pmComponentscatalog_Host= new PluginMonitoringComponentscatalog_Host();
      
      foreach ($a_find as $componentscalalog_id => $is_present) {
         if ($is_present == '0') { // * Remove from dynamic if present      
            $query = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
               WHERE `plugin_monitoring_componentscalalog_id`='".$componentscalalog_id."'
                  AND `itemtype`='".$itemtype."'
                  AND `items_id`='".$items_id."'
                  AND`is_static`='0'";
            $result = $DB->query($query);
            while ($data=$DB->fetch_array($result)) {
               $pmComponentscatalog_Host->delete(array('id'=>$data['id']));
               $pmComponentscatalog_Host->unlinkComponentsToItem($data['id']);
            }
         } else { //  add if not present
            $query = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
               WHERE `plugin_monitoring_componentscalalog_id`='".$componentscalalog_id."'
                  AND `itemtype`='".$itemtype."'
                  AND `items_id`='".$items_id."'";
            $result = $DB->query($query);
            if ($DB->numrows($result) == '0') {
               $input = array();
               $input['plugin_monitoring_componentscalalog_id'] = $componentscalalog_id;
               $input['is_static'] = '0';
               $input['items_id'] = $items_id;
               $input['itemtype'] = $itemtype;
               $componentscatalogs_hosts_id = $pmComponentscatalog_Host->add($input);
               $pmComponentscatalog_Host->linkComponentsToItem($componentscalalog_id, 
                                                               $componentscatalogs_hosts_id);
            }            
         }
         
         
      }
      // If find, check if set in static
      
      // if not find, delete in dynamic if is present
      
   }
   
   
   
   
   /*
    * ************************************************************************ *
    * ************** Functions derived from Search of GLPI core ************** *
    * ************************************************************************ *
    */
   
   
   
   /*
    * Cloned Core function to display with our require.
    */
   function showGenericSearch($itemtype, $params) {
      global $LANG, $CFG_GLPI;

      // Default values of parameters
      $p['link']        = array();//
      $p['field']       = array();
      $p['contains']    = array();
      $p['searchtype']  = array();
      $p['sort']        = '';
      $p['is_deleted']  = 0;
      $p['link2']       = '';//
      $p['contains2']   = '';
      $p['field2']      = '';
      $p['itemtype2']   = '';
      $p['searchtype2'] = '';

      foreach ($params as $key => $val) {
         $p[$key] = $val;
      }

      $options = Search::getCleanedOptions($itemtype);
      $target  = getItemTypeSearchURL($itemtype);

      // Instanciate an object to access method
      $item = NULL;
      if ($itemtype!='States' && class_exists($itemtype)) {
         $item = new $itemtype();
      }

      $linked =  Search::getMetaItemtypeAvailable($itemtype);
      $this->showFormHeader();
//      echo "<form name='searchform$itemtype' method='get' action=\"".
//              $CFG_GLPI['root_doc']."/plugins/monitoring/front/componentscatalog_rule.form.php\">";
//      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo $LANG['common'][16]."&nbsp;:";
      echo "</td>";
      echo "<td>";
      echo "<input type='text' name='name' value='".$_SESSION['plugin_monitoring_rules']['name']."'/>";
      echo "</td>";
      echo "<td>";
      echo $LANG['state'][6]."&nbsp;:";
      echo "</td>";
      echo "<td>";
      Dropdown::dropdownTypes("itemtype", 
                              $_SESSION['plugin_monitoring_rules']['itemtype'],
                              $CFG_GLPI['networkport_types']);
      echo "</td>";
      echo "</tr>";
      if (isset($_SESSION['plugin_monitoring_rules']['itemtype'])) {
      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='3'>";      
      echo "<table>";

      // Display normal search parameters
      for ($i=0 ; $i<$_SESSION["glpisearchcount"][$itemtype] ; $i++) {
         echo "<tr><td class='left' width='50%'>";

         // First line display add / delete images for normal and meta search items
         if ($i==0) {
            echo "<input type='hidden' disabled id='add_search_count' name='add_search_count'
                   value='1'>";
            echo "<a href='#' onClick = \"document.getElementById('add_search_count').disabled=false;
                   document.forms['searchform$itemtype'].submit();\">";
            echo "<img src=\"".$CFG_GLPI["root_doc"]."/pics/plus.png\" alt='+' title=\"".
                   $LANG['search'][17]."\"></a>&nbsp;&nbsp;&nbsp;&nbsp;";
            if ($_SESSION["glpisearchcount"][$itemtype]>1) {
               echo "<input type='hidden' disabled id='delete_search_count'
                      name='delete_search_count' value='1'>";
               echo "<a href='#' onClick = \"document.getElementById('delete_search_count').disabled=false;
                      document.forms['searchform$itemtype'].submit();\">";
               echo "<img src=\"".$CFG_GLPI["root_doc"]."/pics/moins.png\" alt='-' title=\"".
                     $LANG['search'][18]."\"></a>&nbsp;&nbsp;&nbsp;&nbsp;";
            }
            if (is_array($linked) && count($linked)>0) {
               echo "<input type='hidden' disabled id='add_search_count2' name='add_search_count2'
                      value='1'>";
               echo "<a href='#' onClick=\"document.getElementById('add_search_count2').disabled=false;
                      document.forms['searchform$itemtype'].submit();\">";
               echo "<img src=\"".$CFG_GLPI["root_doc"]."/pics/meta_plus.png\" alt='+' title=\"".
                      $LANG['search'][19]."\"></a>&nbsp;&nbsp;&nbsp;&nbsp;";

               if ($_SESSION["glpisearchcount2"][$itemtype]>0) {
                  echo "<input type='hidden' disabled id='delete_search_count2'
                         name='delete_search_count2' value='1'>";
                  echo "<a href='#' onClick=\"document.getElementById('delete_search_count2').disabled=false;
                         document.forms['searchform$itemtype'].submit();\">";
                  echo "<img src=\"".$CFG_GLPI["root_doc"]."/pics/meta_moins.png\" alt='-' title=\"".
                        $LANG['search'][20]."\"></a>&nbsp;&nbsp;&nbsp;&nbsp;";
               }
            }

            $itemtable = getTableForItemType($itemtype);
//            if ($item && $item->maybeDeleted()) {
//               echo "<input type='hidden' id='is_deleted' name='is_deleted' value='".
//                      $p['is_deleted']."'>";
//               echo "<a href='#' onClick = \"toogle('is_deleted','','','');
//                      document.forms['searchform$itemtype'].submit();\">
//                      <img src=\"".$CFG_GLPI["root_doc"]."/pics/showdeleted".
//                       (!$p['is_deleted']?'_no':'').".png\" name='img_deleted'  alt=\"".
//                       (!$p['is_deleted']?$LANG['common'][3]:$LANG['common'][81])."\" title=\"".
//                       (!$p['is_deleted']?$LANG['common'][3]:$LANG['common'][81])."\" ></a>";
//               // Dropdown::showYesNo("is_deleted",$p['is_deleted']);
//               echo '&nbsp;&nbsp;';
//            }
         }



         // Display link item
         if ($i>0) {
            echo "<select name='link[$i]'>";
            echo "<option value = 'AND' ";
            if (is_array($p["link"]) && isset($p["link"][$i]) && $p["link"][$i] == "AND") {
               echo "selected";
            }
            echo ">AND</option>\n";

            echo "<option value='OR' ";
            if (is_array($p["link"]) && isset($p["link"][$i]) && $p["link"][$i] == "OR") {
               echo "selected";
            }
            echo ">OR</option>\n";

            echo "<option value='AND NOT' ";
            if (is_array($p["link"]) && isset($p["link"][$i]) && $p["link"][$i] == "AND NOT") {
               echo "selected";
            }
            echo ">AND NOT</option>\n";

            echo "<option value='OR NOT' ";
            if (is_array($p["link"]) && isset($p["link"][$i]) && $p["link"][$i] == "OR NOT") {
               echo "selected";
            }
            echo ">OR NOT</option>";
            echo "</select>&nbsp;";
         }


         // display select box to define search item
         echo "<select id='Search$itemtype$i' name=\"field[$i]\" size='1'>";
         echo "<option value='view' ";
         if (is_array($p['field']) && isset($p['field'][$i]) && $p['field'][$i] == "view") {
            echo "selected";
         }
         echo ">".$LANG['search'][11]."</option>\n";

         reset($options);
         $first_group = true;
         $selected    = 'view';
         $str_limit   = 28;
         $nb_in_group = 0;
         $group = '';

         foreach ($options as $key => $val) {
            // print groups
            if (!is_array($val)) {
               if (!$first_group) {
                  $group .= "</optgroup>\n";
               } else {
                  $first_group = false;
               }
               if ($nb_in_group) {
                  echo $group;
               }
               $group       = '';
               $nb_in_group = 0;

               $group .= "<optgroup label=\"".utf8_substr($val,0,$str_limit)."\">";
            } else {
               if (!isset($val['nosearch']) || $val['nosearch']==false) {
                  $nb_in_group ++;
                  $group .= "<option title=\"".cleanInputText($val["name"])."\" value='$key'";
                  if (is_array($p['field']) && isset($p['field'][$i]) && $key == $p['field'][$i]) {
                     $group .= "selected";
                     $selected = $key;
                  }
                  $group .= ">". utf8_substr($val["name"], 0, $str_limit) ."</option>\n";
               }
            }
         }
         if (!$first_group) {
            $group .= "</optgroup>\n";
         }
         if ($nb_in_group) {
            echo $group;
         }

         echo "<option value='all' ";
         if (is_array($p['field']) && isset($p['field'][$i]) && $p['field'][$i] == "all") {
            echo "selected";
         }
         echo ">".$LANG['common'][66]."</option>";
         echo "</select>\n";

         echo "</td><td class='left'>";
         echo "<div id='SearchSpan$itemtype$i'>\n";

         $_POST['itemtype']   = $itemtype;
         $_POST['num']        = $i;
         $_POST['field']      = $selected;
         $_POST['searchtype'] = (is_array($p['searchtype'])
                                 && isset($p['searchtype'][$i])?$p['searchtype'][$i]:"" );
         $_POST['value']      = (is_array($p['contains'])
                                 && isset($p['contains'][$i])?stripslashes($p['contains'][$i]):"" );
         include (GLPI_ROOT."/ajax/searchoption.php");
         echo "</div>\n";

         $params = array('field'      => '__VALUE__',
                         'itemtype'   => $itemtype,
                         'num'        => $i,
                         'value'      => $_POST["value"],
                         'searchtype' => $_POST["searchtype"]);
         ajaxUpdateItemOnSelectEvent("Search$itemtype$i", "SearchSpan$itemtype$i",
                                     $CFG_GLPI["root_doc"]."/ajax/searchoption.php", $params, false);

         echo "</td></tr>\n";
      }


      $metanames = array();

      if (is_array($linked) && count($linked)>0) {
         for ($i=0 ; $i<$_SESSION["glpisearchcount2"][$itemtype] ; $i++) {
            echo "<tr><td class='left' colspan='2'>";
            $rand = mt_rand();

            echo "<table width='100%'><tr class='left'><td width='35%'>";
            // Display link item (not for the first item)
            echo "<select name='link2[$i]'>";
            echo "<option value='AND' ";
            if (is_array($p['link2']) && isset($p['link2'][$i]) && $p['link2'][$i] == "AND") {
               echo "selected";
            }
            echo ">AND</option>\n";

            echo "<option value='OR' ";
            if (is_array($p['link2']) && isset($p['link2'][$i]) && $p['link2'][$i] == "OR") {
               echo "selected";
            }
            echo ">OR</option>\n";

            echo "<option value='AND NOT' ";
            if (is_array($p['link2']) && isset($p['link2'][$i]) && $p['link2'][$i] == "AND NOT") {
               echo "selected";
            }
            echo ">AND NOT</option>\n";

            echo "<option value='OR NOT' ";
            if (is_array($p['link2'] )&& isset($p['link2'][$i]) && $p['link2'][$i] == "OR NOT") {
               echo "selected";
            }
            echo ">OR NOT</option>\n";
            echo "</select>&nbsp;";

            // Display select of the linked item type available
            echo "<select name='itemtype2[$i]' id='itemtype2_".$itemtype."_".$i."_$rand'>";
            echo "<option value=''>".DROPDOWN_EMPTY_VALUE."</option>";
            foreach ($linked as $key) {
               if (!isset($metanames[$key])) {
                  $linkitem = new $key();
                  $metanames[$key] = $linkitem->getTypeName();
               }
               echo "<option value='$key'>".utf8_substr($metanames[$key], 0, 20)."</option>\n";
            }
            echo "</select>&nbsp;";

            echo "</td><td>";
            // Ajax script for display search met& item
            echo "<span id='show_".$itemtype."_".$i."_$rand'>&nbsp;</span>\n";

            $params = array('itemtype'    => '__VALUE__',
                            'num'         => $i,
                            'field'       => (is_array($p['field2'])
                                              && isset($p['field2'][$i])?$p['field2'][$i]:""),
                            'value'       => (is_array($p['contains2'])
                                              && isset($p['contains2'][$i])?$p['contains2'][$i]:""),
                            'searchtype2' => (is_array($p['searchtype2'])
                                              && isset($p['searchtype2'][$i])?$p['searchtype2'][$i]:""));

            ajaxUpdateItemOnSelectEvent("itemtype2_".$itemtype."_".$i."_$rand","show_".$itemtype."_".
                                          $i."_$rand",$CFG_GLPI["root_doc"]."/ajax/updateMetaSearch.php",
                                        $params, false);

            if (is_array($p['itemtype2'])
                && isset($p['itemtype2'][$i])
                && !empty($p['itemtype2'][$i])) {

               $params['itemtype'] = $p['itemtype2'][$i];
               ajaxUpdateItem("show_".$itemtype."_".$i."_$rand",
                              $CFG_GLPI["root_doc"]."/ajax/updateMetaSearch.php", $params, false);
               echo "<script type='text/javascript' >";
               echo "window.document.getElementById('itemtype2_".$itemtype."_".$i."_$rand').value='".
                                                    $p['itemtype2'][$i]."';";
               echo "</script>\n";
            }
            echo "</td></tr></table>";

            echo "</td></tr>\n";
         }
      }
      echo "</table>\n";
      echo "</td>\n";

      echo "<td width='150px'>";
      echo "<table width='100%'>";
      // Display sort selection
/*      echo "<tr><td colspan='2'>".$LANG['search'][4];
      echo "&nbsp;<select name='sort' size='1'>";
      reset($options);
      $first_group=true;
      foreach ($options as $key => $val) {
         if (!is_array($val)) {
            if (!$first_group) {
               echo "</optgroup>\n";
            } else {
               $first_group=false;
            }
            echo "<optgroup label=\"$val\">";
         } else {
            echo "<option value='$key'";
            if ($key == $p['sort']) {
               echo " selected";
            }
            echo ">".utf8_substr($val["name"],0,20)."</option>\n";
         }
      }
      if (!$first_group) {
         echo "</optgroup>\n";
      }
      echo "</select> ";
      echo "</td></tr>\n";
*/
      // Display deleted selection

      echo "<tr>";

      // Display submit button
      echo "<td width='80' class='center'>";
      echo "<input type='submit' value=\"".$LANG['buttons'][0]."\" class='submit' >";
      echo "</td><td>";
//      Bookmark::showSaveButton(BOOKMARK_SEARCH,$itemtype);
      echo "<a href='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/componentscatalog_rule.form.php?reset=reset' >";
      echo "&nbsp;&nbsp;<img title=\"".$LANG['buttons'][16]."\" alt=\"".$LANG['buttons'][16]."\" src='".
            $CFG_GLPI["root_doc"]."/pics/reset.png' class='calendrier'></a>";

      echo "</td></tr>";
      
      echo "</table>\n";

      echo "</td></tr>";
      echo "<tr>";
      }
      echo "<td colspan='4' class='center'>";
      echo "<input type='submit' name='addrule' value=\"Add this rule\" class='submit' >";
      echo "</td>";
      echo "</tr>";
      echo "</table>\n";

      // For dropdown
      echo "<input type='hidden' name='itemtype' value='$itemtype'>";

      // Reset to start when submit new search
      echo "<input type='hidden' name='start' value='0'>";
      
      echo "</form>";
   }

   
   
   /*
    * Clone of Search::showList but only to have SQL query
    */
   function constructSQL($itemtype,$params, $items_id_check=0) {
      global $DB, $CFG_GLPI, $LANG;

      // Instanciate an object to access method
      $item = NULL;

      if ($itemtype!='States' && class_exists($itemtype)) {
         $item = new $itemtype();
      }


      // Default values of parameters
      $p['link']        = array();//
      $p['field']       = array();//
      $p['contains']    = array();//
      $p['searchtype']  = array();//
      $p['sort']        = '1'; //
      $p['order']       = 'ASC';//
      $p['start']       = 0;//
      $p['is_deleted']  = 0;
      $p['export_all']  = 0;
      $p['link2']       = '';//
      $p['contains2']   = '';//
      $p['field2']      = '';//
      $p['itemtype2']   = '';
      $p['searchtype2'] = '';

      foreach ($params as $key => $val) {
         $p[$key] = $val;
      }

      if ($p['export_all']) {
         $p['start'] = 0;
      }

      // Manage defautll seachtype value : for bookmark compatibility
      if (count($p['contains'])) {
         foreach ($p['contains'] as $key => $val) {
            if (!isset($p['searchtype'][$key])) {
               $p['searchtype'][$key] = 'contains';
            }
         }
      }
      if (is_array($p['contains2']) && count($p['contains2'])) {
         foreach ($p['contains2'] as $key => $val) {
            if (!isset($p['searchtype2'][$key])) {
               $p['searchtype2'][$key] = 'contains';
            }
         }
      }

      $target = getItemTypeSearchURL($itemtype);

      $limitsearchopt = Search::getCleanedOptions($itemtype);

      if (isset($CFG_GLPI['union_search_type'][$itemtype])) {
         $itemtable = $CFG_GLPI['union_search_type'][$itemtype];
      } else {
         $itemtable = getTableForItemType($itemtype);
      }

      $LIST_LIMIT = $_SESSION['glpilist_limit'];

      // Set display type for export if define
      $output_type = HTML_OUTPUT;
      if (isset($_GET['display_type'])) {
         $output_type = $_GET['display_type'];
         // Limit to 10 element
         if ($_GET['display_type']==GLOBAL_SEARCH) {
            $LIST_LIMIT = GLOBAL_SEARCH_DISPLAY_COUNT;
         }
      }
      // hack for States
      if (isset($CFG_GLPI['union_search_type'][$itemtype])) {
         $entity_restrict = true;
      } else {
         $entity_restrict = $item->isEntityAssign();
      }

      $metanames = array();

      // Get the items to display
//      $toview = Search::addDefaultToView($itemtype);

//      // Add items to display depending of personal prefs
//      $displaypref = DisplayPreference::getForTypeUser($itemtype, getLoginUserID());
//      if (count($displaypref)) {
//         foreach ($displaypref as $val) {
//            array_push($toview,$val);
//         }
//      }
      /* =========== Add for plugin Monitoring ============ */
         $toview = array();
         array_push($toview, 1);
         

      // Add searched items
      if (count($p['field'])>0) {
         foreach ($p['field'] as $key => $val) {
            if (!in_array($val,$toview) && $val!='all' && $val!='view') {
               array_push($toview, $val);
            }
         }
      }

      // Add order item
      if (!in_array($p['sort'],$toview)) {
         array_push($toview, $p['sort']);
      }

//      // Special case for Ticket : put ID in front
//      if ($itemtype=='Ticket') {
//         array_unshift($toview, 2);
//      }

      // Clean toview array
      $toview=array_unique($toview);
      foreach ($toview as $key => $val) {
         if (!isset($limitsearchopt[$val])) {
            unset($toview[$key]);
         }
      }

      $toview_count = count($toview);

      // Construct the request

      //// 1 - SELECT
      // request currentuser for SQL supervision, not displayed
      $SELECT = "SELECT ".Search::addDefaultSelect($itemtype);

      // Add select for all toview item
      foreach ($toview as $key => $val) {
         $SELECT .= Search::addSelect($itemtype, $val, $key, 0);
      }


      //// 2 - FROM AND LEFT JOIN
      // Set reference table
      $FROM = " FROM `$itemtable`";

      // Init already linked tables array in order not to link a table several times
      $already_link_tables = array();
      // Put reference table
      array_push($already_link_tables, $itemtable);

      // Add default join
      $COMMONLEFTJOIN = Search::addDefaultJoin($itemtype, $itemtable, $already_link_tables);
      $FROM .= $COMMONLEFTJOIN;

      $searchopt = array();
      $searchopt[$itemtype] = &Search::getOptions($itemtype);
      // Add all table for toview items
      foreach ($toview as $key => $val) {
         $FROM .= Search::addLeftJoin($itemtype, $itemtable, $already_link_tables,
                                    $searchopt[$itemtype][$val]["table"],
                                    $searchopt[$itemtype][$val]["linkfield"], 0, 0,
                                    $searchopt[$itemtype][$val]["joinparams"]);
      }

      // Search all case :
      if (in_array("all",$p['field'])) {
         foreach ($searchopt[$itemtype] as $key => $val) {
            // Do not search on Group Name
            if (is_array($val)) {
               $FROM .= Search::addLeftJoin($itemtype, $itemtable, $already_link_tables,
                                          $searchopt[$itemtype][$key]["table"],
                                          $searchopt[$itemtype][$key]["linkfield"], 0, 0,
                                          $searchopt[$itemtype][$key]["joinparams"]);
            }
         }
      }


      //// 3 - WHERE

      // default string
      $COMMONWHERE = Search::addDefaultWhere($itemtype);
      $first = empty($COMMONWHERE);

      // Add deleted if item have it
      if ($item && $item->maybeDeleted()) {
         $LINK = " AND " ;
         if ($first) {
            $LINK  = " ";
            $first = false;
         }
         $COMMONWHERE .= $LINK."`$itemtable`.`is_deleted` = '".$p['is_deleted']."' ";
      }

      // Remove template items
      if ($item && $item->maybeTemplate()) {
         $LINK = " AND " ;
         if ($first) {
            $LINK  = " ";
            $first = false;
         }
         $COMMONWHERE .= $LINK."`$itemtable`.`is_template` = '0' ";
      }

      // Add Restrict to current entities
//      if ($entity_restrict) {
//         $LINK = " AND " ;
//         if ($first) {
//            $LINK  = " ";
//            $first = false;
//         }
//
//         if ($itemtype == 'Entity') {
//            $COMMONWHERE .= getEntitiesRestrictRequest($LINK, $itemtable, 'id', '', true);
//
//         } else if (isset($CFG_GLPI["union_search_type"][$itemtype])) {
//            // Will be replace below in Union/Recursivity Hack
//            $COMMONWHERE .= $LINK." ENTITYRESTRICT ";
//         } else {
//            $COMMONWHERE .= getEntitiesRestrictRequest($LINK, $itemtable, '', '',
//                                                       $item->maybeRecursive());
//         }
//      }
      $WHERE  = "";
      $HAVING = "";

      // Add search conditions
      // If there is search items
      if ($_SESSION["glpisearchcount"][$itemtype]>0 && count($p['contains'])>0) {
         for ($key=0 ; $key<$_SESSION["glpisearchcount"][$itemtype] ; $key++) {
            // if real search (strlen >0) and not all and view search
            if (isset($p['contains'][$key]) && strlen($p['contains'][$key])>0) {
               // common search
               if ($p['field'][$key]!="all" && $p['field'][$key]!="view") {
                  $LINK    = " ";
                  $NOT     = 0;
                  $tmplink = "";
                  if (is_array($p['link']) && isset($p['link'][$key])) {
                     if (strstr($p['link'][$key],"NOT")) {
                        $tmplink = " ".str_replace(" NOT","",$p['link'][$key]);
                        $NOT     = 1;
                     } else {
                        $tmplink = " ".$p['link'][$key];
                     }
                  } else {
                     $tmplink = " AND ";
                  }

                  if (isset($searchopt[$itemtype][$p['field'][$key]]["usehaving"])) {
                     // Manage Link if not first item
                     if (!empty($HAVING)) {
                        $LINK = $tmplink;
                     }
                     // Find key
                     $item_num = array_search($p['field'][$key], $toview);
                     $HAVING .= Search::addHaving($LINK, $NOT,$itemtype, $p['field'][$key],
                                                $p['searchtype'][$key], $p['contains'][$key], 0,
                                                $item_num);
                  } else {
                     // Manage Link if not first item
                     if (!empty($WHERE)) {
                        $LINK = $tmplink;
                     }
                     $WHERE .= Search::addWhere($LINK, $NOT, $itemtype, $p['field'][$key],
                                              $p['searchtype'][$key], $p['contains'][$key]);
                  }

               // view and all search
               } else {
                  $LINK       = " OR ";
                  $NOT        = 0;
                  $globallink = " AND ";

                  if (is_array($p['link']) && isset($p['link'][$key])) {
                     switch ($p['link'][$key]) {
                        case "AND" :
                           $LINK       = " OR ";
                           $globallink = " AND ";
                           break;

                        case "AND NOT" :
                           $LINK       = " AND ";
                           $NOT        = 1;
                           $globallink = " AND ";
                           break;

                        case "OR" :
                           $LINK       = " OR ";
                           $globallink = " OR ";
                           break;

                        case "OR NOT" :
                           $LINK       = " AND ";
                           $NOT        = 1;
                           $globallink = " OR ";
                           break;
                     }

                  } else {
                     $tmplink ="  AND ";
                  }

                  // Manage Link if not first item
                  if (!empty($WHERE)) {
                     $WHERE .= $globallink;
                  }
                  $WHERE .= " ( ";
                  $first2 = true;

                  $items = array();

                  if ($p['field'][$key]=="all") {
                     $items = $searchopt[$itemtype];

                  } else { // toview case : populate toview
                     foreach ($toview as $key2 => $val2) {
                        $items[$val2] = $searchopt[$itemtype][$val2];
                     }
                  }

                  foreach ($items as $key2 => $val2) {
                     if (is_array($val2)) {
                        // Add Where clause if not to be done in HAVING CLAUSE
                        if (!isset($val2["usehaving"])) {
                           $tmplink = $LINK;
                           if ($first2) {
                              $tmplink = " ";
                              $first2  = false;
                           }
                           $WHERE .= Search::addWhere($tmplink, $NOT, $itemtype, $key2,
                                                    $p['searchtype'][$key], $p['contains'][$key]);
                        }
                     }
                  }
                  $WHERE .= " ) ";
               }
            }
         }
      }

      //// 4 - ORDER
      $ORDER = " ORDER BY `id` ";
      foreach ($toview as $key => $val) {
         if ($p['sort']==$val) {
            $ORDER = Search::addOrderBy($itemtype, $p['sort'], $p['order'], $key);
         }
      }


      //// 5 - META SEARCH
      // Preprocessing
      if ($_SESSION["glpisearchcount2"][$itemtype]>0 && is_array($p['itemtype2'])) {

         // a - SELECT
         for ($i=0 ; $i<$_SESSION["glpisearchcount2"][$itemtype] ; $i++) {
            if (isset($p['itemtype2'][$i])
                && !empty($p['itemtype2'][$i])
                && isset($p['contains2'][$i])
                && strlen($p['contains2'][$i])>0) {

               $SELECT .= Search::addSelect($p['itemtype2'][$i], $p['field2'][$i], $i, 1,
                                          $p['itemtype2'][$i]);
            }
         }

         // b - ADD LEFT JOIN
         // Already link meta table in order not to linked a table several times
         $already_link_tables2 = array();
         // Link reference tables
         for ($i=0 ; $i<$_SESSION["glpisearchcount2"][$itemtype] ; $i++) {
            if (isset($p['itemtype2'][$i])
                && !empty($p['itemtype2'][$i])
                && isset($p['contains2'][$i])
                && strlen($p['contains2'][$i])>0) {

               if (!in_array(getTableForItemType($p['itemtype2'][$i]), $already_link_tables2)) {
                  $FROM .= Search::addMetaLeftJoin($itemtype, $p['itemtype2'][$i],
                                                 $already_link_tables2,
                                                 (($p['contains2'][$i]=="NULL")
                                                  || (strstr($p['link2'][$i], "NOT"))));
               }
            }
         }
         // Link items tables
         for ($i=0 ; $i<$_SESSION["glpisearchcount2"][$itemtype] ; $i++) {
            if (isset($p['itemtype2'][$i])
                && !empty($p['itemtype2'][$i])
                && isset($p['contains2'][$i])
                && strlen($p['contains2'][$i])>0) {

               if (!isset($searchopt[$p['itemtype2'][$i]])) {
                  $searchopt[$p['itemtype2'][$i]] = &Search::getOptions($p['itemtype2'][$i]);
               }
               if (!in_array($searchopt[$p['itemtype2'][$i]][$p['field2'][$i]]["table"]."_".$p['itemtype2'][$i],
                             $already_link_tables2)) {

                  $FROM .= Search::addLeftJoin($p['itemtype2'][$i],
                                             getTableForItemType($p['itemtype2'][$i]),
                                             $already_link_tables2,
                                             $searchopt[$p['itemtype2'][$i]][$p['field2'][$i]]["table"],
                                             $searchopt[$p['itemtype2'][$i]][$p['field2'][$i]]["linkfield"],
                                             1, $p['itemtype2'][$i],
                                             $searchopt[$p['itemtype2'][$i]][$p['field2'][$i]]["joinparams"]);
               }
            }
         }
      }


      //// 6 - Add item ID
      // Add ID to the select
      if (!empty($itemtable)) {
         $SELECT .= "`$itemtable`.`id` AS id ";
      }


      //// 7 - Manage GROUP BY
      $GROUPBY = "";
      // Meta Search / Search All / Count tickets
      if ($_SESSION["glpisearchcount2"][$itemtype]>0
          || !empty($HAVING)
          || in_array('all',$p['field'])) {

         $GROUPBY = " GROUP BY `$itemtable`.`id`";
      }

      if (empty($GROUPBY)) {
         foreach ($toview as $key2 => $val2) {
            if (!empty($GROUPBY)) {
               break;
            }
            if (isset($searchopt[$itemtype][$val2]["forcegroupby"])) {
               $GROUPBY = " GROUP BY `$itemtable`.`id`";
            }
         }
      }

      // Specific search for others item linked  (META search)
      if (is_array($p['itemtype2'])) {
         for ($key=0 ; $key<$_SESSION["glpisearchcount2"][$itemtype] ; $key++) {
            if (isset($p['itemtype2'][$key])
                && !empty($p['itemtype2'][$key])
                && isset($p['contains2'][$key])
                && strlen($p['contains2'][$key])>0) {

               $LINK = "";

               // For AND NOT statement need to take into account all the group by items
               if (strstr($p['link2'][$key],"AND NOT")
                   || isset($searchopt[$p['itemtype2'][$key]][$p['field2'][$key]]["usehaving"])) {

                  $NOT = 0;
                  if (strstr($p['link2'][$key],"NOT")) {
                     $tmplink = " ".str_replace(" NOT","",$p['link2'][$key]);
                     $NOT     = 1;
                  } else {
                     $tmplink = " ".$p['link2'][$key];
                  }
                  if (!empty($HAVING)) {
                     $LINK = $tmplink;
                  }
                  $HAVING .= Search::addHaving($LINK, $NOT, $p['itemtype2'][$key],
                                             $p['field2'][$key], $p['searchtype2'][$key],
                                             $p['contains2'][$key], 1, $key);
               } else { // Meta Where Search
                  $LINK = " ";
                  $NOT  = 0;
                  // Manage Link if not first item
                  if (is_array($p['link2'])
                      && isset($p['link2'][$key])
                      && strstr($p['link2'][$key],"NOT")) {

                     $tmplink = " ".str_replace(" NOT", "", $p['link2'][$key]);
                     $NOT     = 1;

                  } else if (is_array($p['link2']) && isset($p['link2'][$key])) {
                     $tmplink = " ".$p['link2'][$key];

                  } else {
                     $tmplink = " AND ";
                  }

                  if (!empty($WHERE)) {
                     $LINK = $tmplink;
                  }
                  $WHERE .= Search::addWhere($LINK, $NOT, $p['itemtype2'][$key], $p['field2'][$key],
                                           $p['searchtype2'][$key], $p['contains2'][$key], 1);
               }
            }
         }
      }

      // Use a ReadOnly connection if available and configured to be used
      $DBread = DBConnection::getReadConnection();

      // If no research limit research to display item and compute number of item using simple request
      $nosearch = true;
      for ($i=0 ; $i<$_SESSION["glpisearchcount"][$itemtype] ; $i++) {
         if (isset($p['contains'][$i]) && strlen($p['contains'][$i])>0) {
            $nosearch = false;
         }
      }

      if ($_SESSION["glpisearchcount2"][$itemtype]>0) {
         $nosearch = false;
      }

      $LIMIT   = "";
      $numrows = 0;
      //No search : count number of items using a simple count(ID) request and LIMIT search
      if ($nosearch) {
         $LIMIT = " LIMIT ".$p['start'].", ".$LIST_LIMIT;

         // Force group by for all the type -> need to count only on table ID
         if (!isset($searchopt[$itemtype][1]['forcegroupby'])) {
            $count = "count(*)";
         } else {
            $count = "count(DISTINCT `$itemtable`.`id`)";
         }
         // request currentuser for SQL supervision, not displayed
         $query_num = "SELECT $count
                       FROM `$itemtable`".
                       $COMMONLEFTJOIN;

         $first = true;

         if (!empty($COMMONWHERE)) {
            $LINK = " AND " ;
            if ($first) {
               $LINK  = " WHERE ";
               $first = false;
            }
            $query_num .= $LINK.$COMMONWHERE;
         }
         // Union Search :
         if (isset($CFG_GLPI["union_search_type"][$itemtype])) {
            $tmpquery = $query_num;
            $numrows  = 0;

            foreach ($CFG_GLPI[$CFG_GLPI["union_search_type"][$itemtype]] as $ctype) {
               $ctable = getTableForItemType($ctype);
               $citem  = new $ctype();
               if ($citem->canView()) {
                  // State case
                  if ($itemtype == 'States') {
                     $query_num = str_replace($CFG_GLPI["union_search_type"][$itemtype],
                                              $ctable, $tmpquery);
                     $query_num .= " AND $ctable.`states_id` > '0' ";
                     // Add deleted if item have it
                     if ($citem && $citem->maybeDeleted()) {
                        $query_num .= " AND `$ctable`.`is_deleted` = '0' ";
                     }

                     // Remove template items
                     if ($citem && $citem->maybeTemplate()) {
                        $query_num .= " AND `$ctable`.`is_template` = '0' ";
                     }

                  } else {// Ref table case
                     $reftable = getTableForItemType($itemtype);
                     $replace  = "FROM `$reftable`
                                  INNER JOIN `$ctable`
                                       ON (`$reftable`.`items_id` =`$ctable`.`id`
                                           AND `$reftable`.`itemtype` = '$ctype')";

                     $query_num = str_replace("FROM `".$CFG_GLPI["union_search_type"][$itemtype]."`",
                                              $replace, $tmpquery);
                     $query_num = str_replace($CFG_GLPI["union_search_type"][$itemtype], $ctable,
                                              $query_num);
                  }
                  $query_num = str_replace("ENTITYRESTRICT",
                                           getEntitiesRestrictRequest('', $ctable, '', '',
                                                                      $citem->maybeRecursive()),
                                           $query_num);
                  $result_num = $DBread->query($query_num);
                  $numrows   += $DBread->result($result_num, 0, 0);
               }
            }

         } else {
            $result_num = $DBread->query($query_num);
            $numrows    = $DBread->result($result_num,0,0);
         }
      }

      // If export_all reset LIMIT condition
      if ($p['export_all']) {
         $LIMIT = "";
      }

      if (!empty($WHERE) || !empty($COMMONWHERE)) {
         if (!empty($COMMONWHERE)) {
            $WHERE = ' WHERE '.$COMMONWHERE.(!empty($WHERE)?' AND ( '.$WHERE.' )':'');
         } else {
            $WHERE = ' WHERE '.$WHERE.' ';
         }
         $first = false;
      }

      if (!empty($HAVING)) {
         $HAVING = ' HAVING '.$HAVING;
      }

      /* =========== Add for plugin Monitoring ============ */
      if (($items_id_check > 0)) {
         $WHERE .= " AND `".getTableForItemType($itemtype)."`.`id`='".$items_id_check."' ";
      }

      // Create QUERY
      if (isset($CFG_GLPI["union_search_type"][$itemtype])) {
         $first = true;
         $QUERY = "";
         foreach ($CFG_GLPI[$CFG_GLPI["union_search_type"][$itemtype]] as $ctype) {
            $ctable = getTableForItemType($ctype);
            $citem  = new $ctype();
            if ($citem->canView()) {
               if ($first) {
                  $first = false;
               } else {
                  $QUERY .= " UNION ";
               }
               $tmpquery = "";
               // State case
               if ($itemtype == 'States') {
                  $tmpquery = $SELECT.", '$ctype' AS TYPE ".
                              $FROM.
                              $WHERE;
                  $tmpquery = str_replace($CFG_GLPI["union_search_type"][$itemtype],
                                          $ctable, $tmpquery);
                  $tmpquery .= " AND `$ctable`.`states_id` > '0' ";
                  // Add deleted if item have it
                  if ($citem && $citem->maybeDeleted()) {
                     $tmpquery .= " AND `$ctable`.`is_deleted` = '0' ";
                  }

                  // Remove template items
                  if ($citem && $citem->maybeTemplate()) {
                     $tmpquery .= " AND `$ctable`.`is_template` = '0' ";
                  }

               } else {// Ref table case
                  $reftable = getTableForItemType($itemtype);

                  $tmpquery = $SELECT.", '$ctype' AS TYPE,
                                      `$reftable`.`id` AS refID, "."
                                      `$ctable`.`entities_id` AS ENTITY ".
                              $FROM.
                              $WHERE;
                  $replace = "FROM `$reftable`"."
                              INNER JOIN `$ctable`"."
                                 ON (`$reftable`.`items_id`=`$ctable`.`id`"."
                                     AND `$reftable`.`itemtype` = '$ctype')";
                  $tmpquery = str_replace("FROM `".$CFG_GLPI["union_search_type"][$itemtype]."`",
                                          $replace, $tmpquery);
                  $tmpquery = str_replace($CFG_GLPI["union_search_type"][$itemtype], $ctable,
                                          $tmpquery);
               }
               $tmpquery = str_replace("ENTITYRESTRICT",
                                       getEntitiesRestrictRequest('', $ctable, '', '',
                                                                  $citem->maybeRecursive()),
                                       $tmpquery);

               // SOFTWARE HACK
               if ($ctype == 'Software') {
                  $tmpquery = str_replace("glpi_softwares.serial", "''", $tmpquery);
                  $tmpquery = str_replace("glpi_softwares.otherserial", "''", $tmpquery);
               }
               $QUERY .= $tmpquery;
            }
         }
         if (empty($QUERY)) {
            echo Search::showError($output_type);
            return;
         }
         $QUERY .= str_replace($CFG_GLPI["union_search_type"][$itemtype].".", "", $ORDER) . $LIMIT;
      } else {
         $QUERY = $SELECT.
                  $FROM.
                  $WHERE.
                  $GROUPBY.
                  $HAVING.
                  $ORDER.
                  $LIMIT;
      }

      $DBread->query("SET SESSION group_concat_max_len = 4096;");
      $result = $DBread->query($QUERY);
      /// Check group concat limit : if warning : increase limit
      if ($result2 = $DBread->query('SHOW WARNINGS')) {
         if ($DBread->numrows($result2) > 0) {
            $data = $DBread->fetch_assoc($result2);
            if ($data['Code'] == 1260) {
               $DBread->query("SET SESSION group_concat_max_len = 4194304;");
               $result = $DBread->query($QUERY);
            }
         }
      }

      // Get it from database and DISPLAY
      if ($result) {
         return $result;
      } else {
         return false;
      }      
   }

}

?>