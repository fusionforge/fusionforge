
package org.bull.novaforge.tools.tab2poConverter;

import java.io.File;

public class Main
{
   private static final String USAGE = "Usage: ... languageDirPath translationDirPath";
   
   public static void main(final String[] args)
   {
      if (args.length < 2)
      {
         final StringBuilder out = new StringBuilder("Bad arguments :");
         for (final String s : args)
         {
            out.append("\n- ").append(s);
         }
         throw new IllegalArgumentException(out + "\n\n" + USAGE);
      }
      String outputExtension = ".po.new";
      if (args.length > 2)
      {
         outputExtension = args[2];
      }
      final FileConverter converter = new FileConverter(new File(args[0]), new File(args[1]),
               outputExtension);
      converter.run();
   }
}
