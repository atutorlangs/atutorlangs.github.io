<?php
define('AT_INCLUDE_PATH', '../../include/');

$page = "1";
$system_courses = "";
$forum_info = "";
$forum_id = "7";
$offset = 0;
$pagecount = 200;
$forum_title = "ATutor Support Archive";

require(AT_INCLUDE_PATH.'vitals.inc.php');
// Prints out an single formatted thread message

function print_entry2($row) {
	global $page,$system_courses, $forum_info;
	static $counter;
	$counter++;

	$reply_link = '<a href="forum/view.php?fid='.$row['forum_id'].SEP.'pid=';
	if ($row['parent_id'] == 0) {
		$reply_link .= $row['post_id'];
	} else {
		$reply_link .= $row['parent_id'];
	}
	$reply_link .= SEP.'reply='.$row['post_id'].SEP.'page='.$page.'#post" >Reply</a>';

?>

	<li class="<?php if ($counter %2) { echo 'odd'; } else { echo 'even'; } ?>">
		<a name="<?php echo $row['post_id']; ?>"></a>
		<div class="forum-post-author">
			<label class="title"><?php echo convert_ascii($row['username']); ?></label><br />
		</div>

		<div class="forum-post-content">
			
			<div class="date">
                <p><?php echo $row['date']; ?></p>
            </div>
            <div class="postheader"><h3><?php echo convert_ascii($row['subject']) ; ?></h3></div>
				
			<div class="body">
				<p><?php echo convert_ascii($row['body']); ?></p>
			</div>
		</div>
	</li>
<?php
}
function makepager($threadcount, $pagecount, $thispage){
    $pages = ($threadcount/$pagecount);
    for($p=1;$p<=$pages;$p++){
        if($thispage == $p){
            $paginator .= '<strong><a href="exported_forum'.$p.'.html">'.$p.'</a></strong> ';
        }else{
            $paginator .= '<a href="exported_forum'.$p.'.html?page='.$p.'">'.$p.'</a> ';
        }
    }
    return $paginator;
}




$head = "---\n title: Support Forum Threads \n
---\n";
$sql = "SELECT count(*) as a FROM forums_threads WHERE parent_id=0 AND forum_id=7";
$result = mysql_query($sql, $db);
$thread_count = mysql_fetch_assoc($result);
$threadcount =  $thread_count['a'];


//echo $pagecount;
//exit;
//$threadcount = 600;
//---- Header----
$pages = ($threadcount/$pagecount);
for($i=1; $i<=$pages; $i++){

        $tmpfile = "exported_forum".$i.".html";
        $main = fopen($tmpfile, "w");
        fwrite($main, $head);
        fwrite($main, "<div class='threadlist'>\n");

        fwrite($main, "<h3>".$forum_title."</h3><br />\n");

        $filearr = array();  // will hold all the files that were created

        $sql = "SELECT * FROM forums_threads WHERE parent_id=0 AND forum_id=".$forum_id." ORDER BY date DESC LIMIT ".$offset.", 200";
        $result = mysql_query($sql, $db);


        // Print out each post for each thread
        fwrite($main, "<div><strong>Pages:</strong>".makepager($threadcount, $pagecount, $i)."</div><br />\n");
        fwrite($main, '<table class="forum_table">');
        fwrite($main, '<tr><th class="box subjectcol">Topic</th><th>Replies</th><th>Started By</th><th class="box rc last">Last Post</th></tr>');
        while ($row = mysql_fetch_assoc($result)){ 
            $rowcount++;
            $rownshade ="";
            if($rowcount % 2 == 0){
                $rownshade = ' class="odd"';
            }
            $row['subject'] = str_replace("'","", $row['subject']);
            $row['subject'] = str_replace('"','', $row['subject']);
            $row['subject'] = preg_replace("/[^A-Za-z0-9 ]/", '', $row['subject']);
            $thishead = "---\n title: ".str_replace(":","-", $row['subject'])."\n---\n";

            $handle = fopen("t-".$row['post_id'].".html", "w");
            array_push($filearr, "t-".$row['post_id'].".html");
            fwrite($main, "<tr ".$rownshade."><td class='subjectcol ellipsis'><a href='t-".$row['post_id'].".html' title='".$row['subject']."'>".truncate($row['subject'])."</a></td>\n");
            fwrite($main, "<td>".$row['num_comments']."</td>\n");
            fwrite($main, "<td>".$row['username']."</td>\n");
            fwrite($main, "<td style='white-space: nowrap;'>".$row['date']."</td></tr>\n");
            fwrite($handle, $thishead);
            fwrite($handle, "<div class='threadlist'>\n");
            fwrite($handle, "<br /><div><a href='exported_forum1.html' class='midtext'>Back to: Thread list</a></div>\n<br />");
            fwrite($handle, "<br /><div><strong>Pages:</strong>".makepager($threadcount, $pagecount, $i)."</div>\n");
            fwrite($handle, "<div><p><br /><h3>".$row['subject']."</h3></div><br />\n");
            fwrite($handle, "<div><ul class='forum-thread'>\n");
            $sql	= "SELECT * FROM forums_threads WHERE parent_id=$row[post_id] AND forum_id=$forum_id ORDER BY date ASC LIMIT 0, 200";
            $rows_allposts = mysql_query($sql, $db);
            ob_start();

            print_entry2($row);
            while($rows_posts = mysql_fetch_assoc($rows_allposts)){
                print_entry2($rows_posts);
            }
            fwrite($handle, ob_get_contents());
            ob_end_clean();
            fwrite($handle, "</ul></div>\n");
            fclose($handle);
        }
        fwrite($main, '</table>');
        fwrite($main, "</div>");
        $offset = ($offset+$pagecount);
}
// Clean foreign characters fucntion
function convert_ascii($string) 
{ 
  // Replace Single Curly Quotes
  $search[]  = chr(226).chr(128).chr(152);
  $replace[] = "'";
  $search[]  = chr(226).chr(128).chr(153);
  $replace[] = "'";

  // Replace Smart Double Curly Quotes
  $search[]  = chr(226).chr(128).chr(156);
  $replace[] = '"';
  $search[]  = chr(226).chr(128).chr(157);
  $replace[] = '"';

  // Replace En Dash
  $search[]  = chr(226).chr(128).chr(147);
  $replace[] = '--';

  // Replace Em Dash
  $search[]  = chr(226).chr(128).chr(148);
  $replace[] = '---';

  // Replace Bullet
  $search[]  = chr(226).chr(128).chr(162);
  $replace[] = '*';

  // Replace Middle Dot
  $search[]  = chr(194).chr(183);
  $replace[] = '*';

  // Replace Ellipsis with three consecutive dots
  $search[]  = chr(226).chr(128).chr(166);
  $replace[] = '...';

  // Apply Replacements
  $string = str_replace($search, $replace, $string);

  // Remove any non-ASCII Characters
  $string = preg_replace("/[^\x01-\x7F]/","", $string);
    
  return nl2br($string); 
}
function truncate($string){
    if (strlen($string) <=40) {
      return $string;
    } else {
      return substr($string, 0, 40) . '...';
    }
}
?>