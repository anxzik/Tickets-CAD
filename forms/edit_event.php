<?php
$all_id = mysql_real_escape_string($_GET['all_id']);
$query_all = "SELECT `id`, `member_id`, `skill_type`, `skill_id`, `_on`, UNIX_TIMESTAMP(start) AS `start`, UNIX_TIMESTAMP(end) AS `end` FROM `$GLOBALS[mysql_prefix]allocations` WHERE `id` = {$all_id}";
$result_all = mysql_query($query_all) or do_error($query_all, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
$row_all	= mysql_fetch_array($result_all);

$id = $row_all['member_id'];

$query	= "SELECT *, UNIX_TIMESTAMP(_on) AS `_on` FROM `$GLOBALS[mysql_prefix]member` `m` 
	WHERE `m`.`id`={$id} LIMIT 1";
$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
$row	= stripslashes_deep(mysql_fetch_assoc($result));
?>
<SCRIPT>
window.onresize=function(){set_size()};
var viewportwidth, viewportheight, outerwidth, outerheight, colwidth, leftcolwidth, rightcolwidth;

function set_size() {
	if (typeof window.innerWidth != 'undefined') {
		viewportwidth = window.innerWidth,
		viewportheight = window.innerHeight
		} else if (typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
		viewportwidth = document.documentElement.clientWidth,
		viewportheight = document.documentElement.clientHeight
		} else {
		viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
		viewportheight = document.getElementsByTagName('body')[0].clientHeight
		}
	outerwidth = viewportwidth * .98;
	outerheight = viewportheight * .95;
	colwidth = viewportwidth * .45;
	leftcolwidth = viewportwidth * .45;
	rightcolwidth = viewportwidth * .35;
	if($('outer')) {$('outer').style.width = outerwidth + "px";}
	if($('outer')) {$('outer').style.height = outerheight + "px";}
	if($('leftcol')) {$('leftcol').style.width = leftcolwidth + "px";}
	if($('rightcol')) {$('rightcol').style.width = rightcolwidth + "px";}
	set_fontsizes(viewportwidth, "fullscreen");
	}

function pop_eve(event_id) {								// get initial values from server -  4/7/10
	sendRequest ('./ajax/view_event.php?session=<?php print MD5($sess_id);?>&ev_id=' + event_id ,pop_cb, "");			
		function pop_cb(req) {
			var the_det_arr=JSON.decode(req.responseText);
				$('f1').innerHTML = the_det_arr[1];
				$('f2').innerHTML = the_det_arr[2];
		}				// end function pop_cb()
	}	
	
function rem_record() {
	if (confirm("Are you sure you want to delete the event attendance record")) { 
		document.event_edit_Form.frm_all_remove.value="yes";
		document.forms['event_edit_Form'].submit();
		}
	}
</SCRIPT>
</HEAD>
<BODY onload='pop_eve(<?php print $row_all['skill_id'];?>)'>
	<DIV id = "outer" style='position: absolute; left: 0px; width: 90%;'>
		<DIV CLASS='header text_large' style = "height:32px; width: 100%; float: none; text-align: center;">
			<SPAN ID='theHeading' CLASS='header text_bold text_big' STYLE='background-color: inherit;'><b>Edit <?php print get_text('Event');?> Attendance for "<?php print $row['field2'];?> <?php print $row['field1'];?>"</b></SPAN>
		</DIV>
		<DIV id = "leftcol" style='position: relative; left: 30px; float: left;'>
			<FORM METHOD="POST" NAME= "event_edit_Form" ACTION="member.php?func=member&goedittpack=true&extra=edit">
				<FIELDSET>
				<LEGEND><?php print get_text('Event');?> Attendance Record</LEGEND>
					<BR />
					<LABEL for="frm_skill"><?php print get_text('Event');?> Attended:</LABEL>
						<SELECT NAME="frm_skill" style='height: 20px; font-size=12px;' onChange='pop_eve(this.options[this.selectedIndex].value);'>
							<OPTION class='normalSelect' VALUE=0 SELECTED>Select</OPTION>
<?php
							$query_eve = "SELECT * FROM `$GLOBALS[mysql_prefix]events` ORDER BY `id` ASC";
							$result_eve = mysql_query($query_eve) or do_error($query_eve, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
							while ($row_eve = stripslashes_deep(mysql_fetch_assoc($result_eve))) {
								$sel = ($row_eve['id'] == $row_all['skill_id']) ? "SELECTED" : "";									
								print "\t<OPTION class='normalSelect' VALUE='{$row_eve['id']}' {$sel}>{$row_eve['event_name']}</OPTION>\n";
								}
?>
						</SELECT>
					<BR />
					<LABEL for="frm_start"><?php print get_text('Start');?>:</LABEL>	
					<?php print generate_date_dropdown("start",$row_all['start'],0, $disallow);?>
					<BR />
					<LABEL for="frm_end"><?php print get_text('End');?>:</LABEL>
					<?php print generate_date_dropdown("end",$row_all['end'],0, $disallow);?>		
					<BR />
				</FIELDSET>
				<INPUT TYPE='hidden' NAME='frm_id' VALUE='<?php print $id;?>'>
				<INPUT TYPE='hidden' NAME='id' VALUE='<?php print $id;?>'>
				<INPUT TYPE='hidden' NAME='frm_all_id' VALUE='<?php print $all_id;?>'>	
				<INPUT TYPE="hidden" NAME = "frm_all_remove" VALUE=""/>
				<INPUT TYPE="hidden" NAME = "frm_fullname" VALUE="<?php print $row['field6'];?>"/>						
			</FORM>	
			<DIV id='eve_details' style='width: 90%; border: 2px outset #CECECE; padding: 20px; text-align: left;'>
				<DIV style='width: 100%; text-align: center;' CLASS='tablehead'>SELECTED <?php print get_text('EVENT');?> DETAILS</DIV><BR /><BR />
				<DIV class='td_label' style='width: 30%; display: inline-block;'>Event Name:</DIV><DIV style='margin-left: 20px; width: 30%; display: inline-block; vertical-align: text-top;' ID='f1'>TBA</DIV><BR />
				<DIV class='td_label' style='width: 30%; display: inline-block;'>Description:</DIV><DIV style='margin-left: 20px; width: 30%; display: inline-block; vertical-align: text-top;' ID='f2'>TBA</DIV><BR />						
			</DIV>					
		</DIV>
		<DIV ID="middle_col" style='position: relative; left: 40px; width: 110px; float: left;'>&nbsp;
			<DIV style='position: fixed; top: 50px; z-index: 1;'>
				<SPAN ID = 'rem_but' class = 'plain_centerbuttons text' style='width: 80px; display: block; float: none;' onMouseOver="do_hover_centerbuttons(this.id);" onMouseOut="do_plain_centerbuttons(this.id);" onClick="rem_record();">Remove <?php print get_text('Event');?> Attendance Record <IMG style='vertical-align: middle; float: right;' src="./images/delete.png"/></SPAN>
				<SPAN ID = 'can_but' class = 'plain_centerbuttons text' style='width: 80px; display: block; float: none;' onMouseOver="do_hover_centerbuttons(this.id);" onMouseOut="do_plain_centerbuttons(this.id);" onClick="document.forms['can_Form'].submit();"><?php print get_text('Cancel');?> <IMG style='vertical-align: middle; float: right;' src="./images/back_small.png"/></SPAN>
				<SPAN ID = 'sub_but' class = 'plain_centerbuttons text' style='width: 80px; display: block; float: none;' onMouseOver="do_hover_centerbuttons(this.id);" onMouseOut="do_plain_centerbuttons(this.id);" onClick="validate_skills(document.event_edit_Form);"><?php print get_text('Save');?> <IMG style='vertical-align: middle; float: right;' src="./images/save.png"/></SPAN>			
			</DIV>
		</DIV>
		<DIV id='rightcol' style='position: relative; left: 40px; float: left;'>
			<DIV class='tablehead' style='width: 100%; float: left; z-index: 999'><b><?php print get_text('Event');?> Attendance</b></DIV><BR /><BR />					
			<DIV style='padding: 10px; float: left;'><?php print get_text('Events');?> is for registration of the <?php print get_text('events');?> that members have attended.
			<BR />
			Examples could be regular training nights, event support, exercises.
			<BR />
			<BR />					
			<SPAN style='display: inline-block; float: left;'><?php print get_text('Events');?> need to be added first to the system either from "Config" or by clicking</SPAN>
			<SPAN ID='to_events' class = 'plain' style='display: inline; float: left;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "do_Post('events');">Here</SPAN><BR />
			</DIV>
		</DIV>
	</DIV>	
<FORM NAME='can_Form' METHOD="post" ACTION = "member.php?func=member&edit=true&id=<?php print $id;?>"></FORM>			
</BODY>
<SCRIPT>
if (typeof window.innerWidth != 'undefined') {
	viewportwidth = window.innerWidth,
	viewportheight = window.innerHeight
	} else if (typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
	viewportwidth = document.documentElement.clientWidth,
	viewportheight = document.documentElement.clientHeight
	} else {
	viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
	viewportheight = document.getElementsByTagName('body')[0].clientHeight
	}
outerwidth = viewportwidth * .98;
outerheight = viewportheight * .95;
colwidth = viewportwidth * .45;
leftcolwidth = viewportwidth * .45;
rightcolwidth = viewportwidth * .35;
if($('outer')) {$('outer').style.width = outerwidth + "px";}
if($('outer')) {$('outer').style.height = outerheight + "px";}
if($('leftcol')) {$('leftcol').style.width = leftcolwidth + "px";}
if($('rightcol')) {$('rightcol').style.width = rightcolwidth + "px";}
set_fontsizes(viewportwidth, "fullscreen");
</SCRIPT>
</HTML>						