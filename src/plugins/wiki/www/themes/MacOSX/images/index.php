<html><head><title>Alpha Channel Test</title>
<script type="text/javascript"><!--
var backgroundcolor = new Array();
backgroundcolor[0] = '#ffffff';
backgroundcolor[1] = '#cccccc';
backgroundcolor[2] = '#888888';
backgroundcolor[3] = '#444444';
backgroundcolor[4] = '#000000';
backgroundcolor[5] = '#aa8888';
backgroundcolor[6] = '#88aa88';
backgroundcolor[7] = '#8888aa';
function changebg(color) { document.bgColor = backgroundcolor[color]; }
//--></script>
</head><body bgcolor="#8888aa">
<?php
$dir = opendir(".");
while($fileName = readdir($dir))
if (!(strcmp(substr($fileName, -4), ".png")))
    print("<img src=\"" . urlencode($fileName) . "\" alt=\"$fileName\" />\n");
?>
<hr>
bgcolor:
<script type="text/javascript"><!--
for (var n = 0; n < backgroundcolor.length; n++)
{ document.write(
' <a href="#" onmouseover="javascript:changebg(' + n + ')">' + backgroundcolor[n] + '</a>'
); }
//--></script>
<hr>
</body></html>
