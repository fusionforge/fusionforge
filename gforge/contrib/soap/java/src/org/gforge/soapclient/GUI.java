package org.gforge.soapclient;

import javax.swing.JComponent;
import javax.swing.JFrame;
import javax.swing.JMenu;
import javax.swing.JMenuBar;
import javax.swing.JMenuItem;
import javax.swing.JOptionPane;
import javax.swing.JTabbedPane;
import javax.swing.event.ChangeEvent;
import javax.swing.event.ChangeListener;
import java.awt.Toolkit;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;

public class GUI {

    private class SwitchedTabsListener implements ChangeListener {
        public void stateChanged(ChangeEvent e) {
            if (saveConfigurationOnNextTabSwitch) {
                configurationPanel.save();
                saveConfigurationOnNextTabSwitch = false;
            }
            JTabbedPane pane = (JTabbedPane)e.getSource();
            if (pane.getSelectedComponent() instanceof ConfigurationPanel) {
                saveConfigurationOnNextTabSwitch = true;
            }
        }
    }

    private boolean saveConfigurationOnNextTabSwitch;

    private static class AboutListener implements ActionListener {
        public void actionPerformed(ActionEvent e) {
            JOptionPane.showMessageDialog(null, "Comments?  Suggestions?  http://gforge.org/projects/gforge/.  Thanks!");
        }
    }

    private static class ExitListener implements ActionListener {
        public void actionPerformed(ActionEvent e) {
            System.exit(0);
        }
    }

    private JFrame frame;

    public GUI() {
        frame = new JFrame("GForge");

        frame.getContentPane().add(getTabbedPanel());
        frame.setJMenuBar(getMenuBar());

        frame.setSize(850, 600);
        int screenHeight = Toolkit.getDefaultToolkit().getScreenSize().height;
        int screenWidth = Toolkit.getDefaultToolkit().getScreenSize().width;
        frame.setLocation((screenWidth/2) - frame.getWidth()/2, (screenHeight/2) - frame.getHeight()/2);
        frame.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        frame.show();
    }

    private JMenuBar getMenuBar() {
        JMenuBar menuBar = new JMenuBar();

        JMenu fileMenu = new JMenu("File");
        fileMenu.setMnemonic('f');
        JMenuItem exitItem = new JMenuItem("Exit");
        exitItem.setMnemonic('x');
        exitItem.addActionListener(new ExitListener());
        fileMenu.add(exitItem);
        menuBar.add(fileMenu);

        JMenu helpMenu = new JMenu("Help");
        JMenuItem aboutItem = new JMenuItem("About");
        aboutItem.addActionListener(new AboutListener());
        helpMenu.add(aboutItem);
        menuBar.add(helpMenu);

        return menuBar;
    }

    private ConfigurationPanel configurationPanel = new ConfigurationPanel();
    private GeneralPanel generalPanel = new GeneralPanel();
    private StatisticsPanel statsPanel = new StatisticsPanel();

    private JComponent getTabbedPanel() {
        JTabbedPane tabbedPane = new JTabbedPane();
        tabbedPane.add("General", generalPanel);
        tabbedPane.add("Charts", statsPanel);
        tabbedPane.add("Configuration", configurationPanel);
        tabbedPane.addChangeListener(new SwitchedTabsListener());
        return tabbedPane;
    }

    public static void main(String [] args) {
        new GUI();
    }
}
