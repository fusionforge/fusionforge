package org.gforge.soapclient;

import javax.xml.rpc.ServiceException;
import java.rmi.RemoteException;

public class AcceptanceTest {

    private static final String USERID = "tom";
    private static final String PASSWD = "tomtom";

    public void run() throws Exception {
        Client client = new Client("192.168.10.202", "othello");

        System.out.println("projects = " + client.getNumberOfHostedProjects());
        System.out.println("users = " + client.getNumberOfActiveUsers());

        String[] projectNames = client.getPublicProjectNames();
        for (int i=0;i<projectNames.length; i++) {
            System.out.println("projectNames = " + projectNames[i]);
        }

        if (true) return;

        System.out.print("Testing: login/logout...");
        testLogin(client);
        System.out.println("OK");

        System.out.print("Testing: bad login...");
        testBadLogin(client);
        System.out.println("OK");

        System.out.print("Testing: adding a bug...");
        String summary = "random summary " + System.currentTimeMillis();
        String bugID = testBugAdd(client, summary);
        System.out.println("OK");

        System.out.print("Testing: fetching details of one bug...");
        testBugFetch(client, bugID, summary);
        System.out.println("OK");

        System.out.print("Testing: fetching a list of bugs (and ensuring the bug we just added is there)...");
        testBugList(client, bugID);
        System.out.println("OK");

        System.out.println("ALL IS WELL");
    }

    private void testBugFetch(Client client, String targetID, String summary) throws ServiceException, RemoteException {
        client.login(USERID, PASSWD);
        Bug bug = client.bugFetch(targetID);
        if (!bug.getSummary().equals(summary)) {
            throw new RuntimeException("Summaries didn't match!");
        }
        client.logout();
    }

    private void testBugList(Client client, String targetID) throws ServiceException, RemoteException {
        client.login(USERID, PASSWD);
        String[] bugs = client.bugList();
        boolean found = false;
        for (int i=0; i<bugs.length; i++) {
            if (bugs[i].equals(targetID)) {
                found = true;
                break;
            }
        }
        if (!found) throw new RuntimeException("Couldn't find the bug!");
        client.logout();

    }

    private String testBugAdd(Client client, String summary) throws ServiceException, RemoteException {
        client.login(USERID, PASSWD);
        String id = client.bugAdd(summary, "random comment " + System.currentTimeMillis());
        client.logout();
        return id;
    }

    private void testLogin(Client client) throws Exception {
        client.login(USERID, PASSWD);
        client.logout();
    }

    private void testBadLogin(Client client) throws Exception {
        try {
            client.login(USERID, PASSWD + System.currentTimeMillis());
            throw new RuntimeException("Should have thrown an exception!");
        } catch (Exception ex) {
            // cool
        }
    }
     public static void main(String[] args) throws Exception {
         AcceptanceTest test = new  AcceptanceTest();
         test.run();
    }
}
