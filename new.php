<?
//---------------------------------------------------------------------------------------
// CREATION FUNCTIONS
//---------------------------------------------------------------------------------------
// TODO:
// - add selection dropdown for bgcolor + alternate input field in new_table()

function new_table($parent)
{
   global $submit, $PHP_SELF, $db;
   global $rtable, $width, $bgcolor, $padding, $spacing, $border;

   if( $submit )
   {
      // TODO: sanity checks
      $insert = $db->query_raw("insert into format_tables (rtable,width,bgcolor,padding,spacing,border) values ('$rtable','$width','$bgcolor',$padding,$spacing,$border)");
      $id = $db->insert_id( $insert );
      $posn = $db->query("select max(posn) as posn from page_layouts where layout=$parent");
      $posn = $posn[posn] + 1;
      $db->query_raw("insert into page_layouts (layout,posn,fttable) values ($parent,$posn,$id)");
      exit(header("Location: $PHP_SELF?action=browse_page&page=$parent"));
   }

   output_header();

   echo "<b>Добавить таблицу</b><br>";
   echo "<form><input type=hidden name=action value=new_table><input type=hidden name=parent value=$parent>";
   echo "<table>";
   echo "<tr><td bgcolor=#eeeeff>rtable</td><td><input type=text size=10 name=rtable value='products'> текст</td><td bgcolor=#eeeeff>имя источника выборки</td></tr>";
   echo "<tr><td bgcolor=#eeeeff>width</td><td><input type=text name=width size=10 value='default'> текст</td><td bgcolor=#eeeeff>ширина таблицы</td></tr>";
   echo "<tr><td bgcolor=#eeeeff>bgcolor</td><td><input type=text name=bgcolor size=10 value='default'> текст</td><td bgcolor=#eeeeff>цвет фона таблицы</td></tr>";
   echo "<tr><td bgcolor=#eeeeff>cellpadding</td><td><input type=text name=padding size=10 value=-1> число</td><td bgcolor=#eeeeff>пустое пространство по контуру каждой ячейки</td></tr>";
   echo "<tr><td bgcolor=#eeeeff>cellspacing</td><td><input type=text name=spacing size=10 value=-1> число</td><td bgcolor=#eeeeff>пустое пространство между ячейками</td></tr>";
   echo "<tr><td bgcolor=#eeeeff>border</td><td><input type=text name=border size=10 value=-1> число</td><td bgcolor=#eeeeff>размер контура таблицы</td></tr>";
   echo "<tr><td bgcolor=#eeeeff colspan=2 align=center><input type=image name=submit src=add_btn.gif></td></tr>";
   echo "</table></form>";

   output_footer();
}

function new_page()
{
   global $PHP_SELF, $submit, $db;
   global $pagename;

   if( $submit )
   {
      $pg = $db->query("select max(layout) as layout from page_layouts");
      $pg = $pg[layout] + 1;

      $db->query_raw("insert into page_names (layout,name) values ($pg,'$pagename')");

      exit(header("Location: $PHP_SELF"));
   }

   output_header();

   echo "<b>Добавление страницы</b><br>";
   echo "<form><input type=hidden name=action value=new_page>";
   echo "<table>";
   echo "<tr><td bgcolor=#eeeeff>имя страницы</td><td><input type=text size=40 name=pagename value=''> текст</td>";
   echo "<td bgcolor=#eeeeff><input type=image name=submit src=add_btn.gif></td></tr>";
   echo "</table></form>";

   output_footer();
}
?>