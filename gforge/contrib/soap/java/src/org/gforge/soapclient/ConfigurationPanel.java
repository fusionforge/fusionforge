package org.gforge.soapclient;

import javax.swing.*;

public class ConfigurationPanel {

    public static final String NAME = "GForge";

    public static final String PROJECT_PROPERTY = "project";
    public static final String SERVER_PROPERTY = "server";
    public static final String USERID_PROPERTY = "userid";
    public static final String PASSWD_PROPERTY = "password";

    private JTextField serverField = new JTextField(25);
    private JTextField projectField = new JTextField(12);
    private JTextField useridField = new JTextField(10);
    private JPasswordField passwdField = new JPasswordField(10);

/*
    public void init() {
        JPanel authPanel = new JPanel(new GridLayout(4,2));
        authPanel.add(new JLabel("Server:"));
        serverField.setText(jEdit.getProperty(GForgeJEditPlugin.SERVER_PROPERTY));
        authPanel.add(serverField);
        authPanel.add(new JLabel("Project:"));
        projectField.setText(jEdit.getProperty(GForgeJEditPlugin.PROJECT_PROPERTY));
        authPanel.add(projectField);
        authPanel.add(new JLabel("User id:"));
        useridField.setText(jEdit.getProperty(GForgeJEditPlugin.USERID_PROPERTY));
        authPanel.add(useridField);
        authPanel.add(new JLabel("Password:"));
        passwdField.setText(jEdit.getProperty(GForgeJEditPlugin.PASSWD_PROPERTY));
        authPanel.add(passwdField);

        JPanel mainPanel = new JPanel(new BorderLayout());
        mainPanel.add(authPanel, BorderLayout.NORTH);
        addComponent(mainPanel);
    }

    public void save() {
        jEdit.setProperty(GForgeJEditPlugin.SERVER_PROPERTY, serverField.getText());
        jEdit.setProperty(GForgeJEditPlugin.PROJECT_PROPERTY, projectField.getText());
        jEdit.setProperty(GForgeJEditPlugin.USERID_PROPERTY, useridField.getText());
        jEdit.setProperty(GForgeJEditPlugin.PASSWD_PROPERTY, String.valueOf(passwdField.getPassword()));
    }
*/
}
