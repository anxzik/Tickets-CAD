<?php
/*
2/26/2014 - initial release
3/8/2014 - revised for inline style vs. css
<?php echo __LINE__; ?>
*/
define ( "FORM", "ICS 214" ) ;
define ( "TITLE", "ACTIVITY LOG " ) ;

if ( !defined ( 'E_DEPRECATED' ) ) { define ( 'E_DEPRECATED',8192 ) ;}		// 11/8/09
error_reporting ( E_ALL ^ E_DEPRECATED ) ;
@session_start () ;
require_once ( '../incs/functions.inc.php' ) ;		//7/28/10

if ( empty ($_SESSION) ) {
?>
<body onload = 'setTimeout ( function () { this.window.close () }, 2500 ) ;' >
	<h1 style = 'text-align: center; margin-top:200px;'>Closing window!</h1>
	</BODY>
<?php
	}
else {			// NOTE!

session_write_close () ;
include ( './ics.css.php' ) ;

extract ( $_POST ) ;
/*
dump ( $_POST ) ;
*/
$do_blur = ( can_edit () ) ? "" : "onfocus = 'this.blur () ;'" ;		// limit edit access
$payload_arr = array () ;											// payload as a global PHP associative array
$tabindex = 1;

function get_name ( $the_id ) {
	$query = "SELECT `name` FROM `$GLOBALS[mysql_prefix]ics` WHERE `id` = {$the_id} LIMIT 1";
	$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;
	$row = mysql_fetch_assoc ( $result ) ;
	return FORM . " '" . $row ['name'] . "'";
	}				// end function get_name ()

function in_area ( $name, $cols, $rows, $ph = NULL, $margin=0 ) {			// <textarea ...
	global $tabindex, $payload_arr, $func, $do_blur, $textarea;

	$tabindex++;
	$key = "f_{$name}";
	$value = array_key_exists ( $key, $payload_arr ) ? $payload_arr [$key] : "" ;
	$placeholder = ( ( $_POST['func'] == "m2" ) || ( is_null ( $ph ) ) || ( ! empty ( $value ) ) ) ? "" : " placeholder = '{$ph}' " ;
	return ( $_POST['func'] == "m2" ) ?
		"<span style = '{$textarea}'>{$value}</span>" :
		"<textarea  style = '{$textarea}' id='f_{$name}' cols = {$cols} rows = {$rows} name='f_{$name}' tabindex={$tabindex} onchange = 'this.form.dirty.value = 1;' {$do_blur} style = 'margin-left: {$margin}px;' {$placeholder} >{$value}</textarea>";
	}		// end function

function in_text ( $name, $size, $ph = NULL, $margin=0 ) {		// <input type=text ...
	global $input, $tabindex, $payload_arr, $func, $do_blur;
	$tabindex++;
	$key = "f_{$name}";
	$value = array_key_exists ( $key, $payload_arr ) ? $payload_arr [$key] : "" ;
	$placeholder = ( ( $_POST['func'] == "m2" ) || ( is_null ( $ph ) ) || ( ! empty ( $value ) ) ) ? "" : " placeholder = '{$ph}' " ;
	$ml = strval ( round ( ( floatval ( $size ) ) * 2.0 ) ) ;	// maximum length
	return ( $_POST['func'] == "m2" ) ?
		"<span style = '$input'>{$value}</span>" :
		"<input type=text style = '{$input} margin-left: {$margin}px;' id='f_{$name}' name='f_{$name}' size={$size} maxlength={$ml} value='{$value}' tabindex={$tabindex} {$placeholder} onchange = 'this.form.dirty.value = 1;' {$do_blur}'/>";
	}		// end function


function in_check ( $name, $value, $ph = NULL, $margin=0 ) {		// <input type=checkbox ...
	global $tabindex, $payload_arr, $func, $do_blur;
	$tabindex++;
	$key = "f_{$name}";
	$ischecked = array_key_exists ( $key, $payload_arr ) ? "checked" : "" ;
	$placeholder = ( ( $_POST['func'] == "m2" ) || ( is_null ( $ph ) ) || ( ! empty ( $value ) ) ) ? "" : " placeholder = '{$ph}' " ;
	return ( $_POST['func'] == "m2" ) ?
		"<input type=checkbox id='f_{$name}' name='f_{$name}' value='{$value}' {$ischecked} tabindex={$tabindex} />{$value}\n":
		"<input type=checkbox id='f_{$name}' name='f_{$name}' value='{$value}' {$ischecked} tabindex={$tabindex} onchange = 'this.form.dirty.value = 1;' {$do_blur} style = 'margin-left: {$margin}px;'/>{$value}";
	}

function set_input_strings () {
	$out_arr = array () ;
	$out_arr['f_1'] = in_text ( 1, 10 ) ; 				// $name, $size, $tabindex
	$out_arr['f_2'] = in_text ( 2, 10 ) ;
	$out_arr['f_3'] = in_text ( 3, 30, ' (required)' ) ;
	$out_arr['f_4'] = in_text ( 4, 5 ) ;
	$out_arr['f_5'] = in_text ( 5, 5 ) ;
	$out_arr['f_6'] = in_text ( 6, 28 ) ;
	$out_arr['f_7'] = in_text ( 7, 30 ) ;
	$out_arr['f_8'] = in_text ( 8, 30 ) ;

	$start = 9;
	for ( $i=0; $i< ( 3*8 ) ; $i+=3 ) {					// 3 cols, 8 rows ( 0-23 )
		$key = "f_" . strval ( $start + $i ) ;
		$out_arr[$key] = in_text ( $start + $i, 30 ) ; 		// name
		$key = "f_" . strval ( $start + $i + 1 ) ;
		$out_arr[$key] = in_text ( $start + $i+1, 30 ) ; 		// ICS position
		$key = "f_" . strval ( $start + $i + 2 ) ;
		$out_arr[$key] = in_text ( $start + $i+2, 30 ) ; 		// home agency
		}

	$start = 33;
		for ( $i=0; $i< ( 2*24 ) ; $i+=2 ) {					// 2 cols, 24 rows ( 0-47 )
			$key = "f_" . strval ( $start + $i ) ;
			$out_arr[$key] = in_text ( $start + $i, 16 ) ; 		// date/time
			$key = "f_" . strval ( $start + $i + 1 ) ;
			$out_arr[$key] = in_text ( $start + $i+1, 75 ) ; 	// notable activities
		}

	$out_arr['f_81'] = in_text ( 81, 18 ) ;
	$out_arr['f_82'] = in_text ( 82, 18 ) ;
	$out_arr['f_83'] = in_text ( 83, 18 ) ;
	$out_arr['f_84'] = in_text ( 84, 8 ) ;
	$out_arr['f_85'] = in_text ( 85, 5, NULL, 10 ) ;

	return $out_arr;
	}		// end function set input strings ()

function merge_template () {		// merge argument array with template -- e.g., <td> $my_inputs_arr['fn'] </td>
	include ( './ics.css.php' ) ;
	$my_inputs_arr = set_input_strings () ;
	$out_str = "\n

	<table style = '{$table} width: 8in;'>
	<tr>
		<td style = 'width: 34%; {$td_heading_nb}'><b>1. Incident Name:<br>{$my_inputs_arr['f_3']}</b></td>
		<td style = 'width: 24%; {$td_heading_nb}'><b>2. Operational Period:<br>&nbsp;</b></td>
		<td style = 'width: 21%; {$td_heading_nb}'>From <br>{$my_inputs_arr['f_1']}{$my_inputs_arr['f_4']}</td>
		<td style = 'width: 21%; {$td_heading_nb}'>To   <br>{$my_inputs_arr['f_2']}{$my_inputs_arr['f_5']}</td>
		</tr>
	</table>
	<table style = '{$table} width: 8in;'>
		<tr style = 'height: 1px;'>
		<td style = 'width: 33%; background-color: transparent;'> </td>
		<td style = 'width: 33%; background-color: transparent;'> </td>
		<td style = 'width: 34%; background-color: transparent;'> </td>
		</tr>
	<tr style = '{$tr_thin}' >
			<td colspan=1 style = '{$td_heading}'>&nbsp;3. Name:<br> 				 {$my_inputs_arr['f_6']}</td>
			<td colspan=1 style = '{$td_heading}'>&nbsp;4. ICS Position:<br>		 {$my_inputs_arr['f_7']}</td>
			<td colspan=1 style = '{$td_heading}'>&nbsp;5. Home Agency and Unit:<br> {$my_inputs_arr['f_8']}</td>
		<tr style = '{$tr_thin}'>
			<td colspan=3 style = '{$td_heading}'>&nbsp;6. Resources Assigned:</td>
		</tr>\n
		</tr>\n";

		$start = 9;
		for ( $i=0; $i< ( 3*8 ) ; $i+=3 ) {					// 3 cols, 8 rows ( 0-23 )
			$out_str .= "<tr style = '{$tr_thin}'>\n";
			$key = "f_" . strval ( $start + $i ) ;
			$out_str .= "<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr[$key]}</td>\n";
			$key = "f_" . strval ( $start + $i + 1 ) ;
			$out_str .= "<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr[$key]}</td>\n";
			$key = "f_" . strval ( $start + $i + 2 ) ;
			$out_str .= "<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr[$key]}</td>\n
				</tr>\n";
			}
		$out_str .= "</table>\n
			<table style = '{$table} width: 8in;'>
				<tr style = 'height:1px;'>
				<td style = 'width: 20%; background-color: transparent;'> </td>
				<td style = 'width: 80%; background-color: transparent;'> </td>
			</tr>
			<tr style = '{$tr_thin}'>
				<td colspan=5 style = '{$td_heading}'>&nbsp;7. Activity Log:</td>
				</tr>\n

			<tr style = '{$tr_thin}'>
				<td colspan=1 style = '{$td_plain}'>&nbsp;Date/Time</td>
				<td colspan=1 style = '{$td_plain}'>&nbsp;Notable Activities</td>
				</tr>";

			$start = 33;
			for ( $i=0; $i< ( 2*24 ) ; $i+=2 ) {					// 2 cols, 24 rows ( 0-23 )
				$out_str .= "<tr style = '{$tr_thin}'>\n";
				$key = "f_" . strval ( $start + $i ) ;
				$out_str .= "<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr[$key]}</td>\n";
				$key = "f_" . strval ( $start + $i + 1 ) ;
				$out_str .= "<td colspan=1 style = '{$td_plain}'>{$my_inputs_arr[$key]}</td>\n
				 	</tr>\n";
				}

		$out_str .= "</table>\n
			<table style = '{$table} width: 8in;'>
				<tr style = 'height:1px;'>
				<td style = 'width: 25%; background-color: transparent;'> </td>
				<td style = 'width: 25%; background-color: transparent;'> </td>
				<td style = 'width: 25%; background-color: transparent;'> </td>
				<td style = 'width: 25%; background-color: transparent;'> </td>
			</tr>

			<tr style = '{$tr_thin}'>
			<td colspan=1>&nbsp;<b>8. Prepared by:</b>:<br>&nbsp;</td>
			<td colspan=1><b>Name:</b><br>{$my_inputs_arr['f_81']}</td>
			<td colspan=1><b>Position/Title:</b><br>{$my_inputs_arr['f_82']}</td>
			<td colspan=1><b>Signature:</b><br>{$my_inputs_arr['f_83']}</td>
			</tr>
		<tr style = '{$tr_thin}'>
			<td colspan=2 style = '{$td_heading}'>&nbsp;ICS 214, Page 1</td>
			<td colspan=2 style = '{$td_heading}'>&nbsp;Date/Time: {$my_inputs_arr['f_84']}{$my_inputs_arr['f_85']}</td>
			</tr>\n
		</table><br>\n";
	return $out_str;
	}		// end function merge template ()

?>
<!DOCTYPE html>
<HTML>
<HEAD>
<TITLE><?php echo FORM . "/" . $func; ?></TITLE>
<LINK REL=StyleSheet HREF="../stylesheet.php?version=<?php print time () ;?>" TYPE="text/css">
<!--
	input[type=text] 			{ border: none; }
-->
<style type="text/css">
	input[type=text]:focus 		{ background-color: yellow; }
	textarea:focus 				{ background-color: yellow; }
</style>
<script src="../js/misc_function.js" type="text/javascript"></script>
<script src="../js/jss.js" type="text/javascript"></script>
<script>

	String.prototype.trim = function () {
		return this.replace ( /^\s* ( \S* ( \s+\S+ ) * ) \s*$/, "$1" ) ;
		};

	function $() {															// 12/20/08
		var elements = new Array();
		for (var i = 0; i < arguments.length; i++) {
			var element = arguments[i];
			if (typeof element == 'string')
				element = document.getElementById(element);
			if (arguments.length == 1)
				return element;
			elements.push(element);
			}
		return elements;
		}

	function do_hover (the_id) {
		CngClass(the_id, 'hover');
		return true;
		}

	function do_plain (the_id) {
		CngClass(the_id, 'plain');
		return true;
		}

	function do_sb_hover (the_id) {
		CngClass(the_id, 'screen_but_hover');
		return true;
		}

	function do_sb_plain (the_id) {
		CngClass(the_id, 'screen_but_plain');
		return true;
		}

	function CngClass(obj, the_class){
		$(obj).className=the_class;
		return true;
		}
		
	function do_focus () {
		var success = false;
		for (i = 0; i < document.main_form.elements.length; i++) {
			if ( ( ( document.main_form.elements[i].type = "text" ) || ( document.main_form.elements[i].type = "textarea" ) ) && ( document.main_form.elements[i].value.length == 0 ) ) {
				success = true;
				break;
				}
			}		// end for ()
		if ( success ) {document.main_form.elements[i].focus ();}
		}		// end function

	function validate ( our_form ) {		// ics form name check
		if ( our_form.f_3.value.trim () .length > 0 ) {our_form.submit () ;}
		else {
			alert ( " Incident Name is required." ) ;
			our_form.f_3.focus () ;
			return false;
			}
		}		// end function validate ()

	function save ( our_form ) {
		if ( our_form.f_3.value.trim () == "" ) {
			alert ( " Incident Name is required." ) ;
			our_form.f_3.focus () ;
			return false;
			}
		else {
			our_form.submit () ;					// do it
			return true;
			}
		}		//end function save ()

	 function chk_del ( our_form ) {
	 	if ( confirm ( "Press OK to confirm Delete ( Cannot be undone! ) " ) ) {
	 		our_form.func.value = "d";
	 		our_form.submit () ;
	 		}
	 	else {
	 		return false;
	 		}		// end if/else
	 	}		// end function chk_del ()

</script>
<style>
	@media screen	{ div.buttons { display: inline-block; position: fixed; top: 20px; left: 10px; width:auto; } }
	@media print	{ div.buttons { display: none; } }
	.ics_but		{ float: none; width: 120px; display: inline-block;}
	input[type="text"] 		{ font-size:.8 em; width: auto; outline: none;}
	input[type="textarea"]	{ font-size:.8 em; width: auto; }
<?php						// set up striped table
	@session_start () ;
	$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';
?>
	tr:nth-child(even)	{ background-color: <?php echo get_css("row_light", $day_night);?>; color: <?php echo get_css("row_light_text", $day_night);?>; }
	tr:nth-child(odd)	{ background-color: <?php echo get_css("row_dark", $day_night);?>; color: <?php echo get_css("row_dark_text", $day_night);?>;}
</style>

</HEAD>
<?php
$now_ts = now_ts();
$func = ( array_key_exists ( 'func', $_POST ) ) ? $_POST['func']: "c";

switch ( $func ) {

	case "c" :

?>
<BODY style = '<?php echo $body;?>' onload = "init () ;">		<!-- <?php echo __LINE__ ; ?> -->
<div class="buttons">
 	<SPAN ID='reset_but' 						class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.main_form.reset () ;do_focus () ;"><SPAN style='float: left;'><?php print get_text ( "Reset" ) ;?></SPAN><IMG style='float: right;' SRC='../images/restore_small.png' BORDER=0></SPAN><BR />
	<SPAN ID='can_but' 							class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.can_form.submit () ;"><SPAN style='float: left;'><?php print get_text ( "Cancel" ) ;?></SPAN><IMG style='float: right;' SRC='../images/cancel_small.png' BORDER=0></SPAN><BR />
 	<SPAN ID='save_but' 						class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="save ( document.main_form ) ;">			 <SPAN style='float: left;'><?php print get_text ( "Save to DB" ) ;?></SPAN>	<IMG style='float: right;' SRC='../images/restore_small.png' BORDER=0></SPAN><BR />
	<SPAN ID='mail_but' TITLE='OK - Mail this' 	class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.main_form.func.value='m';validate ( document.main_form ) ;"><SPAN STYLE='float: left;'><?php print get_text ( "Send" ) ;?></SPAN><IMG STYLE='float: right;' SRC='../images/send_small.png' BORDER=0></SPAN><BR />
</div>
<center><br />
<form name = "main_form" method = "post" action = "<?php echo basename ( __FILE__ ) ; ?>" >
<h2><?php echo TITLE . " - " . FORM; ?></h2>

<?php

	echo merge_template () ;		// fills form with default $inputs_arr entries
	$user_id = $_SESSION['user_id'];		// 3/24/2015
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `id` = {$user_id} LIMIT 1";
	$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;
	$row = stripslashes_deep ( mysql_fetch_assoc ( $result ) ) ;
	$the_by = "{$row['name_l']}, {$row['name_f']} {$row['name_mi']}";
	$now_int = time() - ( intval (get_variable('delta_mins')) * 60) ;
	$the_date = date ("m/d/y", $now_int );					// 10/06/1929
	$the_time = date ("Hi", $now_int );						// 1423
?>

<script>
	function init () {
/* */
		document.main_form.f_1.value = '<?php echo $the_date;?>';
		document.main_form.f_2.value = '<?php echo $the_date;?>';
		document.main_form.f_84.value = '<?php echo $the_date;?>';

		document.main_form.f_4.value = '<?php echo $the_time;?>';
		document.main_form.f_5.value = '<?php echo $the_time;?>';
		document.main_form.f_85.value = '<?php echo $the_time;?>';

		document.main_form.f_6.value = '<?php echo $the_by;?>';
		document.main_form.f_81.value = '<?php echo $the_by;?>';
		do_focus () ;
		}		// end function init ()
	</script>
<input type = 'hidden' name = 'func' value = "c2" />		<!-- do INSERT sql -->
<input type = 'hidden' name = 'dirty' value = 0 />
<input type = 'hidden' name = 'ics_id' value = 0 />
</form>		<!-- main_form	-->

<?php
	break;		// end case "c"

	case "c2" :				// insert new data

		$name = mysql_real_escape_string ( $_POST['f_3'] ) ;
		$payload = base64_encode ( json_encode ( $_POST ) ) ;				// whew!!
		$now_ts = now_ts();
		$script = basename ( __FILE__ );
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]ics` ( `name`, `type`, `script`, `payload`, count, `_by`, `_from`, `_as-of`, `_sent` ) VALUES
														 ( '{$name}', '" . FORM ."', '{$script}', '{$payload}', 0, {$_SESSION['user_id']}, '{$_SERVER['REMOTE_ADDR']}', '{$now_ts}', NULL ) ; ";
		$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;
		$temp = mysql_insert_id () ;				// append id # in order to enforce uniqueness
		$query = "UPDATE `$GLOBALS[mysql_prefix]ics` SET
			`name` = CONCAT_WS ( '/', `name`, '{$temp}' )
			WHERE `id` = '{$temp}' LIMIT 1";
		$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;

?>
<body onload = 'setTimeout ( function () { document.dummy.submit () }, 2500 ) ;' >		<!-- <?php echo __LINE__ ;?> -->
<center>
<div style = 'margin-top:250px;'>
<h2><?php echo get_name ( $temp ) ; ?> database insert complete! </h2>
<form name = 'dummy' method = post action = '../ics.php' ></form>
</div>
<?php
			break;		// end case "c2"

	case "u" :
		$query = "SELECT `payload`, `archived` FROM`$GLOBALS[mysql_prefix]ics` WHERE `id` = {$_POST ['id']} LIMIT 1";
		$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;
		if ( mysql_num_rows ( $result ) <> 1 ) { dump ( query ) ;}

		$row = mysql_fetch_assoc ( $result ) ;
		$temp = base64_decode ( $row['payload'] ) ;
		$payload_arr = json_decode ( $temp, true ) ;		// 	get payload as a PHP associative array

//		dump ( $row['archived'] ) ;

?>

<BODY style = '<?php echo $body;?>' onload = "do_focus () ;">		<!-- <?php echo __LINE__ ; ?> -->
<div class="buttons">
 	<SPAN ID='reset_but' 						class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.main_form.reset () ; do_focus () ;"><SPAN style='float: left;'><?php print get_text ( "Reset" ) ;?></SPAN><IMG style='float: right;' SRC='../images/restore_small.png' BORDER=0></SPAN><BR />
	<SPAN ID='can_but' 							class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.can_form.submit () ;"><SPAN style='float: left;'><?php print get_text ( "Cancel" ) ;?></SPAN><IMG style='float: right;' SRC='../images/cancel_small.png' BORDER=0></SPAN><BR />
 	<SPAN ID='save_but' 						class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="save ( document.main_form ) ;">				<SPAN style='float: left;'><?php print get_text ( "Save to DB" ) ;?></SPAN>	<IMG style='float: right;' SRC='../images/restore_small.png' BORDER=0></SPAN><BR />
	<SPAN ID='mail_but' TITLE='OK - Mail this' 	class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.main_form.func.value='m';validate ( document.main_form ) ;"><SPAN STYLE='float: left;'><?php print get_text ( "Send" ) ;?></SPAN><IMG STYLE='float: right;' SRC='../images/send_small.png' BORDER=0></SPAN><BR />
<?php
	if ( is_null ( $row['archived'] ) ) {
?>
<SPAN ID='arch_but' TITLE='Archive this' 	class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.main_form.func.value='a';document.main_form.submit () ;"><SPAN style='float: left;'><?php print get_text ( 'Archive this' ) ;?></SPAN><IMG style='float: right;' SRC='../images/restore_small.png' BORDER=0></SPAN><BR />
<?php
		} else {
?>
	<SPAN ID='arch_but' TITLE='De-archive this' 	class='plain text' style='float: none; width: 140px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.main_form.func.value='e';document.main_form.submit () ;">
		<SPAN STYLE='float: left;'><?php print get_text ( 'De-archive this' ) ;?></SPAN><IMG STYLE='float: right;' SRC='../images/restore_small.png' BORDER=0></SPAN><BR />
	<SPAN ID='dele_but' TITLE='Delete this' 		class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="chk_del ( document.main_form ) ";>
		<SPAN STYLE='float: left;'><?php print get_text ( 'Delete this' ) ;?></SPAN><IMG STYLE='float: right;' SRC='../images/delete.png' BORDER=0></SPAN><BR />
<?php
		}
?>
</div>
<center><br />
<form name = "main_form" method = "post" action = "<?php echo basename ( __FILE__ ) ; ?>" >
<h2><?php echo TITLE . " - " . FORM; ?></h2>

<?php

	echo merge_template () ;		// merge form with default $inputs_arr entries - case "u"

	if ( mysql_num_rows ( $result ) <> 1 ) { dump ( query ) ;}
	$payload = $row['payload'];
	$temp = base64_decode ( $payload ) ;
	$in_array = json_decode ( $temp, true ) ;		// 	f1 	 10/12/17
?>

<input type = 'hidden' name = 'func' value = "u2" />		<!-- do UPDATE sql -->
<input type = 'hidden' name = 'dirty' value = 0 />
<input type = 'hidden' name = 'ics_id' value = <?php echo $_POST['id']; ?> />
</form>		<!-- main_form	-->
<?php

	break;		// end case "u"

	case "u2" :				// update

		$name = mysql_real_escape_string ( $_POST['f_3'] ) . "/" . strval ( $_POST['ics_id'] ) ;
		$payload = base64_encode ( json_encode ( $_POST ) ) ;
		$now_ts = now_ts();

		$query = "UPDATE `$GLOBALS[mysql_prefix]ics` SET
			 `name` = 		'{$name}',
			 `payload` = 	'{$payload}',
			 `_by` = 		{$_SESSION['user_id']},
			 `_from` = 		'{$_SERVER['REMOTE_ADDR']}',
			 `_as-of` = 	'{$now_ts}'
			WHERE `id` = {$_POST['ics_id']} LIMIT 1 ";

		$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;
?>
<body onload = 'setTimeout ( function () { document.dummy.submit () }, 2500 ) ;' >		<!-- <?php echo __LINE__ ;?> -->

<center>
<div style = 'margin-top: 250px;'>
<h2><?php echo get_name ( $_POST['ics_id'] ) ; ?> database update complete! </h2>
<form name = 'dummy' method = post action = '../ics.php' ></form>
</div>
<?php
		break;		// end case "u2"

	case "m" :			// first check data/db status


$name_option = 1;		// set to 1 to invert first-name last-name Member name diosplay  (NO OTHER CHANGES)

?>
<?php
//	dump ( $_POST);
function do_top () {
	echo "\n<P><h2>Select Mail Recipients</h2>
	<FORM NAME='main_form' METHOD='post' ACTION='" . basename ( __FILE__ ) . "'>
	<TABLE ALIGN='center'>
		<TR CLASS = 'even'>
			<TD colspan = 3 ALIGN = 'center'><BR />
				<SPAN id='clr_spn' CLASS='plain text' style='width: 120px; display: inline-block; float: none;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' 	onClick='do_clear () ;'><SPAN STYLE='float: left;'>" . get_text ( "Uncheck All" ). "</SPAN><IMG STYLE='float: right;' SRC='../images/unselect_all_small.png' BORDER=0></SPAN>
				<SPAN id='chk_spn' CLASS='plain text' style='width: 120px; display: none; float: none;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' 			onClick='do_check () ;'><SPAN STYLE='float: left;'>" . get_text ( "Check All" ) . "</SPAN><IMG STYLE='float: right;' SRC='../images/select_all_small.png' BORDER=0></SPAN>
			</TD>
		</TR>\n";
		}		// end function do_top

function do_row ( $i, $addr, $name="", $org="" ) {		// substr ( $string , $start , $length  )
	$return_str = "<TR>";		// striped
	$js_i = $i+1;
	$return_str .= "\t\t<TD>&nbsp;<INPUT TYPE='checkbox' CHECKED NAME='cb{$js_i}' VALUE='{$addr}'>";
	$the_name =  substr ( $name , 0, 32 );
	$the_org =  substr ( $org , 0, 20 );
	$return_str .= "&nbsp;{$addr} </td><td> {$the_name} </td><td> {$the_org}</TD></TR>\n";
	return $return_str;
	}				// end function do_row ()

	if ((array_key_exists ( 'dirty', $_POST)) && ( intval ( $_POST['dirty'] == 1 ) ) ) {				// either update or insert
		if ( intval ( $_POST['id'] ) > 0 ) {				// do UPDATE

			$query = "UPDATE `$GLOBALS[mysql_prefix]ics` SET
				 `name` = 		'{$name}',
				 `payload` = 	'{$payload}',
				 `_by` = 		{$_SESSION['user_id']},
				 `_from` = 		'{$_SERVER['REMOTE_ADDR']}',
				 `_as-of` = 	'{$now_ts}',
				 `_sent` = 		NULL
				WHERE `id` = {$_POST['id'] } LIMIT 1 ";

			$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;
			}			// end if ()

		else {								// do INSERT

			$name = mysql_real_escape_string ( $_POST['f_0'] ) ;
			$payload = base64_encode ( json_encode ( $_POST ) ) ;				// whew!!
			$script = basename ( __FILE__ ) ;
			$query = "INSERT INTO `$GLOBALS[mysql_prefix]ics` ( `name`, `type`, `script`, `payload`, count, `_by`, `_from`, `_as-of`, `_sent` ) VALUES
														 ( '{$name}', '" . FORM ."', '{$script}', '{$payload}', 0, {$_SESSION['user_id']}, '{$_SERVER['REMOTE_ADDR']}', '{$now_ts}', NULL ) ; ";
			$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;
			$temp = mysql_insert_id () ;				// append id # in order to enforce uniqueness
			$_POST['id'] = $temp;				// update default 0 value
			$query = "UPDATE `$GLOBALS[mysql_prefix]ics` SET
				`name` = CONCAT_WS ( '/', `name`, '{$temp}' )
				WHERE `id` = '{$temp}' LIMIT 1";
			$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;
			}		// end else
		 }		// end if ( dirty )
	$top_shown = FALSE;
?>
<script>

	function do_mail_str () {
		sep = "";
		for ( i=0;i<document.main_form.elements.length; i++ ) {
			if ( ( document.main_form.elements[i].type =='checkbox' ) && ( document.main_form.elements[i].checked ) ) {		// frm_add_str - pipe-delimited plain addresses
				document.main_form.frm_add_str.value += sep + document.main_form.elements[i].value;
				sep = "|";
				}
			}
		if ( document.main_form.frm_add_str.value.trim () == "" ) {
			alert ( "Addressees required" ) ;
			return false;
			}
		document.main_form.func.value = "m2";		// mail, step 2
		document.main_form.submit () ;
		return true;
		}


	function do_clear () {
		for ( i=0;i<document.main_form.elements.length; i++ ) {
			if ( document.main_form.elements[i].type =='checkbox' ) {
				document.main_form.elements[i].checked = false;
				}
			}		// end for ()
		$ ( 'clr_spn' ) .style.display = "none";
		$ ( 'chk_spn' ) .style.display = "inline-block";
		}		// end function do_clear

	function do_check () {
		for ( i=0;i<document.main_form.elements.length; i++ ) {
			if ( document.main_form.elements[i].type =='checkbox' ) {
				document.main_form.elements[i].checked = true;
				}
			}		// end for ()
		$ ( 'clr_spn' ) .style.display = "inline-block";
		$ ( 'chk_spn' ) .style.display = "none";
		}		// end function do_check

	</script>
	</HEAD>
	<BODY>
<div class="buttons">
 	<span id='reset_but'	class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.main_form.reset () ;"><SPAN STYLE='float: left;'><?php print get_text ( "Reset" ) ;?></SPAN><IMG STYLE='float: right;' SRC='../images/restore_small.png' BORDER=0></SPAN><BR />
	<span id='can_but'		class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="document.can_form.submit () ;"><SPAN STYLE='float: left;'><?php print get_text ( "Cancel" ) ;?></SPAN><IMG STYLE='float: right;' SRC='../images/cancel_small.png' BORDER=0></SPAN><BR />
	<span id='mail_but'		class='plain text' style='float: none; width: 120px; display: inline-block;' onMouseover='do_hover ( this.id ) ;' onMouseout='do_plain ( this.id ) ;' onClick="do_mail_str () ;document.main_form.submit () ;"><SPAN STYLE='float: left;'><?php print get_text ( "Send" ) ;?></SPAN><IMG STYLE='float: right;' SRC='../images/send_small.png' BORDER=0></SPAN><BR />
</div>

	<center><br /><br />
<?php
	$i=1;						// 3/6/2014		see get_mdb_email($id)
	$in_vals = "''";			// ensure at least one entry
	$in_sep = ", ";

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]contacts` ORDER BY `organization` ASC,`name` ASC;" ;
	$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;

	if ( mysql_num_rows ( $result) > 0 ) {		// got Contacts

		do_top ();
		$top_shown = TRUE;
//		dump ( __LINE__);

		while ( $row = stripslashes_deep ( mysql_fetch_assoc ( $result ) , MYSQL_ASSOC ) ) {
																					// count valid addresses
			if ( is_email ( $row['email'] ) ) 	{ $in_vals .=  $in_sep; $in_vals .= "'{$row['email']}'";  echo do_row ( $i, $row['email'], $row['name'], $row['organization'] ) ;$i++; }
			if ( is_email ( $row['mobile'] ) ) 	{ $in_vals .=  $in_sep; $in_vals .= "'{$row['mobile']}'"; echo do_row ( $i, $row['mobile'], $row['name'], $row['organization'] ) ;$i++; }
			if ( is_email ( $row['other'] ) ) 	{ $in_vals .=  $in_sep; $in_vals .= "'{$row['other']}'";  echo do_row ( $i, $row['other'], $row['name'], $row['organization'] ) ;$i++; }
			}		// end while () - contacts now complete

		}		// end if ( mysql_num_rows () >0 )

//			any members?

			$field = get_mdb_variable('mdb_contact_via_field');;	// "field25" per LW db
			if ( strlen ($field) > 0 ) {

				$concat_str = (isset( $name_option) && ( intval ( $name_option ) > 0 ) ) ? "CONCAT_WS(' ', `field1` , `field2`) " : "CONCAT_WS(' ', `field1` , `field2`)";
//				if ( empty ( $in_vals )) { $in_vals = "'0'";}

				$query2 = "SELECT {$field}, '' AS `organization`, {$concat_str} AS `name` FROM `$GLOBALS[mysql_prefix]member` WHERE `{$field}` NOT IN ({$in_vals})  ORDER BY `organization` ASC,`name` ASC;";

				$result2 = mysql_query($query2);

				if($result2 && mysql_num_rows( $result2) > 0) {					// got Members
					if (!$top_shown) {do_top ();}

					$top_shown = TRUE;

					while($row2 = stripslashes_deep(mysql_fetch_assoc($result2))) {
						if( is_email ( $row2[$field]) ) {
							$temp = $row2[$field];
							echo do_row ( $i, $temp, $row2['name'] , $row2['organization'] ) ;
							$i++;
							}
						}		// end while ()
					}		// end if ()
					if (!$top_shown)  {
						do_top ();
						$top_shown = TRUE;
						}

				}		// end if members
		if ( $top_shown ) {
?>
	</TABLE>
<input type = 'hidden' name = 'func' VALUE='m2' />
<input type = 'hidden' name = 'id' value = <?php echo $_POST['ics_id']; ?> />
<input type = 'hidden' name = 'frm_add_str' VALUE='' />						<!-- for pipe-delimited addr string -->
</form>
<?php
			}
		else {
?>
			<H3>No e-mail addresses!</H3><BR /><BR />
			<INPUT TYPE='button' VALUE='Cancel' onClick = 'window.close () ;'><BR /><BR />
<?php
			}		// end if/else
// ------------------------------
		break;		// end case m

	case "m2" :			// mail step 2 - send mail to selected addresses
		function html_mail ( $to, $subject, $html_message, $from_address, $from_display_name='' ) {
		//	$headers = 'From: ' . $from_display_name . ' <shoreas@gmail.com>' . "\n";
			$from = get_variable ( 'email_from' ) ;
			$from = is_email ( $from ) ? $from : "info@ticketscad.org";
			$headers = "From: {$from_display_name}<{$from}>\n";
			$headers .= 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$temp = get_variable ( 'email_reply_to' ) ;
			if ( is_email ( $temp ) ) {
			 $headers .= "Reply-To: {$temp}\r\n";
			 }

			$temp = @mail ( $to, $subject, $html_message, $headers ) ; // boolean
			}			// end function html mail ()

		$query = "SELECT `name`, `type`, `payload` FROM `$GLOBALS[mysql_prefix]ics` WHERE `id` = {$_POST ['id']} LIMIT 1";
		$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;
		if ( mysql_num_rows ( $result ) <> 1 ) { dump ( $query ) ;}

		$row = mysql_fetch_assoc ( $result ) ;
		$temp = base64_decode ( $row['payload'] ) ;
		$payload_arr = json_decode ( $temp, true ) ;			// get payload as a PHP associative array
		$html_message = merge_template () ;					// case "m2"
//		echo $html_message;									// test - test - test - test - test -

		$to_array = explode ( "|", $_POST['frm_add_str'] ) ;	// de-stringify addresses
		$to = $sep = "";
		for ( $i=0; $i < count ( $to_array ) ; $i++ ) {
			$to .= "{$sep}{$to_array[$i]}";
			$sep = ",";
			}		// end for ()

		$subject = FORM . " - " . $row ['name'];		// subject, per form data
		$temp = get_variable ( 'email_from' ) ;
		$from_address = ( is_email ( $temp ) ) ? $temp: "ticketscad.org";
		$from_display_name = get_variable ( 'title_string' ) ;
		$temp = shorten ( strip_tags ( get_variable ( 'title_string' ) ) , 30 ) ;
		$from_display_name = str_replace ( "'", "", $temp ) ;
		$result = html_mail ( $to, $subject, $html_message, $from_address, $from_display_name ) ;

//				 ( $code, 							$ticket_id=0, 		$responder_id=0, 	$info="", 	$facility_id=0, $rec_facility_id=0, $mileage=0 )
		do_log	 ( $GLOBALS['LOG_ICS_MESSAGE_SEND'], 	$_POST ['id'], 	0, 					$subject, 	0, 				0, 					0 ) ;			// incident name as subject

		$query = "UPDATE `$GLOBALS[mysql_prefix]ics` SET
					`count` = ( `count` + 1 ) ,
					`_sent` = '{$now_ts}'
					WHERE `id` = '{$_POST ['id']}' LIMIT 1";

		$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;
?>
<body onload = 'setTimeout ( function () { document.can_form.submit () }, 2500 ) ;' >		<!-- <?php echo __LINE__ ;?> -->
<center>
<div style = 'margin-top: 250px;'>
<h2><?php echo get_name ( $_POST ['id'] ) ; ?> Mail sent!</h2>
</div>
<?php
	break;						// end case m2

case "a" :						// archive
		$query = "UPDATE `$GLOBALS[mysql_prefix]ics` SET
			 `_by` = 		{$_SESSION['user_id']},
			 `archived` = 	'{$now_ts}'
			WHERE `id` = {$_POST['ics_id']} LIMIT 1 ";

		$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;

?>
<body onload = 'setTimeout ( function () { document.can_form.submit () }, 2500 ) ;' >		<!-- <?php echo __LINE__ ;?> -->
<center>
<div style = 'margin-top: 250px;'>
<h2><?php echo get_name ( $_POST ['ics_id'] ) ; ?> to archive complete! </h2>
</div>
<?php
		break;		// end case "a"

case "e" :						// de-archive
		$query = "UPDATE `$GLOBALS[mysql_prefix]ics` SET
			 `_by` = 		{$_SESSION['user_id']},
			 `archived` = 	NULL
			WHERE `id` = {$_POST['ics_id']} LIMIT 1 ";

		$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;

?>
<body onload = 'setTimeout ( function () { document.can_form.submit () }, 2500 ) ;' >		<!-- <?php echo __LINE__ ;?> -->
<center>
<div style = 'margin-top: 250px;'>
<h2><?php echo get_name ( $_POST ['ics_id'] ) ; ?> <u>De</u>-archiving complete! </h2>
</div>
<?php
		break;		// end case "e"

case "d" :						// delete
		$msg = get_name ( $_POST ['ics_id'] ) ;
		$query = "DELETE FROM `$GLOBALS[mysql_prefix]ics` WHERE `id` = {$_POST['ics_id']} LIMIT 1 ";
		$result = mysql_query ( $query ) or do_error ( $query, 'mysql query failed', mysql_error () , basename ( __FILE__ ) , __LINE__ ) ;
?>
<body onload = 'setTimeout ( function () { document.can_form.submit () }, 2500 ) ;' >		<!-- <?php echo __LINE__ ;?> -->
<center>
<div style = 'margin-top: 250px;'>
<h2><?php echo $msg; ?> deleted! </h2>
</div>
<?php
		break;		// end case "d"

default:
 echo "err-err-err-err-err at: " . __LINE__;

	}		// end switch
	}		// end if/else
?>
<form name = "can_form" method = 'post' action = '../ics.php'>
</form>

</BODY>
<script>
if ( typeof window.innerWidth != 'undefined' ) {
	viewportwidth = window.innerWidth,
	viewportheight = window.innerHeight
	} else if ( typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0 ) {
	viewportwidth = document.documentElement.clientWidth,
	viewportheight = document.documentElement.clientHeight
	} else {
	viewportwidth = document.getElementsByTagName ( 'body' ) [0].clientWidth,
	viewportheight = document.getElementsByTagName ( 'body' ) [0].clientHeight
	}
set_fontsizes ( viewportwidth, "fullscreen" ) ;
</script>
</HTML>
