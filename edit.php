<?
//---------------------------------------------------------------------------------------
// EDITING FUNCTIONS
//---------------------------------------------------------------------------------------
//
// TODO:
// in edit_table()
// - add toolbar with all cell operations
// + add row movement
// + add column movement
// + add row deletion
// + add column deletion
// - add column insertion
// - add cell movement
//
// extra operations to support on table cells:
// - split cell horizontally (copying contents)
// - split cell vertically   (copying contents)
// - split entire row horizontally  (copying contents??)
// - split entire column vertically (copying contents??)
// - union two adjacent cells horizontally    (+ keep cell content or not)
// - union two adjacent cells vertically      (+ keep cell content or not)
// - union two adjacent rows vertically       (+ keep cell content or not)
// - union two adjacent columns horizontally  (+ keep cell content or not)
//
//---------------------------------------------------------------------------------------

$EDITOR_REF = "./../../"; // for referencing outside images/files

function editor_callback_pre( $newrow, $newcol, $format_cells )
{
   if( $newrow )
   {
      echo "<td bgcolor=#aaeeee align=center valign=center>";
      echo "<a href='?action=edit_table&op=insrowb&table=$format_cells[tid]&row=$format_cells[row]'><img src='insrowb.gif' width=20 height=20 alt='вставить строку перед $format_cells[row]' border=0></a><br>";
      if( $format_cells[row] > 1 ) echo "<a href='?action=edit_table&op=rowup&table=$format_cells[tid]&row=$format_cells[row]'><img src='rowup.gif' width=20 height=20 alt='переместить строку вверх' border=0></a><br>";
      echo "$format_cells[row]<br>";
      echo "<a href='?action=edit_table&op=rowdown&table=$format_cells[tid]&row=$format_cells[row]'><img src='rowdown.gif' width=20 height=20 alt='переместить строку вниз' border=0></a><br>";
      echo "<a href='?action=edit_table&op=insrowa&table=$format_cells[tid]&row=$format_cells[row]'><img src='insrowa.gif' width=20 height=20 alt='вставить строку после $format_cells[row]' border=0></a>";
      echo "</td>";

      echo "<td bgcolor=#aaeeee align=center>";
      echo "<a href='?action=edit_table&op=delrow&table=$format_cells[tid]&row=$format_cells[row]'><img src='delrow.gif' width=20 height=20 alt='удалить строку $format_cells[row]' border=0></a><br>";
      echo "<a href='?action=edit_table&op=insert&table=$format_cells[tid]&row=$format_cells[row]&col=0'><img src='inscell.gif' width=20 height=20 alt='вставить ячейку в начало строки' border=0></a><br>";
      echo "<a href='?action=edit_table&op=copyrow&table=$format_cells[tid]&row=$format_cells[row]'><img src='copyrow.gif' width=20 height=20 alt='копировать строку $format_cells[row]' border=0></a>";
      echo "</td>";
   }
}

function editor_callback_in( $newrow, $newcol, $format_cells )
{
   global $edited_cell;

   echo "<table><tr><td bgcolor=#dddddd>"; // framework
   if( $format_cells[id]+0 == $edited_cell+0 ) echo "<a name='current'></a>";
   echo "<table><tr><td rowspan=2>";
   echo "[$format_cells[row],$format_cells[col]]</td>";
   // add cell control handles
   echo "<td><a href='?action=edit_cell&cell=$format_cells[id]&parent=$format_cells[tid]'><img src='content.gif' width=20 height=20 alt='править содержимое и параметры ячейки' border=0></a></td>";
   echo "<td><a href='?action=edit_table&op=copycell&table=$format_cells[tid]&row=$format_cells[row]&col=$format_cells[col]&cell=$format_cells[id]'><img src='copycell.gif' width=20 height=20 alt='копировать ячейку' border=0></a></td></tr>";
   echo "<tr><td align=right><a href='?action=edit_table&op=delcell&table=$format_cells[tid]&row=$format_cells[row]&col=$format_cells[col]&cell=$format_cells[id]'><img src='delcell.gif' width=20 height=20 alt='удалить ячейку' border=0></a></td>";
   echo "<td><a href='?action=edit_table&op=insert&table=$format_cells[tid]&row=$format_cells[row]&col=$format_cells[col]'><img src='inscell.gif' width=20 height=20 alt='вставить ячейку после $format_cells[col]' border=0></a>";
   echo "</td></tr></table></td><td>";
}

function editor_callback_post( $newrow, $newcol, $format_cells )
{
   global $encloses;

   // check for leftopen enclosures
   if( in_array( 1, $encloses ) )
      echo "<font color=red><b>ОСТАВЛЕНА ОТКРЫТАЯ ССЫЛКА</b></font><br>";

   echo "</td></tr></table>"; // framework
}

function edit_table($table,$parent,$op,$cell)
{
   global $encloses, $db, $edited_cell;

   $edited_cell = $cell;

   switch($op)
   {
      case "insert": // insert td into table
         global $PHP_SELF, $submit;
         global $align, $valign, $row, $col, $rowspan, $colspan, $bgcolor;

         if($submit)
         {
            $db->query_raw( "update format_cells set col=col+1 where tid=$table and row=$row and col>$col" );
            $db->query_raw( "insert into format_cells (tid,align,valign,row,col,rowspan,colspan,bgcolor) values ($table,'$align','$valign',$row,$col+1,$rowspan,$colspan,'$bgcolor')" );
            exit(header("Location: $PHP_SELF?action=edit_table&table=$table&cell=$edited_cell#current"));
         }

         output_header();

         echo "<b>Добавление элемента таблицы</b><br>";
         echo "<form>";

         echo "<input type=hidden name=action value=edit_table>";
         echo "<input type=hidden name=op value=insert>";
         echo "<input type=hidden name=table value=$table>";
         echo "<input type=hidden name=row value=$row>";
         echo "<input type=hidden name=col value=$col>";

         echo "<table>";
         echo "<tr><td bgcolor=#eeeeff>align</td><td><select name=align><option>left<option>center<option>right</select></td></tr>";
         echo "<tr><td bgcolor=#eeeeff>valign</td><td><select name=valign><option>top<option>center<option>bottom</select></td></tr>";
         echo "<tr><td bgcolor=#eeeeff>rowspan</td><td><input type=text name=rowspan size=10 value=1></td></tr>";
         echo "<tr><td bgcolor=#eeeeff>colspan</td><td><input type=text name=colspan size=10 value=1></td></tr>";
         echo "<tr><td bgcolor=#eeeeff>bgcolor</td><td><input type=text name=bgcolor size=10 value='default'></td></tr>";
         echo "<tr><td bgcolor=#eeeeff colspan=2 align=center><input type=image name=submit src=add_btn.gif></td></tr>";
         echo "</table>";

         echo "</form>";

         output_footer();
         break;

      case "props": // modify table props
         global $PHP_SELF, $submit;
         global $rtable, $width, $bgcolor, $align, $padding, $spacing, $border;
         $db->query_raw( "update format_tables set rtable='$rtable',width='$width',bgcolor='$bgcolor',align='$align',padding=$padding,spacing=$spacing,border=$border where id=$table" );
         exit(header("Location: $PHP_SELF?action=edit_table&table=$table&cell=$edited_cell#current"));
         break;

      case "insrowb": // insert row before $row
         global $PHP_SELF, $row;
         $db->query_raw("update format_cells set row=row+1 where tid=$table and row>$row-1");
         $db->query_raw("insert into format_cells (tid,row,col) values ($table,$row,1)");
         exit(header("Location: $PHP_SELF?action=edit_table&table=$table&cell=$edited_cell#current"));
         break;

      case "insrowa": // insert row after $row
         global $PHP_SELF, $row;
         $db->query_raw("update format_cells set row=row+1 where tid=$table and row>$row");
         $db->query_raw("insert into format_cells (tid,row,col) values ($table,$row+1,1)");
         exit(header("Location: $PHP_SELF?action=edit_table&table=$table&cell=$edited_cell#current"));
         break;

      case "copycell": // insert cell from $row,$col in col after $col in row $row, moving all cells further
         global $PHP_SELF, $row, $col;

         $db->query_raw("update format_cells set col=col+1 where tid=$table and row=$row and col>$col");
         $cell = $db->query("select * from format_cells where tid=$table and row=$row and col=$col");
         $id = $cell[id];
         $table = $cell[tid];
         $align = $cell[align];
         $valign = $cell[valign];
         $row = $cell[row];
         $col = $cell[col] + 1;
         $rowspan = $cell[rowspan];
         $colspan = $cell[colspan];
         $bgcolor = $cell[bgcolor];
         $insert = $db->query_raw( "insert into format_cells (tid,align,valign,row,col,rowspan,colspan,bgcolor) values ($table,'$align','$valign',$row,$col,$rowspan,$colspan,'$bgcolor')" );
         $cid = $db->insert_id( $insert );

         $cont = $db->query_array("select * from cell_content where cid=$id");
         while( $c = array_shift($cont) )
         {
            $posn = $c[posn];
            $type = $c[type];
            $enclose = $c[enclose];
            $text = $c[text];
            $rtable = $c[rtable];
            $rname = $c[rname];
            $rcond = $c[rcond];
            $rid = $c[rid];
            $db->query_raw("insert into cell_content (cid,posn,type,enclose,text,rtable,rname,rcond,rid) values ($cid,$posn,'$type',$enclose,'$text','$rtable','$rname','$rcond','$rid')");
         }
         exit(header("Location: $PHP_SELF?action=edit_table&table=$table&cell=$edited_cell#current"));
         break;

      case "copyrow": // insert all cells from $row in row after $row
         global $PHP_SELF, $row;

         $db->query_raw("update format_cells set row=row+1 where tid=$table and row>$row");
         $cells = $db->query_array("select * from format_cells where tid=$table and row=$row");
         while( $cell = array_shift($cells) )
         {
            $id = $cell[id];
            $table = $cell[tid];
            $align = $cell[align];
            $valign = $cell[valign];
            $row = $cell[row] + 1;
            $col = $cell[col];
            $rowspan = $cell[rowspan];
            $colspan = $cell[colspan];
            $bgcolor = $cell[bgcolor];
            $insert = $db->query_raw( "insert into format_cells (tid,align,valign,row,col,rowspan,colspan,bgcolor) values ($table,'$align','$valign',$row,$col,$rowspan,$colspan,'$bgcolor')" );
            $cid = $db->insert_id( $insert );

            $cont = $db->query_array("select * from cell_content where cid=$id");
            while( $c = array_shift($cont) )
            {
               $posn = $c[posn];
               $type = $c[type];
               $enclose = $c[enclose];
               $text = $c[text];
               $rtable = $c[rtable];
               $rname = $c[rname];
               $rcond = $c[rcond];
               $rid = $c[rid];
               $db->query_raw("insert into cell_content (cid,posn,type,enclose,text,rtable,rname,rcond,rid) values ($cid,$posn,'$type',$enclose,'$text','$rtable','$rname','$rcond','$rid')");
            }
         }
         exit(header("Location: $PHP_SELF?action=edit_table&table=$table&cell=$edited_cell#current"));
         break;

      case "copycol": // insert all cells from $col in col after $col
         global $PHP_SELF, $col;

         $db->query_raw("update format_cells set col=col+1 where tid=$table and col>$col");
         $cells = $db->query_array("select * from format_cells where tid=$table and col=$col");
         while( $cell = array_shift($cells) )
         {
            $id = $cell[id];
            $table = $cell[tid];
            $align = $cell[align];
            $valign = $cell[valign];
            $row = $cell[row];
            $col = $cell[col] + 1;
            $rowspan = $cell[rowspan];
            $colspan = $cell[colspan];
            $bgcolor = $cell[bgcolor];
            $insert = $db->query_raw( "insert into format_cells (tid,align,valign,row,col,rowspan,colspan,bgcolor) values ($table,'$align','$valign',$row,$col,$rowspan,$colspan,'$bgcolor')" );
            $cid = $db->insert_id( $insert );

            $cont = $db->query_array("select * from cell_content where cid=$id");
            while( $c = array_shift($cont) )
            {
               $posn = $c[posn];
               $type = $c[type];
               $enclose = $c[enclose];
               $text = $c[text];
               $rtable = $c[rtable];
               $rname = $c[rname];
               $rcond = $c[rcond];
               $rid = $c[rid];
               $db->query_raw("insert into cell_content (cid,posn,type,enclose,text,rtable,rname,rcond,rid) values ($cid,$posn,'$type',$enclose,'$text','$rtable','$rname','$rcond','$rid')");
            }
         }
         exit(header("Location: $PHP_SELF?action=edit_table&table=$table&cell=$edited_cell#current"));
         break;

      case "delcell": // remove cell by row,col
         global $submit, $noremove, $PHP_SELF, $row, $col;

         if( $noremove )
         {
            exit(header("Location: $PHP_SELF?action=edit_table&table=$table&cell=$edited_cell#current"));
         }

         if( $submit )
         {
            $q = $db->query("select count(id) as num from format_cells where tid=$table and row=$row");
            $count = $q[num];

            if( $count > 0 )
            {
               $db->query_raw("delete from format_cells where tid=$table and row=$row and col=$col");

               if( $count > 1 )
               {
                  $db->query_raw("update format_cells set col=col-1 where tid=$table and row=$row and col>$col");
               }
               else // we've deleted last cell in this row, move rows up
               {
                  $db->query_raw("update format_cells set row=row-1 where tid=$table and row>$row");
               }
            }

            exit(header("Location: $PHP_SELF?action=edit_table&table=$table&cell=$edited_cell#current"));
         }

         output_header();

         echo "<b>Удалить ячейку [$row,$col]</b><br>";
         echo "<form>";
         echo "<input type=hidden name=action value=edit_table>";
         echo "<input type=hidden name=op value=delcell>";
         echo "<input type=hidden name=table value=$table>";
         echo "<input type=hidden name=cell value=$edited_cell>";
         echo "<input type=hidden name=row value=$row>";
         echo "<input type=hidden name=col value=$col>";
         echo "<table>";
         echo "<tr><td colspan=2 align=center><b>Вы действительно хотите удалить ячейку и ВСЕ ее содержимое?</b></td></tr>";
         echo "<tr><td colspan=2 align=center><b>Удаленные данные будет НЕвозможно восстановить.</b></td></tr>";
         echo "<tr><td align=center><input type=submit name=submit value='[ удалить ]'></td>";
         echo "<td align=center><input type=submit name=noremove value='[ НЕ удалять ]'></td></tr>";
         echo "</table>";
         echo "</form>";

         output_footer();
         break;

      default:
         // table browsing with control handles
         $q = $db->query("select count(id) as n from format_cells where tid=$table");
         $n = $q[n];

         output_header( "menu.php?action=edit_table&table=$table&parent=$parent" );

         echo "<b>Таблица из $n ячеек</b><br>";

         $r = $db->query("select rtable,width,bgcolor,align,padding,spacing,border from format_tables where id=$table");

         $contents_default[rtable] = $r[rtable]; // *** ??? ***
         $encloses = array();

         echo "<form>";
         echo "<input type=hidden name=action value=edit_table>";
         echo "<input type=hidden name=op value=props>";
         echo "<input type=hidden name=table value=$table>";

         echo "<table>";
         echo "<tr><td bgcolor=#eeeeff>rtable</td><td><input type=text name=rtable size=10 value='$r[rtable]'></td><td>источник по умолчанию, из которого извлекаются поля БД</td></tr>";
         echo "<tr><td bgcolor=#eeeeff>width</td><td><input type=text name=width size=10 value='$r[width]'></td><td>ширина таблицы, по умолчанию <b>default</b></td></tr>";
         echo "<tr><td bgcolor=#eeeeff>bgcolor</td><td><input type=text name=bgcolor size=10 value='$r[bgcolor]'></td><td>цвет фона таблицы, по умолчанию <b>default</b></td></tr>";
         echo "<tr><td bgcolor=#eeeeff>align</td><td><select name=align><option".($r[align]=="default"?" selected":"").">default<option".($r[align]=="left"?" selected":"").">left<option".($r[align]=="center"?" selected":"").">center<option".($r[align]=="right"?" selected":"").">right</select></td><td>выравнивание таблицы, по умолчанию <b>default</b></td></tr>";
         echo "<tr><td bgcolor=#eeeeff>padding</td><td><input type=text name=padding size=10 value=$r[padding]></td><td>размер пустого пространства по контуру каждой ячейки, по умолчанию <b>-1</b></td></tr>";
         echo "<tr><td bgcolor=#eeeeff>spacing</td><td><input type=text name=spacing size=10 value=$r[spacing]></td><td>размер пустого пространства между ячейками, по умолчанию <b>-1</b></td></tr>";
         echo "<tr><td bgcolor=#eeeeff>border</td><td><input type=text name=border size=10 value=$r[border]></td><td>размер контура таблицы, по умолчанию <b>-1</b></td></tr>";
         echo "<tr><td bgcolor=#eeeeff colspan=2 align=center><input type=image name=submit src=modify_btn.gif></td></tr>";
         echo "</form>";
         echo "</table>";

         echo "<hr>";
         echo "<b>Правка таблицы</b><br>";

         if( $n == 0 ) echo "<a href='?action=edit_table&op=insert&table=$table&row=1&col=0'>создать первую ячейку</a>";

         // count columns
         $q = $db->query("select max(col) as cols from format_cells where tid=$table");
         $cols = $q[cols];

         // *** RENDER ENGINE BEGIN ***

         echo "<form name='editor'>";

         echo "<input type=hidden name=test value=test_value>";
			echo "<div id='toolbar' style='position:absolute; left:200; top:200'><input type=submit name=submit value=submit></div>";

         echo "<table bgcolor=#eeeeee cellpadding=0 cellspacing=2>"; // framework

         // *** editor specific ***
         echo "<tr><td></td><td></td>";
         for($i = 1; $i <= $cols; $i++)
         {
            echo "<td><table width=100% cellspacing=4 cellpadding=4><tr><td bgcolor=#aaeeee align=center valign=center>";
            if( $i > 1 ) echo "<a href='?action=edit_table&op=colleft&table=$table&col=$i'><img src='colleft.gif' width=20 height=20 alt='переместить столбец $i влево' border=0></a>";
            echo "$i";
            if( $i < $cols ) echo "<a href='?action=edit_table&op=colright&table=$table&col=$i'><img src='colright.gif' width=20 height=20 alt='переместить столбец $i вправо' border=0></a>";
            echo "<a href='?action=edit_table&op=copycol&table=$table&col=$i'><img src='copycol.gif' width=20 height=20 alt='копировать столбец $i' border=0></a>";
            echo "</td></tr></table></td>";
         }
         echo "</tr>";

         negine_display_cells( $table, $contents_default, "editor_callback_pre", "editor_callback_in", "editor_callback_post" );

         echo "</td></tr></table>"; // don't forget to close last td/tr

         echo "</form>";

         // *** RENDER ENGINE END ***
         echo "<hr>";
         output_footer();
         break;
   }
}

function rtable_default($table)
{
   global $db;
   $q = $db->query("select rtable from format_tables where id=$table");
   return $q[rtable];
}

function edit_cell($cell,$op,$parent)
{
   global $db;

   switch($op)
   {
      case "insert":
         global $PHP_SELF,$after,$submit,$submit_and_go;
         global $type,$text,$enclose,$rtable,$rname,$rcond,$rid;

         if($submit || $submit_and_go)
         {
            if(empty($enclose)) $enclose=0;
            $db->query_raw( "update cell_content set posn=posn+1 where cid=$cell and posn>$after" );
            $db->query_raw( "insert into cell_content values (null,$cell,$after+1,'$type',$enclose,'$text','$rtable','$rname','$rcond','$rid')" );
            if( $submit_and_go ) exit(header("Location: $PHP_SELF?action=edit_table&table=$parent&cell=$cell#current"));
            else                 exit(header("Location: $PHP_SELF?action=edit_cell&cell=$cell&parent=$parent"));
         }

         output_header( "menu.php?action=edit_cell&parent=$parent&cell=$cell" );

         echo "<b>Добавление элемента ячейки</b><br>";
         echo "<form>";

         echo "<input type=hidden name=action value=edit_cell>";
         echo "<input type=hidden name=op value=insert>";
         echo "<input type=hidden name=cell value=$cell>";
         echo "<input type=hidden name=parent value=$parent>";
         echo "<input type=hidden name=after value=$after>";

         echo "<table>";
         echo "<tr><td bgcolor=#eeeeff>Тип</td><td><select name=type><option>text<option>href<option>img<option>data<option>dref<option>dimg<option>table</select></td></tr>";
         echo "<tr><td bgcolor=#eeeeff>Текст</td><td><textarea name=text rows=15 cols=40></textarea></td></tr>";
         echo "<tr><td bgcolor=#eeeeff>Вложенность</td><td><input type=text name=enclose size=5></td></tr>";
         echo "<tr><td bgcolor=#eeeeff colspan=2 align=center>Выборка</td></tr>";
         echo "<tr><td bgcolor=#eeeeff>Источник</td><td><input type=text name=rtable size=30></td></tr>";
         echo "<tr><td bgcolor=#eeeeff>Поле</td><td><input type=text name=rname size=30></td></tr>";
         echo "<tr><td bgcolor=#eeeeff>Условие</td><td><select name=rcond><option value='='>=<option value='<>'>&lt;&gt;<option value='<'>&lt;<option value='>'>&gt;<option value='<='>&lt;=<option value='>='>&gt;=</select></td></tr>";
         echo "<tr><td bgcolor=#eeeeff>Значение</td><td><input type=text name=rid size=30></td></tr>";
         echo "<tr><td bgcolor=#eeeeff colspan=2 align=center><input type=image name=submit src=add_btn.gif></td></tr>";
         echo "<tr><td bgcolor=#eeeeff colspan=2 align=center><input type=submit name=submit_and_go value='[ добавить и править таблицу ]'></td></tr>";
         echo "</table>";

         echo "</form>";
         cell_content_edit_help();
         break;

      case "modify":
         global $PHP_SELF,$posn,$submit,$submit_and_go;
         global $type,$text,$enclose,$rtable,$rname,$rcond,$rid;

         if($submit || $submit_and_go)
         {
            if(empty($enclose)) $enclose=0;
            $db->query_raw( "update cell_content set type='$type',enclose=$enclose,text='$text',rtable='$rtable',rname='$rname',rcond='$rcond',rid='$rid' where cid=$cell and posn=$posn" );
            if( $submit_and_go ) exit(header("Location: $PHP_SELF?action=edit_table&table=$parent&cell=$cell#current"));
            else                 exit(header("Location: $PHP_SELF?action=edit_cell&cell=$cell&parent=$parent"));
         }

         $v = $db->query("select type,text,enclose,rtable,rname,rcond,rid from cell_content where cid=$cell and posn=$posn");
         extract($v);

         output_header( "menu.php?action=edit_cell&parent=$parent&cell=$cell" );

         echo "<b>Изменение элемента ячейки</b><br>";
         echo "<form>";

         echo "<input type=hidden name=action value=edit_cell>";
         echo "<input type=hidden name=op value=modify>";
         echo "<input type=hidden name=cell value=$cell>";
         echo "<input type=hidden name=parent value=$parent>";
         echo "<input type=hidden name=posn value=$posn>";

         echo "<table>";
         echo "<tr><td bgcolor=#eeeeff>Тип</td><td><select name=type><option".($type=="text"?" selected":"").">text<option".($type=="href"?" selected":"").">href<option".($type=="img"?" selected":"").">img<option".($type=="data"?" selected":"").">data<option".($type=="dref"?" selected":"").">dref<option".($type=="dimg"?" selected":"").">dimg<option".($type=="table"?" selected":"").">table</select></td></tr>";
         echo "<tr><td bgcolor=#eeeeff>Текст</td><td><textarea name=text rows=15 cols=40>$text</textarea></td></tr>";
         echo "<tr><td bgcolor=#eeeeff>Вложенность</td><td><input type=text name=enclose size=5 value='$enclose'></td></tr>";
         echo "<tr><td bgcolor=#eeeeff colspan=2 align=center>Выборка</td></tr>";
         echo "<tr><td bgcolor=#eeeeff>Источник</td><td><input type=text name=rtable size=30 value='$rtable'></td></tr>";
         echo "<tr><td bgcolor=#eeeeff>Поле</td><td><input type=text name=rname size=30 value='$rname'></td></tr>";
         echo "<tr><td bgcolor=#eeeeff>Условие</td><td><select name=rcond><option value='='".($rcond=="="?" selected":"").">=<option value='<>'".($rcond=="<>"?" selected":"").">&lt;&gt;<option value='<'".($rcond=="<"?" selected":"").">&lt;<option value='>'".($rcond==">"?" selected":"").">&gt;<option value='<='".($rcond=="<="?" selected":"").">&lt;=<option value='>='".($rcond==">="?" selected":"").">&gt;=</select></td></tr>";
         echo "<tr><td bgcolor=#eeeeff>Значение</td><td><input type=text name=rid size=30 value='$rid'></td></tr>";
         echo "<tr><td bgcolor=#eeeeff colspan=2 align=center><input type=image name=submit src=modify_btn.gif></td></tr>";
         echo "<tr><td bgcolor=#eeeeff colspan=2 align=center><input type=submit name=submit_and_go value='[ изменить и править таблицу ]'></td></tr>";
         echo "</table>";

         echo "</form>";
         cell_content_edit_help();
         break;

      case "remove":
         global $PHP_SELF, $posn, $submit, $noremove;

         if( $noremove )
         {
            exit(header("Location: $PHP_SELF?action=edit_cell&cell=$cell&parent=$parent"));
         }

         if( $submit )
         {
            $db->query_raw("delete from cell_content where cid=$cell and posn=$posn");
            $db->query_raw("update cell_content set posn=posn-1 where cid=$cell and posn>$posn");
            exit(header("Location: $PHP_SELF?action=edit_cell&cell=$cell&parent=$parent"));
         }

         output_header( "menu.php?action=edit_cell&parent=$parent&cell=$cell" );

         echo "<b>Удалить элемент ячейки $posn</b><br>";
         echo "<form>";
         echo "<input type=hidden name=action value=edit_cell>";
         echo "<input type=hidden name=op value=remove>";
         echo "<input type=hidden name=cell value=$cell>";
         echo "<input type=hidden name=parent value=$parent>";
         echo "<input type=hidden name=posn value=$posn>";
         echo "<table>";
         echo "<tr><td colspan=2 align=center><b>Вы действительно хотите удалить элемент ячейки?</b></td></tr>";
         echo "<tr><td colspan=2 align=center><b>Удаленные данные будет НЕвозможно восстановить.</b></td></tr>";
         echo "<tr><td align=center><input type=submit name=submit value='[ удалить ]'></td>";
         echo "<td align=center><input type=submit name=noremove value='[ НЕ удалять ]'></td></tr>";
         echo "</table>";
         echo "</form>";
         break;

      case "props":
         global $PHP_SELF, $submit, $submit_and_go;
         global $align, $valign, $row, $col, $rowspan, $colspan, $bgcolor;
         $db->query_raw( "update format_cells set align='$align',valign='$valign',rowspan=$rowspan,colspan=$colspan,bgcolor='$bgcolor' where id=$cell" );
         if($submit_and_go) exit(header("Location: $PHP_SELF?action=edit_table&table=$parent&cell=$cell#current"));
         else               exit(header("Location: $PHP_SELF?action=edit_cell&cell=$cell&parent=$parent"));
         break;

      default:
         $v = $db->query("select align,valign,rowspan,colspan,bgcolor from format_cells where id=$cell");
         extract($v);

         $n = $db->query("select count(id) as n from cell_content where cid=$cell order by posn asc");
         $n = $n[n];

         output_header( "menu.php?action=edit_cell&parent=$parent&cell=$cell" );

         echo "<b>Ячейка из $n элементов</b><br>";

         echo "<form>";
         echo "<input type=hidden name=action value=edit_cell>";
         echo "<input type=hidden name=op value=props>";
         echo "<input type=hidden name=cell value=$cell>";
         echo "<input type=hidden name=parent value=$parent>";
         echo "<table>";
         echo "<tr><td bgcolor=#eeeeff>align</td><td><select name=align><option".($align=="left"?" selected":"").">left<option".($align=="center"?" selected":"").">center<option".($align=="right"?" selected":"").">right</select></td></tr>";
         echo "<tr><td bgcolor=#eeeeff>valign</td><td><select name=valign><option".($valign=="top"?" selected":"").">top<option".($valign=="center"?" selected":"").">center<option".($valign=="bottom"?" selected":"").">bottom</select></td></tr>";
         echo "<tr><td bgcolor=#eeeeff>rowspan</td><td><input type=text name=rowspan size=8 value=$rowspan></td></tr>";
         echo "<tr><td bgcolor=#eeeeff>colspan</td><td><input type=text name=colspan size=8 value=$colspan></td></tr>";
         echo "<tr><td bgcolor=#eeeeff>bgcolor</td><td><input type=text name=bgcolor size=8 value='$bgcolor'></td></tr>";
         echo "<tr><td bgcolor=#eeeeff align=center><input type=image name=submit src=modify_btn.gif></td>";
         echo "<td bgcolor=#eeeeff align=center><input type=submit name=submit_and_go value='[ изменить и править таблицу ]'></td></tr>";
         echo "</table>";
         echo "</form>";

         echo "<table>";
         echo "<tr bgcolor=#eeeeff><td>posn</td><td>type</td><td>enclose</td><td>text</td><td>select</td></tr>";
         echo "<tr><td bgcolor=#eeeeff colspan=5 align=center><a href='?action=edit_cell&op=insert&cell=$cell&parent=$parent&after=0'>вставить поле</a></td></tr>";

         $q = $db->query_array("select * from cell_content where cid=$cell order by posn asc");
         while( $n = array_shift($q) )
         {
            echo "<tr><td>$n[posn]</td><td>$n[type]</td><td>$n[enclose]</td><td>$n[text]</td><td>$n[rtable].$n[rname]$n[rcond]$n[rid]</td>";
            echo "<td bgcolor=#eeeeff><a href='?action=edit_cell&op=modify&cell=$cell&parent=$parent&posn=$n[posn]'>правка</a></td>";
            echo "<td bgcolor=#eeeeff><a href='?action=edit_cell&op=remove&cell=$cell&parent=$parent&posn=$n[posn]'>удалить</a></td>";
            echo "<td bgcolor=#eeffee>";
            $n[rtable] = $n[rtable] ? $n[rtable] : rtable_default($parent);
            negine_display_content($n);
            echo "</td></tr>";
            echo "<tr><td bgcolor=#eeeeff colspan=5 align=center><a href='?action=edit_cell&op=insert&cell=$cell&parent=$parent&after=$n[posn]'>вставить поле</a></td></tr>";
         }

         echo "</table>";
         break;
   }

   output_footer();
}

function cell_content_edit_help()
{
   echo "<hr>";
   echo "Для типа <b>text</b> значение имеет только поле <b>Текст</b>.<br>";
   echo "Для типа <b>href</b> задается значение url в поле <b>Текст</b> и идентификатор в поле <b>Вложенность</b> (для закрытия этой ссылки)<br>";
   echo "Для типа <b>img</b> в поле <b>Текст</b> задается значение параметра src= тэга img (т.е. &lt;img src=<b>Текст</b>&gt;)<br>";
   echo "Для типа <b>data</b> в поле <b>Текст</b> задается <u>имя поля БД</u> из которого извлекается соответствующий текст<br>";
   echo "Для типов <b>dimg</b> и <b>dref</b> все так же как и для <b>img</b> и <b>href</b> соответственно, только в поле <b>Текст</b> задается <u>имя поля БД</u>.<br>";
   echo "Для типа <b>table</b> в поле <b>Вложенность</b> указывается ID таблицы, которая должна отображаться.<br>";
   echo "Для выборки используется выражение <b>Источник.Поле 'Условие' Значение</b> например <b>products.ID '=' 5</b><br>";
   echo "Если <b>Источник</b> не указан, используется значение по умолчанию для всей таблицы.<br>";
}

function copy_table($table,$parent)
{
   global $db, $PHP_SELF, $submit, $page;

   if( $submit )
   {
      // copy table
      $v = $db->query("select rtable,width,bgcolor,align,padding,spacing,border from format_tables where id=$table");
      $insert = $db->query_raw("insert into format_tables (rtable,width,bgcolor,align,padding,spacing,border) values ('$v[rtable]','$v[width]','$v[bgcolor]','$v[align]','$v[padding]','$v[spacing]','$v[border]')");
      $newtable = $db->insert_id( $insert );
      // copy table in layout
      $maxp = $db->query("select max(posn) as maxp from page_layouts where layout=$page");
      $db->query_raw("insert into page_layouts (layout,posn,fttable) values ($page,$maxp[maxp],$newtable)");
      // copy table cells with contents
      $cells = $db->query_array("select * from format_cells where tid=$table");
      while( $cell = array_shift($cells) )
      {
         $id = $cell[id];
         $table = $cell[tid];
         $align = $cell[align];
         $valign = $cell[valign];
         $row = $cell[row] + 1;
         $col = $cell[col];
         $rowspan = $cell[rowspan];
         $colspan = $cell[colspan];
         $bgcolor = $cell[bgcolor];
         $insert = $db->query_raw( "insert into format_cells (tid,align,valign,row,col,rowspan,colspan,bgcolor) values ($newtable,'$align','$valign',$row,$col,$rowspan,$colspan,'$bgcolor')" );
         $cid = $db->insert_id( $insert );

         $cont = $db->query_array("select * from cell_content where cid=$id");
         while( $c = array_shift($cont) )
         {
            $posn = $c[posn];
            $type = $c[type];
            $enclose = $c[enclose];
            $text = $c[text];
            $rtable = $c[rtable];
            $rname = $c[rname];
            $rcond = $c[rcond];
            $rid = $c[rid];
            $db->query_raw("insert into cell_content (cid,posn,type,enclose,text,rtable,rname,rcond,rid) values ($cid,$posn,'$type',$enclose,'$text','$rtable','$rname','$rcond','$rid')");
         }
      }

      exit(header("Location: $PHP_SELF?action=browse_page&page=$parent"));
   }

   $v = $db->query_array("select layout,name from page_names");

   output_header( "menu.php?action=copy_table&parent=$parent" );

   echo "<form>";
   echo "<input type=hidden name=action value=copy_table>";
   echo "<input type=hidden name=table value=$table>";
   echo "<input type=hidden name=parent value=$parent>";
   echo "<table><tr>";
   echo "<td>Копировать таблицу (ID $table) на страницу:</td>";
   echo "<td><select name=page>";
   while( $r = array_shift($v) ) echo "<option value=$r[layout]>$r[name]</option>";
   echo "</select></td>";
   echo "</tr><tr><td colspan=2 align=center><input type=submit name=submit value='[ копировать ]'></td></tr>";
   echo "</form>";
   echo "</table>";

   output_footer();
}
?>