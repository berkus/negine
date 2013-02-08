<?
echo "<html><body bgcolor=#eeeeff>";

// output several different menus depending on the context
switch( $action )
{
   case "browse_page":
      echo "<a href='main.php?action=' target=main><img src='mainmenu.gif' width=32 height=32 border=0 alt='Главное меню'></a>";
      echo "<a href='main.php?action=new_table&parent=$page' target=main><img src='newpage.gif' width=32 height=32 border=0 alt='Создать таблицу'></a>";
      break;
      
   case "browse_table":
      echo "<a href='main.php?action=' target=main><img src='mainmenu.gif' width=32 height=32 border=0 alt='Главное меню'></a>";
      echo "<a href='main.php?action=browse_page&page=$parent' target=main><img src='page.gif' width=32 height=32 border=0 alt='Страница $parent'></a>";
      echo "<a href='main.php?action=edit_table&table=$table' target=main><img src='edittable.gif' width=32 height=32 border=0 alt='Правка таблицы'></a>";
      break;   
      
   case "edit_table":
      echo "<a href='main.php?action=' target=main><img src='mainmenu.gif' width=32 height=32 border=0 alt='Главное меню'></a>";
      echo "<a href='main.php?action=browse_page&page=$parent' target=main><img src='page.gif' width=32 height=32 border=0 alt='Страница $parent'></a>";
      echo "<a href='main.php?action=browse_table&table=$table' target=main><img src='viewtable.gif' width=32 height=32 border=0 alt='Предварительный просмотр'></a>";
      break;
      
   case "edit_cell":
      echo "<a href='main.php?action=' target=main><img src='mainmenu.gif' width=32 height=32 border=0 alt='Главное меню'></a>";
      echo "<a href='main.php?action=edit_table&table=$parent&cell=$cell#current' target=main><img src='edittable.gif' width=32 height=32 border=0 alt='Правка таблицы'></a>";
      break;      
   
   case "copy_table":
      echo "<a href='main.php?action=' target=main><img src='mainmenu.gif' width=32 height=32 border=0 alt='Главное меню'></a>";
      echo "<a href='main.php?action=browse_page&page=$parent' target=main><img src='page.gif' width=32 height=32 border=0 alt='Страница $parent'></a>";
      break;
      
   default: // browse_pages
      echo "<a href='main.php?action=new_page' target=main><img src='newpage.gif' width=32 height=32 border=0 alt='Создать страницу'></a>";
      break;
}

echo "</body></html>";
?>