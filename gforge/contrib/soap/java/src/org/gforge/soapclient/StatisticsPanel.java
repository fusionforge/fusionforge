package org.gforge.soapclient;

import org.jfree.chart.ChartPanel;
import org.jfree.chart.JFreeChart;
import org.jfree.chart.axis.DateAxis;
import org.jfree.chart.axis.HorizontalDateAxis;
import org.jfree.chart.axis.NumberAxis;
import org.jfree.chart.axis.VerticalNumberAxis;
import org.jfree.chart.plot.XYPlot;
import org.jfree.data.time.Day;
import org.jfree.data.time.TimePeriodValues;
import org.jfree.data.time.TimePeriodValuesCollection;

import javax.swing.JButton;
import javax.swing.JPanel;
import javax.swing.JScrollPane;
import javax.swing.JOptionPane;
import javax.swing.JLabel;
import java.awt.BorderLayout;
import java.awt.Dimension;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;

public class StatisticsPanel extends JPanel {

    private class RefreshListener implements ActionListener {
        public void actionPerformed(ActionEvent e) {
            try {
                if (Settings.getInstance().get(ConfigurationPanel.SERVER_PROPERTY) == null || Settings.getInstance().get(ConfigurationPanel.SERVER_PROPERTY).equals("")) {
                    JOptionPane.showMessageDialog(null, "Please fill in a server name on the Configuration tab (i.e., cougaarforge.cougaar.org)");
                    return;
                }
                Client client = new Client(Settings.getInstance().get(ConfigurationPanel.SERVER_PROPERTY), Settings.getInstance().get(ConfigurationPanel.GROUP_PROPERTY));
                stats = client.getSiteStats();
                refresh();
            }  catch (Exception ex) {
                 ex.printStackTrace();
            }
        }
    }

    private SiteStatsDataPoint[] stats;

    public StatisticsPanel() {
        super();
        refresh();
    }

    private void refresh() {
        removeAll();
        repaint();

        if (stats == null) {
            JButton refreshButton = new JButton("Refresh");
            refreshButton.addActionListener(new RefreshListener());
            add(refreshButton);
            add(new JLabel("No stats available"));
            return;
        }

        JPanel topRow = new JPanel();
        topRow.add(createUsersOverTimeChart());
        topRow.add(createSessionsPerDayChart());

        JPanel nextRow = new JPanel();
        nextRow.add(createPageViewsPerDayChart());

        JPanel all = new JPanel(new BorderLayout());
        all.add(topRow, BorderLayout.NORTH);
        all.add(nextRow, BorderLayout.SOUTH);

        setLayout(new BorderLayout());
        JPanel buttonPanel = new JPanel();
        JButton refreshButton = new JButton("Refresh");
        refreshButton.addActionListener(new RefreshListener());
        buttonPanel.add(refreshButton);
        add(buttonPanel, BorderLayout.NORTH);
        add(new JScrollPane(all), BorderLayout.CENTER);
    }

    private JPanel createUsersOverTimeChart() {
        TimePeriodValues tpv = new TimePeriodValues("UsersToDate");
        for (int i=0; i<stats.length; i++) {
            tpv.add(Day.parseDay(stats[i].getDate()), new Integer(stats[i].getUsers()));
        }
        TimePeriodValuesCollection data = new TimePeriodValuesCollection();
        data.addSeries(tpv);

        return wrapPlot(createPlot(data, "Users", "As of Date"), "Total Active Users");
    }

    private JPanel createSessionsPerDayChart() {
        TimePeriodValues tpv = new TimePeriodValues("SessionsPerDay");
        for (int i=0; i<stats.length; i++) {
            tpv.add(Day.parseDay(stats[i].getDate()), new Integer(stats[i].getSessions()));
        }
        TimePeriodValuesCollection data = new TimePeriodValuesCollection();
        data.addSeries(tpv);
        return wrapPlot(createPlot(data, "Sessions", "Day"), "Sessions Per Day");
    }

    private JPanel createPageViewsPerDayChart() {
        TimePeriodValues tpv = new TimePeriodValues("PageViewsPerDay");
        for (int i=0; i<stats.length; i++) {
            tpv.add(Day.parseDay(stats[i].getDate()), new Integer(stats[i].getPageviews()));
        }
        TimePeriodValuesCollection data = new TimePeriodValuesCollection();
        data.addSeries(tpv);
        return wrapPlot(createPlot(data, "Pageviews", "Day"), "Pageviews Per Day");
    }

    private XYPlot createPlot(TimePeriodValuesCollection data, String y, String x) {
        NumberAxis yAxis = new VerticalNumberAxis(y);
        DateAxis xAxis = new HorizontalDateAxis(x);
        return new XYPlot(data, xAxis, yAxis);
    }

    private JPanel wrapPlot(XYPlot plot, String title) {
        JFreeChart c = new JFreeChart(plot);
        c.setTitle(title);
        ChartPanel p = new ChartPanel(c);
        p.setPreferredSize(new Dimension(400,200));
        return p;
    }
}
