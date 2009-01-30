<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 3.2//EN">
<html>
	<head>
		<link rel="shortcut icon" href="favicon.ico">
		<title>FusionForge home page</title>
		<style type="text/css" media="screen,projection">/*<![CDATA[*/ @import "main.css"; /*]]>*/</style>
		<script type="text/javascript" src="tabber.js"></script>
		<link rel="stylesheet" href="tabber.css" TYPE="text/css" MEDIA="screen">
		<script type="text/javascript">
		document.write('<style type="text/css">.tabber{display:none;}<\/style>');
		</script>
	</head>
	<body>
	<blockquote>
		<h1>FusionForge home page</h1>
		<h4>[<a HREF="http://packages.qa.debian.org/g/gforge.html">Forge package</a> |
		<a HREF="http://fusionforge.org">FusionForge Project</a> |
		<a href="http://wiki.planetforge.org/">PlanetForge Wiki</a> |
		<a href="http://planetforge.org/">PlanetForge Agregator</a> |
		<a href="mib.html">IRC Chat</a> ]</h4>
			

		<div id="tabber" class="tabber" >
			<div class="tabbertab" title="Debian">
				<h4>For debian you should create /etc/apt/sources.list.d/fusionforge file containing one of:</h4>
				<pre>deb http://<?php echo $_SERVER['SERVER_NAME']; ?>/debian lenny main
deb-src http://<?php echo $_SERVER['SERVER_NAME']; ?>/debian lenny main</pre>
				<pre>deb http://<?php echo $_SERVER['SERVER_NAME']; ?>/debian sid main
deb-src http://<?php echo $_SERVER['SERVER_NAME']; ?>/debian sid main</pre>
				<h5>You may have to add the archive key by running:</h5>
				<pre>wget -q http://<?php echo $_SERVER['SERVER_NAME']; ?>/key -O- | sudo apt-key add -</pre>
				<h5>then run the usual</h5>
				<pre>apt-get update ; apt-get install fusionforge</pre>
			</div>
			<div class="tabbertab" title="Ubuntu">
				<h4>For ubuntu you should create /etc/apt/sources.list.d/fusionforge file containing one of:</h4>
				<pre>deb http://<?php echo $_SERVER['SERVER_NAME']; ?>/debian gutsy main
deb-src http://<?php echo $_SERVER['SERVER_NAME']; ?>/debian gutsy main</pre>
				<pre>deb http://<?php echo $_SERVER['SERVER_NAME']; ?>/debian hardy main
deb-src http://<?php echo $_SERVER['SERVER_NAME']; ?>/debian hardy main</pre>
				<pre>deb http://<?php echo $_SERVER['SERVER_NAME']; ?>/debian intrepid main
deb-src http://<?php echo $_SERVER['SERVER_NAME']; ?>/debian intrepid main</pre>
				<h5>You may have to add the archive key by running:</h5>
				<pre>wget -q http://<?php echo $_SERVER['SERVER_NAME']; ?>/key -O- | sudo apt-key add -</pre>
				<h5>then run the usual</h5>
				<pre>apt-get update ; apt-get install fusionforge</pre>
			</div>
			<div class="tabbertab" title="Install CD">
				<h4>Install CD is <a href="http://www.planetforge.org/downloads/" >Here</a></h4>
			</div>
			<div class="tabbertab" title="Chat">
				<h5>For more info you can join FusionForge developpers on IRC at:</h5>
				<pre>irc.freenode.net channel #fusionforge </pre>
				<script>
				function bigit() {
					document.getElementById('myiframe').width = '800, *';
					document.getElementById('myiframe').height = '800, *';
				}
				function smallit() {
					document.getElementById('myiframe').width = '800, *';
					document.getElementById('myiframe').height = '200, *';
				}
				</script>
				<h6>Join #fusionforge on irc.freenode.net. Choose a nick and enter password if you have one. </h6>
				<form>
					<input id="mybutton1" type="button" onClick=bigit() value="Big Window">
					<input id="mybutton2" type="button" onClick=smallit() value="Small Window">
				</form>
				<iframe id="myiframe" width=800 height=200 scrolling=no style="border:0" src="http://embed.mibbit.com/?server=irc.freenode.net&channel=%23fusionforge&noServerNotices=true&noServerMotd=true&promptPass=true"></iframe>
			</div>
		</div>
	</blockquote>
	</body>
</html>
