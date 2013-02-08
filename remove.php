<?
//---------------------------------------------------------------------------------------
// REMOVAL FUNCTIONS
//---------------------------------------------------------------------------------------

function internal_remove_table($table)
{
   // find all cells that belong to this table
   // traverse them, finding and destroying their contents and then destroying cells themselves
   // then destroy the table
   global $db;
   
   $cells = $db->query_array("select id from format_cells where tid=$table");
   while( $cell = array_shift($cells) )
   {
      $db->query_raw("delete from cell_content where cid=$cell[id]");
      $db->query_raw("delete from format_cells where id=$cell[id]");
   }
   $db->query_raw("delete from format_tables where id=$table");
   $db->query_raw("delete from page_layouts where fttable=$table");
}

// remove table and all its subcontained cells and their contents
function remove_table($table,$parent)
{
   global $submit, $noremove, $PHP_SELF;

   if( $noremove )
   {
      exit(header("Location: $PHP_SELF?action=browse_page&page=$parent"));
   }

   if( $submit )
   {
      internal_remove_table($table);
      exit(header("Location: $PHP_SELF?action=browse_page&page=$parent"));
   }

   output_header();
   
   echo "<b>Удалить таблицу (ID $table)</b><br>";
   echo "<form><input type=hidden name=action value=remove_table><input type=hidden name=table value=$table><input type=hidden name=parent value=$parent>";
   echo "<table>";
   echo "<tr><td colspan=2 align=center><b>Вы действительно хотите удалить таблицу и ВСЕ ее содержимое?</b></td></tr>";
   echo "<tr><td colspan=2 align=center><b>Удаленные данные будет НЕвозможно восстановить.</b></td></tr>";
   echo "<tr><td align=center><input type=submit name=submit value='[ удалить ]'></td>";
   echo "<td align=center><input type=submit name=noremove value='[ НЕ удалять ]'></td></tr>";
   echo "</table>";
   echo "</form>";

   echo "<br><br><a href='?action='>В главное меню</a><br>";
   
   output_footer();
}

// remove page and all its contained sets and their contents
function remove_page($page)
{
   global $submit, $noremove, $PHP_SELF, $db;

   if( $noremove )
   {
      exit(header("Location: $PHP_SELF"));
   }

   if( $submit )
   {
      // delete all contained sets
      // then delete page itself
      $tables = $db->query_array("select fttable from page_layouts where layout=$page");
      while( $table = array_shift($tables) )
      {
         internal_remove_table($table[fttable]);
      }
      $db->query_raw("delete from page_layouts where layout=$page");
      $db->query_raw("delete from page_names where layout=$page");
      
      exit(header("Location: $PHP_SELF"));
   }

   output_header();
   
   echo "<b>Удалить страницу (ID $page)</b><br>";
   echo "<form><input type=hidden name=action value=remove_page><input type=hidden name=page value=$page>";
   echo "<table>";
   echo "<tr><td colspan=2 align=center><b>Вы действительно хотите удалить страницу и ВСЕ ее содержимое?</b></td></tr>";
   echo "<tr><td colspan=2 align=center><b>Удаленные данные будет НЕвозможно восстановить.</b></td></tr>";
   echo "<tr><td align=center><input type=submit name=submit value='[ удалить ]'></td>";
   echo "<td align=center><input type=submit name=noremove value='[ НЕ удалять ]'></td></tr>";
   echo "</table>";
   echo "</form>";

   echo "<br><br><a href='?action='>В главное меню</a><br>";
   
   output_footer();
}
?>
