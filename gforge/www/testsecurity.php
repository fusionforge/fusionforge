<?php

require_once('pre.php');

$HTML->header(array('title'=>'Security Test'));

$group_id=1;

$grp =& group_get_object($group_id);
$perm =& $grp->getPermission( session_get_user() );

echo '<BR>Is admin: '.$perm->isAdmin();
echo '<BR>Is DocEditor: '.$perm->isDocEditor();
echo '<BR>Is ForumAdmin: '.$perm->isForumAdmin();
echo '<BR>Is PMadmin: '.$perm->isPMAdmin();
echo '<BR>Is ArtifactAdmin: '.$perm->isArtifactAdmin();
echo '<BR>Is release: '.$perm->isReleaseTechnician();

echo '<BR>user_ismember(): '.user_ismember($group_id);
echo '<BR>user_ismember(A): '.user_ismember($group_id,'A');
echo '<BR>user_ismember(B1): '.user_ismember($group_id,'B1');
echo '<BR>user_ismember(B2): '.user_ismember($group_id,'B2');
echo '<BR>user_ismember(S1): '.user_ismember($group_id,'S1');
echo '<BR>user_ismember(S2): '.user_ismember($group_id,'S2');
echo '<BR>user_ismember(C1): '.user_ismember($group_id,'C1');
echo '<BR>user_ismember(C2): '.user_ismember($group_id,'C2');
echo '<BR>user_ismember(P1): '.user_ismember($group_id,'P1');
echo '<BR>user_ismember(P2): '.user_ismember($group_id,'P2');
echo '<BR>user_ismember(F2): '.user_ismember($group_id,'F2');
echo '<BR>user_ismember(D1): '.user_ismember($group_id,'D1');


echo '<P>Group2<P>';

$group_id=2;

$grp =& group_get_object($group_id);
$perm =& $grp->getPermission( session_get_user() );

echo '<BR>Is admin: '.$perm->isAdmin();
echo '<BR>Is DocEditor: '.$perm->isDocEditor();
echo '<BR>Is ForumAdmin: '.$perm->isForumAdmin();
echo '<BR>Is PMadmin: '.$perm->isPMAdmin();
echo '<BR>Is ArtifactAdmin: '.$perm->isArtifactAdmin();
echo '<BR>Is release: '.$perm->isReleaseTechnician();

echo '<BR>user_ismember(): '.user_ismember($group_id);
echo '<BR>user_ismember(A): '.user_ismember($group_id,'A');
echo '<BR>user_ismember(B1): '.user_ismember($group_id,'B1');
echo '<BR>user_ismember(B2): '.user_ismember($group_id,'B2');
echo '<BR>user_ismember(S1): '.user_ismember($group_id,'S1');
echo '<BR>user_ismember(S2): '.user_ismember($group_id,'S2');
echo '<BR>user_ismember(C1): '.user_ismember($group_id,'C1');
echo '<BR>user_ismember(C2): '.user_ismember($group_id,'C2');
echo '<BR>user_ismember(P1): '.user_ismember($group_id,'P1');
echo '<BR>user_ismember(P2): '.user_ismember($group_id,'P2');
echo '<BR>user_ismember(F2): '.user_ismember($group_id,'F2');
echo '<BR>user_ismember(D1): '.user_ismember($group_id,'D1');

$HTML->footer(array());

?>
