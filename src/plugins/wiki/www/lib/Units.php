<?php // $Id: Units.php 7964 2011-03-05 17:05:30Z vargenau $
/**
 *
 * Interface to man units(1), /usr/share/units.dat
 *
 * $ units "372.0 mi2"
 *         Definition: 9.6347558e+08 m^2
 * $ units "372.0 mi2" m^2
 *         Definition: 9.6347558e+08 m^2
 *
 * Called by:
 *    CachedMarkup.php: Cached_SemanticLink::_expandurl()
 *    SemanticWeb.php: class SemanticAttributeSearchQuery
 *
 * Windows requires the cygwin /usr/bin/units.
 * All successfully parsed unit definitions are stored in the wikidb,
 * so that subsequent expansions will not require /usr/bin/units be called again.
 * So far even on windows (cygwin) the process is fast enough.
 *
 * TODO: understand dates and maybe times
 *   YYYY-MM-DD, "CW"ww/yy (CalendarWeek)
 */

class Units {
    function Units ($UNITSFILE = false) {
        if (DISABLE_UNITS)
            $this->errcode = 1;
        elseif (defined("UNITS_EXE")) // ignore dynamic check
        $this->errcode = 0;
    else
            exec("units m2", $o, $this->errcode);
    }

    /**
     * $this->_attribute_base = $units->Definition($this->_attribute);
     */
    function Definition ($query) {
    static $Definitions = array();
    if (isset($Definitions[$query])) return $Definitions[$query];
    if ($this->errcode)
            return $query;
    $query = preg_replace("/,/","", $query);
    if ($query == '' or $query == '*')
        return ($Definitions[$query] = '');
    // detect date values, currently only ISO: YYYY-MM-DD or YY-MM-DD
    if (preg_match("/^(\d{2,4})-(\d{1,2})-(\d{1,2})$/",$query, $m)) {
        $date = mktime(0,0,0,$m[2],$m[3],$m[1]);
        return ($Definitions[$query] = "$date date");
    }
    if (preg_match("/^(\d{2,4})-(\d{1,2})-(\d{1,2}) (\d{1,2}):(\d{2}):?(\d{2})?$/",$query, $m)) {
        $date = mktime($m[4],$m[5],@$m[6],$m[2],$m[3],$m[1]);
        return ($Definitions[$query] = "$date date");
    }
    $def = $this->_cmd("\"$query\"");
    if (preg_match("/Definition: (.+)$/",$def,$m))
        return ($Definitions[$query] = $m[1]);
    else {
        trigger_error("units: ". $def, E_USER_WARNING);
        return '';
    }
    }

    /**
     * We must ensure that the same baseunits are matched against.
     * We cannot compare m^2 to m or ''
     * $val_base = $this->_units->basevalue($value); // SemanticAttributeSearchQuery
     */
    function basevalue($query, $def = false) {
    if (!$def) $def = $this->Definition($query);
    if ($def) {
        if (is_numeric($def)) // e.g. "1 million"
            return $def;
        if (preg_match("/^([-0-9].*) \w.*$/",$def,$m))
        return $m[1];
    }
    return '';
    }

    /**
     * $this->_unit = $units->baseunit($this->_attribute);  // SemanticAttributeSearchQuery
     * and Cached_SemanticLink::_expandurl()
     */
    function baseunit($query, $def  = false) {
    if (!$def) $def = $this->Definition($query);
    if ($def) {
        if (preg_match("/ (.+)$/",$def,$m))
        return $m[1];
    }
    return '';
    }

    function _cmd($args) {
    if ($this->errcode) return $args;
    if (defined("UNITS_EXE")) {
        $s = UNITS_EXE ." $args";
        $result = `$s`;
    }
    else
        $result = `units $args`;
    return trim($result);
    }
}
