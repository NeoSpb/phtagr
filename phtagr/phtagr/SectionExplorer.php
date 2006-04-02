<?php

global $prefix;
global $db;

include_once("$prefix/SectionBody.php");
include_once("$prefix/Search.php");
include_once("$prefix/Edit.php");
include_once("$prefix/image.php");
include_once("$prefix/sync.php");
include_once("$prefix/Sql.php");

/** Explore the images.
  @class SectionExplorer */
class SectionExplorer extends SectionBody
{

function SectionExplorer()
{
  $this->name="explorer";
}

/** Print the page navigation bar. It prints the first, the current and the last pagest. Also a preview and a next page link. 
  @param link Base link for pages
  @param current Index of current page 
  @param count Absolut count of pages*/
function print_navigator($link, $current, $count)
{
  if ($count<2) return;

  echo "<div class=\"navigator\">\nPage:&nbsp;";
  
  if ($current>0)
  {
    $i=$current-1;
    printf("<a href=\"$link%s\">&lt;</a>&nbsp;\n", ($i>0?"&amp;page=$i":""), $i);
  }
    
  for ($i=0; $i < $count; $i++)
  {
    if ($i == $current)
    {
      echo "&nbsp;<span class=\"current\">$i</span>&nbsp;\n";
    }
    else if ($i != $current && 
            ($count <= 10) ||
            ($i < 3 || $i > $count-4 || 
            ($i-$current < 4 && $current-$i<4)))
    {
      printf("<a href=\"$link%s\">%d</a>\n", ($i>0?"&amp;page=$i":""), $i);
    }
    else if ($i == $count-4 || $i == 3) 
      echo "&nbsp;...&nbsp;\n";
  }
  if ($current<$count-1)
  {
    $i=$current+1;
    printf("&nbsp;<a href=\"$link%s\">&gt;</a>\n", ($i>0?"&amp;page=$i":""), $i);
  }
  echo "</div>\n\n";
}

/** Print the current page with an table */
function print_content()
{
  global $db;
  global $search;
  global $user; 

  $sql=$search->get_num_query();
  $result = $db->query($sql);
  if (!$result)
  {
    return;
  }
  $row=mysql_fetch_row($result);
  $count=$row[0];
  
  if (count($search->tags)>0)
  {
    echo "<h2>Explore Tag: ";
    foreach ($search->tags as $tag)
      echo "$tag ";
    echo "</h2>\n";
  }
  else
    echo "<h2>Explore Tags</h2>\n";

  if ($count==0)
  {
    echo "<p>No images found!</p>\n";
    return;
  }
  
  $sql=$search->get_query();
  $result = $db->query($sql);
  if (!$result)
  {
    return;
  }
  
  $search_nav=$search;
  $page=$search_nav->page_num;
  $search_nav->page_num=0;
  $url_nav="index.php?section=explorer";
  $url_nav.=$search_nav->to_URL();
  $this->print_navigator($url_nav, $page, ceil($count/$search_nav->page_size));
  
  // Formular for further actions
  echo "<form action=\"index.php\" method=\"post\">";

  echo "<table class=\"tableview\">\n";
  $cell=0;
  $pos=$search->get_page_size()*$search->get_page_num();
  while($row = mysql_fetch_row($result)) 
  {
    if ($cell % 2 == 0) {
        echo "<tr>\n";
    }
    echo "<td class=\"preview\">";
    $search->set_pos($pos);
    print_preview($row[0], $search);
    echo "</td>\n\n";
    
    if ($cell % 2 == 1) {
      echo "</tr>\n";
    }
    $cell++;
    $pos++;
  }

  echo "</table>\n\n";

  $this->print_navigator($url_nav, $page, ceil($count/$search_nav->page_size));
  
  echo "<div><input type=\"hidden\" name=\"section\" value=\"explorer\" />\n";
  echo $search->to_form();
  echo "</div>\n";

  $edit=new Edit();
  if ($user->can_edit())
    $edit->print_edit_inputs();
  $edit->print_buttons();
  echo "</form>\n";
}

}

?>
