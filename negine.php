<? // nEgine table engine (c) 2001 madfire
   // connection to database should be OPEN! and referenced through standard DB '$db' object

// display whole page
function negine_display_page($page)
{
   global $db;
   
   $r = $db->query_array("select fttable,visible from page_layouts where layout=$page order by posn asc");
   while( $table = array_shift($r) )
   {                                            
      if( $table[visible] ) negine_display_table($table[fttable]);
   }
}

// display whole table
function negine_display_table($table)
{
   global $db;
   
   $v = $db->query("select rtable,width,bgcolor,align,padding,spacing,border from format_tables where id=$table");

   $wd = $v[width]   != "default" ? " width='$v[width]'"       : "";
   $bg = $v[bgcolor] != "default" ? " bgcolor='$v[bgcolor]'"   : "";
   $pd = $v[padding] != -1        ? " cellpadding=$v[padding]" : "";
   $sp = $v[spacing] != -1        ? " cellspacing=$v[spacing]" : "";
   $br = $v[border]  != -1        ? " border=$v[border]"       : "";
   $al = $v[align]   != "default" ? "<div align='$v[align]'>"  : "";
   $cl = $v[align]   != "default" ? "</div>"                   : "";

   $defaults[rtable] = $v[rtable];   
   
   echo "$al<table$wd$bg$pd$sp$br>";
   negine_display_cells($table, $defaults);
   echo "</td></tr></table>$cl";
}

// display all cells of a table with provision for simple callback hooks
function negine_display_cells($table, $contents_default, $callback_pre = "", $callback_in = "", $callback_post = "")
{
   global $db;
   
   $prevrow = -1;
   $prevcol = -1;

   $q = $db->query_array("select * from format_cells where tid=$table order by row,col asc");
   while( $r = array_shift($q) )
   {
      $newrow = $r[row] <> $prevrow;
      $newcol = $r[col] <> $prevcol;

      if( $newrow )
         if( $prevrow != -1 )
            echo "</tr><tr>";
         else
            echo "<tr>";

      $rs  = $r[rowspan] != 1         ? " rowspan=$r[rowspan]"   : "";
      $cs  = $r[colspan] != 1         ? " colspan=$r[colspan]"   : "";
      $aln = $r[align]   != "left"    ? " align=$r[align]"       : "";
      $vln = $r[valign]  != "top"     ? " align=$r[valign]"      : "";
      $bgc = $r[bgcolor] != "default" ? " bgcolor='$r[bgcolor]'" : "";

      if($callback_pre) // pre acts before opening td
         $callback_pre($newrow,$newcol,$r);
         
      if( $newcol || $newrow )
         echo "<td$rs$cs$aln$vln$bgc>";

      if($callback_in) // in acts right after opening td
         $callback_in($newrow,$newcol,$r);

      negine_display_cell_contents( $r[id], $contents_default );
      
      if($callback_post) // post acts right before closing td
         $callback_post( $newrow, $newcol, $r );
         
      echo "</td>";
      
      $prevrow = $r[row];
      $prevcol = $r[col];
   }
}

// display all cell contents with substitutions of default values when necessary
function negine_display_cell_contents($cid, $contents_default)
{
   global $db;
   
   $contents = $db->query_array("select * from cell_content where cid=$cid order by posn asc");
   while( $content = array_shift($contents) )
   {
      $content[rtable] = $content[rtable] ? $content[rtable] : $contents_default[rtable];
      negine_display_content($content);
   }
}      

// display contents of a single cell
function negine_display_content($content)
{
   global $EDITOR_REF, $encloses;
   
   switch($content[type])
   {
      case "text":
         echo $content[text];
         break;

      case "data":
         echo retrieve($content[text], $content[rtable], $content[rname], $content[rcond], $content[rid]);
         break;

      case "img":
         echo "<img src=$EDITOR_REF$content[text]>";
         break;

      case "dimg":
         echo "<img src=$EDITOR_REF".retrieve($content[text], $content[rtable], $content[rname], $content[rcond], $content[rid]).">";
         break;

      case "href":
         if(!$content[enclose]) echo "<font color=red><b>href: ОШИБКА В ЯЧЕЙКЕ</b></font><br>";
         else
            if( $encloses[$content[enclose]] ) //already open, now close
            {
               echo "</a>";
               $encloses[$content[enclose]] = 0;
            }
            else
            {
               echo "<a href=$EDITOR_REF$content[text]>";
               $encloses[$content[enclose]] = 1; // mark as open
            }
         break;

      case "dref":
         if(!$content[enclose]) echo "<font color=red><b>dref: ОШИБКА В ЯЧЕЙКЕ</b></font><br>";
         else
            if( $encloses[$content[enclose]] ) //already open, now close
            {
               echo "</a>";
               $encloses[$content[enclose]] = 0;
            }
            else
            {
               echo "<a href=$EDITOR_REF".retrieve($content[text], $content[rtable], $content[rname], $content[rcond], $content[rid]).">";
               $encloses[$content[enclose]] = 1; // mark as open
            }
         break;

      case "table":
         negine_display_table($content[enclose]);
         break;
         
      default:
         echo "<font color=red><b>default: ОШИБКА В ЯЧЕЙКЕ</b></font><br>";
         break;
   }
}

// retrieve value from database
function retrieve($what, $from, $rname, $rcond, $rid)
{
   global $db;
   
   if( not_numeric($rid) ) $rid = "'".$rid."'"; // prevent non-numeric IDs to be mistreated by mysql
   $query = "select $what from $from where $rname $rcond $rid";
   //echo "<div align=center>retrieve query <b>$query</b></div>";
   $value = $db->query($query);
   return $value[$what];
}

// return true if argument is not numeric
function not_numeric($x)
{
   return !ereg("^([\+\-]{0,1}[0-9.]+([Ee][0-9]*){0,1}){1}$", $x);
}
?>