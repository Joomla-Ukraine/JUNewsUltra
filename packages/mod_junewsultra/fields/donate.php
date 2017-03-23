<?php
/**
 * JUNewsUltra Pro
 *
 * @version 	6.x
 * @package 	UNewsUltra Pro
 * @author 		Denys D. Nosov (denys@joomla-ua.org)
 * @copyright 	(C) 2007-2015 by Denys D. Nosov (http://joomla-ua.org)
 * @license 	GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 *
 **/

define( '_JEXEC', 1 );
define('JPATH_BASE', dirname(__FILE__) ."/../../..");
define ("MAX_SIZE","500");

require_once ( JPATH_BASE .'/includes/defines.php' );
require_once ( JPATH_BASE .'/includes/framework.php' );
require_once ( JPATH_BASE .'/libraries/joomla/factory.php' );

$mainframe  = JFactory::getApplication('administrator');
$joomlaUser = JFactory::getUser();
$lang       = JFactory::getLanguage();

$mainframe->initialise();
$lang->load('mod_junewsultra', JPATH_SITE);

$language   = mb_strtolower($lang->getTag());

$csslink = '<link href="../../../../../administrator/templates/isis/css/template.css" rel="stylesheet" type="text/css" />';

function alert($text, $error)
{
    if($error == 'message') {
        $error = 'alert-info';
    }
    if($error == 'notice') {
        $error = 'alert-error';
    }

    return '<div class="alert '. $error .'">'. $text .'</div>';
}

if ($joomlaUser->get('id') < 1) {
?>
<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<?php echo $csslink; ?>
	</head>
	<body>
	    <?php echo alert(JText::_('MOD_JUNEWS_LOGIN'), 'notice'); ?>
	</body>
</html>
<?php
    return;
}

?>
<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<?php echo $csslink; ?>
	</head>
	<body>
		<fieldset class="adminform">
		    <legend><?php echo JText::_('MOD_JUNEWS_DONATE'); ?></legend>
		    <h3 style="margin-top:0;">PayPal</h3>
		    <div class="well well-small">
		    <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank" style="margin:0;">
		        <input type="hidden" name="cmd" value="_s-xclick">
		        <input type="hidden" name="hosted_button_id" value="3EDSBT4BL6KD4">
		        <input class="border" type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal — The safer, easier way to pay online.">
		        <img alt="" border="0" src="https://www.paypalobjects.com/ru_RU/i/scr/pixel.gif" width="1" height="1">
		    </form>
		    </div>
		    <h3>Interkassa</h3>
		    <div class="well well-small">
		    <form accept-charset="cp1251" action="https://interkassa.com/lib/payment.php" enctype="application/x-www-form-urlencoded" method="post" name="payment" target="_blank" style="margin:0;">
			    <input name="ik_shop_id" type="hidden" value="6B90164E-0507-DF3F-0AD2-1D4E8B0B4CDD" />
		        USD <input name="ik_payment_amount" value="10.00"  style="width:150px;" />
		        <input name="ik_payment_id" type="hidden" value="1" />
		        <input name="ik_payment_desc" type="hidden" value="Donate" />
		        <input name="process" type="submit" value="Donate" class="btn btn-primary" />
		    </form>
		    </div>
		    <h3>WebMoney</h3>
		    <div class="well well-small">
		    <ul>
		    	<li>Z162084860012</li>
		    	<li>R371967759323</li>
		    </ul>
		    </div>
		</fieldset>
	</body>
</html>