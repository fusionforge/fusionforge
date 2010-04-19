<?php
/**
 * MailingList Search Engine for GForge
 *
 * Copyright 2006 (c) Alain Peyrat
 *
 * @version $Id: NewsHtmlSearchRenderer.class,v 1.1 2004/10/16 16:36:31 gsmet Exp $
 */
global $gfwww,$gfcommon; 
require_once 'preplugins.php';
require_once $gfwww.'search/include/renderers/HtmlGroupSearchRenderer.class.php';
require_once(dirname(__FILE__).'/../include/ForumML_HTMLPurifier.class.php');
require_once(dirname(__FILE__).'/../include/ForumML_MessageDao.class.php');
require_once 'ForumMLSearchQuery.class.php';

class ForumMLHtmlSearchRenderer extends HtmlGroupSearchRenderer {

        var $groupId;
        /**
         * Constructor
         *
         * @param string $words words we are searching for
         * @param int $offset offset
         * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
         * @param int $groupId group id
         * @param array $sections array of all sections to search in (array of strings)
         *
         */
        function ForumMLHtmlSearchRenderer($words, $offset, $isExact, $groupId) {
                $this->groupId = $groupId;

                $searchQuery = new ForumMLSearchQuery($words, $offset, $isExact, $groupId);

                //init the searchrendererr
                $this->HtmlGroupSearchRenderer(SEARCH__TYPE_IS_LIST, $words, $isExact, 
                                $searchQuery, $groupId, 'list');

               // $this->tableHeaders = array(_('Thread'),_('Submitted on'), _('Author'));
                


        }

        /**
         * getRows - get the html output for result rows
         *
         * @return string html output
         */
        function getRows() {
                $plugin_manager =& PluginManager::instance();
                $p =& $plugin_manager->getPluginByName('forumml');
                $rowsCount = $this->searchQuery->getRowsCount();
                $result =& $this->searchQuery->getResult();
                $dateFormat = _('Y-m-d H:i');

                $group = group_get_object($this->groupId);
                $group_name = $group->getUnixName();

                $data = unserialize(db_result($result, 0, 'versiondata'));

                $return = "<table width='100%'>
                        <tr>
                        <th class=forumml>".
                        _('Thread')."
                        </th>
                        <th class=forumml>".
                        _('Submitted on')."
                        </th>
                        <th class=forumml>".
                        _('Author')."
                        </th>
                        </tr>";
                $idx=0;
                while ($rows = db_fetch_array($result)) {
                        $idx++;
                        if ($idx % 2 == 0) {
                                $class="boxitemalt bgcolor-white";
                        } else {
                                $class="boxitem bgcolor-grey";
                        }
                        $subject=$rows['subject'];

                        $res2 = $this->getForumMLDao()->getHeaderValue($rows['id_message'],array(2,3));
                        $k = 1;
                        while ($rows2 =$res2->getRow()) {
                                $header[$k] = $rows2['value'];
                                $k++;
                        }
                        $from = mb_decode_mimeheader($header[1]);

                        // Replace '<' by '&lt;' and '>' by '&gt;'. Otherwise the email adress won't be displayed 
                        // because it will be considered as an xhtml tag.
                        $from = preg_replace('/\</', '&lt;', $from);
                        $from = preg_replace('/\>/', '&gt;', $from);
                        $date = date("Y-m-d H:i",strtotime($header[2]));
                        // purify message subject (CODENDI_PURIFIER_FORUMML level)
                        $hp =& ForumML_HTMLPurifier::instance();
                        $subject = $hp->purify($subject,CODENDI_PURIFIER_FORUMML);

                        // display the resulting threads in rows 
                        $return .= "<tr class='".$class."'>
                                <td class='subject'>
                                &nbsp;<img src='".$p->getThemePath()."/images/ic/comment.png'/>
                                <a href='/plugins/forumml/message.php?group_id=".$this->groupId."&topic=".$rows['id_message']."&list=".$rows['id_list']."'><b>".$subject."</b></a>                                            
                                </td>
                                <td>                                            
                                <font class='info'>".$date."</font>
                                </td>
                                <td>
                                <font class='info'>".$from."</font>
                                </td>
                                </tr>";
                }
                $return .='</table>';
                return $return;
        }

        function getForumMLDao() {
                return new ForumML_MessageDao(CodendiDataAccess::instance());
        }
}

?>
