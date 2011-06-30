<?php

require_once 'ajaxSetup.php';

if ( 'load' == $_POST['action'] ) {
	$userinfo = wp_get_current_user();
	//print_r($userinfo);
	?>
		<h3 class="no-margin"><?php _e('Want to tell me what a great/horrible job I did?', $gcd) ?></h3>
		<form id="submit-feedback" class="feedback" action="<?php echo $folder;?>feedback.ajax.php" method="post">
			<input type="hidden" name="nonce" value="<?php echo $_POST['nonce']; ?>" />
			<input type="hidden" name="action" value="feedback" />
			<div>
				<label><?php _e('Your name:', $gcd) ?> <input type="text" name="name" value="<?php echo $userinfo->display_name; ?>" /></label>
				<select id="nameOptions">
					<option><?php echo $userinfo->display_name; ?></option>
					<option><?php echo $userinfo->nickname; ?></option>
					<?php if ( isset($userinfo->user_firstname) ) : ?>
						<option><?php echo $userinfo->user_firstname; ?></option>
						<?php if ( isset($userinfo->user_lastname) ) : ?>
							<option><?php echo $userinfo->user_firstname; ?> <?php echo $userinfo->user_lastname; ?></option>
						<?php endif; ?>
					<?php endif; ?>
				</select>
			</div>
			<div><label><?php _e('Email Address:', $gcd) ?> <input type="text" name="email" value="<?php echo $userinfo->user_email; ?>" /></label></div>
			<div><label>
				<?php _e('Type of feedback:', $gcd) ?> 
				<select name="type">
					<option value='Bug Report'><?php _e('Bug Report', $gcd) ?></option>
					<option value='Feedback/Comments'><?php _e('Feedback/Comments', $gcd) ?></option>
					<option value='Feature Request'><?php _e('Feature Request', $gcd) ?></option>
				</select>
			</label></div>
			<div><?php _e("If you're reporting a bug, please be as specific as possible.  Also, please use English if you can.", $gcd) ?><br /><textarea name="message"></textarea></div>
			<h4 class="no-margin"><?php _e("Additional information that might help (deselect anything you don't want to send, but I might ask you for it anyway)", $gcd) ?></h4>
			<div id="additional-info">
				<div><label><input type="checkbox" value="blogurl" checked="checked" /> <?php _e('Blog URL', $gcd) ?></label> <input type="text" readonly="readonly" value="<?php bloginfo('wpurl'); ?>" name="blogurl" id="blogurl" /></div>
				<div><label><input type="checkbox" value="wpversion" checked="checked" /> <?php _e('WordPress Version', $gcd) ?></label> <input type="text" readonly="readonly" value="<?php echo $wp_version; ?>" name="wpversion" id="wpversion" /></div>
				<div><label><input type="checkbox" value="phpversion" checked="checked" /> <?php _e('PHP Version', $gcd) ?></label> <input type="text" readonly="readonly" value="<?php echo phpversion(); ?>" name="phpversion" id="phpversion" /></div>
				<div><label><input type="checkbox" value="mysql" checked="checked" /> <?php _e('MySQL Info', $gcd) ?></label> <input type="text" readonly="readonly" value="<?php echo mysql_get_server_info() ?>" name="mysql" id="mysql" /></div>
				<div><label><input type="checkbox" value="browser" checked="checked" /> <?php _e('Browser Info', $gcd) ?></label> <input type="text" readonly="readonly" value="<?php echo $_SERVER['HTTP_USER_AGENT'] ?>" name="browser" id="browser" /></div>
			</div>
			<div><label><input type="checkbox" value="1" name="settings" checked="checked" /> <?php _e('Include your plugin settings', $gcd) ?></label></div>
			<input type="submit" class="button" value="<?php dtcGigs::escapeForInput(_e('Send Feedback', $gcd)) ?>" />
			<input type="reset" class="button cancel" id="feedback-reset" value="<?php dtcGigs::escapeForInput(_e('Cancel', $gcd)) ?>" />
		</form>
		<script type="text/javascript">
			
			target = ajaxTarget + "categories.ajax.php";
			jQuery("#additional-info input:checkbox").change(function(){
				jQuery("#" + this.value).attr("disabled", !this.checked);
			});
			
			jQuery("form#submit-feedback").ajaxForm({
				url:pageTarget,
				dataType: "json",
				success:function(json){
					if ( json.success ) {
						jQuery("#feedback").html('<div style="text-align: center"><?php _e("Thanks for your feedback!  I&rsquo;ll try to get back to you as soon as I can.", $gcd) ?></div>');
					}
				}
			});
			
			(function($){
				$("input[name=name]").focus();
				
				$("#nameOptions").change(function(){
					$("input[name=name]").val(($(this).val()));
				});
			}(jQuery));
		</script>
	<?php
} elseif ( 'feedback' == $_POST['action'] ) {
	$feedbackEmail = 'feedback.music' . (true?'@':'blah') . 'ssdn.us';  // Take that, spambots reading my repository!
	$msg = '';
	foreach ( $_POST as $key => $value ) {
		if ( $key != 'nonce' && $key != 'message' && $key != 'action' ) {
			$msg .= $key . ': ' . $value . "\n";
		}
	}

	$msg .= 'Plugin Version: ' . DTC_GIGS_PLUGIN_VERSION . "\n";
	$msg .= 'Database Version: ' . DTC_GIGS_DB_VERSION . "\n";
	
	$msg .= "\nMessage:\n" . $_POST['message'];
	
	if ( $_POST['settings'] == '1' ) {
		$msg .= "\n\n\nSettings:\n" . print_r($options, true);
	}
	if ( wp_mail($feedbackEmail, 'Gigs Calendar - ' . $_POST['type'], $msg, 'Reply-To: ' . str_replace(array("\r", "\n"), '', $_POST['email'])) ) {
		echo '{"success": true}';
	} else {
		echo '{"success": false}';
	}
}

?>
