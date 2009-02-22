
package org.bull.novaforge.tools.tab2poConverter;

public class I18nMessage
{
   private final String domain;
   
   private final String id;
   
   private final String tabTranslation;
   
   public I18nMessage(final String domain, final String id, final String tabTranslation)
   {
      this.domain = domain;
      this.id = id;
      this.tabTranslation = tabTranslation == null ? "" : tabTranslation.trim();
   }
   
   public String getDomain()
   {
      return this.domain;
   }
   
   public String getId()
   {
      return this.id;
   }
   
   public String getTabTranslation()
   {
      return this.tabTranslation;
   }
   
   protected String getPoTranslation()
   {
      return MessageConverter.instance.toPoMessage(this.tabTranslation);
   }
   
}
