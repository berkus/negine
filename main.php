<? // table editor (c) 2001 madfire.net

function output_header( $loc = "" )
{              
   //<head><title>Редактор таблиц nEgine</title></head>
   echo "<html><body bgcolor=#ffffff text=#000000>";
   if( $loc != "" ) echo "<script>parent.frames[1].location='$loc';</script>";
}

function output_footer()
{
   echo "<div align=center><font size=-1 color=#bbbbcc>powered by nEgine &copy; 2001 <a href='mailto:berk@madfire.net'>madfire</a></font></div>";
   echo "</body></html>";
}

include("globals.inc.php");
include("negine.php");
include("browse.php");
include("new.php");
include("edit.php");
include("remove.php");

//---------------------------------------------------------------------------------------
// MAIN
//---------------------------------------------------------------------------------------

switch($action)
{
   case "new_page": // [&submit]
      new_page();
      break;

   case "new_table": // parent=containerpage[&submit]
      new_table($parent);
      break;

   case "browse_page": // page=pageid&op=pageoperation
      browse_page($page,$op);
      break;

   case "browse_table": // table=tableid&parent=parentpage
      browse_table($table,$parent);
      break;

   case "edit_table": // table=tableid&op=tableoperation&cell=editedcellid&parent=parentpage
      edit_table($table,$parent,$op,$cell);
      break;

   case "edit_cell": // cell=cellid&op=cellaction&parent=containingtable[&submit]
      edit_cell($cell,$op,$parent);
      break;
      
   case "remove_table": // table=removedtable&parent=containingpage[&submit]
      remove_table($table,$parent);
      break;

   case "remove_page": // page=removedpage[&submit]
      remove_page($page);
      break;
  
   case "copy_table": // table=copiedtable&parent=prevpage
      copy_table($table,$parent);
      break;
      
   default:
      browse_pages();
      break;
}
?>