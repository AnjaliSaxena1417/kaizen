<?php
//If the form is submitted
if(isset($_POST['submitted'])) {

		//Check to make sure that the name field is not empty
		if(trim($_POST['contactName']) === '') {
			$nameError = true; //erro especifico do campo
			$hasError = true; //tem erro no form logo não envia
		} else {
			$name = trim($_POST['contactName']);
		}

		//Check to make sure sure that a valid email address is submitted
		if(trim($_POST['email']) === '')  {
			$emailError = true;
			$hasError = true;
 //		} else if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/", trim($_POST['email']))) {
        } else if ( !is_email( trim($_POST['email']) ) ) {
        	$emailError = true;
			$hasError = true;
		} else {
			$email = trim($_POST['email']);
		}

		//Check to make sure comments were entered
		if(trim($_POST['comments']) === '') {
			$commentError = true;
			$hasError = true;
		} else {
			if(function_exists('stripslashes')) {
				$comments = stripslashes(trim($_POST['comments']));
			} else {
				$comments = trim($_POST['comments']);
			}
		}

		//
		if(trim($_POST['assunto']) === '') {
			$assuntoError = true;
			$hasError = true;
		} else {
			$assunto = trim($_POST['assunto']);
		}
if ( fdx_option('p3_txt9') <> "" ) {
         $extrafield1 = trim($_POST['extraField1']);
} else {$extrafield1 = '';}

if ( fdx_option('p3_txt10') <> "" ) {
         $extrafield2 = trim($_POST['extraField2']);
} else {$extrafield2 = '';}

if ( fdx_option('p3_txt11') <> "" ) {
         $extrafield3 = trim($_POST['extraField3']);
} else {$extrafield3 = '';}

$str = get_site_url();
$str = preg_replace('#^https?://#', '', $str);


		//If there is no error, send the email
		if(!isset($hasError)) {
            $emailTo = get_option('admin_email');
            $urlsend = $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
		    $ip = $_SERVER['REMOTE_ADDR']; //ip
            $data = date('d/M/Y H:i:s'); //data
            $subject = '['.$str.']~' .$assunto;
			$body = "&rArr; $name <p>&rArr; $email </p><p>&rArr; $assunto </p><p>&rArr; $extrafield1</p><p>&rArr; $extrafield2</p><p>&rArr; $extrafield3</p><p>&rArr; $comments </p>-------------------------------------------<br /><small>IP: $ip | $data(gmt)<br /> $urlsend </small>";
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=utf-8" . "\r\n";
            $headers .= 'From: '.$name.' <'.$email.'>' . "\r\n" . 'Reply-To: ' . $email . "\r\n";

            mail($emailTo, $subject, $body, $headers);
    		$emailSent = true;
		}

}
?>
<?php get_header(); ?>
<div class="fdx_topheading"><?php _e('Contact', 'wp-mobile-edition') ?></div>
<div class="fdx_content" style="padding: 10px">
 <?php if(isset($emailSent) && $emailSent == true) { ?>

	<div style="text-align: center">
		<h1><?php _e('Thanks', 'wp-mobile-edition') ?>, <a><?=$name;?></a></h1>
		<p><?php _e('Your message has been sent successfully', 'wp-mobile-edition') ?>!</p>
	</div>

<?php } else { ?>

<!-- ====================================== -->


<form action="<?php echo home_url('?fdxvar1=contact'); ?>" id="contactForm" method="post">

<div class="input-group">
<span class="input-group-addon">&rarr;</span><input class="form-control" name="contactName" id="contactName" value="<?php if(isset($_POST['contactName'])) echo $_POST['contactName'];?>" type="text" <?php if(@$nameError != '') { ?>style="border: 1px solid #DD0000;"<?php } ?> placeholder="<?php if ( fdx_option('p3_txt7') <> "" ) { echo fdx_option('p3_txt7');}?>">
</div>
<br>
<div class="input-group">
<span class="input-group-addon">&rarr;</span><input class="form-control" name="email" id="email" value="<?php if(isset($_POST['email']))  echo $_POST['email'];?>" <?php if(@$emailError != '') { ?>style="border: 1px solid #DD0000;"<?php } ?> placeholder="E-mail">
</div>

<br>
<div class="input-group">
<span class="input-group-addon">&rarr;</span><input class="form-control" name="assunto" id="assunto" value="<?php if(isset($_POST['assunto'])) echo $_POST['assunto'];?>" <?php if(@$assuntoError != '') { ?>style="border: 1px solid #DD0000;"<?php } ?> placeholder="<?php if ( fdx_option('p3_txt8') <> "" ) { echo fdx_option('p3_txt8');}?>">
</div>

<?php if ( fdx_option('p3_txt9') <> "" ) { ?>
<br>
<div class="input-group">
<span class="input-group-addon">&rarr;</span><input class="form-control" name="extraField1" id="extraField1" value="<?php if(isset($_POST['extraField1'])) echo $_POST['extraField1'];?>" type="text" placeholder="<?php echo fdx_option('p3_txt9'); ?>">
</div>
<?php }  ?>

<?php if ( fdx_option('p3_txt10') <> "" ) { ?>
<br>
<div class="input-group">
<span class="input-group-addon">&rarr;</span><input class="form-control" name="extraField2" id="extraField2" value="<?php if(isset($_POST['extraField2'])) echo $_POST['extraField2'];?>" type="text" placeholder="<?php echo fdx_option('p3_txt10'); ?>">
</div>
<?php }  ?>


<?php if ( fdx_option('p3_txt11') <> "" ) { ?>
<br>
<div class="input-group">
<span class="input-group-addon">&rarr;</span><input class="form-control" name="extraField3" id="extraField3" value="<?php if(isset($_POST['extraField3'])) echo $_POST['extraField3'];?>" type="text" placeholder="<?php echo fdx_option('p3_txt11'); ?>">
</div>
<?php }  ?>


<br>
<textarea name="comments" id="commentsText" class="form-control" rows="3" <?php if(@$commentError != '') { ?>style="border: 1px solid #DD0000;"<?php } ?> placeholder="<?php if ( fdx_option('p3_txt12') <> "" ) { echo fdx_option('p3_txt12');}?>"><?php if(isset($_POST['comments'])) { if(function_exists('stripslashes')) { echo stripslashes($_POST['comments']); } else { echo $_POST['comments']; } } ?></textarea>
<input type="hidden" name="submitted" id="submitted" value="true" />
<div style="text-align: center; margin-top: 10px"><button type="submit" name="submit" id="submit" style="padding: 5px"><?php _e('Send message', 'wp-mobile-edition') ?></button></div>

</form>


<!-- ====================================== -->
<?php } ?>
 </div>
<?php get_footer(); ?>