
package org.bull.novaforge.tools.tab2poConverter;

import java.io.File;
import java.util.List;
import java.util.regex.Matcher;

import junit.framework.Assert;
import junit.framework.TestCase;

public class FileConverterTest extends TestCase
{
   FileConverter converter = null;
   
   private static final String ROOT_PATH = "src/test/resources";
   
   private static final String LANGUAGES_PATH = "plugincore/include/languages";
   
   private static final String TRANSLATIONS_PATH = "translations";
   
   @Override
   protected void setUp() throws Exception
   {
      final File root = new File(ROOT_PATH);
      this.converter = new FileConverter(new File(root, LANGUAGES_PATH), new File(root,
               TRANSLATIONS_PATH), ".po.new");
   }
   
   public void testPattern()
   {

inserting a syntax error here, so someone MUST revisit this code

 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

this ↑ is the correct new address of the FSF!


      final String TAB_LINE_STR = "# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA\r\n"
         + "#---------------------------------------------------------------------------\r\n"
         + "\r\n"
         + "gforge-plugin-novacontinuum   title_helloworld  Salut le monde !!!\r\n"
         + "gforge-plugin-novacontinuum   tab_title   Nova Continuum\r\n" + "";
      Matcher m_tab = FileConverter.PATTERN_TAB_LINE.matcher(TAB_LINE_STR);
      Assert.assertTrue("Doit trouver les lignes de traduction dans TAB", m_tab.find());
      
      m_tab = FileConverter.PATTERN_TAB_LINE.matcher("gforge-plugin-novacontinuum   pas_de_trad\ndomName keyId msg");
      Assert.assertTrue("Message sans traduction", m_tab.find());
      Assert.assertNull("Message final sans traduction", m_tab.group(4));
   }
   
   public void testRun()
   {
      this.converter.run();
   }
   
   private static final String poFileContent = ""
      //
      + "#: pluginwww/siteAdmin/index.php:37\n"
      + "msgid \"title_site_admin\"\n"
      + "msgstr \"\"\n\n"
      //
      + "#: plugincore/include/NovaContinuumPlugin.class.php:138\n"
      + "msgid \"your_continuum_projects\"\n"
      + "msgstr \"\"\n\n"
      //
      + "#: plugincore/include/NovaContinuumPlugin.class.php:168\n"
      + "#: plugincore/include/NovaContinuumPlugin.class.php:180\n"
      + "msgid \"no_project_assigned\"\n" + "msgstr \"\""
   //
   ;
   
   private static final String tabFileContent = "gforge-plugin-novacontinuum  title_site_admin  Administration du plugin Nova Continuum\n"
      + "gforge-plugin-novacontinuum   no_project_assigned  Vous n'avez pas de projet Continuum\n"
      + "gforge-plugin-novacontinuum   pas_de_trad\n"
      + "gforge-plugin-novacontinuum   your_continuum_projects Vos Projets Continuum";
   
   public void testExtractI18nMessage()
   {
      final List<I18nMessage> result = this.converter.extractMessage(tabFileContent);
      Assert.assertEquals(4, result.size());
   }
   
   public void testReplace()
   {
      final String expected = ""
         //
         + "#: pluginwww/siteAdmin/index.php:37\n"
         + "msgid \"title_site_admin\"\n"
         + "msgstr \"Administration du plugin Nova Continuum\"\n\n"
         //
         + "#: plugincore/include/NovaContinuumPlugin.class.php:138\n"
         + "msgid \"your_continuum_projects\"\n"
         + "msgstr \"\"\n\n"
         //
         + "#: plugincore/include/NovaContinuumPlugin.class.php:168\n"
         + "#: plugincore/include/NovaContinuumPlugin.class.php:180\n"
         + "msgid \"no_project_assigned\"\n" + "msgstr \"\"";
      
      final String result = this.converter.replace(poFileContent, new I18nMessage(
               "gforge-plugin-novacontinuum", "title_site_admin",
               "Administration du plugin Nova Continuum"));
      
      Assert.assertEquals(expected, result);
   }
   
   public void testGetReplacedText()
   {
      final String expected = ""
         //
         + "#: pluginwww/siteAdmin/index.php:37\n"
         + "msgid \"title_site_admin\"\n"
         + "msgstr \"Administration du plugin Nova Continuum\"\n\n"
         //
         + "#: plugincore/include/NovaContinuumPlugin.class.php:138\n"
         + "msgid \"your_continuum_projects\"\n"
         + "msgstr \"Vos Projets Continuum\"\n\n"
         //
         + "#: plugincore/include/NovaContinuumPlugin.class.php:168\n"
         + "#: plugincore/include/NovaContinuumPlugin.class.php:180\n"
         + "msgid \"no_project_assigned\"\n" + "msgstr \"Vous n'avez pas de projet Continuum\"";
      
      final String result = this.converter.getReplacedText(tabFileContent, poFileContent);
      Assert.assertEquals(expected, result);
   }
}
