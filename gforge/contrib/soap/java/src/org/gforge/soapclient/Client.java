package org.gforge.soapclient;

import org.apache.axis.Constants;
import org.apache.axis.client.Call;
import org.apache.axis.client.Service;
import org.apache.axis.encoding.ser.ArrayDeserializerFactory;
import org.apache.axis.encoding.ser.ArraySerializerFactory;
import org.apache.axis.encoding.ser.BeanDeserializerFactory;
import org.apache.axis.encoding.ser.BeanSerializerFactory;

import javax.xml.namespace.QName;
import javax.xml.rpc.ParameterMode;
import javax.xml.rpc.ServiceException;
import java.rmi.RemoteException;

public class Client {

    private Service service;
    private String server;
    private String sessionKey;
    private String group;

    private QName siteStats;
    private QName activeUsers;
    private QName publicProjectNames;
    private QName hostedProjects;
    private QName bugFetch;
    private QName bugList;
    private QName bugAdd;
    private QName login;
    private QName logout;

    public Client(String server, String initialGroup) throws ServiceException{
        this.server = "http://" + server + "/soap/SoapAPI.php";

        bugFetch = new QName(this.server, "bugFetch");
        bugList = new QName(this.server, "bugList");
        bugAdd = new QName(this.server, "bugAdd");
        login = new QName(this.server, "login");
        logout = new QName(this.server, "logout");
        hostedProjects = new QName(this.server, "getNumberOfHostedProjects");
        activeUsers = new QName(this.server, "getNumberOfActiveUsers");
        publicProjectNames = new QName(this.server, "getPublicProjectNames");
        siteStats = new QName(this.server, "getSiteStats");

        this.group = initialGroup;
        this.service = new Service();

        Call call = (Call)service.createCall();
        call.setEncodingStyle(Constants.URI_DEFAULT_SOAP_ENC);
        call.registerTypeMapping(Bug.class, Bug.QNAME, new BeanSerializerFactory(Bug.class, Bug.QNAME), new BeanDeserializerFactory(Bug.class, Bug.QNAME));
        call.registerTypeMapping(SiteStatsDataPoint.class, SiteStatsDataPoint.QNAME, new BeanSerializerFactory(SiteStatsDataPoint.class, SiteStatsDataPoint.QNAME), new BeanDeserializerFactory(SiteStatsDataPoint.class, SiteStatsDataPoint.QNAME));
    }

    public void switchToGroup(String newGroup) {
        this.group = newGroup;
    }

    public SiteStatsDataPoint[] getSiteStats() throws ServiceException, RemoteException {
        Call call = createCallTo(siteStats);
        call.setReturnType(SiteStatsDataPoint.QNAME, SiteStatsDataPoint[].class);
        return (SiteStatsDataPoint[])call.invoke(new Object[] {});
    }

    public int getNumberOfHostedProjects() throws ServiceException, RemoteException {
        Call call = createCallTo(hostedProjects);
        call.setReturnType(Constants.XSD_STRING);
        return Integer.parseInt((String)call.invoke(new Object[] {}));
    }

    public String[] getPublicProjectNames() throws ServiceException, RemoteException {
        Call call = createCallTo(publicProjectNames);
        call.setReturnType(Constants.XSD_ANYTYPE);
        return returnStringArrayOrEmptyArray((Object[])call.invoke(new Object[] {}));
    }

    public int getNumberOfActiveUsers() throws ServiceException, RemoteException {
        Call call = createCallTo(activeUsers);
        call.setReturnType(Constants.XSD_STRING);
        return Integer.parseInt((String)call.invoke(new Object[] {}));
    }

    public void login(String userid, String passwd) throws ServiceException, RemoteException {
        Call call = createCallTo(login);
        call.addParameter("userid", Constants.XSD_STRING, ParameterMode.IN);
        call.addParameter("passwd", Constants.XSD_STRING, ParameterMode.IN);
        call.setReturnType(Constants.XSD_STRING);
        sessionKey = (String)call.invoke(new Object[] {userid,passwd});
    }

    public String[] bugList() throws ServiceException, RemoteException {
        Call call = createCallTo(bugList);
        call.addParameter("sessionkey", Constants.XSD_STRING, ParameterMode.IN);
        call.addParameter("project", Constants.XSD_STRING, ParameterMode.IN);
        call.setReturnType(Constants.XSD_ANYTYPE);
        return returnStringArrayOrEmptyArray((Object[])call.invoke(new Object[] {}));
    }

    public Bug bugFetch(String bugID)  throws ServiceException, RemoteException {
        Call call = createCallTo(bugFetch);
        call.addParameter("sessionkey", Constants.XSD_STRING, ParameterMode.IN);
        call.addParameter("project", Constants.XSD_STRING, ParameterMode.IN);
        call.addParameter("bugid", Constants.XSD_STRING, ParameterMode.IN);
        call.setReturnType(Bug.QNAME);
        return (Bug)call.invoke(new Object[] {sessionKey,group,bugID});
    }

    public String bugAdd(String summary, String comment)  throws ServiceException, RemoteException {
        Call call = createCallTo(bugAdd);
        call.addParameter("sessionkey", Constants.XSD_STRING, ParameterMode.IN);
        call.addParameter("project", Constants.XSD_STRING, ParameterMode.IN);
        call.addParameter("summary", Constants.XSD_STRING, ParameterMode.IN);
        call.addParameter("comment", Constants.XSD_STRING, ParameterMode.IN);
        call.setReturnType(Constants.XSD_STRING);
        return (String)call.invoke(new Object[] {sessionKey, group, summary, comment});
    }

    public void logout() throws ServiceException, RemoteException {
        Call call = createCallTo(logout);
        call.addParameter("sessionkey", Constants.XSD_STRING, ParameterMode.IN);
        call.setReturnType(Constants.XSD_STRING);
        call.invoke(new Object[] {sessionKey}).toString();
    }

    private Call createCallTo(QName operation) throws ServiceException{
        Call call = (Call)service.createCall();
        call.setTargetEndpointAddress(server);
        call.setOperationName(operation);
        return call;
    }

    private String[] returnStringArrayOrEmptyArray(Object[] arr) {
        if (arr.length == 0) {
            return new String[] {};
        }
        return (String[]) arr;
    }

}
/*
Some test code for future munging

private QName echoStruct;
private QName echoStructArray;

echoStruct = new QName(this.server, "echoStruct");
echoStructArray = new QName(this.server, "echoStructArray");


call.registerTypeMapping(SOAPStruct.class, SOAPStruct.QNAME, new BeanSerializerFactory(SOAPStruct.class, SOAPStruct.QNAME), new BeanDeserializerFactory(SOAPStruct.class, SOAPStruct.QNAME));
call.registerTypeMapping(ArrayOfSOAPStruct.class, ArrayOfSOAPStruct.QNAME, new ArraySerializerFactory(), new ArrayDeserializerFactory());

public SOAPStruct echoStruct() throws ServiceException, RemoteException, SOAPException  {
    Call call = createCallTo(echoStruct);
    call.setReturnType(SOAPStruct.QNAME);
    return (SOAPStruct)call.invoke(new Object[] {});
}

public SOAPStruct[] echoStructArray() throws ServiceException, RemoteException, SOAPException {
    Call call = createCallTo(echoStructArray);
    call.setReturnQName(ArrayOfSOAPStruct.QNAME);
    return (SOAPStruct[])call.invoke(new Object[] {});
}




package org.gforge.soapclient;

import javax.xml.namespace.QName;

public class SOAPStruct {
    public static final QName QNAME = new QName("SOAPStruct");
    public SOAPStruct() {}
    public SOAPStruct(String in) {this.varString = in;}
    public String getVarString() {
        return varString;
    }
    public void setVarString(String varString) {
        this.varString = varString;
    }
    private String varString;
    public String toString() {
        return "SOAPStruct: " + varString;
    }
}




package org.gforge.soapclient;

import javax.xml.namespace.QName;

public class ArrayOfSOAPStruct {

    public static final QName QNAME = new QName("ArrayOfSOAPStruct");

    public SOAPStruct[] getRet() {
        return ret;
    }

    public void setRet(SOAPStruct[] ret) {
        this.ret = ret;
    }

    private SOAPStruct[] ret;

}






package org.gforge.soapclient;

import javax.xml.namespace.QName;

public class ArrayOfSiteStatsDataPoint {

    public static final QName QNAME = new QName("ArrayOfSiteStatsDataPoint");

    public void setSiteStats(SiteStatsDataPoint[] siteStats) {
        this.siteStats = siteStats;
    }

    private SiteStatsDataPoint[] siteStats;

}

*/