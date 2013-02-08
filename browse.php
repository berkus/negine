<?
//---------------------------------------------------------------------------------------
// BROWSE FUNCTIONS
//---------------------------------------------------------------------------------------

function browse_pages()
{
   global $db;

   output_header( "menu.php?action=" );
      
   $q = $db->query_array("select name,layout from page_names");
   $n = count($q);

   echo "<b>$n страниц".numeral($n)."</b><br>";
   $n = 1;

   echo "<table>";
   while( $row = array_shift($q) )
   {
      echo "<tr>";
      echo "<td>Страница $n (ID $row[layout])</td>";
      echo "<td>--</td><td>$row[name]</td><td>--</td>";
      echo "<td bgcolor=#eeeeff><a href='?action=browse_page&page=$row[layout]'><img src='viewtable.gif' width=32 height=32 border=0 alt='Просмотр'></a></td>";
      echo "<td bgcolor=#eeeeff><a href='?action=remove_page&page=$row[layout]'><img src='droptable.gif' width=32 height=32 border=0 alt='Удалить'></a></td>";
      echo "</tr>";
      $n++;
   }
   echo "</table>";

   echo "<hr>";
   echo "Страницы - объединяющие структуры для отображения информации из БД средствами движка nEgine.<br>";
   echo "Каждая страница состоит из таблиц и имеет уникальный идентификатор и текстовое имя (для удобства).<br>";
   echo "Для отображения страницы вызовите из своей PHP программы <b>negine_display_page(<i>page id</i>)</b>.<br><br>";
   
   output_footer();
}

function browse_page($page,$op)
{
   global $PHP_SELF, $db;

   switch($op)
   {
      case "modify_name": // modify page name
         global $pagename;
         $db->query_raw("update page_names set name='$pagename' where layout=$page");
         exit(header("Location: $PHP_SELF?action=browse_page&page=$page"));
         break;

      case "move_up": // move table up
         global $pos;
         $db->query_raw("lock tables page_layouts write");
         $db->query_raw("update page_layouts set posn=posn+1 where layout=$page and posn>=$pos-1");
         $db->query_raw("update page_layouts set posn=$pos-1 where layout=$page and posn=$pos+1");
         $db->query_raw("update page_layouts set posn=posn-1 where layout=$page and posn>$pos+1");
         $db->query_raw("unlock tables");
         exit(header("Location: $PHP_SELF?action=browse_page&page=$page"));
         break;

      case "move_down":
         global $pos;
         $db->query_raw("lock tables page_layouts write");
         $db->query_raw("update page_layouts set posn=posn-1 where layout=$page and posn<=$pos+1");
         $db->query_raw("update page_layouts set posn=$pos+1 where layout=$page and posn=$pos-1");
         $db->query_raw("update page_layouts set posn=posn+1 where layout=$page and posn<$pos-1");
         $db->query_raw("unlock tables");
         exit(header("Location: $PHP_SELF?action=browse_page&page=$page"));
         break;
            
      case "show":
         global $table, $posn;
         $db->query_raw("update page_layouts set visible=1 where layout=$page and fttable=$table and posn=$posn");
         exit(header("Location: $PHP_SELF?action=browse_page&page=$page"));
         break;
         
      case "hide":
         global $table, $posn;
         $db->query_raw("update page_layouts set visible=0 where layout=$page and fttable=$table and posn=$posn");
         exit(header("Location: $PHP_SELF?action=browse_page&page=$page"));
         break;
         
      default: 
         $name = $db->query("select name from page_names where layout=$page");
         $name = $name[name];

         $q = $db->query_array("select fttable,posn,visible from page_layouts where layout=$page order by posn asc");
         $n = count($q);

         output_header( "menu.php?action=browse_page&page=$page" );
         
         echo "<b>Страница $name, $n таблиц".numeral($n)."</b><br>";
         $n = 1;
   
         echo "<form><input type=hidden name=action value=browse_page><input type=hidden name=page value=$page><input type=hidden name=op value=modify_name>";
         echo "Имя страницы: <input type=text name=pagename size=40 value='$name'> <input type=image name=submit src=modify_btn.gif></form>";

         $maxp = $db->query("select max(posn) as maxp from page_layouts where layout=$page");
         $maxp = $maxp[maxp];

         echo "<table>";
         while( $row = array_shift($q) )
         {
            echo "<tr>";
            echo "<td><img src='";
            if( !$row[visible] ) echo "invisible_";
            echo "table.gif' width=32 height=32 alt='";
            if( !$row[visible] ) echo "не";
            echo "видимая таблица'></td>";
            echo "<td>Таблица $n (ID $row[fttable])</td>";
            echo "<td>--</td>";
            echo "<td bgcolor=#eeeeff><a href='?action=browse_table&table=$row[fttable]&parent=$page'><img src='viewtable.gif' width=32 height=32 border=0 alt='Просмотр'></a></td>";
            echo "<td bgcolor=#eeeeff><a href='?action=edit_table&table=$row[fttable]&parent=$page'><img src='edittable.gif' width=32 height=32 border=0 alt='Правка'></a></td>";
            echo "<td bgcolor=#eeeeff><a href='?action=remove_table&table=$row[fttable]&parent=$page'><img src='droptable.gif' width=32 height=32 border=0 alt='Удалить'></a></td>";
            
            if( $row[visible] ) echo "<td bgcolor=#eeeeff><a href='?action=browse_page&op=hide&page=$page&table=$row[fttable]&posn=$row[posn]'><img src='hidetable.gif' width=32 height=32 border=0 alt='Скрыть'></a></td>";
            else                echo "<td bgcolor=#eeeeff><a href='?action=browse_page&op=show&page=$page&table=$row[fttable]&posn=$row[posn]'><img src='showtable.gif' width=32 height=32 border=0 alt='Показать'></a></td>";
            
            echo "<td bgcolor=#eeeeff>";
            if($row[posn] > 1) echo "<a href='?action=browse_page&op=move_up&page=$page&pos=$row[posn]'><img src='moveup.gif' width=32 height=32 alt='Переместить таблицу вверх' border=0></a>";
            echo "</td><td bgcolor=#eeeeff>";
            if($row[posn] < $maxp) echo "<a href='?action=browse_page&op=move_down&page=$page&pos=$row[posn]'><img src='movedown.gif' width=32 height=32 alt='Переместить таблицу вниз' border=0></a>";
            echo "</td>";
            
            echo "<td bgcolor=#eeeeff><a href='?action=copy_table&table=$row[fttable]&parent=$page'><img src='copytable.gif' width=32 height=32 alt='Копировать таблицу' border=0></a></td>";
            
            echo "</tr>";
            $n++;
         }
         echo "</table>";

         echo "<hr><b>Просмотр страницы</b><br>";
         negine_display_page($page);
         echo "<hr>";
         
         output_footer();
         break;
   }
}

function browse_table($table,$parent)
{
   global $encloses, $db;
   
   output_header( "menu.php?action=browse_table&table=$table&parent=$parent" );
   
   $q = $db->query("select count(id) as n from format_cells where tid=$table");
   $n = $q[n];

   echo "<b>Таблица из $n ячеек</b><br>";

   $r = $db->query("select rtable,width,bgcolor,align,padding,spacing,border from format_tables where id=$table");

   echo "<table><tr>";
   echo "<td bgcolor=#eeeeff>|&nbsp;rtable</td><td>$r[rtable]</td>";
   echo "<td bgcolor=#eeeeff>|&nbsp;width</td><td>$r[width]</td>";
   echo "<td bgcolor=#eeeeff>|&nbsp;bgcolor</td><td>$r[bgcolor]</td>";
   echo "<td bgcolor=#eeeeff>|&nbsp;align</td><td>$r[align]</td>";
   echo "<td bgcolor=#eeeeff>|&nbsp;cellpadding</td><td>$r[padding]</td>";
   echo "<td bgcolor=#eeeeff>|&nbsp;cellspacing</td><td>$r[spacing]</td>";
   echo "<td bgcolor=#eeeeff>|&nbsp;border</td><td>$r[border]</td>";
   echo "<td bgcolor=#eeeeff>|</td>";
   echo "</tr></table>";

   echo "<hr><b>Просмотр таблицы</b><br>";
   negine_display_table($table);
   echo "<hr>";
   
   output_footer();
}
?>