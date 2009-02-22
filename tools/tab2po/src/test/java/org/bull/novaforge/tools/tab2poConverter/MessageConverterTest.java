
package org.bull.novaforge.tools.tab2poConverter;

import junit.framework.Assert;
import junit.framework.TestCase;

public class MessageConverterTest extends TestCase
{
   
   public void testGetPoTranslation()
   {
      final MessageConverter c = MessageConverter.instance;
      Assert.assertEquals("Escape les caractère \"", "\\\"Creation du tag\\\"",
         c.toPoMessage("\"Creation du tag\""));
      Assert.assertEquals("Transformation des substitutions", "module : '%1$s'",
         c.toPoMessage("module : '$1'"));
      
      Assert.assertEquals("Transformation des substitutions", "alerte %1$s",
         c.toPoMessage("alerte {0}"));
      Assert.assertEquals("Mixte", "module : \\\"%1$s\\\" %1$s",
         c.toPoMessage("module : \"$1\" {0}"));
   }
   
}
