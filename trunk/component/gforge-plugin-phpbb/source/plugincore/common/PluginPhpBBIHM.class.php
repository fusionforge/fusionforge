<?php
/**
 * PhpBB plugin
 *
 * This class is used to display the user interface
 *
 * @author Panneer CANABADY <panneerselvam.canabady@bull.net>
 * @version 1.0
 * @package PluginPhpBB
 */
class PluginPhpBBIHM{

    function display_confirm_form($question){
        global $HTML,$Language;
        $lang_yes = dgettext('gforge-plugin-phpbb','yes');
        $lang_no = dgettext('gforge-plugin-phpbb','no');
        echo <<<END
                   <table
                 style="width: 500px; height: 70px; text-align: left; margin-left: auto; margin-right: auto;"
                 border="0"  cellpadding="0" cellspacing="0">
                <tr>
                <td>
                
END;
echo $HTML->boxTop($question);
        echo <<<END
<form method="post" action="{$_SERVER ['REQUEST_URI']}" name="delete">
<input name="phpbb_post" value="1" type="hidden">
         <table >
          <tbody>
            <tr>
              <td style="width: 250px; font-weight: bold;text-align: center;">
                    <input name="post_yes" value="{$lang_yes}" type="submit">
              </td>
              <td style="width: 250px; font-weight: bold;text-align: center;">
                    <input name="post_non" value="{$lang_no}" type="submit">
              </td>
            </tr>
          </tbody>
        </table> 

END;

        echo $HTML->boxBottom();

        echo <<<END
                 </form>
                </td>
                </tr>
                </table>
               
                
END;

    }

    function display_instance_form($action,$group_id ,$instance_id, $name = '',$url = '',$encoding = ''){
        global $HTML,$Language;

        if($action == 'add'){
            $title = dgettext('gforge-plugin-phpbb','add_new_instance');
        }else if($action == 'edit'){
            $title = dgettext('gforge-plugin-phpbb','edit_instance');
        }else {
            return;
        }


        $lang_name = dgettext('gforge-plugin-phpbb','name');
        $lang_url = dgettext('gforge-plugin-phpbb','url');
        $lang_encoding = dgettext('gforge-plugin-phpbb','encoding');
        $lang_send = dgettext('gforge-plugin-phpbb','send');

        echo $HTML->boxTop($title);
        echo <<<END
<form method="post" action="{$_SERVER ['REQUEST_URI']}" name="instance">
<input name="group_id" value="{$group_id}" type="hidden">
<input name="phpbb_post" value="1" type="hidden">
<input name="post_instance_id" value="{$instance_id}" type="hidden">
 <table 
 style="width: 533px; height: 100px; text-align: left; margin-left: auto; margin-right: auto;"
 border="0" cellpadding="2" cellspacing="2">
          <tbody>
            <tr>
              <td style="width: 150px; font-weight: bold;">{$lang_name} :</td>
              <td style="width: 100px;"><input name="post_name"  value= "{$name}" ></td>
            </tr>

            <tr>
              <td><span style="font-weight: bold;">{$lang_url} :</span> </td>
              <td style="width: 150px;"><input size="40" name="post_url" value= "{$url}" ></td>
            </tr>

            <tr>
              <td><span style="font-weight: bold;">{$lang_encoding} :</span> </td>
              <td><input name="post_encoding"  value= "{$encoding}" ></td>
            </tr>

          </tbody>
        </table> 
  <br>
  <div style="text-align: center;"><input name="send" value="{$lang_send}" type="submit">
  <br>
</form>
END;
echo $HTML->boxBottom();


    }

    function display_instance($data){
        global $HTML,$Language;
        echo <<<END
                
                <table
                 style="width: 533px; height: 100px; text-align: left; margin-left: auto; margin-right: auto;"
                 border="0"  cellpadding="0" cellspacing="0">
                <tr>
                <td>
END;
$lang_url = dgettext('gforge-plugin-phpbb','url');
        $lang_encoding = dgettext('gforge-plugin-phpbb','encoding');
        $lang_send = dgettext('gforge-plugin-phpbb','send');
        $lang_manage_category = dgettext('gforge-plugin-phpbb','manage_category');
        $lang_edit_parameters = dgettext('gforge-plugin-phpbb','edit_parameters');
        $lang_delete_instance = dgettext('gforge-plugin-phpbb','delete_instance');
        echo $HTML->boxTop($data['name']);

        echo <<<END
                <table
                 style="height: 60px; text-align: left; margin-left: auto; margin-right: auto;"
                 border="0" cellpadding="0" cellspacing="0">
                 <tbody>
                    <tr>
                      <td colspan="2" rowspan="1"><span style="font-weight: bold;">{$lang_url} : </span>&nbsp;{$data['url']}</td>
                    </tr>
                    <tr>
                      <td  colspan="2" rowspan="1"><span style="font-weight: bold;">{$lang_encoding} : </span>{$data['encoding']}</td>
                    </tr>
                    <tr>
                      <td colspan="2" rowspan="1">
                      <table style="text-align: left; margin-left: auto; margin-right: auto; width: 521px; height: 12px;" border="0" cellpadding="0" cellspacing="0">
                        <tbody>
                          <tr>
                            <td style="text-align: center;" colspan="1" rowspan="1">
                                <a href="managecategory.php?group_id={$data['group_id']}&instance_id={$data['instance_id']}">[{$lang_manage_category}]</a></td>
                            <td style="text-align: center;" colspan="1" rowspan="1">
                                <a href="instance.php?action=edit&group_id={$data['group_id']}&instance_id={$data['instance_id']}" >[{$lang_edit_parameters}]</a></td>
                            <td style="text-align: center;" colspan="1" rowspan="1">
                                <a href="instance.php?action=delete&group_id={$data['group_id']}&instance_id={$data['instance_id']}">[{$lang_delete_instance}]</a></td>
                          </tr>
                        </tbody>
                      </table>
                      </td>
                
                    </tr>
                
                  </tbody>
                </table>  
END;
        
        echo $HTML->boxBottom();

        echo <<<END
                </td>
                </tr>
                </table>
                
                
END;
    }

    function display_bookmarks($arr_bookmarks){
        global $HTML,$Language;
        $lang_bookmarks = dgettext('gforge-plugin-phpbb','bookmarks');
        $lang_subject = dgettext('gforge-plugin-phpbb','subject');
        $lang_last_update = dgettext('gforge-plugin-phpbb','last_update');
        $lang_bookmarks = dgettext('gforge-plugin-phpbb','bookmarks');
        $lang_not_read = dgettext('gforge-plugin-phpbb','not_read');
        $lang_no_bookmarks = dgettext('gforge-plugin-phpbb','no_bookmarks');

        echo $HTML->boxMiddle($lang_bookmarks,false,false);

        if(count($arr_bookmarks) == 1 && isset($arr_bookmarks[0]['MESSAGE']) && !empty($arr_bookmarks[0]['MESSAGE'])){

            echo '<b>'.$arr_bookmarks[0]['MESSAGE'].'</b>';
            
        }else if(count($arr_bookmarks) > 0){
            echo <<<END
            <table style="text-align: left;width: 100%; " border="0"
             cellpadding="2" cellspacing="0">
              <tbody>
                <tr  >
                  <td style="width: 50%;" ><span class="titlebar">{$lang_subject}</span></td>
                  <td style="width: 50%;" ><span class="titlebar">{$lang_last_update}</span></td>
                </tr>
END;
$i = 0;
            foreach($arr_bookmarks as $book){
                // $style = $HTML->boxGetAltRowStyle($i);
                $style = '';
                $url= $book['URL'];
                $last_url = $book['LAST_URL'];

                if(isset($book['NAME']) && $book['READ'] == 'false'){
                    echo <<<END
                <tr  {$style} >
                  <td><a href="{$url}"><b>{$book['NAME']} ({$lang_not_read})</b></a></td>
                  <td><a href="{$last_url}"><b>{$book['DATE']}</b></a></td>
                </tr>
END;
                }else{
                    echo <<<END
                <tr  {$style} >
                  <td><a href="{$url}">{$book['NAME']}</a></td>
                  <td><a href="{$last_url}">{$book['DATE']}</a></td>
                </tr>
END;
                }
                $i++;
            }
            echo <<<END
                <tr>
                  <td></td>
                  <td></td>
                </tr>
              </tbody>
            </table>
END;
        }else{
            echo '<b>'.$lang_no_bookmarks.'</b>';
        }

    }

    function display_subcat($group_id,$instance_id,$arr_sub_cat){
        global $HTML,$Language;
        $g =& group_get_object($group_id);

        $lang_list_subcat_of = dgettext('gforge-plugin-phpbb','list_subcat_of');
        $lang_rename = dgettext('gforge-plugin-phpbb','rename');
        $lang_delete = dgettext('gforge-plugin-phpbb','delete');
        $lang_apply_rule = dgettext('gforge-plugin-phpbb','apply_rule');

        echo $HTML->boxTop($lang_list_subcat_of.' : <b>'.$g->getPublicName().'</b>');
        echo<<<END
            <table style="text-align: left; width: 100%;" border="0"
                cellpadding="2" cellspacing="0">
              <tbody>
END;

        foreach($arr_sub_cat as $sub_cat){
            $catName = $sub_cat['NAME'];
            $catID  = $sub_cat['ID'];

            echo<<<END
                <tr>
                  <td style="width: 50%;" colspan="1" rowspan="1">{$catName}</td>
                  <td style="text-align: center;">
                    <a href="managecategory.php?action=rename&cat_id={$catID}&cat_name={$catName}&group_id={$group_id}&instance_id={$instance_id}">[{$lang_rename}]</a></td>
                  <td style="text-align: center;">
                    <a href="managecategory.php?action=delete&cat_id={$catID}&cat_name={$catName}&group_id={$group_id}&instance_id={$instance_id}">[{$lang_delete}]</a>
                  </td>
                  <td style="text-align: center;"><a href="applyrule.php?cat_id={$catID}&group_id={$group_id}&instance_id={$instance_id}">[{$lang_apply_rule}]</a></td>
                </tr>

END;


        }
         
        echo<<<END
              </tbody>
            </table>

END;

        echo $HTML->boxBottom();
    }

    function display_subcat_form($action,$group_id ,$instance_id,$parent_id, $name = ''){
        global $HTML,$Language;
        $lang_add_category = dgettext('gforge-plugin-phpbb','add_category');
        $lang_rename_category = dgettext('gforge-plugin-phpbb','rename_category');
        $lang_send = dgettext('gforge-plugin-phpbb','send');
        $lang_name = dgettext('gforge-plugin-phpbb','name');

        if($action == 'add'){
            $title = $lang_add_category;
        }else if($action == 'edit'){
            $title = $lang_rename_category;
        }else {
            return;
        }

        echo $HTML->boxTop($title);
        echo <<<END
            <form method="post" action="{$_SERVER ['REQUEST_URI']}" name="subcat">
            <input name="group_id" value="{$group_id}" type="hidden">
            <input name="phpbb_post" value="1" type="hidden">
            <input name="instance_id" value="{$instance_id}" type="hidden">
            <input name="parent_id" value="{$parent_id}" type="hidden">
             <table 
             style="width: 333px; height: 50px; text-align: left; margin-left: auto; margin-right: auto;"
             border="0" cellpadding="2" cellspacing="2">
                      <tbody>
                        <tr>
                          <td style="width: 150px; font-weight: bold;">{$lang_name} :</td>
                          <td style="width: 100px;"><input name="post_name"  value= "{$name}" ></td>
                        </tr>
                      </tbody>
                    </table> 
              <br>
              <div style="text-align: center;">
                <input name="send" value="{$lang_send}" type="submit">
              </div>
              <br>
            </form>
END;
echo $HTML->boxBottom();
    }

    function build_select($id, $arr,$cuurent_rule){
        global $HTML,$Language;
        $lang_select_rule = dgettext('gforge-plugin-phpbb','select_rule');

        $select = "<select name=\"post_roles[{$id}]\">";
        $select .= "<option value =\"0\" >{$lang_select_rule}</option>";

        foreach($arr as $id => $value){
            $lang_rule = dgettext('gforge-plugin-phpbb',trim($value));
            $lang_rule = trim($lang_rule);
            $lang_rule = (empty($lang_rule))?$value:$lang_rule;
            
            $selected = ($cuurent_rule == $id)?'selected="selected"':'';
            $select .= "<option value =\"{$id}\"  {$selected} >{$lang_rule}</option>";
        }

        $select .= "</select>";
        return $select;
    }

    function display_rules_affect($group_id,$instance_id,$cat_id,$array_roles_id,$array_rule,$rules){
        global $HTML,$Language;
        $g =& group_get_object($group_id);
        $lang_apply_rule_role = dgettext('gforge-plugin-phpbb','apply_rule_role');
        $lang_send = dgettext('gforge-plugin-phpbb','send');
        echo $HTML->boxTop($lang_apply_rule_role);
        echo<<<END
            <form method="post" action="{$_SERVER ['REQUEST_URI']}" name="rules_affect">
            <input name="group_id" value="{$group_id}" type="hidden">
            <input name="phpbb_post" value="1" type="hidden">
            <input name="instance_id" value="{$instance_id}" type="hidden">
            <input name="cat_id" value="{$cat_id}" type="hidden">
            <table style="text-align: left; width: 333; margin-left: auto; margin-right: auto;" border="0" cellpadding="2" cellspacing="0">
              <tbody>
END;

        foreach($array_roles_id as $key => $roles_id){
            //$roles_name = $array_roles_name[$key];
            
            $r = new Role($g,$roles_id);
            $roles_name = $r->getName();
            $cuurent_rule = $array_rule[$key];
            
            $select = PluginPhpBBIHM::build_select($roles_id,$rules,$cuurent_rule);
            echo<<<END
                <tr>
                  <td style="width: 50%;" colspan="1" rowspan="1">{$roles_name}</td>
                  <td style="text-align: center;">{$select}</td>
                </tr>

END;
        }
         
        echo<<<END
              </tbody>
            </table>
              <br>
              <div style="text-align: center;">
                <input name="send" value="{$lang_send}" type="submit">
              </div>
              <br>
            </form>
END;

        echo $HTML->boxBottom();
    }
}


?>
