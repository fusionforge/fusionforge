package org.gforge.soapclient;

import javax.swing.JLabel;
import javax.swing.JPanel;
import javax.swing.JPasswordField;
import javax.swing.JTextField;
import java.awt.BorderLayout;
import java.awt.GridLayout;

public class ConfigurationPanel extends JPanel {

    public static final String GROUP_PROPERTY = "group";
    public static final String SERVER_PROPERTY = "server";
    public static final String USERID_PROPERTY = "userid";
    public static final String PASSWD_PROPERTY = "password";

    private JTextField serverField = new JTextField(25);
    private JTextField groupField = new JTextField(12);
    private JTextField useridField = new JTextField(10);
    private JPasswordField passwdField = new JPasswordField(10);

    public ConfigurationPanel() {
        super();

        JPanel authPanel = new JPanel(new GridLayout(4,2));
        authPanel.add(new JLabel("Server:"));
        serverField.setText(Settings.getInstance().get(SERVER_PROPERTY));
        authPanel.add(serverField);
        authPanel.add(new JLabel("Project:"));
        groupField.setText(Settings.getInstance().get(GROUP_PROPERTY));
        authPanel.add(groupField);
        authPanel.add(new JLabel("User id:"));
        useridField.setText(Settings.getInstance().get(USERID_PROPERTY));
        authPanel.add(useridField);
        authPanel.add(new JLabel("Password:"));
        passwdField.setText(Settings.getInstance().get(PASSWD_PROPERTY));
        authPanel.add(passwdField);

        JPanel mainPanel = new JPanel(new BorderLayout());
        mainPanel.add(authPanel, BorderLayout.NORTH);
        add(mainPanel);
    }

    public void save() {
        Settings.getInstance().save(SERVER_PROPERTY, serverField.getText());
        Settings.getInstance().save(GROUP_PROPERTY, groupField.getText());
        Settings.getInstance().save(USERID_PROPERTY, useridField.getText());
        Settings.getInstance().save(PASSWD_PROPERTY, String.valueOf(passwdField.getPassword()));
    }
}
