#!/usr/bin/env ruby
require 'soap/driver'

soapClient = SOAP::Driver.new( Devel::Logger.new(STDERR) , nil, 'GForgeAPI', 'http://shire/soap/SoapAPI.php' )
soapClient = SOAP::Driver.new( nil , nil, 'GForgeAPI', 'http://shire/soap/SoapAPI.php' )
#soapClient.setWireDumpDev(STDERR)


# set up session control operations
soapClient.addMethod( 'login', 'userid', 'passwd')
soapClient.addMethod( 'logout', 'sessionkey')

# set up bug operations
soapClient.addMethod( 'bugList', 'sessionkey', 'groupid')
soapClient.addMethod( 'bugFetch', 'sessionkey', 'groupid', 'bugid')
soapClient.addMethod( 'bugAdd', 'sessionkey', 'groupid', 'summary', 'details')
soapClient.addMethod( 'bugUpdate', 'sessionkey', 'groupid', 'bugid', 'comment')

func = ARGV.shift

if (func == "add") 
 sessionKey = soapClient.login("tom", "tomtom") 
 soapClient.bugAdd(sessionKey, "18", "a summary", "a comment")
 soapClient.logout(sessionKey) 
elsif (func == "update") 
 sessionKey = soapClient.login("tom", "tomtom") 
 soapClient.bugUpdate(sessionKey, "18", "16", "Bizbuz")
 soapClient.logout(sessionKey) 
elsif (func == "log")
 soapClient.login("tom", "tomtom") 
 soapClient.logout(sessionKey) 
elsif (func == "list")
 sessionKey = soapClient.login("tom", "tomtom") 
 bugs= soapClient.bugList(sessionKey, "18")
 bugs.each {|bugid|
  bug = soapClient.bugFetch(sessionKey, "18", bugid)
  puts bug.summary
  puts bugid
 }
 soapClient.logout(sessionKey) 
else
 puts "Usage: soapClient.rb add|update|log|list"
end

#soapClient.addMethod( 'hello', 'helloRequest')
#puts soapClient.hello(ARGV[0])
#exit

exit
