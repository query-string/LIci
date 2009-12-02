<?


/*
Plugin Name: LIci WP
Plugin URI: http://www.lici.ru/
Description: Плагин кросс-постинга сообщений в сервис блогов LiveInternet
Version: 0.4.1
Author: LIci team [ Alexander Timofeev, Vlad Jdanov ]
Author URI: http://www.reactant.ru/project
*/


/*  Copyright 2009  Alexander Timofeev  (email : atimofeev@reactant.ru)

	Creative Commons Attribution-Noncommercial-No Derivative Works 3.0
	http://creativecommons.org/licenses/by-nc-nd/3.0/

	You are free:
	to Share — to copy, distribute and transmit the work

	Under the following conditions:
	Attribution. You must attribute the work in the manner specified by the author or licensor (but not in any way that suggests that they endorse you or your use of the work).
	Noncommercial. You may not use this work for commercial purposes.
	No Derivative Works. You may not alter, transform, or build upon this work.

	For any reuse or distribution, you must make clear to others the license terms of this work. The best way to do this is with a link to this web page.
	Any of the above conditions can be waived if you get permission from the copyright holder.
	Nothing in this license impairs or restricts the author's moral rights.


	Лицензия «С указанием авторства — Некоммерческая — Без производных»

	Эта лицензия позволяет копировать, распространять и делиться с другими до тех пор, пока упоминается авторство и сохраняется ссылка на www.lici.ru
	Эта лицензия не разрешает ни под каким видом вносить изменения в код продукта или использовать его в коммерческих целях.


*/


/* Активация
----------------------------------------------- */
session_start();
add_action('activate_lici-wp/lici-wp.php', 'lici_wp_install');
add_action('admin_menu', 'lici_wp_addmenu');

ob_start();
	bloginfo('charset');
$blog_char = ob_get_clean();
$version = '0.4.1';

/* Функции
----------------------------------------------- */
	/* -----------[ Вытаскиваем из фоафа разные данные ]----------- */
		function lici_wp_getfoaf ($type,$username)
		{
			// Проверка на наличие XSLT процессора
			if (class_exists('XSLTProcessor'))
			{
		        $username = str_replace(" ","_",$username);
		        $username = iconv($blog_char, 'Windows-1251', $username);
				$xmlDoc = new DOMDocument();
				$xmlDoc->load("http://www.liveinternet.ru/lici_foaf.php?nick=".$username."");

				/* - Тип разбора документа - */
					if ($type=="weblog")
					{
						$xslDoc = new DOMDocument();
						$xslDoc->loadXML('<?xml version="1.0" encoding="windows-1251"?>
							<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
								<xsl:output method="html"/>

								<xsl:template match="foaf">
									<xsl:value-of select="userinfo/weblog"/>
								</xsl:template>

						</xsl:stylesheet>');
					}
					if ($type=="avatar")
					{
						$xslDoc = new DOMDocument();
						$xslDoc->loadXML('<?xml version="1.0" encoding="windows-1251"?>
							<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
								<xsl:output method="html"/>

								<xsl:template match="foaf">
									<xsl:value-of select="avatars/pic"/>
								</xsl:template>

						</xsl:stylesheet>');
					}

					$proc = new XSLTProcessor();
					$proc->importStylesheet($xslDoc);
					return $proc->transformToXML($xmlDoc);
			}
			else
			{
				return '';
			}
		}

function lici_wp_install() {
   global $wpdb;

   $options_table = $wpdb->prefix . "lici_options";
   $posts_table = $wpdb->prefix . "lici_posts";

   if($wpdb->get_var("show tables like '$options_table'") != $options_table){
	   	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');

   		$sql = "CREATE TABLE ".$options_table." (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `login` VARCHAR(254) NOT NULL,
		  `pass` VARCHAR(254) NOT NULL,
		  `jid` int(10) NOT NULL,
		  `whereiam` VARCHAR(254) NOT NULL,
		  `mood` VARCHAR(254) NOT NULL,
		  `listening` VARCHAR(254) NOT NULL,
		  `comments` enum('yes','no') NOT NULL default 'yes',
		  `closerec` enum('yes','no') NOT NULL default 'no',
		  `includecomm` enum('yes','no') NOT NULL default 'yes',
		  `fontsize` int(10) NOT NULL,
		  `fontcolor` VARCHAR(254) NOT NULL,
		  `font` VARCHAR(254) NOT NULL,
		  `autocheck` enum('yes','no') NOT NULL default 'no',
		  `original` enum('yes','no') NOT NULL default 'yes',
		  `onlymore` enum('yes','no') NOT NULL default 'yes',
		  PRIMARY KEY (`id`)
		);";
		dbDelta($sql);
		$sql = "CREATE TABLE ".$posts_table." (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `pid` int(10) NOT NULL,
		  PRIMARY KEY (`id`)
		);";
		dbDelta($sql);

		$sql = "INSERT INTO $options_table VALUES('1','login','pass','0','In da city','Good','Radio','yes','no','yes',10,'000000','vera.ttf', 'no','yes');";
		$results = $wpdb->query( $sql );
		add_option("lici-default","1");
	} else {

		if($wpdb->get_var("show tables like '$posts_table'") != $posts_table){
			$sql = "CREATE TABLE ".$posts_table." (`id` int(10) NOT NULL AUTO_INCREMENT,`pid` int(10) NOT NULL,	PRIMARY KEY (`id`));";
			$wpdb->query($sql);
		}

		if (!get_option("lici-default")) {
			$lici_def = $wpdb->get_var("SELECT `id` FROM `$options_table` ORDER BY `id` ASC;");
			add_option("lici-default",$lici_def);
		}

		$logins = $wpdb->get_results("SELECT * FROM $options_table ");
		$wpdb->query("DROP TABLE $options_table");
		$sql = "CREATE TABLE ".$options_table." (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `login` VARCHAR(254) NOT NULL,
		  `pass` VARCHAR(254) NOT NULL,
		  `jid` int(10) NOT NULL,
		  `whereiam` VARCHAR(254) NOT NULL,
		  `mood` VARCHAR(254) NOT NULL,
		  `listening` VARCHAR(254) NOT NULL,
		  `comments` enum('yes','no') NOT NULL default 'yes',
	  	  `closerec` enum('yes','no') NOT NULL default 'no',
	  	  `includecomm` enum('yes','no') NOT NULL default 'yes',
	  	  `fontsize` int(10) NOT NULL,
	  	  `fontcolor` VARCHAR(254) NOT NULL,
	  	  `font` VARCHAR(254) NOT NULL,
	  	  `autocheck` enum('yes','no') NOT NULL default 'no',
	  	  `original` enum('yes','no') NOT NULL default 'yes',
	  	  `onlymore` enum('yes','no') NOT NULL default 'yes',
	  	  PRIMARY KEY (`id`)
		);";
		$wpdb->query($sql);
		foreach($logins as $l) {
			$sql = "INSERT INTO $options_table VALUES(
				'$l->id',
				'$l->login',
				'$l->pass',
				'$l->jid',
				'$l->whereiam',
				'$l->mood',
				'$l->listening',
				'$l->comments',
				'$l->closerec'";

			if (isset($l->includecomm)) { $sql .= ",'$l->includecomm'"; } else { $sql .= ",'yes'";}
			if (isset($l->fontsize))    { $sql .= ",'$l->fontsize'"; }    else { $sql .= ",'10'";}
			if (isset($l->fontcolor))   { $sql .= ",'$l->fontcolor'"; }   else { $sql .= ",'000000'";}
			if (isset($l->font))        { $sql .= ",'$l->font'"; }        else { $sql .= ",'vera.ttf'";}
			if (isset($l->autocheck))   { $sql .= ",'$l->autocheck'"; }   else { $sql .= ",'no'";}
			if (isset($l->original))    { $sql .= ",'$l->original'"; }    else { $sql .= ",'yes'";}
			if (isset($l->onlymore))    { $sql .= ",'$l->onlymore'"; }    else { $sql .= ",'yes'";}

			$sql .=");";
			$wpdb->query($sql);
		}

	}
}

function lici_wp_addmenu() {		// add menu item to admin panel
	add_options_page('LIci WP', 'LIci WP', 3, __FILE__, 'lici_wp_options');
}

function lici_wp_options() {
	global $wpdb;

	$options_table = $wpdb->prefix."lici_options";

	if (isset($_GET['confirm'])) {
		if ($_GET['confirm'] === 'del') {
			print '<div style="background-color: rgb(207, 235, 247);" id="message" class="updated fade">
					 <p>Учетная запись удалена</p></div>';
		} elseif ($_GET['confirm'] === 'add') {
			print '<div style="background-color: rgb(207, 235, 247);" id="message" class="updated fade">
					 <p>Учетная запись добавлена</p></div>';
		} elseif ($_GET['confirm'] === 'edit') {
			print '<div style="background-color: rgb(207, 235, 247);" id="message" class="updated fade">
					 <p>Учетная запись изменена</p></div>';
		} elseif ($_GET['confirm'] === 'cantadd') {
			print '<div style="background-color: #ff5f5f;" id="message" class="updated fade">
					 <p>Такой логин уже внесен в базу данных</p></div>';
		} elseif ($_GET['confirm'] === 'defu') {
			print '<div style="background-color: #ff5f5f;" id="message" class="updated fade">
					 <p>Дополнительные опции обновлены</p></div>';
		}
	}

	print "<div class='wrap'>";

	if (isset($_GET['op']))
	{
		if ($_GET['op'] === 'del')
		{
			// Удаление акка
				$id = intval($_GET['id']);
				$wpdb->query("DELETE FROM $options_table WHERE `id`='$id' LIMIT 1;");
				header("Location:options-general.php?page=lici-wp/lici-wp.php&confirm=del");
		}
		elseif ($_GET['op'] === 'add')
		{
			// Добавление акка
				if (isset($_POST['set']))
				{
					$login = mysql_real_escape_string($_POST['login']);
					$pass = mysql_real_escape_string($_POST['pass']);
					$jid = intval($_POST['jid']);

					$whereiam = mysql_real_escape_string($_POST['whereiam']);
					$mood = mysql_real_escape_string($_POST['mood']);
					$listening = mysql_real_escape_string($_POST['listening']);
					$font = mysql_real_escape_string($_POST['font']);
					$fontsize = mysql_real_escape_string($_POST['fontsize']);
					$fontcolor = mysql_real_escape_string($_POST['fontcolor']);

					if (isset($_POST['comments'])) { $comments = 'yes'; } else { $comments = 'no'; }
					if (isset($_POST['closerec'])) { $closerec = 'yes'; } else { $closerec = 'no'; }
					if (isset($_POST['autocheck'])) { $autocheck = 'yes'; } else { $autocheck = 'no'; }
					if (isset($_POST['includecomm'])) { $includecomm = 'yes'; } else { $includecomm = 'no'; }
					if (isset($_POST['original'])) { $original = 'yes'; } else { $original = 'no'; }
					if (isset($_POST['onlymore'])) { $onlymore = 'yes'; } else { $onlymore = 'no'; }

					if (!$wpdb->get_row("SELECT * FROM $options_table WHERE `login`='$login' ;"))
					{
						$wpdb->query("INSERT INTO $options_table VALUES('','$login','$pass','$jid','$whereiam','$mood','$listening','$comments','$closerec','$includecomm','$fontsize','$fontcolor','$font','$autocheck','$original','$onlymore');");
						header("Location:options-general.php?page=lici-wp/lici-wp.php&confirm=add");
					}
					else
					{
						header("Location:options-general.php?page=lici-wp/lici-wp.php&op=add&confirm=cantadd");
					}
				}
				else
				{
				print "<h2>LiveInternet Crossposter</h2>";
				print "
				<h3>Добавление учетной записи</h3>
				<form action='".$_SERVER['REQUEST_URI']."' method='post'>
					<table width='100%' class='form-table'>
					 <tr>
					  <td align='left'><b style='font-size: 16px'>Учетные данные</b></td>
					  <td align='left'><b style='font-size: 16px'>Дополнительные параметры</b></td>
					  <td align='left'><b style='font-size: 16px'>Настройки по-умолчанию</b></td>
					 </tr>

					 <tr>

					  <td valign='top'>
					   *Логин:<br />
					   <input type='text' name='login' /><Br />
					   *Пароль:<br />
					   <input type='password' name='pass' /><Br />
					   ID дневника (для сообществ):<br />
					   <input type='text' name='jid' />
					  </td>

					  <td valign='top'>
					   Я сейчас нахожусь:<br />
					   <input type='text' name='whereiam' /><Br />
					   Мой настрой:<br />
					   <input type='text' name='mood' /><Br />
					   Я слушаю:<br />
					   <input type='text' name='listening' />
					  </td>

					  <td>
					   <label><input type='checkbox' name='comments' value='1' checked='checked' /> Разрешить комментарии</label><br />
					   <label><input type='checkbox' name='closerec' value='1' /> Закрывать записи</label><br />
					   <label><input type='checkbox' name='autocheck' value='1' /> Автовыбор логина при создании записи</label><br /><br />
					   <label><input type='checkbox' name='original' value='1' /> Ссылка на оригинал статьи</label><br />
					   <label><input type='checkbox' name='onlymore' value='1' /> Кросспостить только до &lt;!--more--&gt;</label><br /><br />
					   <hr /><br />
					   <label><input type='checkbox' name='includecomm' value='1' /> Добавить счетчик комментариев</label><br />

					   Размер шрифта при выводе кол-ва комментариев:<br />
					   <input type='text' name='fontsize' value='10' size='2'/><br />

					   Цвет текста при выводе кол-ва комментариев (в HEX):<br />
					   #<input type='text' name='fontcolor' value='000000' size='6'/><br />

		  				Шрифт:<br />";
		  				if(phpversion()>5)
		  				{
		  			   		print "<select name='font'>".lici_get_fonts()."</select>";
		  			   	}
		  			   	else
		  			   	{
		  			   		print "<input type='text' value='vera.ttf' />";
		  			   	}
		  			  	print "</td>

					 </tr>
					</table>
					<p align='right'><input type='submit' name='set' value='Сохранить' class='button' /> <input class='button-secondary' type='button' onClick='history.back();' value='Отмена' /></p>
				</form>
				";
			}
		}
		elseif ($_GET['op'] === 'edit' )
		{
			// Редактировние акка
				$id = intval($_GET['id']);
				if (isset($_POST['set']))
				{
					$login = mysql_real_escape_string($_POST['login']);
					$pass = mysql_real_escape_string($_POST['pass']);
					$jid = intval($_POST['jid']);

					$whereiam = mysql_real_escape_string($_POST['whereiam']);
					$mood = mysql_real_escape_string($_POST['mood']);
					$listening = mysql_real_escape_string($_POST['listening']);
					$font = mysql_real_escape_string($_POST['font']);
					$fontsize = mysql_real_escape_string($_POST['fontsize']);
					$fontcolor = mysql_real_escape_string($_POST['fontcolor']);

					if (isset($_POST['comments'])) { $comments = 'yes'; } else { $comments = 'no'; }
					if (isset($_POST['closerec'])) { $closerec = 'yes'; } else { $closerec = 'no'; }
					if (isset($_POST['autocheck'])) { $autocheck = 'yes'; } else { $autocheck = 'no'; }
					if (isset($_POST['includecomm'])) { $includecomm = 'yes'; } else { $includecomm = 'no'; }
					if (isset($_POST['original'])) { $original = 'yes'; } else { $original = 'no'; }
					if (isset($_POST['onlymore'])) { $onlymore = 'yes'; } else { $onlymore = 'no'; }

					if(!empty($_POST['pass']))
					{
						$wpdb->query("UPDATE $options_table SET `login`='$login',`pass`='$pass',`jid`='$jid',`whereiam`='$whereiam',`mood`='$mood',`listening`='$listening',`comments`='$comments',`closerec`='$closerec',`autocheck`='$autocheck',`includecomm`='$includecomm',`original`='$original',`font`='$font',`fontsize`='$fontsize',`fontcolor`='$fontcolor' WHERE `id`='$id' LIMIT 1 ;");
					}
					else
					{
						$wpdb->query("UPDATE $options_table SET `login`='$login',`jid`='$jid',`whereiam`='$whereiam',`mood`='$mood',`listening`='$listening',`comments`='$comments',`closerec`='$closerec',`autocheck`='$autocheck',`includecomm`='$includecomm',`original`='$original',`font`='$font',`fontsize`='$fontsize',`fontcolor`='$fontcolor' WHERE `id`='$id' LIMIT 1 ;");
					}
					header("Location:options-general.php?page=lici-wp/lici-wp.php&confirm=edit");
				}
				else
				{
					$uchetka = $wpdb->get_row("SELECT * FROM $options_table WHERE `id`='$id' LIMIT 1;");
					$avatar = lici_wp_getfoaf ('avatar',$uchetka->login);
					if (empty($avatar))
					{
						$avatar = 'http://www.gravatar.com/avatar/ad516503a11cd5ca435acc9bb6523536';
					}
					print "<h2>LiveInternet Crossposter</h2>";
					print "
					<img src=\"$avatar\" alt=\"Аватар\" width=\"50\" height=\"50\" style=\"float:left;margin-right:10px;\" />
					<h3>Редактирование учетной записи</h3>
					<form action='".$_SERVER['REQUEST_URI']."' method='post'>
						<table width='100%' class='form-table'>
						 <tr>
						  <td align='left'><b style='font-size: 16px'>Учетные данные</b></td>
						  <td align='left'><b style='font-size: 16px'>Дополнительные параметры</b></td>
						  <td align='left'><b style='font-size: 16px'>Настройки по-умолчанию</b></td>
						 </tr>

						 <tr>

						  <td valign='top'>
						   *Логин:<br />
						   <input type='text' name='login' value='".$uchetka->login."' /><Br />
						   Пароль (не вводите если не хотите изменять):<br />
						   <input type='password' name='pass' value='' /><Br />
						   ID дневника (для сообществ):<br />
						   <input type='text' name='jid' value='".$uchetka->jid."' />
						  </td>

						  <td valign='top'>
						   Я сейчас нахожусь:<br />
						   <input type='text' name='whereiam' value='".$uchetka->whereiam."' /><Br />
						   Мой настрой:<br />
						   <input type='text' name='mood' value='".$uchetka->mood."' /><Br />
						   Я слушаю:<br />
						   <input type='text' name='listening' value='".$uchetka->listening."' />
						  </td>

						  <td>
						   <label><input type='checkbox' name='comments' value='1' ";
						   if ($uchetka->comments === "yes") { print "checked='checked'";}
						   print " /> Разрешить комментарии</label><br />
						   <label><input type='checkbox' name='closerec' value='1' ";
						   if ($uchetka->closerec === "yes") { print "checked='checked'";}
						   print " /> Закрывать записи</label><br />

						   <label><input type='checkbox' name='autocheck' value='1' ";
						   if ($uchetka->autocheck === "yes") { print "checked='checked'";}
						   print "/> Автовыбор логина при создании записи</label><br /><br />
						   <label><input type='checkbox' name='original' value='1' ";
						   if ($uchetka->original === "yes") { print "checked='checked'";}
						   print "/> Ссылка на оригинал статьи</label><br />
						   <label><input type='checkbox' name='onlymore' value='1' ";
						   if ($uchetka->onlymore === "yes") { print "checked='checked'";}
						   print "/> Кросспостить только до &lt;!--more--&gt;</label><br /><br />
						   <hr /><br />
						   <label><input type='checkbox' name='includecomm' value='1' ";
						   if ($uchetka->includecomm === "yes") { print "checked='checked'";}
						   print "/> Добавить счетчик комментариев</label><br />

						   Размер шрифта при выводе кол-ва комментариев:<br />
						   <input type='text' name='fontsize' value='".$uchetka->fontsize."' size='2'/><br />

						   Цвет текста при выводе кол-ва комментариев (в HEX):<br />
						   #<input type='text' name='fontcolor' value='".$uchetka->fontcolor."' size='6'/><br />

			  				Шрифт:<br />";
			  				if(phpversion()>5)
			  				{
			  			   print "<select name='font'>".lici_get_fonts($uchetka->font)."</select>";
			  			   }else{
			  			   	print "<input type='text' value='".$uchetka->font."' />";
			  			   }
						  print "</td>

						 </tr>
						</table>
						<p align='right'><input type='submit' class='button' name='set' value='Сохранить' /> <input type='button' class='button-secondary' onClick='history.back();' value='Отмена' /></p>
					</form>
					";
				}
		}
	} else {

	?>

	  <script type="text/javascript">

	  var width_now = 0;
	  var len = 0;
	  var len_now = 0;

	  function add_percents(width) {
	  	width_now = width+width_now;
	  	len_now = len_now+1;
	  	jQuery("#line-in").animate({width: width_now+'%'});
	  	if (len_now == len) {
	  		jQuery("#line-out").remove();
	  		jQuery("#arch").after("<h3>Архив отправлен</h3>");
	  	}
	  }

	  function send_archive(lid)
	  {
	  	jQuery("#arch").attr("disabled", "true");
	  	jQuery.get("<? bloginfo("url"); ?>/wp-content/plugins/lici-wp/lici-archive.php", {op:'get_posts'},
		  function(data){
		  	var posts = data.split(",");
		  	var width =Math.round(100/posts.length);
		  	len = posts.length;

		  	jQuery("#arch").after("<div id='line-out' style='width: 100%; height: 10px; border: 1px solid black; background: white; display: block;'><div id='line-in' style='width: 1px; height: 8px; background: #aaffaa; display: block; margin-top: 1px;'></div></div>");
		  	for(var p = 0; p < posts.length; p = p+1)
		  	{
				jQuery.get("<? bloginfo("url"); ?>/wp-content/plugins/lici-wp/lici-archive.php", {op:'sop', pid: posts[p], lid: lid}, function(data){add_percents(width); });
		  	}

		  }
		);
	  }

	  function set_default(obj)
	  {
	  	var defid = jQuery(obj).val();
	  	jQuery.get
	  	(
	  		"<? bloginfo("url"); ?>/wp-content/plugins/lici-wp/lici-archive.php",{op:'set_default',id:defid},
		  	function()
		  	{
				jQuery("#URow-"+defid).animate({ opacity: 0.1 }, 500);
				jQuery("#URow-"+defid).animate({ opacity: 1 }, 500);
		  	}
	  	);
	  }

	  </script>

	  <?
	print "<h2>LiveInternet Crossposter</h2>";
	print "Заполнить учетные записи";
	print "<p>
			<input type='button' value='Добавить учетную запись' class='button' onClick='document.location.href=\"options-general.php?page=lici-wp/lici-wp.php&op=add\"' /></p>";
	print "
	<script type='text/javascript'>
	 function delId(id) {
	 	if (confirm('Вы действительно хотите удалить эту учетную запись?')) {
	 		document.location.href='options-general.php?page=lici-wp/lici-wp.php&op=del&id='+id;
	 	}
	 }
	</script>

	<table class=\"widefat\">
	<thead>
	<tr>

	<th scope='col'>ID</th>
	<th scope='col'>Использовать<br />для XML-RPC</th>
	<th scope='col'>Имя пользователя</th>
	<th scope='col'>Местонахождение</th>
	<th scope='col'>Настроение</th>
	<th scope='col'>Музыка</th>
	<th colspan='2' scope='col'><div style='text-align:center;'>Действия</div></th>

	</tr>
	</thead>
	<tbody>
	";

	$uchetki = $wpdb->get_results("SELECT * FROM $options_table ORDER BY `id`;");
	$alt_row = true;
	foreach($uchetki as $uchetka)
	{
		$avatar = lici_wp_getfoaf ('avatar',$uchetka->login);
		if (empty($avatar))
		{
			$avatar = 'http://www.gravatar.com/avatar/ad516503a11cd5ca435acc9bb6523536';
		}
		$weblog = lici_wp_getfoaf ('weblog',$uchetka->login);
		echo '<div id="arch"></div>';
		echo '<tr'; echo " id=\"URow-$uchetka->id\""; echo ($alt_row?' class="alternate"':''); echo '>';
		$alt_row = !$alt_row;
		print "<td width='30'><div s	tyle=\"text-align: center\">".$uchetka->id."</div></td>\n";
		print "<td><div style=\"text-align: center\"><input onClick='javascript:set_default(this);' type='radio' name =' defaultxmlrpc' value='$uchetka->id' ";
		if (get_option("lici-default") === $uchetka->id) { print " checked='checked'"; }
		print "/></div></td>";
		print "<td valign=\"middle\"><div style=\"text-align: left\"><img src=\"$avatar\" alt=\"Аватар $uchetka->login\" width=\"30\" height=\"30\" align=\"absmiddle\" /> <a target=\"_blank\" href=\"$weblog\" title='Перейти к журналу'>".$uchetka->login."</a></div></td>\n";
		print "<td><div style=\"text-align: left\">".$uchetka->whereiam."</div></td>\n";
		print "<td><div style=\"text-align: left\">".$uchetka->mood."</div></td>\n";
		print "<td><div style=\"text-align: left\">".$uchetka->listening."</div></td>\n";
		print "<td><div style=\"text-align: center\"><input type='button' class='button' value='Изменить' onClick='document.location.href=\"options-general.php?page=lici-wp/lici-wp.php&op=edit&id=".$uchetka->id."\"' /><input type='button' class='button-secondary delete' value='Удалить' onClick='delId(".$uchetka->id.")' /><input type='button' value='Экспорт' onClick='javascript:send_archive(".$uchetka->id.")' class='button' /></div></td>\n";
		print "</tr>";
	}
	print "
	</tbody>
	</table>
";

	}

	print "</div>";
}


add_action('admin_head', 'lici_wp_admin_head');
add_action('submitpost_box', 'lici_wp_add_bottom_post');

function lici_wp_add_bottom_post() {
	global $wpdb;
	$options_table = $wpdb->prefix."lici_options";


	echo '
	<div id="licipost" class="postbox ' . postbox_classes("licipost", "post") . '" style="margin: 0px; background: #fff">
<h3><a class="togbox">+</a> LIci Crossposter - Параметры</h3>
<div class="inside">';
	echo '<h4>В какие журналы постить?</h4><ul>';
	// begin

	$logins = $wpdb->get_results("SELECT * FROM $options_table ORDER BY `id`;");
	foreach($logins as $login) {
		$sel = ($login->autocheck === "yes")? " checked='checked'" : "";
		print "<li><label><input type='checkbox' name='lici-".$login->id."' value='".$login->id."'$sel /> ".$login->login."</label></li>";
	}
	// end
	print "</ul><p>&nbsp;</p><h4>Опции журнала</h4>";

	// begin
	$logins = $wpdb->get_results("SELECT * FROM $options_table ORDER BY `id`;");
	print "
	<select id='sellici' onChange='javascript:licioptions();'>
	";
	foreach ($logins as $login) {
		print "<option value='".$login->id."'$sel>".$login->login."</option>\n";
	}
	print "
	</select>
	";
	$show = true;
	foreach ($logins as $login) {
		if ($show) { print "<div id='lici-".$login->id."'><script type='text/javascript'> licinow = 'lici-".$login->id."';</script>"; $show = !$show; } else {
			print "<div id='lici-".$login->id."' style='display: none;'>";
		}
		print "<br />
		<label><input type='checkbox' name='lici-".$login->id."-comments' ";
        if ($login->comments === "yes") { print "checked='checked'";}
		print " /> Разрешить комментарии</label><br />
		<label><input type='checkbox' name='lici-".$login->id."-closerec' ";
		if ($login->closerec === "yes") { print "checked='checked'";}
		print " /> Закрыть запись</label><br />
		<label><input type='checkbox' name='lici-".$login->id."-original' ";
		if ($login->original === "yes") { print "checked='checked'";}
		print " /> Ссылка на оригинал</label><br />
		<label><input type='checkbox' name='lici-".$login->id."-onlymore'  ";
		if ($login->onlymore === "yes") { print "checked='checked'";}
		print " /> Кросспостить только до &lt;!--more--&gt;</label><br /><br />";
		print "
		<table width='100%'>
		 <tr>
		  <td><label>Я сейчас нахожусь<br /><input type='text' value='".$login->whereiam."' name='lici-".$login->id."-whereiam' /></label></td>
		 </tr>
		 <tr>
		  <td><label>Мой настрой<br /><input type='text' value='".$login->mood."' name='lici-".$login->id."-mood' /></label></td>
		 </tr>
		 <tr>
		  <td><label>Я слушаю<br /><input type='text' value='".$login->listening."' name='lici-".$login->id."-listening' /></label></td>
		  </tr>
		</table>
		";
		print "</div>";
	}
	// end
	echo '</div></div>';
	?>

	<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery(".side-info").before("<div style='display: block; background: white; width: 100%; height: 10px;'></div>");
		jQuery(".side-info").before(jQuery("#licipost"));
	});
	</script>
	<?
}


function lici_wp_admin_head() {
	?>
	<script type='text/javascript'>
	var licinow = "";
	 function licioptions() {
	 	var sel = document.getElementById("sellici").value;
	 	var nextlici = "lici-"+sel;
	 	jQuery("#"+licinow).slideUp('fast',function(){
	 		jQuery("#"+nextlici).slideDown('fast');
		});
		licinow = nextlici;
	 }
	</script>
	<?
}

add_action("publish_post","lici_wp_send");

function lici_wp_send($pid) {
	global $wpdb;
	$options_table = $wpdb->prefix."lici_options";
	$posts_table = $wpdb->prefix . "lici_posts";

	$logins = $wpdb->get_results("SELECT * FROM $options_table ORDER BY `id`;");
	$added = false;
$is_save = $wpdb->get_row("SELECT * FROM $posts_table WHERE `pid`='$pid' LIMIT 1");
if(!$is_save){
	foreach($logins as $login) {
		$id = $login->id;
		if (isset($_POST['lici-'.$id])) {
			$added = true;
			$post = get_post($pid);
			$cr = (isset($_POST['lici-'.$login->id.'-closerec']))?"yes":"no";
			$co = (isset($_POST['lici-'.$login->id.'-comments']))?"yes":"no";
			$original = (isset($_POST['lici-'.$login->id.'-original']))?"yes":"no";
			$onlymore = (isset($_POST['lici-'.$login->id.'-onlymore']))?"yes":"no";
			$errors = lici_send_data($post, $login, $_POST['lici-'.$login->id.'-whereiam'], $_POST['lici-'.$login->id.'-mood'], $_POST['lici-'.$login->id.'-listening'], $cr, $co, $original, $onlymore);
		}
	}
}
	return $pid;
}

add_action("admin_notices","lici_wp_notices");

function lici_wp_notices() {
	if ((isset($_SESSION['wplicierror'])) && ($_SESSION['wplicierror'] !== "")) {
		print '<div style="background-color: rgb(207, 235, 247);" id="message" class="updated fade">
					 <p>'.$_SESSION['wplicierror'].'</p></div>';
		$_SESSION['wplicierror'] = "";
	}
}

function alert($text) {
	print "<script>alert($text);</script>";
}

function lici_send_data($post, $login, $whereiam = "", $mood = "", $listening = "", $closerec = "", $comments = "", $original = "", $onlymore = "")
{
	global $wpdb, $version;
	$posts_table = $wpdb->prefix . "lici_posts";
		$wpdb->query("INSERT INTO $posts_table VALUES('','$post->ID');");

		if (empty($whereiam))  { $whereiam = $login->whereiam; }
		if (empty($mood)) 	   { $mood = $login->mood; }
		if (empty($listening)) { $listening = $login->listening; }
		if (empty($closerec))  { $closerec = $login->closerec; }
		if (empty($comments))  { $comments = $login->comments; }
		if (empty($original))  { $original = $login->original; }
		if (empty($onlymore))  { $onlymore = $login->onlymore; }
		$server = "www.liveinternet.ru";
		$path = "/lici_offline.php";
		$r = "\r\n";
		$username = $login->login;
		$password = $login->pass;
		$messageheader = apply_filters("the_title",$post->post_title);
		$messagepost = apply_filters("the_content",$post->post_content);
		$origurl = get_permalink($post->ID);
		$boundary = '---------------------' . substr(md5(rand(0, 32000)), 0, 10);
		$xml .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
		$xml .= "<QUERIS username=\"$username\" password=\"$password\">\r\n";
		$xml .= " <query QID=\"1\" TYPE=\"3\">\r\n";
		$xml .= "  <headerofpost>$messageheader</headerofpost>\r\n";
		$xml .= "<message>";

			if($origurl)
			{
				$xml .= htmlspecialchars("<b><a href = \"$origurl\" target = \"_blank\">Оригинал сообщения</a></b><br />");
			}
			if ($login->includecomm === "yes")
			{
				$xml .= htmlspecialchars("<a href='".$origurl."' target = \"_blank\">Комментарии: <img src='".get_bloginfo("url")."/wp-content/plugins/lici-wp/lici-comment-count.php?id=".$post->ID."&lilogin=".$login->id."' alt='Комментарии' align='absbottom' /></a><br /><br />");
			}

			//if post has a more cut <!--more--> then display only text before more
		if($onlymore === 'yes') {
	      		if(preg_match('/<!--more(.*?)?-->/', $messagepost, $matches))
			{
	    		$content = explode($matches[0], $messagepost, 2);
    			if ( !empty($matches[1]) )
				$more_link_text = strip_tags(wp_kses_no_null(trim($matches[1])));
				if (empty($more_link_text))
				{

					if($original === "yes")
					{
					$xml.= htmlspecialchars($content[0]);
					$xml.= htmlspecialchars('<p><a href="'.$origurl.'#more-'.$post->ID.'">Читать далее...</a></p>');
					}
				}
				else
				{
					if($original === "yes")
					{
	   				$xml.= htmlspecialchars($content[0]);
					$xml.= htmlspecialchars('<p><a href="'.$origurl.'#more-'.$post->ID.'">'.__($more_link_text.' &rarr;', LICI_WP) .'</a></p>');
					}
				}
		  	}
			else
			{
				$xml .= htmlspecialchars($messagepost);
			}
		   }else{
		   	    $xml .= htmlspecialchars($messagepost);
		   }
		  	// end of cut text post

			if(!empty($whereiam))
			{
				$xml .= htmlspecialchars("<b>Я сейчас нахожусь:</b> ".$whereiam."<br />");
			}
			if(!empty($mood))
			{
				$xml .= htmlspecialchars("<b>Мой настрой:</b> ".$mood."<br />");
			}
			if(!empty($listening))
			{
				$xml .= htmlspecialchars("<b>Я слушаю:</b> <a href = \"http://www.liveinternet.ru/msearch/?q=".urlencode($listening)."\" target = \"_blank\">".$listening."</a>");
			}

			$xml .= htmlspecialchars("<br /><br /><a style=\"font-size:9px;font-style:italic;font-weight:bold;\" title=\"Плагин кросспостинга для WordPress LIci WP\" href=\"http://www.lici.ru/\">LIci WP</a>");

		$xml .= "</message>\r\n";
		$xml .= "  <parseurl>1</parseurl>\r\n";
		if(($closerec === "yes"))
			{
				$xml .= "  <privatepost>1</privatepost>\r\n";
			}
		if($comments === "yes")
		{
			$xml .= "<nocomment>1</nocomment>\r\n";
		}
		$xml .= " </query>\r\n";
		$xml .= "</QUERIS>\r\n";


		$data  = "--$boundary\r\n";
		$data .= "Content-Disposition: form-data; name=\"xmlfile\"; filename=\"xmlfile\"\r\n";
		$data .= "Content-Type: text/xml; charset=UTF-8\r\n\r\n";
		$data .= "$xml\r\n";
		$data .= "--$boundary\r\n";
		$lenght = strlen($data);
		$request  = "POST $path HTTP/1.0\r\n";
		$request .= "Host: $server\r\n";
		$request .= "Content-Type: multipart/form-data, boundary=$boundary\r\n";
		$request .= "Content-Length: " . $lenght . "\r\n\r\n";
		$request .= $data;
		$fs = fsockopen($server, 80, $errno, $errstr, 30);
		if ($fs)
		{
			fwrite($fs, $request);
			$content = '';
			while(!feof($fs))
			{
				$content .= fgets($fs, 128);
			}
			fclose($fs);
		}


		$s_server = 'lici.ru';
		$s_path = '/lib/stat.php';
		$s_ip = $_SERVER["REMOTE_ADDR"];
		$s_browser = $_SERVER["HTTP_USER_AGENT"];
		if ( $closerec == 'no' ) { $s_type = 'open'; } else { $s_type = 'closed'; }

		$s_data = 'username='.$username;
		$s_data .= '&post_id='.$post->ID;
		$s_data .= '&post_type='.$closerec;
		$s_data .= '&post_title='.$messageheader;
		$s_data .= '&post_place='.$whereiam;
		$s_data .= '&post_mood='.$mood;
		$s_data .= '&poster_music='.$listening;
		$s_data .= '&poster_ip='.$s_ip;
		$s_data .= '&permalink='.$origurl;
		$s_data .= '&poster_browser='.$s_browser;
		$s_data .= '&cross_client=lici_wp';
		$s_data .= '&version='.$version;
		$d_data .= '&char='.$blog_char;

		$s_len = strlen($s_data);

		$s_req  = "POST $s_path HTTP/1.0$r";
    	$s_req .= "Host: $s_server$r";
    	$s_req .= "Content-Type: application/x-www-form-urlencoded$r";
    	$s_req .= "User-Agent: WordPress LIci CrossPoster$r";
    	$s_req .= "Content-length: $s_len$r$r";
    	$s_req .= $s_data;

    	$s_fs = fsockopen($s_server, 80, $s_errno, $s_errstr, 30);
		if ($s_fs)
		{
			fwrite($s_fs, $s_req);
			$s_content = '';
			while(!feof($s_fs))
			{
				$s_content .= fgets($s_fs, 128);
			}
			fclose($s_fs);
		}


		$weblog = lici_wp_getfoaf('weblog', $username);
		if (!empty($weblog))
		{
			$_SESSION['wplicierror'] .= "Запись опубликована на Liveinternet.ru в блоге <a href = \"".$weblog."\" target = \"_blank\">".$username."</a>.<br />";
		}
		else
		{
			$_SESSION['wplicierror'] .= "Запись опубликована на Liveinternet.ru в блоге <a href = 'http://www.liveinternet.ru/users/".$username."' target = \"_blank\">.$username.</a>.<br />";
		}

}

function lici_get_fonts($selected = "") {
	$dir = '../wp-content/plugins/lici-wp/fonts/';
    $files = scandir($dir);
    $r = "";
    foreach($files as $f) {
    	if (preg_match("/\.ttf/",$f)) {
    		$sel = ($selected === $f) ? " selected" : "";
    		$r .= "<option value='$f'$sel>$f</option>\n";
    	}
	}
	return $r;
}


/* Виджет вывода бложиков
----------------------------------------------- */
	/* -----------[ Код виджета ]----------- */
		function widget_lici_register()
		{
			if (function_exists('register_sidebar_widget'))
			{
				/* - Описываем вывод виджета - */
					function widget_lici()
					{
						function wigdet_lici_href ($href,$title,$class)
						{
							if ($href!="http://" && $href!="sidebar") echo "<li class=\"lici-$class\"><a target=\"_blank\" href=\"$href\">$title</a></li>";
						}

						$options = get_option('widget_lici');
						$liru = $options['liru'] ? $options['liru'] : 'sidebar';
						$lj = $options['lj'] ? $options['lj'] : 'sidebar';
						$mail = $options['mail'] ? $options['mail'] : 'sidebar';
						$yaru = $options['yaru'] ? $options['yaru'] : 'sidebar';
						$blogger = $options['blogger'] ? $options['blogger'] : 'sidebar';
						$diarea = $options['diarea'] ? $options['diarea'] : 'sidebar';
						$blogru = $options['blogru'] ? $options['blogru'] : 'sidebar';
						$twitter = $options['twitter'] ? $options['twitter'] : 'sidebar';
						$juick = $options['juick'] ? $options['juick'] : 'sidebar';

						echo "<div class=\"widget lici_widget\">";
							echo "<h2>Другие мои блоги</h2>";
							echo "<ul>";
								wigdet_lici_href ($liru, 'На LiveInternet', 'liru');
								wigdet_lici_href ($lj, 'На LiveJournal', 'lj');
								wigdet_lici_href ($mail, 'На Mail.Ru', 'mail');
								wigdet_lici_href ($yaru, 'На Я.ру', 'yaru');
								wigdet_lici_href ($blogger, 'На Blogger', 'blogger');
								wigdet_lici_href ($diarea, 'На Diary.ru', 'diarea');
								wigdet_lici_href ($blogru, 'На Блог ру', 'blogru');
								wigdet_lici_href ($twitter, 'На Twitter', 'twitter');
								wigdet_lici_href ($juick, 'На Juick', 'juick');
							echo "</ul>";
							echo "<strong><a target=\"_blank\" title=\"Кросспостинг из WordPress в LiveInternet\" href=\"http://www.lici.ru/\">Плагин кросспостинга</a></strong>";
						echo "</div>";
					}
				/* - Описываем опции виджета - */
					function widget_lici_options()
					{
					    $options = $newoptions = get_option('widget_lici');

					    if ( $_POST['widget_lici_submit'] )
					    {
		        			$newoptions['liru'] = stripslashes($_POST['widget_lici_liru']);
							$newoptions['lj'] = stripslashes($_POST['widget_lici_lj']);
							$newoptions['mail'] = stripslashes($_POST['widget_lici_mail']);
							$newoptions['yaru'] = stripslashes($_POST['widget_lici_yaru']);
							$newoptions['blogger'] = stripslashes($_POST['widget_lici_blogger']);
							$newoptions['diarea'] = stripslashes($_POST['widget_lici_diarea']);
							$newoptions['blogru'] = stripslashes($_POST['widget_lici_blogru']);
							$newoptions['twitter'] = stripslashes($_POST['widget_lici_twitter']);
							$newoptions['juick'] = stripslashes($_POST['widget_lici_juick']);
		        		}
		    			if ( $options != $newoptions )
		    			{
		        			$options = $newoptions;
		        			update_option('widget_lici', $options);
		    			}

		    			$liru = attribute_escape($options['liru']);
						$lj = attribute_escape($options['lj']);
						$mail = attribute_escape($options['mail']);
						$yaru = attribute_escape($options['yaru']);
						$blogger = attribute_escape($options['blogger']);
						$diarea = attribute_escape($options['diarea']);
						$blogru = attribute_escape($options['blogru']);
						$twitter = attribute_escape($options['twitter']);
						$juick = attribute_escape($options['juick']);

						$lici_widget_prefix = "http://";
						if (empty($liru))
						{
							global $wpdb;
							$options_table = $wpdb->prefix."lici_options";
							$lid = get_option("lici-default");
							$login = $wpdb->get_row("SELECT * FROM $options_table WHERE `id`='$lid' LIMIT 1;");
							$liru = lici_wp_getfoaf ('weblog',$login->login);
							if (empty($liru))
							{
								$liru = 'http://www.liveinternet.ru/users/'.$login->login;
							}
						}
						if (empty($lj)) {$lj = $lici_widget_prefix.$login->login.'.livejournal.com/';}
						if (empty($mail)) {$mail = $lici_widget_prefix.'blogs.mail.ru/mail/'.$login->login.'/';}
						if (empty($yaru)) {$yaru = $lici_widget_prefix.$login->login.'.ya.ru/';}
						if (empty($blogger)) {$blogger = $lici_widget_prefix.$login->login.'.blogspot.com/';}
						if (empty($diarea)) {$diarea = $lici_widget_prefix.'www.diary.ru/~'.$login->login.'/';}
						if (empty($blogru)) {$blogru = $lici_widget_prefix.$login->login.'.blog.ru/';}
						if (empty($twitter)) {$twitter = $lici_widget_prefix.'twitter.com/'.$login->login.'/';}
						if (empty($juick)) {$juick = $lici_widget_prefix.'juick.ru/'.$login->login.'/';}

		    			echo <<<EOF
		    				Адрес дневника LiveInternet:
		    				<input id="widget_lici_liru"
								name="widget_lici_liru"
								type="text"
								value="{$liru}" />
								<br />
		    				Адрес дневника LiveJournal:
		    				<input id="widget_lici_lj"
								name="widget_lici_lj"
								type="text"
								value="{$lj}" />
								<br />
		    				Адрес дневника Mail.Ru:
		    				<input id="widget_lici_mail"
								name="widget_lici_mail"
								type="text"
								value="{$mail}" />
								<br />
		    				Адрес дневника Я.ру:
		    				<input id="widget_lici_yaru"
								name="widget_lici_yaru"
								type="text"
								value="{$yaru}" />
								<br />
		    				Адрес дневника Blogger:
		    				<input id="widget_lici_blogger"
								name="widget_lici_blogger"
								type="text"
								value="{$blogger}" />
								<br />
		    				Адрес дневника Diary.ru:
		    				<input id="widget_lici_diarea"
								name="widget_lici_diarea"
								type="text"
								value="{$diarea}" />
								<br />
		    				Адрес дневника Блог ру:
		    				<input id="widget_lici_blogru"
								name="widget_lici_blogru"
								type="text"
								value="{$blogru}" />
								<br />
		    				Адрес микроблога Twitter:
		    				<input id="widget_lici_twitter"
								name="widget_lici_twitter"
								type="text"
								value="{$twitter}" />
								<br />
		    				Адрес микроблога <a target="_blank" href="http://juick.com/">Juick</a>:
		    				<input id="widget_lici_juick"
								name="widget_lici_juick"
								type="text"
								value="{$juick}" />
								<br />
							<input type="hidden"
								id="widget_lici_submit"
								name="widget_lici_submit"
								value="1" />
EOF;
					}
			}

			/* - Регистрируем виджет и опции - */
				register_sidebar_widget('LIci my♥Blogs', 'widget_lici');
				register_widget_control('LIci my♥Blogs', 'widget_lici_options');

		}

	/* -----------[ Код стиля ]----------- */
		function widget_lici_head()
		{
			$css_url = get_bloginfo("wpurl") . "/wp-content/plugins/lici-wp/lici-wp.css";
			if (file_exists(TEMPLATEPATH . "/lici-wp.css"))
			{
				$css_url = get_bloginfo("template_url") . "/lici-wp.css";
			}
			echo "\n".'<link rel="stylesheet" href="' . $css_url . '" type="text/css" media="screen" />'."\n";
		}

	add_action('init', 'widget_lici_register');
	register_activation_hook( __FILE__, array('LIci my♥Blos', 'activate'));
	add_action('wp_head', 'widget_lici_head');
?>