package org.gforge.soapclient;

import javax.xml.namespace.QName;

public class SiteStatsDataPoint {

    public static final QName QNAME = new QName("SiteStatsDataPoint");

    private String date;
    private String users;
    private String sessions;

    public String getDate() {
        return date;
    }

    public void setDate(String date) {
        this.date = date;
    }

    public String getUsers() {
        return users;
    }

    public void setUsers(String users) {
        this.users = users;
    }

    public String getSessions() {
        return sessions;
    }

    public void setSessions(String sessions) {
        this.sessions = sessions;
    }

    public String toString() {
        return "date - " + date + ": users - " + users + ": sessions - " + sessions;
    }
}
