<?

/*
  jpcache.php v1.0.1 [2001-03-25]
  Copyright  2001 Jean-Pierre Deckers <jpcache@weirdpier.com>

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

/*
 Credits:

	Most things taken from 
		phpCache v1.2 <nathan@0x00.org> (http://www.0x00.org/phpCache)
		gzdoc.php <catoc@163.net> and <jlim@natsoft.com.my> 
		jr-cache.php <jr-jrcache@quo.to>

	Inspired by the following threads:
		http://www.aota.net/ubb/Forum15/HTML/000738-1.html
		http://www.aota.net/ubb/Forum15/HTML/000746-1.html
		http://www.aota.net/ubb/Forum15/HTML/000749-1.html

 Note:
	I do not claim anything. 
	Just a first try to 'release' something under the GPL.

 More info on http://www.weirdpier.com/jpcache/


--
--  Table for new caching system
--
CREATE TABLE cache_store (
name varchar(255),
data text,
indate int not null default 0
);

CREATE UNIQUE INDEX cachestore_name ON cache_store(name);

 */
 
/******************************************************************************/

	$CACHE_TIME=900;	// Default: 900 - number seconds to cache
	$CACHE_DEBUG=0;		// Default: 0 - Turn debugging on/off
	$SINGLE_SITE=1;		// Default: 1 - No servername in file
	$CACHE_POST=0;		// Default: 0 - don't cache when HTTP_POST_VARS present
	$CACHE_COOKIE=0;	// Default: 0 - don't cache when HTTP_COOKIE_VARS present
	$CACHE_ON=1;		// Default: 1 - Turn caching on/off
	$CACHE_USE_GZIP=1;		// Default: 0 - Whether or not to use GZIP

	define(CACHE_DIR, "/tmp");  // Default: /tmp - Default cache directory
	define(CACHE_GC, .1);		// Default: 1 - Probability of garbage collection (i.e: 1%)

/******************************************************************************/


	/* This resets the cache state */
	function cache_reset() 
	{
		global $cache_file, $cache_data;
		$cache_file = NULL;
		$cache_data	= array();
	}

	/* duh ? */
	function cache_debug($s) {
		global $CACHE_DEBUG,$cache_debug_comments;
		if ($CACHE_DEBUG) {
			header("X-Debug: $s");
		}
		$cache_debug_comments .= "\n".$s;
	}

	/* Returns the default key used by the helper functions */
	function cache_default_key() 
	{
		global $HTTP_POST_VARS, $HTTP_GET_VARS, $HTTP_COOKIE_VARS, $REQUEST_URI;
		return md5("POST=" . serialize($HTTP_POST_VARS) . " GET=" . serialize($HTTP_GET_VARS) . " COOKIE=" . serialize($HTTP_COOKIE_VARS) . $REQUEST_URI );
	}

	/* Returns the default object used by the helper functions */
	function cache_default_object() 
	{
		global $SCRIPT_URI, $SERVER_NAME, $SCRIPT_NAME, $SINGLE_SITE;

		if ($SINGLE_SITE) {
			$name=$SCRIPT_NAME;
		} else {
			$name=$SCRIPT_URI;
		}

		if ($name=="") {
			$name="http://$SERVER_NAME/$SCRIPT_NAME";
		}
		return $name;
	}

	/* This is the function that writes out the cache */
	function cache_write($file, $data) 
	{
		global $CACHE_TIME,$CACHE_ON;

		if (!$CACHE_ON || $CACHE_TIME < 1) {
			cache_debug("Not caching, CACHE_ON is off");
			return 0;
		}

		db_begin();

		//
		//	Get a lock on this object in the db
		//
		$res=db_query("SELECT * 
			FROM cache_store 
			WHERE 
				name='$file' 
			FOR UPDATE");

		if (!$res || db_numrows($res) < 1) {
			$res=db_query("INSERT INTO cache_store (name,data,indate) 
				VALUES ('$file','". addslashes($data) ."','". time() ."')");
			//
			//	Assume it worked - it may have been rejected by the UNIQUE INDEX
			//	but that just means another thread inserted in the meantime
			//
			db_commit();
			return true;
		} else {
			//
			//	See if we still need to update or not...
			//
			if (db_result($res,0,'indate') > (time()-$CACHE_TIME)) {
				//
				//	Another thread updated the db already....
				//
				db_commit();
				return true;
			} else {
				$res=db_query("UPDATE cache_store 
					SET 
						data='". addslashes($data) ."', 
						indate='". time() ."'
					WHERE
						name='$file'");
				//
				//  Assume it worked - it may have been rejected by the UNIQUE INDEX
				//  but that just means another thread inserted in the meantime
				//
				db_commit();
				return true;
			}

		}
		return TRUE;
	}

	/* This function reads in the cache, duh */
	function cache_read($file) 
	{
		global $CACHE_TIME;
		$res=db_query("SELECT data 
			FROM cache_store 
			WHERE 
				name='$file'
				AND indate > '". (time()-$CACHE_TIME) ."'");
		return db_result($res,0,'data');
	}

	/* Cache garbage collection */
	function cache_gc() 
	{
		global $CACHE_TIME;

		if (CACHE_GC>0) {
			mt_srand(time(NULL));
			$precision=100000;
			$r=(mt_rand()%$precision)/$precision;
			if ($r >= (CACHE_GC/100)) {
				return false;
			} else {
				db_query("DELETE FROM cache_store WHERE indate < '". (time()-$CACHE_TIME) ."'");
			}
		}
	}

	/* 
		Caches $object based on $key for $cachetime, will return 0 if the 
		object has expired or the object does not exist. 
	*/
	function check_cache() 
	{
		global $cache_file, $cache_data, $gzcontent, $CACHE_ON;
		
		if (!$CACHE_ON) {
			cache_debug("Not caching, CACHE_ON is off");
			return false;
		}

		$cache_file=eregi_replace("[^A-Z,0-9,=]", "_", cache_default_object());
		$key=eregi_replace("[^A-Z,0-9,=]", "_", cache_default_key());
	
		cache_debug("Caching based on <b>OBJECT</b>=$cache_file <b>KEY</b>=$key");
				
		$cache_file=$cache_file . ":" . $key;
		
		// Can we access the cache_file ?
		if ($buff=cache_read($cache_file)) {
			cache_debug("Opened the cache file");
			return $buff;
		} else {
			// No cache file (yet) or unable to read
			cache_debug("No previous cache of $cache_file or unable to read");
		
			// If we came here: start caching!
		
			return false;
		}
	}

	/* 
		Sets the handler 
	*/
	function cache_start()
	{
		global $CACHE_ON,$CACHE_POST,$CACHE_COOKIE,$CACHE_TIME,$cache_debug_comments;

		//
		//	If you chose not to cache when POST occurs, force cache off
		//
		if (!$CACHE_POST && (count($HTTP_POST_VARS) > 0)) {
			$CACHE_ON = 0;
			$CACHE_TIME = -1;
		}

		//
		//	If you chose not to cache when COOKIES present, force cache off
		//
		if (!$CACHE_COOKIE && (count($HTTP_COOKIE_VARS) > 0)) {
			$CACHE_ON = 0;
			$CACHE_TIME = -1;
		}

		//
		//	CACHE_TIME of -1 disables caching, just using gzip if possible
		//
		if ($CACHE_TIME == -1) {
			$CACHE_ON=0;
		}

		// Reset
		cache_reset();

		// Check cache
		if ($et=check_cache()) {
			// Cache is valid: flush it!
			$size = strlen($et);
			$crc32 = crc32($et);
			print cache_flush($et, $size, $crc32);
//exec("/bin/echo \"$cache_debug_comments\" >> /tmp/out.txt");
			exit; 
		} else {
			cache_gc();

			// Start a new cache file
			ob_start("cache_end");
			ob_implicit_flush(0);
		}
//exec("/bin/echo \"$cache_debug_comments\" >> /tmp/out.txt");
	}

	/* Are we capable of receiving gzipped data ?
	 *
	 * Returns the encoding that is accepted. Maybe additional check for Mac ?
	 */
	function cache_get_encoding() { 
		global $HTTP_ACCEPT_ENCODING;
		if (headers_sent() || connection_aborted()) { 
			return false; 
		} 
		if (strpos($HTTP_ACCEPT_ENCODING,'x-gzip') !== false) {
			return "x-gzip"; 
		}
		if (strpos($HTTP_ACCEPT_ENCODING,'gzip') !== false) {
			return "gzip"; 
		}
		return false;
	}

	/* cache_flush()
	 *
	 * Responsible for final flushing everything.
	 * Sets ETag-headers and returns "Not modified" when possible
	 *
	 * When ETag doesn't match (or is invalid), it is tried to send
	 * the gzipped data. If that is also not possible, we sadly have to
	 * uncompress...
	 */
	function cache_flush($contents, $size, $crc32)
	{
		global $HTTP_SERVER_VARS,$CACHE_USE_GZIP;

		// First check if we can send last-modified
		$myETag = "\"jpd-$crc32.$size\"";
		header("ETag: $myETag");
		$foundETag = stripslashes($HTTP_SERVER_VARS["HTTP_IF_NONE_MATCH"]);
		$ret = NULL;
		
		if (strstr($foundETag, $myETag)) {
			// Not modified!
			header("HTTP/1.0 304");
		} else {
			if ($CACHE_USE_GZIP) {
				$ENCODING = cache_get_encoding(); 
				if ($ENCODING) { 
					// compressed output
					$contents = gzcompress($contents, 9);
					header("Content-Encoding: $ENCODING");
					$ret =  "\x1f\x8b\x08\x00\x00\x00\x00\x00";
					$ret .= substr($contents, 0, strlen($contents) - 4);
					$ret .= pack('V',$crc32);
					$ret .= pack('V',$size);
				} else {
					// Darn, we need to uncompress :(
					$ret = $contents;
				}
			} else {
				$ret=$contents;
			}
		}
		return $ret;
	}

	/**
	 *	cache_end()
	 *
	 *	This one is called by the callback-funtion of the ob_start
	 */
	function cache_end($contents)
	{
		cache_debug("Callback happened");
		global $size, $cache_file, $crc32;

		$size = strlen($contents);
		$crc32 = crc32($contents);

		// write the cache
		cache_write($cache_file,$contents);
		
		// Return flushed data
		return cache_flush($contents, $size, $crc32);
	}

	cache_start();
?>
