<%@ page session="false" %>
<html><body>

<a href="jsp/fast.jsp">fast page</a><br/>
<a href="jsp/slow.jsp">slow page</a><br/>
<a href="jsp/slower.jsp">slower page</a><br/>
<a href="jsp/slowest.jsp">slowest page</a><br/>
<a href="lorem_ipsum.txt?random=<%=  + Math.round(1000 * Math.random()) %>">lorem_ipsum.txt (ramdom)</a><br/>
<a href="notfound">not found</a><br/>
<a href="jsp/needsSession.jsp">session init</a><br/>
<br/>
<a href="monitoring">monitoring</a><br/>
</body></html>
