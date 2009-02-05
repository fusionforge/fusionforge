<?php


    function login_novaforge(&$username, &$password){
    global $db, $config;

    $sql = 'SELECT user_id, username, user_password
        FROM ' . USERS_TABLE . "
        WHERE username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'";
    $result = $db->sql_query($sql);
    $row = $db->sql_fetchrow($result);
    $db->sql_freeresult($result);

    if (!$row)
    {
        return array(
            'status'    => LOGIN_ERROR_USERNAME,
            'error_msg' => 'LOGIN_ERROR_USERNAME',
            'user_row'  => array('user_id' => ANONYMOUS),
        );
    }else {
            return array(
            'status'        => LOGIN_SUCCESS,
            'error_msg'     => false,
            'user_row'      => $row,
        );
        }
    }
?>
