<div class="row" >
    <div class="span6 offset3 well text-center" >

	    <h3 class="text-center">
		Sign in
	    </h3>

	    <form action="<?php $base_url = \Idno\Core\site()->config()->url; 
   
                if (\Idno\Core\site()->config()->secure_sensitive_pages)
                    $base_url = 'https:' . $base_url;
            echo $base_url; ?>session/login" method="post">
		<div class="control-group">
		    <div class="controls">
			<input type="text" id="inputEmail" name="email" placeholder="Your username or email address" class="span4">
		    </div>
		</div>
		<div class="control-group">
		    <div class="controls">
			<input type="password" id="inputPassword" name="password" placeholder="Password" class="span4">
		    </div>
		</div>
		<div class="control-group">
		    <div class="controls">
			<button type="submit" class="btn">Sign in</button>
		    </div>
		</div>
		<?= \Idno\Core\site()->actions()->signForm('/session/login') ?>

	    </form>

    </div>
</div>