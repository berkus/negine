<?
//---------------------------------------------------------------------------------------
// GLOBALS
//---------------------------------------------------------------------------------------

// win32 localhost workaround  -- berkus 2001.08.02 01:26 YEKST
if( strstr($SCRIPT_NAME, "\\\\") )
{
   $PHP_SELF = substr($SERVER_URL,0,-1).str_replace("\\\\", "/", $SCRIPT_NAME);
}

function numeral($n)
{
   $n = substr($n, -1, 1);
   if( $n == 1 ) $r = "а";
   if( $n >= 2 && $n <= 4 ) $r = "ы";
   return $r;
}

require("db.php");
$db = new DB;
?>