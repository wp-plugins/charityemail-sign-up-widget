<?php
/*
Plugin Name: CharityeMail Sign Up Widget
Plugin URI: http://charityemail.co.uk/
Description: Add a "Subscribe to Newsletter" widget to your Wordpress powered website that will insert your contact in one of your CharityeMail Address Book ( you can select this in Settings > CharityeMail )
Version: 1.0
Author: Matt Rose
Author URI: http://www.charityemail.co.uk/
*/


/*  Copyright 2012  CharityeMail (email : support@CharityeMail.co.uk)

    Based on v1.1 of the dotMailer WordPress Plugin

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function clean($data){
    $trimmed = trim($data);
    $stripped = strip_tags($trimmed);
    $clean = htmlspecialchars($stripped);
    return $clean;
    
    
}


function charityemail_create_menu()
{
    add_options_page('CharityeMail Sign Up Widget', 'CharityeMail Sign Up Widget','manage_options', 'charityemail', 'charityemail_settings_page');
}

function charityemail_install()
{
    add_option('charityemail_username',	"", "");
    add_option('charityemail_password', 	"", "");
    add_option('charityemail_address_book', 	"", "");
    add_option('charityemail_datafields',"","");
}
function charityemail_uninstall()
{
    delete_option('charityemail_username');
    delete_option('charityemail_password');
    delete_option('charityemail_address_book');
    delete_option('charityemail_datafields');
}

add_action('admin_menu', 'charityemail_create_menu');
add_action('wp_head', 'settings_head');

     wp_register_sidebar_widget(
	 "charityemail Sign Up Widget",        // your unique widget id
    "CharityeMail Sign Up Widget",          // widget name
    'manage_news_letter',  // callback function
    array(                  // options
        'description' => 'Signup website visitors'
    )
     );


function control_news_letter()
{
    echo 'Subscribe to our Newsletter';
}

function settings_head(){
       wp_enqueue_script('jquery'); 
       wp_enqueue_script('settings',plugins_url( 'settings.js' , __FILE__ )) ;
}   ;

function manage_news_letter($args) {
	extract($args);
    ?>
<?php echo $before_widget; ?>
	<?php echo $before_title
                . 'Subscribe to our newsletter'
                . $after_title; ?>
    
    <form id="dotMailer_news_letter" method="post" style="margin-top:5px;">
       Your email: <input type="text" id="dotMailer_email" name="dotMailer_email" />
        <?php
        if (get_option('charityemail_datafields') != "") {
            $dmdatafields = get_option('charityemail_datafields');
            foreach ($dmdatafields as $dmdatafield) {
                echo $dmdatafield."<input type='text' name='datafields[".$dmdatafield."]' id={$dmdatafield} />";
            }
        }
        ?>
        
        <img src="<?php echo(plugins_url().'/charityemail-sign-up-widget/ajax-loader.gif') ?>" id="ajax-loader" style="display:none">
        <br/><div id="dotMailer_status_div"></div>
        <input type="submit"  name="submit" value="Subscribe" style="margin-top:5px;">
		<p>Powered by <a href="http://www.charityemail.co.uk">CharityeMail</a></p>
    </form>
    
     <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.js"></script>
    <script>
    jQuery.noConflict();
    jQuery(document).ready(function(){
    jQuery("form#dotMailer_news_letter").submit(function() {
                   var formdata = jQuery(this).serialize();
                // we want to store the values from the form input box, then send via ajax below
    		var email  = jQuery('#dotMailer_email').attr('value');

                var reg = /^[Р-пр-џA-Za-z0-9.+_'%\-&]+@(?:[A-Za-z0-9-]+\.)+[0-9A-Za-z-]{2,}$/;

               if(reg.test(email) == false) {
                  alert('Invalid Email Address');
                  return false;
               }

                jQuery('#ajax-loader').show();

    			jQuery.ajax({
    				type: "POST",
    				url: "<?php echo (plugins_url("update.php",__FILE__)); ?>",
    				data: formdata,
    				success: function(data){
    					jQuery("#dotMailer_status_div").html(data);
                                        jQuery('#ajax-loader').hide();
    				}

    			});

    		return false;
    		});
            
            });
    </script>

<?php echo $after_widget; ?>

<?php
}


register_activation_hook(__FILE__,"charityemail_install"); 
register_uninstall_hook(__FILE__,"charityemail_uninstall");


function charityemail_settings_page() { 
     
     echo '<head>';
      wp_head();
      echo '</head>';

		$messaderror = "";
      $saved = "";  
    $user_name =  get_option('charityemail_username');
    $password  =  get_option('charityemail_password');
    $addressbook = get_option('charityemail_address_book');
    $datafields = get_option('charityemail_datafields');
  if( isset( $_POST['get_address_book'] ) && ($_POST['charityemail_username'] == '' || $_POST['charityemail_password'] != '') ){
	  
	  $messaderror = "Credentials cannot be empty";
	  
	  
	  }

    if( isset( $_POST['get_address_book'] ) && $_POST['charityemail_username'] != '' && $_POST['charityemail_password'] != '' )
    {      update_option( 'charityemail_username', $_POST['charityemail_username']  );
       update_option( 'charityemail_password', $_POST['charityemail_password']  );
       $user_name =  get_option('charityemail_username');
    $password  =  get_option('charityemail_password');
       require_once('DotMailerConnect.php');
       $mailObj = new DotMailerConnect( $_POST['charityemail_username'], $_POST['charityemail_password'] );
       $AddressBookR = $mailObj->ListAddressBooks();

       
    }
    else if( $user_name != '' && $password != '' )
    {

       require_once('DotMailerConnect.php');
       $mailObj = new DotMailerConnect( $user_name,$password );
       $AddressBookR = $mailObj->ListAddressBooks();

    }

    if( isset($_POST['save_settings']) && isset( $_POST['charityemail_address_book'] ) )
    {
        update_option( 'charityemail_address_book', $_POST['charityemail_address_book']  );
     
       if (isset ($_POST['datafields'] )){ update_option( 'charityemail_datafields', $_POST['datafields']  );}else
       {update_option( 'charityemail_datafields',"");}
	   $saved = "Saved!";
	   
	   
    }




?>
<div class="wrap">
<h2>CharityeMail API Plugin</h2>

<form method="post" action="?page=charityemail">
    <?php // settings_fields( 'baw-settings-group' ); ?>
    <br/>
    
    <table class="form-table">
        <tr>
            <td colspan=2>
                <h3>Connection Settings (API Credentials)</h3>
    <p>First, you need to populate your CharityeMail API credentials to retrieve the list of address books in your CharityeMail account.
    You can retrieve these credentials by connecting to <a href="https://www.charityemail.co.uk/login.aspx">https://www.charityemail.co.uk/login.aspx</a> and going to My Account > Manage Users > API > Add Account</p>


<?php if( isset($AddressBookR ) && $AddressBookR === FALSE ): ?>
<p style="color:red;">Invalid login. Please verify your API credentials and try again.</p>
<?php endif; ?>
<?php

 if ($messaderror !="") echo "<div id='message' class='error'>".$messaderror."</div>";

?>

            </td>
        </tr>
        <tr valign="top">
        <th scope="row">API Email: </th>
        <td><input type="text" size="50" name="charityemail_username" value="<?php echo $user_name; ?>" /> <small><i>(i.e. apiuser-123456789@apiconnector.com )</i></small></td>
        </tr>

        <tr valign="top">
        <th scope="row">API Password:</th>
        <td><input type="password" size="50" name="charityemail_password" value="<?php echo $password; ?>" />
        <br/><br/><input type="submit" class="button-primary" name="get_address_book" value="<?php _e('Connect and list Address Books') ?>" />
    </td>
        </tr>
        <?php
        if ($saved !="") echo "<div id='message' class='updated'>".$saved."</div>";
		
		?>

        <?php  if( isset($_POST['get_address_book']) && $user_name != '' && $password != '' && $AddressBookR !== FALSE ): ?>


        <tr>
            <td colspan=2>
                <h3>Your CharityeMail Address Books</h3>
                <p>Please select below the address book that you would like your website visitors to be registered to.</p>
            </td>
        </tr>
        <tr valign="top">
        <th scope="row">Select Address Book: </th>
        <td>
            <?php foreach( $AddressBookR as $address ){ 
            if ($address->Name == "Test"){continue;} ?>
            <input type="radio"  name="charityemail_address_book" value="<?php echo ($address->ID) ?>" 

                <?php echo (get_option('charityemail_address_book') == $address->ID )?'checked':''; ?>
                   /><?php echo ($address->Name) ?> <br>
            <?php } ?>
        </tr>
        <tr>
        <td>
        Include data fields?
        <input type="checkbox" name="include" id="include" value="include"/>
        </td>
        
        </tr>
        
         <tr id="datafields" >
            <td>
                <h3>Datafields:</h3>
                <p>Please select below the data fields you want to be included in the form (email datafield will be included by default)</p>
            </td>
            <td>
            First name: <input type="checkbox" name="datafields[]" value="First name:"/>
            Last name: <input type="checkbox" name="datafields[]" value="Last name:"/> 
            Full name: <input type="checkbox" name="datafields[]" value="Full name:"/> 
            </td>
        </tr>
        
        
        
        
        <tr>
            <th>&nbsp;</th>
            <td> <input type="submit" class="button-primary" name="save_settings" value="<?php _e('Save settings') ?>" /></td>
        </tr>
        <?php endif;  ?>
    </table>

    <p>&nbsp;</p>

</form>
</div>
<?php } ?>
