<html>

<?php // $Id: validate-form.php 7181 2009-10-05 14:25:48Z vargenau $


/*
  This file is part of, or distributed with, libXMLRPC - a C library for
  xml-encoded function calls.

  Author: Dan Libby (dan@libby.com)
  Epinions.com may be contacted at feedback@epinions-inc.com
*/

/*
  Copyright 2001 Epinions, Inc.

  Subject to the following 3 conditions, Epinions, Inc.  permits you, free
  of charge, to (a) use, copy, distribute, modify, perform and display this
  software and associated documentation files (the "Software"), and (b)
  permit others to whom the Software is furnished to do so as well.

  1) The above copyright notice and this permission notice shall be included
  without modification in all copies or substantial portions of the
  Software.

  2) THE SOFTWARE IS PROVIDED "AS IS", WITHOUT ANY WARRANTY OR CONDITION OF
  ANY KIND, EXPRESS, IMPLIED OR STATUTORY, INCLUDING WITHOUT LIMITATION ANY
  IMPLIED WARRANTIES OF ACCURACY, MERCHANTABILITY, FITNESS FOR A PARTICULAR
  PURPOSE OR NONINFRINGEMENT.

  3) IN NO EVENT SHALL EPINIONS, INC. BE LIABLE FOR ANY DIRECT, INDIRECT,
  SPECIAL, INCIDENTAL OR CONSEQUENTIAL DAMAGES OR LOST PROFITS ARISING OUT
  OF OR IN CONNECTION WITH THE SOFTWARE (HOWEVER ARISING, INCLUDING
  NEGLIGENCE), EVEN IF EPINIONS, INC.  IS AWARE OF THE POSSIBILITY OF SUCH
  DAMAGES.

*/

// some utilities
include("xmlrpc_utils.php");

// ensure extension is loaded.
xu_load_extension();


function do_test_case($title, $desc, $xml) {
   if($desc) {
      $desc = "<p>$desc</p>";
   }
echo <<< END
<h1>TEST: $title</h1>
$desc
<h3>Enter your xmlrpc query</h3>
<form method="get" action="validate.php">
<textarea rows='20' cols='80' name='xml'>
$xml
</textarea><br>

<b>encoding</b><br>
<input type='text' name='encoding' value='iso-8859-1'>
<br>

<b>output type</b><br>
<input type='radio' name='output_type' value='xml' checked>xml
<input type='radio' name='output_type' value='php'>php
<br>
<br>
<i>These options apply to xml output type only.</i><br>

<b>output version</b><br>
<input type='radio' name='version' value='xmlrpc' checked>xmlrpc
<input type='radio' name='version' value='simple'>simple
<br>

<b>output verbosity</b><br>
<input type='radio' name='verbosity' value='pretty' checked>pretty
<input type='radio' name='verbosity' value='newlines_only'>newlines only
<input type='radio' name='verbosity' value='no_white_space'>no white space
<br>

<b>output escaping</b><br>
<input type='checkbox' name='escaping[]' value='markup' checked>markup
<input type='checkbox' name='escaping[]' value='cdata'>cdata
<input type='checkbox' name='escaping[]' value='non-ascii'>non-ascii
<input type='checkbox' name='escaping[]' value='non-print'>non-print
<br>

<input type='submit' value="Get your fresh hot xmlrpc!">
<input type='hidden' name='disp' value='html'>
</form>
<hr>
END;

}


$test_cases = array(
   array(
      title => "custom user input",
      desc => "Enter your own xml here if you have a specific xmlrpc query you would like to perform.",
      xml => ""),
array(
   title => "arrayOfStructsTest",
   desc =>
   "This handler takes a single parameter, an array of structs, each of which contains at least three elements
    named moe, larry and curly, all <i4>s. Your handler must add all the struct elements named curly and
    return the result.",
   xml =>
"<?xml version='1.0'?>
<methodCall>
<!-- expected answer = -166 -->
<methodName>validator1.arrayOfStructsTest</methodName>
<params>
<param>
<value><array>
<data>
<value>
<struct>
<member>
<name>curly</name>
<value>
<i4>-79</i4>
</value>
</member>
<member>
<name>larry</name>
<value>
<i4>34</i4>
</value>
</member>
<member>
<name>moe</name>
<value>
<i4>19</i4>
</value>
</member>
</struct>
</value>
<value>
<struct>
<member>
<name>curly</name>
<value>
<i4>-33</i4>
</value>
</member>
<member>
<name>larry</name>
<value>
<i4>36</i4>
</value>
</member>
<member>
<name>moe</name>
<value>
<i4>40</i4>
</value>
</member>
</struct>
</value>
<value>
<struct>
<member>
<name>curly</name>
<value>
<i4>-30</i4>
</value>
</member>
<member>
<name>larry</name>
<value>
<i4>52</i4>
</value>
</member>
<member>
<name>moe</name>
<value>
<i4>1</i4>
</value>
</member>
</struct>
</value>
<value>
<struct>
<member>
<name>curly</name>
<value>
<i4>-7</i4>
</value>
</member>
<member>
<name>larry</name>
<value>
<i4>82</i4>
</value>
</member>
<member>
<name>moe</name>
<value>
<i4>35</i4>
</value>
</member>
</struct>
</value>
<value>
<struct>
<member>
<name>curly</name>
<value>
<i4>0</i4>
</value>
</member>
<member>
<name>larry</name>
<value>
<i4>54</i4>
</value>
</member>
<member>
<name>moe</name>
<value>
<i4>82</i4>
</value>
</member>
</struct>
</value>
<value>
<struct>
<member>
<name>curly</name>
<value>
<i4>-5</i4>
</value>
</member>
<member>
<name>larry</name>
<value>
<i4>5</i4>
</value>
</member>
<member>
<name>moe</name>
<value>
<i4>66</i4>
</value>
</member>
</struct>
</value>
<value>
<struct>
<member>
<name>curly</name>
<value>
<i4>-10</i4>
</value>
</member>
<member>
<name>larry</name>
<value>
<i4>65</i4>
</value>
</member>
<member>
<name>moe</name>
<value>
<i4>53</i4>
</value>
</member>
</struct>
</value>
<value>
<struct>
<member>
<name>curly</name>
<value>
<i4>-2</i4>
</value>
</member>
<member>
<name>larry</name>
<value>
<i4>66</i4>
</value>
</member>
<member>
<name>moe</name>
<value>
<i4>43</i4>
</value>
</member>
</struct>
</value>
</data>
</array></value>
</param>
</params>
</methodCall>" ),

      array(
      title => "countTheEntities",
      desc => "This handler takes a single parameter, a string, that contains any number of predefined entities, namely <,
               >, &, ' and \".

               Your handler must return a struct that contains five fields, all numbers: ctLeftAngleBrackets,
               ctRightAngleBrackets, ctAmpersands, ctApostrophes, ctQuotes.

               To validate, the numbers must be correct",
      xml =>
"<?xml version='1.0'?>
<methodCall>
<methodName>validator1.countTheEntities</methodName>
<params>
<param>
<value>b&amp;amp;htj&amp;gt;q&amp;lt;e&amp;gt;ow&amp;lt;&amp;gt;&amp;lt;a&amp;quot;&amp;gt;&amp;quot;&amp;gt;m&amp;amp;&amp;lt;y&amp;gt;&amp;gt;g&amp;lt;&amp;quot;kf&amp;amp;nup&amp;gt;&amp;amp;lsz&amp;amp;lt;xi&amp;amp;d&amp;quot;&amp;lt;&amp;apos;crv&amp;lt;</value>
</param>
</params>
</methodCall>"),
   array(
      title => "easyStructTest",
      desc => "This handler takes a single parameter, a struct, containing at least three elements named moe, larry and curly, all <i4>s. Your handler must add the three numbers and return the result.",
      xml =>
"<?xml version='1.0'?>
<methodCall>
        <methodName>validator1.easyStructTest</methodName>
        <params>
                <param>
                        <value><struct>
                                <member>
                                        <name>curly</name>
                                        <value>
                                                <i4>-60</i4>
                                                </value>
                                        </member>
                                <member>
                                        <name>larry</name>
                                        <value>
                                                <i4>22</i4>
                                                </value>
                                        </member>
                                <member>
                                        <name>moe</name>
                                        <value>
                                                <i4>37</i4>
                                                </value>
                                        </member>
                                </struct></value>
                        </param>
                </params>
        </methodCall>"),
      array(
         title => "echoStructTest",
         desc => "This handler takes a single parameter, a struct. Your handler must return the struct.",
         xml =>
"<?xml version='1.0'?>
<methodCall>
        <methodName>validator1.echoStructTest</methodName>
        <params>
                <param>
                        <value><struct>
                                <member>
                                        <name>substruct0</name>
                                        <value>
                                                <struct>
                                                        <member>
                                                                <name>curly</name>
                                                                <value>
                                                                        <i4>-52</i4>
                                                                        </value>
                                                                </member>
                                                        <member>
                                                                <name>larry</name>
                                                                <value>
                                                                        <i4>55</i4>
                                                                        </value>
                                                                </member>
                                                        <member>
                                                                <name>moe</name>
                                                                <value>
                                                                        <i4>82</i4>
                                                                        </value>
                                                                </member>
                                                        </struct>
                                                </value>
                                        </member>
                                <member>
                                        <name>substruct1</name>
                                        <value>
                                                <struct>
                                                        <member>
                                                                <name>curly</name>
                                                                <value>
                                                                        <i4>-59</i4>
                                                                        </value>
                                                                </member>
                                                        <member>
                                                                <name>larry</name>
                                                                <value>
                                                                        <i4>70</i4>
                                                                        </value>
                                                                </member>
                                                        <member>
                                                                <name>moe</name>
                                                                <value>
                                                                        <i4>20</i4>
                                                                        </value>
                                                                </member>
                                                        </struct>
                                                </value>
                                        </member>
                                <member>
                                        <name>substruct2</name>
                                        <value>
                                                <struct>
                                                        <member>
                                                                <name>curly</name>
                                                                <value>
                                                                        <i4>-26</i4>
                                                                        </value>
                                                                </member>
                                                        <member>
                                                                <name>larry</name>
                                                                <value>
                                                                        <i4>1</i4>
                                                                        </value>
                                                                </member>
                                                        <member>
                                                                <name>moe</name>
                                                                <value>
                                                                        <i4>4</i4>
                                                                        </value>
                                                                </member>
                                                        </struct>
                                                </value>
                                        </member>
                                <member>
                                        <name>substruct3</name>
                                        <value>
                                                <struct>
                                                        <member>
                                                                <name>curly</name>
                                                                <value>
                                                                        <i4>-42</i4>
                                                                        </value>
                                                                </member>
                                                        <member>
                                                                <name>larry</name>
                                                                <value>
                                                                        <i4>73</i4>
                                                                        </value>
                                                                </member>
                                                        <member>
                                                                <name>moe</name>
                                                                <value>
                                                                        <i4>45</i4>
                                                                        </value>
                                                                </member>
                                                        </struct>
                                                </value>
                                        </member>
                                <member>
                                        <name>substruct4</name>
                                        <value>
                                                <struct>
                                                        <member>
                                                                <name>curly</name>
                                                                <value>
                                                                        <i4>-48</i4>
                                                                        </value>
                                                                </member>
                                                        <member>
                                                                <name>larry</name>
                                                                <value>
                                                                        <i4>16</i4>
                                                                        </value>
                                                                </member>
                                                        <member>
                                                                <name>moe</name>
                                                                <value>
                                                                        <i4>57</i4>
                                                                        </value>
                                                                </member>
                                                        </struct>
                                                </value>
                                        </member>
                                <member>
                                        <name>substruct5</name>
                                        <value>
                                                <struct>
                                                        <member>
                                                                <name>curly</name>
                                                                <value>
                                                                        <i4>-1</i4>
                                                                        </value>
                                                                </member>
                                                        <member>
                                                                <name>larry</name>
                                                                <value>
                                                                        <i4>77</i4>
                                                                        </value>
                                                                </member>
                                                        <member>
                                                                <name>moe</name>
                                                                <value>
                                                                        <i4>100</i4>
                                                                        </value>
                                                                </member>
                                                        </struct>
                                                </value>
                                        </member>
                                <member>
                                        <name>substruct6</name>
                                        <value>
                                                <struct>
                                                        <member>
                                                                <name>curly</name>
                                                                <value>
                                                                        <i4>-44</i4>
                                                                        </value>
                                                                </member>
                                                        <member>
                                                                <name>larry</name>
                                                                <value>
                                                                        <i4>2</i4>
                                                                        </value>
                                                                </member>
                                                        <member>
                                                                <name>moe</name>
                                                                <value>
                                                                        <i4>42</i4>
                                                                        </value>
                                                                </member>
                                                        </struct>
                                                </value>
                                        </member>
                                <member>
                                        <name>substruct7</name>
                                        <value>
                                                <struct>
                                                        <member>
                                                                <name>curly</name>
                                                                <value>
                                                                        <i4>-84</i4>
                                                                        </value>
                                                                </member>
                                                        <member>
                                                                <name>larry</name>
                                                                <value>
                                                                        <i4>18</i4>
                                                                        </value>
                                                                </member>
                                                        <member>
                                                                <name>moe</name>
                                                                <value>
                                                                        <i4>95</i4>
                                                                        </value>
                                                                </member>
                                                        </struct>
                                                </value>
                                        </member>
                                <member>
                                        <name>substruct8</name>
                                        <value>
                                                <struct>
                                                        <member>
                                                                <name>curly</name>
                                                                <value>
                                                                        <i4>-48</i4>
                                                                        </value>
                                                                </member>
                                                        <member>
                                                                <name>larry</name>
                                                                <value>
                                                                        <i4>93</i4>
                                                                        </value>
                                                                </member>
                                                        <member>
                                                                <name>moe</name>
                                                                <value>
                                                                        <i4>80</i4>
                                                                        </value>
                                                                </member>
                                                        </struct>
                                                </value>
                                        </member>
                                <member>
                                        <name>substruct9</name>
                                        <value>
                                                <struct>
                                                        <member>
                                                                <name>curly</name>
                                                                <value>
                                                                        <i4>-82</i4>
                                                                        </value>
                                                                </member>
                                                        <member>
                                                                <name>larry</name>
                                                                <value>
                                                                        <i4>58</i4>
                                                                        </value>
                                                                </member>
                                                        <member>
                                                                <name>moe</name>
                                                                <value>
                                                                        <i4>21</i4>
                                                                        </value>
                                                                </member>
                                                        </struct>
                                                </value>
                                        </member>
                                </struct></value>
                        </param>
                </params>
        </methodCall>"),
         array(
            title => "manyTypesTest",
            desc => "This handler takes six parameters, and returns an array containing all the parameters.",
            xml =>
"<?xml version='1.0'?>
<methodCall>
        <methodName>validator1.manyTypesTest</methodName>
        <params>
                <param>
                        <value><i4>24288</i4></value>
                        </param>
                <param>
                        <value><boolean>0</boolean></value>
                        </param>
                <param>
                        <value>Texas</value>
                        </param>
                <param>
                        <value><double>1762.0</double></value>
                        </param>
                <param>
                        <value><dateTime.iso8601>19040101T05:24:54</dateTime.iso8601></value>
                        </param>
                <param>
                        <value><base64>R0lGODlhFgASAJEAAP/////OnM7O/wAAACH5BAEAAAAALAAAAAAWABIAAAJAhI+py40zDIzujEDBzW0n74AaFGChqZUYylyYq7ILXJJ1BU95l6r23RrRYhyL5jiJAT/Ink8WTPoqHx31im0UAAA7</base64></value>
                        </param>
                </params>
        </methodCall>"),
   array(
      title => "moderateSizeArrayCheck",
      desc => "This handler takes a single parameter, which is an array containing between 100 and 200 elements. Each of the items is a string, your handler must return a string containing the concatenated text of the first and last elements.",
      xml =>
"<?xml version='1.0'?>
<methodCall>
        <methodName>validator1.moderateSizeArrayCheck</methodName>
        <params>
                <param>
                        <value><array>
                                <data>
                                        <value>Maine</value>
                                        <value>Nebraska</value>
                                        <value>Nebraska</value>
                                        <value>Kansas</value>
                                        <value>Tennessee</value>
                                        <value>Ohio</value>
                                        <value>Oregon</value>
                                        <value>Missouri</value>
                                        <value>Mississippi</value>
                                        <value>Michigan</value>
                                        <value>Pennsylvania</value>
                                        <value>Rhode Island</value>
                                        <value>Iowa</value>
                                        <value>Iowa</value>
                                        <value>Nebraska</value>
                                        <value>Washington</value>
                                        <value>Oregon</value>
                                        <value>Virginia</value>
                                        <value>Arizona</value>
                                        <value>Utah</value>
                                        <value>South Carolina</value>
                                        <value>Montana</value>
                                        <value>Tennessee</value>
                                        <value>Iowa</value>
                                        <value>Maryland</value>
                                        <value>Michigan</value>
                                        <value>Iowa</value>
                                        <value>Wisconsin</value>
                                        <value>Delaware</value>
                                        <value>Kansas</value>
                                        <value>North Dakota</value>
                                        <value>Massachusetts</value>
                                        <value>New Mexico</value>
                                        <value>Alaska</value>
                                        <value>Michigan</value>
                                        <value>Colorado</value>
                                        <value>Wisconsin</value>
                                        <value>South Dakota</value>
                                        <value>Vermont</value>
                                        <value>Virginia</value>
                                        <value>Arkansas</value>
                                        <value>Wisconsin</value>
                                        <value>Colorado</value>
                                        <value>Iowa</value>
                                        <value>Oregon</value>
                                        <value>Arizona</value>
                                        <value>Michigan</value>
                                        <value>Illinois</value>
                                        <value>Virginia</value>
                                        <value>Florida</value>
                                        <value>South Carolina</value>
                                        <value>Florida</value>
                                        <value>Arkansas</value>
                                        <value>Maryland</value>
                                        <value>Rhode Island</value>
                                        <value>Washington</value>
                                        <value>Georgia</value>
                                        <value>Arizona</value>
                                        <value>Iowa</value>
                                        <value>Louisiana</value>
                                        <value>Washington</value>
                                        <value>Nevada</value>
                                        <value>Alaska</value>
                                        <value>Hawaii</value>
                                        <value>New Hampshire</value>
                                        <value>West Virginia</value>
                                        <value>South Carolina</value>
                                        <value>Vermont</value>
                                        <value>Tennessee</value>
                                        <value>Connecticut</value>
                                        <value>Maine</value>
                                        <value>Louisiana</value>
                                        <value>Alaska</value>
                                        <value>Maine</value>
                                        <value>California</value>
                                        <value>Vermont</value>
                                        <value>Rhode Island</value>
                                        <value>West Virginia</value>
                                        <value>Colorado</value>
                                        <value>Delaware</value>
                                        <value>Massachusetts</value>
                                        <value>Rhode Island</value>
                                        <value>Nevada</value>
                                        <value>Oklahoma</value>
                                        <value>Nebraska</value>
                                        <value>Ohio</value>
                                        <value>Indiana</value>
                                        <value>Mississippi</value>
                                        <value>Mississippi</value>
                                        <value>Washington</value>
                                        <value>Tennessee</value>
                                        <value>Arkansas</value>
                                        <value>Alaska</value>
                                        <value>Rhode Island</value>
                                        <value>Oklahoma</value>
                                        <value>Massachusetts</value>
                                        <value>Connecticut</value>
                                        <value>Connecticut</value>
                                        <value>Virginia</value>
                                        <value>Nebraska</value>
                                        <value>Alabama</value>
                                        <value>Louisiana</value>
                                        <value>Colorado</value>
                                        <value>Vermont</value>
                                        <value>New Hampshire</value>
                                        <value>Ohio</value>
                                        <value>Nebraska</value>
                                        <value>Wisconsin</value>
                                        <value>Kansas</value>
                                        </data>
                                </array></value>
                        </param>
                </params>
        </methodCall>"),
      array(
         title => "nestedStructTest",
         desc => "This handler takes a single parameter, a struct, that models a daily calendar. At the top level, there is one
                  struct for each year. Each year is broken down into months, and months into days. Most of the days are
                  empty in the struct you receive, but the entry for April 1, 2000 contains a least three elements named moe,
                  larry and curly, all <i4>s. Your handler must add the three numbers and return the result.
                  <p>
                  Ken MacLeod: \"This description isn't clear, I expected '2000.April.1' when in fact it's '2000.04.01'. Adding
                  a note saying that month and day are two-digits with leading 0s, and January is 01 would help.\" Done.",
         xml =>
"<?xml version='1.0'?>
<methodCall>
<methodName>validator1.nestedStructTest</methodName>
<params>
<param>
<value><struct>
<member>
<name>2000</name>
<value>
<struct>
<member>
<name>03</name>
<value>
<struct>
<member>
<name>10</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>11</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>12</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>13</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>14</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>15</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>16</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>17</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>18</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>19</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>20</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>21</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>22</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>23</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>24</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>25</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>26</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>27</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>28</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>29</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>30</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>31</name>
<value>
<struct>
</struct>
</value>
</member>
</struct>
</value>
</member>
<member>
<name>04</name>
<value>
<struct>
<member>
<name>01</name>
<value>
<struct>
<member>
<name>curly</name>
<value>
	<i4>-23</i4>
	</value>
</member>
<member>
<name>larry</name>
<value>
	<i4>96</i4>
	</value>
</member>
<member>
<name>moe</name>
<value>
	<i4>17</i4>
	</value>
</member>
</struct>
</value>
</member>
<member>
<name>02</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>03</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>04</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>05</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>06</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>07</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>08</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>09</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>10</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>11</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>12</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>13</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>14</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>15</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>16</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>17</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>18</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>19</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>20</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>21</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>22</name>
<value>
<struct>
</struct>
</value>
</member>
<member>
<name>23</name>
<value>
<struct>
</struct>
</value>
</member>
</struct>
</value>
</member>
</struct>
</value>
</member>
</struct></value>
</param>
</params>
</methodCall>"),
         array(
            title => "simpleStructReturnTest",
            desc => "This handler takes one parameter, and returns a struct containing three elements, times10, times100 and times1000, the result of multiplying the number by 10, 100 and 1000.",
            xml =>
"<?xml version='1.0'?>
<methodCall>
<methodName>validator1.simpleStructReturnTest</methodName>
<params>
<param>
	<value><i4>55</i4></value>
	</param>
</params>
</methodCall>")

);

foreach($test_cases as $test_case) {
   do_test_case($test_case[title], $test_case[desc], $test_case[xml]);
}

?>

</html>
