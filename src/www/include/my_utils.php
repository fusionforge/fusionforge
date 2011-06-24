<?php
//
// Codendi
// Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
// http://www.codendi.com
//
// 
//
//      Originally written by Laurent Julliard 2001, 2002, Codendi Team, Xerox
//


/*
  Function that generates hide/show urls to expand/collapse
  sections of the personal page

Input:
  $svc : service name to hide/show (sr, bug, pm...)
  $db_item_id : the item (group, forum, task sub-project,...) from the
     database that we are curently processing and about to display
  $item_id : the item_id as given in the URL and on which the show/hide switch
     is going to apply.
  $hide = hide param as given in the script URL (-1 means no param was given)

Output:
  $hide_url: URL to use in the page to switch from hide to show or vice versa
  $count_diff: difference between the number of items in the list between now and
     the previous last time the section was open (can be negative if items were removed)
  $hide_flag: true if the section must be hidden, false otherwise

*/
function my_hide_url ($svc, $db_item_id, $item_id, $count, $hide) {

    global $Language;

    $pref_name = 'my_hide_'.$svc.$db_item_id;
    $old_hide = $old_count = $old_pref_value = UserManager::instance()->getCurrentUser()->getPreference($pref_name);
    if ($old_pref_value) {
        list($old_hide,$old_count) = explode('|', $old_pref_value);
    }

    // Make sure they are both 0 if never set before
    if ($old_count == false) { $old_count = 0; }
    if ($old_hide == false) { $old_hide = 0; }

    if ($item_id == $db_item_id) {
                if (isset($hide)) {
                    $pref_value = "$hide|$count";
                } else {
                    $pref_value = "$old_hide|$count";
                    $hide = $old_hide;
                }
    } else {
                if ($old_hide) {
                    // if items are hidden keep the old count and keep pref as is
                    $pref_value = $old_pref_value;
                } else {
                    // only update the item count if the items are visible
                    // if they are hidden keep reporting the old count
                    $pref_value = "$old_hide|$count";
                }
                $hide = $old_hide;
    }

    // Update pref value if needed
    if ($old_pref_value != $pref_value) {
        UserManager::instance()->getCurrentUser()->setPreference($pref_name, $pref_value);
    }

    if ($hide) {
                $hide_url= '<a href="?hide_'.$svc.'=0&amp;hide_item_id='.$db_item_id.'"><img src="../images/pointer_right.png"  border="0" title="'._('Expand').'" alt="'._('Expand').'" /></a> ';
                $hide_now = true;
    } else {
                $hide_url= '<a href="?hide_'.$svc.'=1&amp;hide_item_id='.$db_item_id.'"><img src="../images/pointer_down.png"   border="0" title="'._('Collapse').'" alt="'._('Collapse').'" /></a> ';
                $hide_now = false;
    }

    return array($hide_now, $count-$old_count, $hide_url);
}

function my_hide($svc, $db_item_id, $item_id, $hide) {
    $pref_name = 'my_hide_'.$svc.$db_item_id;
    $old_pref_value = UserManager::instance()->getCurrentUser()->getPreference($pref_name);
    list($old_hide,$old_count) = explode('|', $old_pref_value);

    // Make sure they are both 0 if never set before
    if ($old_hide == false) { $old_hide = 0; }

    if ($item_id == $db_item_id) {
                if (!isset($hide)) {
                    $hide = $old_hide;
                }
    } else {
                $hide = $old_hide;
    }
    return $hide;
}

function my_format_as_flag($assigned_to, $submitted_by, $multi_assigned_to=null) {
    $AS_flag = '';
    if ($assigned_to == user_getid()) {
        $AS_flag = 'A';
    } else if ($multi_assigned_to) {
     // For multiple assigned to
       for ($i=0; $i<count($multi_assigned_to); $i++) {
            if ($multi_assigned_to[$i]==user_getid()) {
                $AS_flag = 'A';
            }
        }
    }
    if ($submitted_by == user_getid()) {
        $AS_flag .= 'S';
    }
    if ($AS_flag) { $AS_flag = '[<b>'.$AS_flag.'</b>]'; }

    return $AS_flag;
}

/* second case */
function my_format_as_flag2($assignee, $submitter) {
    $AS_flag = '';
    if ($assignee) $AS_flag = 'A';

    if ($submitter) $AS_flag .= 'S';

    if ($AS_flag != '') $AS_flag = '[<b>'.$AS_flag.'</b>]';

    return $AS_flag;
}

function my_item_count($total, $new) {
    global $Language;
    return '['.$total.($new ? ", <b>".vsprintf(_('%s new items'), array($new))."</b>]" : ']');
}

?>
