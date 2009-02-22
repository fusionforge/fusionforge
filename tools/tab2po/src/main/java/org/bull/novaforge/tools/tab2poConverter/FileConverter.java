
package org.bull.novaforge.tools.tab2poConverter;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.FilenameFilter;
import java.io.IOException;
import java.io.StringReader;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import org.apache.commons.io.IOUtils;

// TODO Les Ajouts de messages manquants doivent être réalisé également dans le POT
// TODO Prise en compte des messages en doublons
/**
 * Release notes:
 * <ul>
 * <li>Suppression de la fonctionnalité d'ajout des messages non trouvés de le PO. Méthode à
 * préférer : ajout dans un fichier PHP dédié</li>
 * </ul>
 */
public class FileConverter implements Runnable
{
   private static final Map<String, String> FILENAME_MAPPING = new HashMap<String, String>();
   
   private static final FilenameFilter FILTER_FILE_TAB = new FilenameFilter()
   {
      public boolean accept(final File root, final String name)
      {
         return name.endsWith(".tab");
      }
   };
   
   static
   {
      FILENAME_MAPPING.put("Base", "en");
      FILENAME_MAPPING.put("French", "fr");
      FILENAME_MAPPING.put("Spanich", "es");
   }
   
   private final boolean debug = false;
   
   private final File languageDir;
   
   private final File translationDir;
   
   private final String resultExtension;
   
   public FileConverter(final File languageDirPath,
                        final File translationDirPath,
                        final String resultExtension)
   {
      if (languageDirPath == null || !languageDirPath.isDirectory())
      {
         throw new IllegalArgumentException("languageDir (" + languageDirPath
            + ") is not a directory");
      }
      this.languageDir = languageDirPath;
      this.translationDir = translationDirPath;
      this.resultExtension = resultExtension;
   }
   
   public void run()
   {
      System.out.println("---------------------------------------------");
      System.out.println("TAB translation file migration");
      if (this.debug)
      {
         System.out.println("languageDir=" + this.languageDir);
         System.out.println("translationDir=" + this.translationDir);
         System.out.println("resultExtension=" + this.resultExtension);
         System.out.println("---------------------------------------------");
      }
      
      // récupération des fichiers de traduction (*.tab)
      for (final File tabFile : this.languageDir.listFiles(FILTER_FILE_TAB))
      {
         System.out.println("\n> Processing file " + tabFile.getName());
         // . récupérer le fichier *.po correspondant à la langue du fichier tab
         final String poFilename = FILENAME_MAPPING.get(tabFile.getName().replaceFirst("\\.tab$",
            ""));
         if (poFilename == null)
         {
            throw new RuntimeException("No filename mapping found for " + poFilename);
         }
         
         final File poFile = new File(this.translationDir, poFilename + ".po");
         final File outputFile = new File(this.translationDir, poFilename + this.resultExtension);
         this.process(tabFile, poFile, outputFile);
      }
      
      System.out.println("\n\nMigration finished\n---------------------------------------------");
   }
   
   protected void process(final File tabFile, final File poFile, final File outputFile)
   {
      if (!poFile.exists())
      {
         System.out.print("\n\t-> PO File (" + poFile + ") do not exist");
         return;
      }
      final String poContent;
      try
      {
         poContent = IOUtils.toString(new FileInputStream(poFile));
      }
      catch (final Exception e)
      {
         throw new RuntimeException("Problem when reading file " + poFile, e);
      }
      final String tabFileContent;
      try
      {
         tabFileContent = IOUtils.toString(new FileInputStream(tabFile));
      }
      catch (final Exception e)
      {
         throw new RuntimeException("Problem when reading file " + tabFile, e);
      }
      final String result = this.getReplacedText(tabFileContent, poContent);
      try
      {
         IOUtils.write(result, new FileOutputStream(outputFile));
      }
      catch (final Exception e)
      {
         throw new RuntimeException("Problem when writing file " + outputFile, e);
      }
      System.out.println("\n-> Update file " + outputFile.getName());
   }
   
   protected static final Pattern PATTERN_TAB_LINE = Pattern.compile(
      "(^|[\\n\\r])([^#\\n\\r\\s]+)[ \\t]+([^\\n\\r\\s]+)([ \\t]+[^\\n\\r]+)?($|[\\n\\r])",
      Pattern.DOTALL);
   
   protected String getReplacedText(final String tabFileContent, final String poFileContent)
   {
      String result = poFileContent;
      int update = 0;
      for (final I18nMessage message : this.extractMessage(tabFileContent))
      {
         // . pour chaque ligne :
         // . . extraire le "domaine", l' "ID" du message, sa "traduction"
         // . . remplacer les motifs msgid\s+"(^[\n\r]+)"[\s\n\r]+msgstr\s+""
         // . . pour chaque occurance :
         // . . . rechercher dans le fichier tab, récupérer l'ID catché par group 1
         // . . . remplacer l'occurence par la bonne traduction
         final String updatedText = this.replace(result, message);
         if (!result.equals(updatedText))
         {
            update++;
            result = updatedText;
         }
      }
      System.out.println("\n\t-> Nb updates: " + update);
      return result;
   }
   
   protected List<I18nMessage> extractMessage(final String tabFileContent)
   {
      final List<I18nMessage> result = new ArrayList<I18nMessage>();
      // . lecture des lignes du fichier tab
      final BufferedReader r = new BufferedReader(
               new StringReader(tabFileContent.replace("\r", "")));
      String line;
      try
      {
         while ((line = r.readLine()) != null)
         {
            final Matcher matcherTabFile = PATTERN_TAB_LINE.matcher(line);
            if (matcherTabFile.find())
            {
               result.add(new I18nMessage(// domain
                        matcherTabFile.group(2),
                        // id
                        matcherTabFile.group(3),
                        // translation
                        matcherTabFile.group(4)));
            }
         }
      }
      catch (final IOException e)
      {
         throw new RuntimeException(e);
      }
      return result;
   }
   
   protected String replace(final String input, final I18nMessage msg)
   {
      if (this.debug)
      {
         System.out.print("\t" + msg.getId());
      }
      
      final String strFind = "(^|[\\n\\r])msgid\\s+\"" + Pattern.quote(msg.getId())
         + "\"[\\s\\n\\r]+msgstr\\s+\"\"";
      final String strRepl = "msgid \"" + msg.getId() + "\"\nmsgstr \"" + msg.getPoTranslation()
         + "\"";
      
      final Matcher m = Pattern.compile(strFind).matcher(input);
      String output;
      if (!m.find())
      {
         System.out.println("\tMsg not found in PO file: " + msg.getId() + "\t("
            + msg.getTabTranslation() + ")");
         output = input;
      }
      else
      {
         output = m.replaceAll("$1" + Matcher.quoteReplacement(strRepl));
      }
      return output;
   }
}
