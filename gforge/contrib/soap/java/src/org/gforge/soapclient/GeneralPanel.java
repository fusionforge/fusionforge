package org.gforge.soapclient;

import javax.swing.JButton;
import javax.swing.JLabel;
import javax.swing.JPanel;
import java.awt.GridLayout;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;

public class GeneralPanel extends JPanel {

    private class RefreshListener implements ActionListener {
        public void actionPerformed(ActionEvent e) {
            try {
                Client client = new Client(Settings.getInstance().get(ConfigurationPanel.SERVER_PROPERTY),
                        Settings.getInstance().get(ConfigurationPanel.GROUP_PROPERTY)
                        );
                userCount = String.valueOf(client.getNumberOfActiveUsers());
                projectCount = String.valueOf(client.getNumberOfHostedProjects());
                refresh();
           }  catch (Exception ex) {
                ex.printStackTrace();
                userCount = "Can't contact server";
                projectCount = "Can't contact server";
            }
        }
    }

    private String userCount = "Unknown";
    private String projectCount = "Unknown";

    public GeneralPanel() {
        super();
        refresh();
    }

    private void refresh() {
        this.removeAll();
        this.repaint();
        JPanel stats = new JPanel(new GridLayout(3,1));
        stats.add(new JLabel("Projects: " + projectCount));
        stats.add(new JLabel("Users: " + userCount));
        JButton refreshButton = new JButton("Refresh");
        refreshButton.setMnemonic('r');
        refreshButton.addActionListener(new RefreshListener());
        stats.add(refreshButton);
        add(stats);
    }
}
