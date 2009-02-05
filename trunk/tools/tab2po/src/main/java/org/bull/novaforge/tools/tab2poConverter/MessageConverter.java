
package org.bull.novaforge.tools.tab2poConverter;

import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class MessageConverter
{
   protected static final Pattern PATTERN_PARAMETER2 = Pattern.compile("\\{(\\d+)\\}");
   
   private static final Pattern PATTERN_PARAMETER1 = Pattern.compile("\\$(\\d+)");
   
   public static final MessageConverter instance = new MessageConverter();
   
   private MessageConverter()
   {
   }
   
   public String toPoMessage(final String tabTranslation)
   {
      // remplacement de " par \"
      String message = tabTranslation.replace("\"", "\\\"");
      
      // Prise en compte du remplacement de $1 par %1$s
      message = PATTERN_PARAMETER1.matcher(message).replaceAll(
         "%$1" + Matcher.quoteReplacement("$s"));
      
      // Prise en compte du remplacement de {0} par %1$s
      final Matcher m = PATTERN_PARAMETER2.matcher(message);
      final StringBuffer sb = new StringBuffer();
      while (m.find())
      {
         final int index = Integer.parseInt(m.group(1)) + 1;
         final String repl = Matcher.quoteReplacement("%" + index + "$s");
         m.appendReplacement(sb, repl);
      }
      m.appendTail(sb);
      
      message = sb.toString();
      
      return message;
      
   }
}
