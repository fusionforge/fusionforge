package org.gforge.soapclient;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.IOException;
import java.util.Date;
import java.util.Properties;

public class Settings {

    private File settingsFile;

    public static Settings getInstance() {
        return new Settings();
    }

    private Settings() {
        File homeDir = new File(System.getProperty("user.home"));
        if (!homeDir.exists()) {
            homeDir.mkdir();
        }

        File settingsDir = new File(homeDir.getAbsolutePath() + System.getProperty("file.separator") + ".gforgejavasoapclient");
        if (!settingsDir.exists()) {
            settingsDir.mkdir();
        }

        settingsFile = new File(settingsDir.getAbsolutePath(), "settings.txt");
    }

    public void save(String key, String value) {
        try {
            Properties savedProperties = new Properties();

            if (settingsFile.exists()) {
                loadProps(savedProperties);
            }

            savedProperties.setProperty(key, value);

            FileOutputStream fos = new FileOutputStream(settingsFile);
            savedProperties.store(fos, "GForge Java SOAP client settings " + new Date());
            fos.close();
        } catch (Exception e) {
            e.printStackTrace();
            throw new RuntimeException(e.getMessage());
        }

    }

    public String get(String key) {
        try {
            Properties properties = new Properties();
            if (settingsFile.exists()) {
                loadProps(properties);
                return properties.getProperty(key);
            }
            return "";
        } catch (Exception e) {
            e.printStackTrace();
            throw new RuntimeException(e.getMessage());
        }

    }

    private void loadProps(Properties properties) throws IOException {
        FileInputStream fis = new FileInputStream(settingsFile);
        properties.load(fis);
        fis.close();
    }
}

