<?php 
// rcs_id('$Id: ArchiveCleaner.php 7417 2010-05-19 12:57:42Z vargenau $');
/* Copyright (C) 2002 Geoffrey T. Dairiki <dairiki@dairiki.org>
 *
 * This file is part of PhpWiki.
 * 
 * PhpWiki is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * PhpWiki is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

class ArchiveCleaner
{
    function ArchiveCleaner ($expire_params) {
        $this->expire_params = $expire_params;
    }
    
    function isMergeable($revision) {
        if ( ! $revision->get('is_minor_edit') )
            return false;

        $page = $revision->getPage();
        $author_id = $revision->get('author_id');

        $previous = $page->getRevisionBefore($revision, false);

        return !empty($author_id)
            && $author_id == $previous->get('author_id');
    }

    function cleanDatabase($dbi) {
        $iter = $dbi->getAllPages();
        while ($page = $iter->next())
            $this->cleanPageRevisions($page);
    }
        
    function cleanPageRevisions($page) {
        $INFINITY = 0x7fffffff;

        $expire = &$this->expire_params;
        foreach (array('major', 'minor', 'author') as $class)
            $counter[$class] = new ArchiveCleaner_Counter($expire[$class]);
        // shortcut to keep all    
        if (($counter['minor']->min_keep == $INFINITY) 
            and ($counter['major']->min_keep == $INFINITY))
            return;

        $authors_seen = array();
        
        $current = $page->getCurrentRevision(false);

        for ( $revision = $page->getRevisionBefore($current,false);
              $revision->getVersion() > 0;
              $revision = $page->getRevisionBefore($revision,false) ) {

            if ($revision->get('is_minor_edit'))
                $keep = $counter['minor']->keep($revision);
            else
                $keep = $counter['major']->keep($revision);

            if ($this->isMergeable($revision)) {
                if (!$keep) {
                    $page->mergeRevision($revision);
                }
            }
            else {
                $author_id = $revision->get('author_id');
                if (empty($authors_seen[$author_id])) {
                    if ($counter['author']->keep($revision))
                        $keep = true;
                    $authors_seen[$author_id] = true;
                }
                if (!$keep) {
                    $page->deleteRevision($revision);
                }
            }
        }
    }
}

/**
 * @access private
 */
class ArchiveCleaner_Counter
{
    function ArchiveCleaner_Counter($params) {

        if (!empty($params))
            extract($params);
        $INFINITY = 0x7fffffff;

        $this->max_keep = isset($max_keep) ? $max_keep : $INFINITY;

        $this->min_age  = isset($min_age)  ? $min_age  : 0;
        $this->min_keep = isset($min_keep) ? $min_keep : 0;

        $this->max_age  = isset($max_age)  ? $max_age  : $INFINITY;
        $this->keep     = isset($keep)     ? $keep     : $INFINITY;

        if ($this->keep > $this->max_keep)
            $this->keep = $this->max_keep;
        if ($this->min_keep == $INFINITY) { // shortcut to keep all
            $this->max_keep = $this->keep = $this->min_age = $this->max_age = $INFINITY;
        }
        if ($this->min_keep > $this->keep)
            $this->min_keep = $this->keep;

        if ($this->min_age > $this->max_age)
            $this->min_age = $this->max_age;
            
        $this->now = time();
        $this->count = 0;
        $this->previous_supplanted = false;
        
    }

    function computeAge($revision) {
        $supplanted = $revision->get('_supplanted');

        if (!$supplanted) {
            // Every revision but the most recent should have a supplanted time.
            // However, if it doesn't...
            trigger_error(sprintf("Warning: Page '%s', version '%d' has no '_supplanted' timestamp",
                                  $revision->getPageName(),
                                  $revision->getVersion()),
                          E_USER_NOTICE);
            // Assuming revisions are chronologically ordered, the previous
            // supplanted time is a good value to use...
            if ($this->previous_supplanted > 0)
                $supplanted = $this->previous_supplanted;
            else {
                // no supplanted timestamp.
                // don't delete this revision based on age.
                return 0;
            }
        }

        $this->previous_supplanted = $supplanted;
        return ($this->now - $supplanted) / (24 * 3600);
    }
        
    function keep($revision) {
    	$INFINITY = 0x7fffffff;
    	if ($this->min_keep == $INFINITY)
    	    return true;
        $count = ++$this->count;
        $age = $this->computeAge($revision);
        
        if ($count > $this->max_keep)
            return false;
        if ($age <= $this->min_age || $count <= $this->min_keep)
            return true;
        return $age <= $this->max_age && $count <= $this->keep;
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:   
?>
