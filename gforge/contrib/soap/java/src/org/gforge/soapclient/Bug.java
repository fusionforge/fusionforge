package org.gforge.soapclient;

import javax.xml.namespace.QName;

public class Bug {

    public static final QName QNAME = new QName("Bug");

    private String id;
    private String summary;

    public void setId(String id) {
        this.id = id;
    }
    public void setSummary(String summary) {
        this.summary = summary;
    }

    public String getId() {
        return id;
    }

    public String getSummary() {
        return summary;
    }

    public String toString() {
        return "Bug: id=" + this.id + ", summary=" + this.summary;
    }
}
