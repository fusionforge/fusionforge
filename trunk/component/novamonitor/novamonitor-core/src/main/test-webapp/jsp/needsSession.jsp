<%@ page session="true" %>
<%= "Session " + (request.getSession().isNew() ? "cr��e" : "d�j� existante") %>
<br/>
<a href="../index.jsp">back</a>
