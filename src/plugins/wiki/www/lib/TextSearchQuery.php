<?php // $Id: TextSearchQuery.php 7964 2011-03-05 17:05:30Z vargenau $
/**
 * A text search query, converting queries to PCRE and SQL matchers.
 *
 * This represents an enhanced "Google-like" text search query:
 * <dl>
 * <dt> default: case-insensitive glob-style search with special operators OR AND NOT -
 * <dt> wiki -test
 *   <dd> Match strings containing the substring 'wiki', and NOT containing the
 *        substring 'test'.
 * <dt> wiki word or page
 *   <dd> Match strings containing the substring 'wiki' AND either the substring
 *        'word' OR the substring 'page'.
 * <dt> auto-detect regex hints, glob-style or regex-style, and converts them
 *      to PCRE and SQL matchers:
 *   <dd> "^word$" => EXACT(phrase)
 *   <dd> "^word"  => STARTS_WITH(phrase)
 *   <dd> "word$"  => ENDS_WITH(phrase)
 *   <dd> "^word" ... => STARTS_WITH(word)
 *   <dd> "word$" ... => ENDS_WITH(word)
 *   <dd> "word*"  => STARTS_WITH(word)
 *   <dd> "*word"  => ENDS_WITH(word)
 *   <dd> "/^word.* /" => REGEX(^word.*)
 *   <dd> "word*word" => REGEX(word.*word)
 * </dl>
 *
 * The full query syntax, in order of precedence, is roughly:
 *
 * The unary 'NOT' or '-' operator (they are equivalent) negates the
 * following search clause.
 *
 * Search clauses may be joined with the (left-associative) binary operators
 * 'AND' and 'OR'. (case-insensitive)
 *
 * Two adjoining search clauses are joined with an implicit 'AND'.  This has
 * lower precedence than either an explicit 'AND' or 'OR', so "a b OR c"
 * parses as "a AND ( b OR c )", while "a AND b OR c" parses as
 * "( a AND b ) OR c" (due to the left-associativity of 'AND' and 'OR'.)
 *
 * Search clauses can be grouped with parentheses.
 *
 * Phrases (or other things which don't look like words) can be forced to
 * be interpreted as words by quoting them, either with single (') or double (")
 * quotes.  If you wan't to include the quote character within a quoted string,
 * double-up on the quote character: 'I''m hungry' is equivalent to
 * "I'm hungry".
 *
 * Force regex on "re:word" => posix-style, "/word/" => pcre-style
 * or use regex='glob' to use file wildcard-like matching. (not yet)
 *
 * The parsed tree is then converted to the needed PCRE (highlight,
 * simple backends) or SQL functions.
 *
 * @author: Jeff Dairiki
 * @author: Reini Urban (case and regex detection, enhanced sql callbacks)
 */

// regex-style: 'auto', 'none', 'glob', 'posix', 'pcre', 'sql'
define ('TSQ_REGEX_NONE', 0);
define ('TSQ_REGEX_AUTO', 1);
define ('TSQ_REGEX_POSIX',2);
define ('TSQ_REGEX_GLOB', 4);
define ('TSQ_REGEX_PCRE', 8);
define ('TSQ_REGEX_SQL', 16);

define ('TSQ_TOK_VOID',   0);
define ('TSQ_TOK_BINOP',  1);
define ('TSQ_TOK_NOT',    2);
define ('TSQ_TOK_LPAREN', 4);
define ('TSQ_TOK_RPAREN', 8);
define ('TSQ_TOK_WORD',   16);
define ('TSQ_TOK_STARTS_WITH', 32);
define ('TSQ_TOK_ENDS_WITH',   64);
define ('TSQ_TOK_EXACT',      128);
define ('TSQ_TOK_REGEX',      256);
define ('TSQ_TOK_REGEX_GLOB', 512);
define ('TSQ_TOK_REGEX_PCRE', 1024);
define ('TSQ_TOK_REGEX_SQL',  2048);
define ('TSQ_TOK_ALL',        4096);
// all bits from word to the last.
define ('TSQ_ALLWORDS', (4096*2)-1 - (16-1));

/*
define ('TSQ_NODE_ALL',   0);
define ('TSQ_NODE_EXACT', 1);
define ('TSQ_NODE_NOT',   2);
define ('TSQ_NODE_WORD',  3);
define ('TSQ_NODE_STARTS_WITH', 4);
define ('TSQ_NODE_ENDS_WITH',   5);
define ('TSQ_NODE_REGEX',       6);
define ('TSQ_NODE_REGEX_GLOB',  7);
define ('TSQ_NODE_REGEX_PCRE',  8);
define ('TSQ_NODE_REGEX_SQL',   9);
define ('TSQ_NODE_AND',        10);
define ('TSQ_NODE_OR',         11);
*/


class TextSearchQuery {
    /**
     * Create a new query.
     *
     * @param $search_query string The query.  Syntax is as described above.
     * Note that an empty $search_query will match anything.
     * @param $case_exact boolean
     * @param $regex string one of 'auto', 'none', 'glob', 'posix', 'pcre', 'sql'
     * @see TextSearchQuery
     */
    function TextSearchQuery($search_query, $case_exact=false, $regex='auto') {
        if ($regex == 'none' or !$regex)
            $this->_regex = 0;
        elseif (defined("TSQ_REGEX_".strtoupper($regex)))
            $this->_regex = constant("TSQ_REGEX_".strtoupper($regex));
        else {
            trigger_error(fmt("Unsupported argument: %s=%s", 'regex', $regex));
            $this->_regex = 0;
        }
    $this->_regex_modifier = ($case_exact?'':'i').'sS';
        $this->_case_exact = $case_exact;
        if ($regex != 'pcre') {
        $parser = new TextSearchQuery_Parser;
        $this->_tree = $parser->parse($search_query, $case_exact, $this->_regex);
        $this->_optimize(); // broken under certain circumstances: "word -word -word"
        if (defined("FULLTEXTSEARCH_STOPLIST"))
        $this->_stoplist = FULLTEXTSEARCH_STOPLIST;
        else // default stoplist, localizable.
        $this->_stoplist = _("(A|An|And|But|By|For|From|In|Is|It|Of|On|Or|The|To|With)");
    }
    else {
        $this->_tree = new TextSearchQuery_node_regex_pcre($search_query);
        if (preg_match("/^\/(.*)\/(\w*)$/", $search_query, $m)) {
            $this->_tree->word = $m[1];
            $this->_regex_modifier = $m[2]; // overrides case_exact
        }
    }
    }

    function getType() { return 'text'; }

    function _optimize() {
        $this->_tree = $this->_tree->optimize();
    }

    /**
     * Get a PCRE regexp which matches the query.
     */
    function asRegexp() {
        if (!isset($this->_regexp)) {
            if (!isset($this->_regex_modifier))
                $this->_regex_modifier = ($this->_case_exact?'':'i').'sS';
            if ($this->_regex)
                $this->_regexp =  '/' . $this->_tree->regexp() . '/'.$this->_regex_modifier;
            else
                $this->_regexp =  '/^' . $this->_tree->regexp() . '/'.$this->_regex_modifier;
        }
        return $this->_regexp;
    }

    /**
     * Match query against string.
     * EXACT ("Term") ignores the case_exact setting.
     *
     * @param $string string The string to match.
     * @return boolean True if the string matches the query.
     */
    function match($string) {
        if ($this->_tree->_op == TSQ_TOK_ALL)   return true;
        if ($this->_tree->_op == TSQ_TOK_EXACT) return $this->_tree->word == $string;
        return preg_match($this->asRegexp(), $string);
    }

    /* How good does it match? Returns a number */
    function score($string) {
    $score = 0.0;
    $i = 10;
    foreach (array_unique($this->_tree->highlight_words()) as $word) {
        if ($nummatch = preg_match_all("/".preg_quote($word, '/')."/".
                                           $this->_regex_modifier,
                                       $string, $out))
            $score += ($i-- * $nummatch);
    }
    return min(1.0, $score / 10.0);
    }


    /**
     * Get a regular expression suitable for highlighting matched words.
     *
     * This returns a PCRE regular expression which matches any non-negated
     * word in the query.
     *
     * @return string The PCRE regexp.
     */
    function getHighlightRegexp() {
        if (!isset($this->_hilight_regexp)) {
            $words = array_unique($this->_tree->highlight_words());
            if (!$words) {
                $this->_hilight_regexp = false;
            } else {
                foreach ($words as $key => $word)
                    $words[$key] = preg_quote($word, '/');
                $this->_hilight_regexp = '(?'.($this->_case_exact?'':'i').':'
                                             . join('|', $words) . ')';
            }
        }
        return $this->_hilight_regexp;
    }

    /**
     * Make an SQL clause which matches the query.
     * Deprecated, use makeSqlClauseObj instead.
     *
     * @param $make_sql_clause_cb WikiCallback
     * A callback which takes a single word as an argument and
     * returns an SQL clause which will match exactly those records
     * containing the word.  The word passed to the callback will always
     * be in all lower case.
     *
     * Support db-specific extensions, like MATCH AGAINST or REGEX
     * mysql => 4.0.1 can also do Google: MATCH AGAINST IN BOOLEAN MODE
     * by using makeSqlClauseObj
     *
     * Old example usage:
     * <pre>
     *     function sql_title_match($word) {
     *         return sprintf("LOWER(title) like '%s'",
     *                        addslashes($word));
     *     }
     *
     *     ...
     *
     *     $query = new TextSearchQuery("wiki -page");
     *     $cb = new WikiFunctionCb('sql_title_match');
     *     $sql_clause = $query->makeSqlClause($cb);
     * </pre>
     * This will result in $sql_clause containing something like
     * "(LOWER(title) like 'wiki') AND NOT (LOWER(title) like 'page')".
     *
     * @return string The SQL clause.
     */
    function makeSqlClause($sql_clause_cb) {
        $this->_sql_clause_cb = $sql_clause_cb;
        return $this->_sql_clause($this->_tree);
    }
    // deprecated: use _sql_clause_obj now.
    function _sql_clause($node) {
        switch ($node->_op) {
        case TSQ_TOK_WORD:        // word => %word%
            return $this->_sql_clause_cb->call($node->word);
        case TSQ_TOK_NOT:
            return "NOT (" . $this->_sql_clause($node->leaves[0]) . ")";
        case TSQ_TOK_BINOP:
            $subclauses = array();
            foreach ($node->leaves as $leaf)
                $subclauses[] = "(" . $this->_sql_clause($leaf) . ")";
            return join(" $node->op ", $subclauses);
        default:
            assert($node->_op == TSQ_TOK_VOID);
            return '1=1';
        }
    }

    /** Get away with the callback and use a db-specific search class instead.
     * @see WikiDB_backend_PearDB_search
     */
    function makeSqlClauseObj(&$sql_search_cb) {
        $this->_sql_clause_cb = $sql_search_cb;
        return $this->_sql_clause_obj($this->_tree);
    }

    function _sql_clause_obj($node) {
        switch ($node->_op) {
        case TSQ_TOK_NOT:
            return "NOT (" . $this->_sql_clause_cb->call($node->leaves[0]) . ")";
        case TSQ_TOK_BINOP:
            $subclauses = array();
            foreach ($node->leaves as $leaf)
                $subclauses[] = "(" . $this->_sql_clause_obj($leaf) . ")";
            return join(" $node->op ", $subclauses);
        case TSQ_TOK_VOID:
            return '0=1';
        case TSQ_TOK_ALL:
            return '1=1';
        default:
            return $this->_sql_clause_cb->call($node);
        }
    }

    /*
     postgresql tsearch2 uses no WHERE operators, just & | and ! in the searchstring
     */
    function makeTsearch2SqlClauseObj(&$sql_search_cb) {
        $this->_sql_clause_cb = $sql_search_cb;
        return $this->_Tsearch2Sql_clause_obj($this->_tree);
    }

    function _Tsearch2Sql_clause_obj($node) {
        // TODO: "such a phrase"
        switch ($node->_op) {
        case TSQ_TOK_NOT:
            return "!" . $node->leaves[0];
        case TSQ_TOK_BINOP:
            $subclauses = array();
            foreach ($node->leaves as $leaf)
                $subclauses[] = $this->_Tsearch2Sql_clause_obj($leaf);
            return join($node->_op == 'AND' ? "&" : "|", $subclauses);
        case TSQ_TOK_VOID:
            return '';
        case TSQ_TOK_ALL:
            return '1';
        default:
            return $this->_sql_clause_cb->call($node);
        }
    }

    function sql() { return '%'.$this->_sql_quote($this->word).'%'; }

    /**
     * Get printable representation of the parse tree.
     *
     * This is for debugging only.
     * @return string Printable parse tree.
     */
    function asString() {
        return $this->_as_string($this->_tree);
    }

    function _as_string($node, $indent = '') {
        switch ($node->_op) {
        case TSQ_TOK_WORD:
            return $indent . "WORD: $node->word";
        case TSQ_TOK_VOID:
            return $indent . "VOID";
        case TSQ_TOK_ALL:
            return $indent . "ALL";
        default:
            $lines = array($indent . $node->op . ":");
            $indent .= "  ";
            foreach ($node->leaves as $leaf)
                $lines[] = $this->_as_string($leaf, $indent);
            return join("\n", $lines);
        }
    }
}

/**
 * This is a TextSearchQuery which matches nothing.
 */
class NullTextSearchQuery extends TextSearchQuery {
    /**
     * Create a new query.
     *
     * @see TextSearchQuery
     */
    function NullTextSearchQuery() {}
    function asRegexp()        { return '/^(?!a)a/x'; }
    function match($string)    { return false; }
    function getHighlightRegexp() { return ""; }
    function makeSqlClause($make_sql_clause_cb) { return "(1 = 0)"; }
    function asString() { return "NullTextSearchQuery"; }
};

/**
 * A simple algebraic matcher for numeric attributes.
 *  NumericSearchQuery can do ("population < 20000 and area > 1000000", array("population", "area"))
 *  ->match(array('population' => 100000, 'area' => 10000000))
 *
 * Supports all mathematical PHP comparison operators, plus ':=' for equality.
 *   "(x < 2000000 and x >= 10000) or (x >= 100 and x < 2000)"
 *   "x := 100000" is the same as "x == 100000"
 *
 * Since this is basic numerics only, we simply try to get away with
 * replacing the variable values at the right positions and do an eval then.
 *
 * @package NumericSearchQuery
 * @author Reini Urban
 * @see SemanticAttributeSearchQuery
 */
class NumericSearchQuery
{
    /**
     * Create a new query.
     *   NumericSearchQuery("population > 20000 or population < 200", "population")
     *   NumericSearchQuery("population < 20000 and area > 1000000", array("population", "area"))
     *
     * With a single variable it is easy: The valid name must be matched elsewhere, just
     * replace the given number in match in the query.
     *   ->match(2000)
     *
     * With matching a struct we need strict names, no * as name is allowed.
     * So always when the placeholder is an array, the names of the target struct must match
     * and all vars be defined. Use the method can_match($struct) therefore.
     *
     * @access public
     * @param $search_query string   A numerical query with placeholders as variable.
     * @param $placeholders array or string  All placeholders in the query must be defined
     *     here, and will be replaced by the matcher.
     */
    function NumericSearchQuery($search_query, $placeholders) {
    // added some basic security checks against user input
        $this->_query = $search_query;
    $this->_placeholders = $placeholders;

    // we should also allow the M_ constants
    $this->_allowed_functions = explode(':','abs:acos:acosh:asin:asinh:atan2:atan:atanh:base_convert:bindec:ceil:cos:cosh:decbin:dechex:decoct:deg2rad:exp:expm1:floor:fmod:getrandmax:hexdec:hypot:is_finite:is_infinite:is_nan:lcg_value:log10:log1p:log:max:min:mt_getrandmax:mt_rand:mt_srand:octdec:pi:pow:rad2deg:rand:round:sin:sinh:sqrt:srand:tan:tanh');
    $this->_allowed_operators = explode(',', '-,<,<=,>,>=,==,!=,*,+,/,(,),%,and,or,xor,<<,>>,===,!==,&,^,|,&&,||');
    $this->_parser_check = array();
    // check should be fast, so make a hash
    foreach ($this->_allowed_functions as $f)
        $this->_parser_check[$f] = 1;
    foreach ($this->_allowed_operators as $f)
        $this->_parser_check[$f] = 1;
    if (is_array($placeholders))
        foreach ($placeholders as $f)
        $this->_parser_check[$f] = 1;
    else $this->_parser_check[$placeholders] = 1;

    // This is a speciality: := looks like the attribute definition and is
    // therefore a dummy check for this definition.
    // php-4.2.2 has a problem with /\b:=\b/ matching "population := 1223400"
    $this->_query = preg_replace("/:=/", "==", $this->_query);
    $this->_query = $this->check_query($this->_query);
    }

    function getType() { return 'numeric'; }

    /**
     * Check the symbolic definition query against unwanted functions and characters.
     * "population < 20000 and area > 1000000" vs
     *   "area > 1000000 and mail($me,file("/etc/passwd"),...)"
     * http://localhost/wikicvs/SemanticSearch?attribute=*&attr_op=<0 and find(1)>&s=-0.01&start_debug=1
     */
    function check_query ($query) {
    $tmp = $query; // check for all function calls, in case the tokenizer is not available.
        while (preg_match("/([a-z][a-z0-9]+)\s*\((.*)$/i", $tmp, $m)) {
        if (!in_array($m[1], $this->_allowed_functions)
        and !in_array($m[1], $this->_allowed_operators))
        {
        trigger_error("Illegal function in query: ".$m[1], E_USER_WARNING);
        return '';
        }
        $tmp = $m[2];
    }

    // Strictly check for illegal functions and operators, which are no placeholders.
    if (function_exists('token_get_all')) {
        $parsed = token_get_all("<?$query?>");
        foreach ($parsed as $x) { // flat, non-recursive array
        if (is_string($x) and !isset($this->_parser_check[$x])) {
            // single char op or name
            trigger_error("Illegal string or operator in query: \"$x\"", E_USER_WARNING);
            $query = '';
            }
        elseif (is_array($x)) {
            $n = token_name($x[0]);
            if ($n == 'T_OPEN_TAG' or $n == 'T_WHITESPACE'
                or $n == 'T_CLOSE_TAG' or $n == 'T_LNUMBER'
                or $n == 'T_CONST' or $n == 'T_DNUMBER' ) continue;
            if ($n == 'T_VARIABLE') { // but we do allow consts
            trigger_error("Illegal variable in query: \"$x[1]\"", E_USER_WARNING);
            $query = '';
            }
            if (is_string($x[1]) and !isset($this->_parser_check[$x[1]])) {
            // multi-char char op or name
            trigger_error("Illegal $n in query: \"$x[1]\"", E_USER_WARNING);
            $query = '';
            }
        }
        }
        //echo "$query <br>";
        //$this->_parse_token($parsed);
        //echo "<br>\n";
        //var_dump($parsed);
        /*
"_x > 0" =>
{ T_OPEN_TAG "<?"} { T_STRING "_x"} { T_WHITESPACE " "} ">" { T_WHITESPACE " "} { T_LNUMBER "0"} { T_CLOSE_TAG "?>"}
    Interesting: on-char ops, as ">" are not tokenized.
"_x <= 0"
{ T_OPEN_TAG "< ?" } { T_STRING "_x" } { T_WHITESPACE " " } { T_IS_SMALLER_OR_EQUAL "<=" } { T_WHITESPACE " " } { T_LNUMBER "0" } { T_CLOSE_TAG "?>" }
         */
    } else {
        // Detect illegal characters besides nums, words and ops.
        // So attribute names can not be utf-8
        $c = "/([^\d\w.,\s".preg_quote(join("",$this->_allowed_operators),"/")."])/";
        if (preg_match($c, $query, $m)) {
        trigger_error("Illegal character in query: ".$m[1], E_USER_WARNING);
        return '';
        }
    }
    return $query;
    }

    /**
     * Check the bound, numeric-only query against unwanted functions and sideeffects.
     * "4560000 < 20000 and 1456022 > 1000000"
     */
    function _live_check () {
    // TODO: check $this->_workquery again?
    return !empty($this->_workquery);
    }

    /**
     * A numeric query can only operate with predefined variables. "x < 0 and y < 1"
     *
     * @return array The names as array of strings. => ('x', 'y') the placeholders.
     */
    function getVars() {
    if(is_array($this->_placeholders)) return $this->_placeholders;
    else return array($this->_placeholders);
    }

    /**
     * Strip non-numeric chars from the variable (as the groupseperator) and replace
     * it in the symbolic query for evaluation.
     *
     * @access private
     * @param $value number   A numerical value: integer, float or string.
     * @param $x string       The variable name to be replaced in the query.
     * @return string
     */
    function _bind($value, $x) {
    // TODO: check is_number, is_float, is_integer and do casting
    $this->_bound[] = array('linkname'  => $x,
                    'linkvalue' => $value);
    $value = preg_replace("/[^-+0123456789.,]/", "", $value);
    //$c = "/\b".preg_quote($x,"/")."\b/";
    $this->_workquery = preg_replace("/\b".preg_quote($x,"/")."\b/", $value, $this->_workquery);
    // FIXME: do again a final check. now only numbers and some operators are allowed.
    return $this->_workquery;
    }

    /* array of successfully bound vars, and in case of success, the resulting vars
     */
    function _bound() {
        return $this->_bound;
    }

    /**
     * With an array of placeholders we need a hash to check against, if all required names are given.
     * Purpose: Be silent about missing vars, just return false.
     `*
     * @access public
     * @param $variable string or hash of name => value  The keys must satisfy all placeholders in the definition.
     * We want the full hash and not just the keys because a hash check is faster than the array of keys check.
     * @return boolean
     */
    function can_match(&$variables) {
        if (empty($this->_query))
            return false;
    $p =& $this->_placeholders;
    if (!is_array($variables) and !is_array($p))
        return $variables == $p; // This was easy.
    // Check if all placeholders have definitions. can be overdefined but not underdefined.
    if (!is_array($p)) {
        if (!isset($variables[$p])) return false;
    } else {
        foreach ($p as $x) {
        if (!isset($variables[$x])) return false;
        }
    }
    return true;
    }

    /**
     * We can match against a single variable or against a hash of variables.
     * With one placeholder we need just a number.
     * With an array of placeholders we need a hash.
     *
     * @access public
     * @param $variable number or array of name => value  The keys must satisfy all placeholders in the definition.
     * @return boolean
     */
    function match(&$variable) {
    $p =& $this->_placeholders;
    $this->_workquery = $this->_query;
    if (!is_array($p)) {
        if (is_array($variable)) { // which var to match? we cannot decide this here
        if (!isset($variable[$p]))
            trigger_error("Required NumericSearchQuery->match variable $x not defined.", E_USER_ERROR);
        $this->_bind($variable[$p], $p);
        } else {
        $this->_bind($variable, $p);
        }
    } else {
        foreach ($p as $x) {
        if (!isset($variable[$x]))
            trigger_error("Required NumericSearchQuery->match variable $x not defined.", E_USER_ERROR);
        $this->_bind($variable[$x], $x);
        }
    }
        if (!$this->_live_check()) // check returned an error
            return false;
        $search = $this->_workquery;
    $result = false;
    //if (DEBUG & _DEBUG_VERBOSE)
    //    trigger_error("\$result = (boolean)($search);", E_USER_NOTICE);
    // We might have a numerical problem:
    // php-4.2.2 eval'ed as module: "9.636e+08 > 1000" false;
    // php-5.1.2 cgi true, 4.2.2 cgi true
    eval("\$result = (boolean)($search);");
    if ($result and is_array($p)) {
        return $this->_bound();
    }
        return $result;
    }
}


////////////////////////////////////////////////////////////////
//
// Remaining classes are private.
//
////////////////////////////////////////////////////////////////
/**
 * Virtual base class for nodes in a TextSearchQuery parse tree.
 *
 * Also serves as a 'VOID' (contentless) node.
 */
class TextSearchQuery_node
{
    var $op = 'VOID';
    var $_op = 0;

    /**
     * Optimize this node.
     * @return object Optimized node.
     */
    function optimize() {
        return $this;
    }

    /**
     * @return regexp matching this node.
     */
    function regexp() {
        return '';
    }

    /**
     * @param bool True if this node has been negated (higher in the parse tree.)
     * @return array A list of all non-negated words contained by this node.
     */
    function highlight_words($negated = false) {
        return array();
    }

    function sql()    { return $this->word; }

    function _sql_quote() {
    global $request;
        $word = preg_replace('/(?=[%_\\\\])/', "\\", $this->word);
        return $request->_dbi->_backend->qstr($word);
    }
}

/**
 * A word. Exact or substring?
 */
class TextSearchQuery_node_word
extends TextSearchQuery_node
{
    var $op = "WORD";
    var $_op = TSQ_TOK_WORD;

    function TextSearchQuery_node_word($word) {
        $this->word = $word;
    }
    function regexp() {
        return '(?=.*\b' . preg_quote($this->word, '/') . '\b)';
    }
    function highlight_words ($negated = false) {
        return $negated ? array() : array($this->word);
    }
    function sql()    { return '%'.$this->_sql_quote($this->word).'%'; }
}

class TextSearchQuery_node_all
extends TextSearchQuery_node {
    var $op = "ALL";
    var $_op = TSQ_TOK_ALL;
    function regexp() { return '(?=.*)'; }
    function sql()    { return '%'; }
}

class TextSearchQuery_node_starts_with
extends TextSearchQuery_node_word {
    var $op = "STARTS_WITH";
    var $_op = TSQ_TOK_STARTS_WITH;
    function regexp() { return '(?=.*\b' . preg_quote($this->word, '/') . ')'; }
    function sql ()   { return $this->_sql_quote($this->word).'%'; }
}

// ^word: full phrase starts with
class TextSearchQuery_phrase_starts_with
extends TextSearchQuery_node_starts_with {
    function regexp() { return '(?=^' . preg_quote($this->word, '/') . ')'; }
}

class TextSearchQuery_node_ends_with
extends TextSearchQuery_node_word {
    var $op = "ENDS_WITH";
    var $_op = TSQ_TOK_ENDS_WITH;
    function regexp() { return '(?=.*' . preg_quote($this->word, '/') . '\b)'; }
    function sql ()   { return '%'.$this->_sql_quote($this->word); }
}

// word$: full phrase ends with
class TextSearchQuery_phrase_ends_with
extends TextSearchQuery_node_ends_with {
    function regexp() { return '(?=' . preg_quote($this->word, '/') . '$)'; }
}

class TextSearchQuery_node_exact
extends TextSearchQuery_node_word {
    var $op = "EXACT";
    var $_op = TSQ_TOK_EXACT;
    function regexp() { return '(?=\b' . preg_quote($this->word, '/') . '\b)'; }
    function sql ()   { return $this->_sql_squote($this->word); }
}

class TextSearchQuery_node_regex // posix regex. FIXME!
extends TextSearchQuery_node_word {
    var $op = "REGEX"; // using REGEXP or ~ extension
    var $_op = TSQ_TOK_REGEX;
    function regexp() { return '(?=.*\b' . $this->word . '\b)'; }
    function sql ()   { return $this->_sql_quote($this->word); }
}

class TextSearchQuery_node_regex_glob
extends TextSearchQuery_node_regex {
    var $op = "REGEX_GLOB";
    var $_op = TSQ_TOK_REGEX_GLOB;
    function regexp() { return '(?=.*\b' . glob_to_pcre($this->word) . '\b)'; }
}

class TextSearchQuery_node_regex_pcre // how to handle pcre modifiers? /i
extends TextSearchQuery_node_regex {
    var $op = "REGEX_PCRE";
    var $_op = TSQ_TOK_REGEX_PCRE;
    function regexp() { return $this->word; }
}

class TextSearchQuery_node_regex_sql
extends TextSearchQuery_node_regex {
    var $op = "REGEX_SQL"; // using LIKE
    var $_op = TSQ_TOK_REGEX_SQL;
    function regexp() { return str_replace(array("/%/","/_/"), array(".*","."), $this->word); }
    function sql()    { return $this->word; }
}

/**
 * A negated clause.
 */
class TextSearchQuery_node_not
extends TextSearchQuery_node
{
    var $op = "NOT";
    var $_op = TSQ_TOK_NOT;

    function TextSearchQuery_node_not($leaf) {
        $this->leaves = array($leaf);
    }

    function optimize() {
        $leaf = &$this->leaves[0];
        $leaf = $leaf->optimize();
        if ($leaf->_op == TSQ_TOK_NOT)
            return $leaf->leaves[0]; // ( NOT ( NOT x ) ) -> x
        return $this;
    }

    function regexp() {
        $leaf = &$this->leaves[0];
        return '(?!' . $leaf->regexp() . ')';
    }

    function highlight_words ($negated = false) {
        return $this->leaves[0]->highlight_words(!$negated);
    }
}

/**
 * Virtual base class for 'AND' and 'OR conjoins.
 */
class TextSearchQuery_node_binop
extends TextSearchQuery_node
{
    var $_op = TSQ_TOK_BINOP;
    function TextSearchQuery_node_binop($leaves) {
        $this->leaves = $leaves;
    }

    function _flatten() {
        // This flattens e.g. (AND (AND a b) (OR c d) e)
        //        to (AND a b e (OR c d))
        $flat = array();
        foreach ($this->leaves as $leaf) {
            $leaf = $leaf->optimize();
            if ($this->op == $leaf->op)
                $flat = array_merge($flat, $leaf->leaves);
            else
                $flat[] = $leaf;
        }
        $this->leaves = $flat;
    }

    function optimize() {
        $this->_flatten();
        assert(!empty($this->leaves));
        if (count($this->leaves) == 1)
            return $this->leaves[0]; // (AND x) -> x
        return $this;
    }

    function highlight_words($negated = false) {
        $words = array();
        foreach ($this->leaves as $leaf)
            array_splice($words,0,0,
                         $leaf->highlight_words($negated));
        return $words;
    }
}

/**
 * A (possibly multi-argument) 'AND' conjoin.
 */
class TextSearchQuery_node_and
extends TextSearchQuery_node_binop
{
    var $op = "AND";

    function optimize() {
        $this->_flatten();

        // Convert (AND (NOT a) (NOT b) c d) into (AND (NOT (OR a b)) c d).
        // Since OR's are more efficient for regexp matching:
        //   (?!.*a)(?!.*b)  vs   (?!.*(?:a|b))

        // Suck out the negated leaves.
        $nots = array();
        foreach ($this->leaves as $key => $leaf) {
            if ($leaf->_op == TSQ_TOK_NOT) {
                $nots[] = $leaf->leaves[0];
                unset($this->leaves[$key]);
            }
        }

        // Combine the negated leaves into a single negated or.
        if ($nots) {
            $node = ( new TextSearchQuery_node_not
                      (new TextSearchQuery_node_or($nots)) );
            array_unshift($this->leaves, $node->optimize());
        }

        assert(!empty($this->leaves));
        if (count($this->leaves) == 1)
            return $this->leaves[0];  // (AND x) -> x
        return $this;
    }

    /* FIXME!
     * Either we need all combinations of all words to be position independent,
     * or we have to use multiple match calls for each AND
     * (AND x y) => /(?(:x)(:y))|(?(:y)(:x))/
     */
    function regexp() {
        $regexp = '';
        foreach ($this->leaves as $leaf)
            $regexp .= $leaf->regexp();
        return $regexp;
    }
}

/**
 * A (possibly multi-argument) 'OR' conjoin.
 */
class TextSearchQuery_node_or
extends TextSearchQuery_node_binop
{
    var $op = "OR";

    function regexp() {
        // We will combine any of our direct descendents which are WORDs
        // into a single (?=.*(?:word1|word2|...)) regexp.

        $regexps = array();
        $words = array();

        foreach ($this->leaves as $leaf) {
            if ($leaf->op == TSQ_TOK_WORD)
                $words[] = preg_quote($leaf->word, '/');
            else
                $regexps[] = $leaf->regexp();
        }

        if ($words)
            array_unshift($regexps,
                          '(?=.*' . $this->_join($words) . ')');

        return $this->_join($regexps);
    }

    function _join($regexps) {
        assert(count($regexps) > 0);

        if (count($regexps) > 1)
            return '(?:' . join('|', $regexps) . ')';
        else
            return $regexps[0];
    }
}


////////////////////////////////////////////////////////////////
//
// Parser:
//   op's (and, or, not) are forced to lowercase in the tokenizer.
//
////////////////////////////////////////////////////////////////
class TextSearchQuery_Parser
{
    /*
     * This is a simple recursive descent parser, based on the following grammar:
     *
     * toplist    :
     *        | toplist expr
     *        ;
     *
     *
     * list    : expr
     *        | list expr
     *        ;
     *
     * expr    : atom
     *        | expr BINOP atom
     *        ;
     *
     * atom    : '(' list ')'
     *        | NOT atom
     *        | WORD
     *        ;
     *
     * The terminal tokens are:
     *
     *
     * and|or          BINOP
     * -|not          NOT
     * (          LPAREN
     * )          RPAREN
     * /[^-()\s][^()\s]*  WORD
     * /"[^"]*"/      WORD
     * /'[^']*'/      WORD
     *
     * ^WORD              TextSearchQuery_phrase_starts_with
     * WORD*              STARTS_WITH
     * *WORD              ENDS_WITH
     * ^WORD$             EXACT
     * *                  ALL
     */

    function parse ($search_expr, $case_exact=false, $regex=TSQ_REGEX_AUTO) {
        $this->lexer = new TextSearchQuery_Lexer($search_expr, $case_exact, $regex);
        $this->_regex = $regex;
        $tree = $this->get_list('toplevel');
        // Assert failure when using the following URL in debug mode.
        // /TitleSearch?action=FullTextSearch&s=WFXSSProbe'")/>&case_exact=1&regex=sql
        //        assert($this->lexer->eof());
        unset($this->lexer);
        return $tree;
    }

    function get_list ($is_toplevel = false) {
        $list = array();

        // token types we'll accept as words (and thus expr's) for the
        // purpose of error recovery:
        $accept_as_words = TSQ_TOK_NOT | TSQ_TOK_BINOP;
        if ($is_toplevel)
            $accept_as_words |= TSQ_TOK_LPAREN | TSQ_TOK_RPAREN;

        while ( ($expr = $this->get_expr())
                || ($expr = $this->get_word($accept_as_words)) )
    {
            $list[] = $expr;
        }

        if (!$list) {
            if ($is_toplevel)
                return new TextSearchQuery_node;
            else
                return false;
        }
        if ($is_toplevel and count($list) == 1) {
            if ($this->lexer->query_str[0] == '^')
                return new TextSearchQuery_phrase_starts_with($list[0]->word);
            else
                return $list[0];
        }
        return new TextSearchQuery_node_and($list);
    }

    function get_expr () {
        if ( ($expr = $this->get_atom()) === false ) // protect against '0'
            return false;

        $savedpos = $this->lexer->tell();
        // Bug#1791564: allow string '0'
        while ( ($op = $this->lexer->get(TSQ_TOK_BINOP)) !== false) {
            if ( ! ($right = $this->get_atom()) ) {
                break;
            }

            if ($op == 'and')
                $expr = new TextSearchQuery_node_and(array($expr, $right));
            else {
                assert($op == 'or');
                $expr = new TextSearchQuery_node_or(array($expr, $right));
            }

            $savedpos = $this->lexer->tell();
        }
        $this->lexer->seek($savedpos);

        return $expr;
    }


    function get_atom() {
        if ($atom = $this->get_word(TSQ_ALLWORDS)) // Bug#1791564 not involved: '*'
            return $atom;

        $savedpos = $this->lexer->tell();
        if ( $this->lexer->get(TSQ_TOK_LPAREN) ) {
            if ( ($list = $this->get_list()) && $this->lexer->get(TSQ_TOK_RPAREN) ) {
                return $list;
            } else {
                // Fix Bug#1792170
                // Handle " ( " or "(test" without closing ")" as plain word
        $this->lexer->seek($savedpos);
                return new TextSearchQuery_node_word($this->lexer->get(-1));
            }
        }
        elseif ( $this->lexer->get(TSQ_TOK_NOT) ) {
            if ( ($atom = $this->get_atom()) )
                return new TextSearchQuery_node_not($atom);
        }
        $this->lexer->seek($savedpos);
        return false;
    }

    function get_word($accept = TSQ_ALLWORDS) {
        // Performance shortcut for ( and ). This is always false
    if (!empty($this->lexer->tokens[$this->lexer->pos])) {
        list ($type, $val) = $this->lexer->tokens[$this->lexer->pos];
        if ($type == TSQ_TOK_LPAREN or $type == TSQ_TOK_RPAREN)
        return false;
    }
        foreach (array("WORD","STARTS_WITH","ENDS_WITH","EXACT",
                       "REGEX","REGEX_GLOB","REGEX_PCRE","ALL") as $tok) {
            $const = constant("TSQ_TOK_".$tok);
            // Bug#1791564: allow word '0'
            if ( $accept & $const and
                (($word = $this->lexer->get($const)) !== false))
            {
                // phrase or word level?
                if ($tok == 'STARTS_WITH' and $this->lexer->query_str[0] == '^')
                    $classname = "TextSearchQuery_phrase_".strtolower($tok);
                elseif ($tok == 'ENDS_WITH' and
                        string_ends_with($this->lexer->query_str,'$'))
                    $classname = "TextSearchQuery_phrase_".strtolower($tok);
                else
                    $classname = "TextSearchQuery_node_".strtolower($tok);
                return new $classname($word);
            }
        }
        return false;
    }
}

class TextSearchQuery_Lexer {
    function TextSearchQuery_Lexer ($query_str, $case_exact=false,
                                    $regex=TSQ_REGEX_AUTO)
    {
        $this->tokens = $this->tokenize($query_str, $case_exact, $regex);
        $this->query_str = $query_str;
        $this->pos = 0;
    }

    function tell() {
        return $this->pos;
    }

    function seek($pos) {
        $this->pos = $pos;
    }

    function eof() {
        return $this->pos == count($this->tokens);
    }

    /**
     * TODO: support more regex styles, esp. prefer the forced ones over auto
     * re: and // stuff
     */
    function tokenize($string, $case_exact=false, $regex=TSQ_REGEX_AUTO) {
        $tokens = array();
        $buf = $case_exact ? ltrim($string) : strtolower(ltrim($string));
        while (!empty($buf)) {
            if (preg_match('/^([()])\s*/', $buf, $m)) {
                $val = $m[1];
                $type = $m[1] == '(' ? TSQ_TOK_LPAREN : TSQ_TOK_RPAREN;
            }
            // * => ALL
            elseif ($regex & (TSQ_REGEX_AUTO|TSQ_REGEX_POSIX|TSQ_REGEX_GLOB)
                    and preg_match('/^\*\s*/', $buf, $m)) {
                $val = "*";
                $type = TSQ_TOK_ALL;
            }
            // .* => ALL
            elseif ($regex & (TSQ_REGEX_PCRE)
                    and preg_match('/^\.\*\s*/', $buf, $m)) {
                $val = ".*";
                $type = TSQ_TOK_ALL;
            }
            // % => ALL
            elseif ($regex & (TSQ_REGEX_SQL)
                    and preg_match('/^%\s*/', $buf, $m)) {
                $val = "%";
                $type = TSQ_TOK_ALL;
            }

            // ^word
            elseif ($regex & (TSQ_REGEX_AUTO|TSQ_REGEX_POSIX|TSQ_REGEX_PCRE)
                    and preg_match('/^\^([^-()][^()\s]*)\s*/', $buf, $m)) {
                $val = $m[1];
                $type = TSQ_TOK_STARTS_WITH;
            }
            // word*
            elseif ($regex & (TSQ_REGEX_AUTO|TSQ_REGEX_POSIX|TSQ_REGEX_GLOB)
                    and preg_match('/^([^-()][^()\s]*)\*\s*/', $buf, $m)) {
                $val = $m[1];
                $type = TSQ_TOK_STARTS_WITH;
            }
            // *word
            elseif ($regex & (TSQ_REGEX_AUTO|TSQ_REGEX_POSIX|TSQ_REGEX_GLOB)
                    and preg_match('/^\*([^-()][^()\s]*)\s*/', $buf, $m)) {
                $val = $m[1];
                $type = TSQ_TOK_ENDS_WITH;
            }
            // word$
            elseif ($regex & (TSQ_REGEX_AUTO|TSQ_REGEX_POSIX|TSQ_REGEX_PCRE)
                    and preg_match('/^([^-()][^()\s]*)\$\s*/', $buf, $m)) {
                $val = $m[1];
                $type = TSQ_TOK_ENDS_WITH;
            }
            // ^word$
            elseif ($regex & (TSQ_REGEX_AUTO|TSQ_REGEX_POSIX|TSQ_REGEX_PCRE)
                    and preg_match('/^\^([^-()][^()\s]*)\$\s*/', $buf, $m)) {
                $val = $m[1];
                $type = TSQ_TOK_EXACT;
            }
            elseif (preg_match('/^(and|or)\b\s*/i', $buf, $m)) {
                $val = strtolower($m[1]);
                $type = TSQ_TOK_BINOP;
            }
            elseif (preg_match('/^(-|not\b)\s*/i', $buf, $m)) {
                $val = strtolower($m[1]);
                $type = TSQ_TOK_NOT;
            }

            // "words "
            elseif (preg_match('/^ " ( (?: [^"]+ | "" )* ) " \s*/x', $buf, $m)) {
                $val = str_replace('""', '"', $m[1]);
                $type = TSQ_TOK_WORD;
            }
            // 'words '
            elseif (preg_match("/^ ' ( (?:[^']+|'')* ) ' \s*/x", $buf, $m)) {
                $val = str_replace("''", "'", $m[1]);
                $type = TSQ_TOK_WORD;
            }
            // word
            elseif (preg_match('/^([^-()][^()\s]*)\s*/', $buf, $m)) {
                $val = $m[1];
                $type = TSQ_TOK_WORD;
            }
            else {
                assert(empty($buf));
                break;
            }
            $buf = substr($buf, strlen($m[0]));

            /* refine the simple parsing from above: bla*bla, bla?bla, ...
            if ($regex and $type == TSQ_TOK_WORD) {
                if (substr($val,0,1) == "^")
                    $type = TSQ_TOK_STARTS_WITH;
                elseif (substr($val,0,1) == "*")
                    $type = TSQ_TOK_ENDS_WITH;
                elseif (substr($val,-1,1) == "*")
                    $type = TSQ_TOK_STARTS_WITH;
            }
            */
            $tokens[] = array($type, $val);
        }
        return $tokens;
    }

    function get($accept) {
        if ($this->pos >= count($this->tokens))
            return false;

        list ($type, $val) = $this->tokens[$this->pos];
        if (($type & $accept) == 0)
            return false;

        $this->pos++;
        return $val;
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
