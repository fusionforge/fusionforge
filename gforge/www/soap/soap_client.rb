#!/usr/bin/env ruby
require 'soap/driver'

soapClient = SOAP::Driver.new( Devel::Logger.new(STDERR) , nil, 'GForgeAPI', 'http://shire/soap/SoapAPI.php' )
soapClient = SOAP::Driver.new( nil , nil, 'GForgeAPI', 'http://shire/soap/SoapAPI.php' )
soapClient.setWireDumpDev(STDERR)


# set up session control operations
soapClient.addMethod( 'login', 'userid', 'passwd')
soapClient.addMethod( 'logout', 'sessionkey')

# set up bug operations
soapClient.addMethod( 'bugList', 'sessionkey', 'project')
soapClient.addMethod( 'bugFetch', 'sessionkey', 'project', 'bugid')
soapClient.addMethod( 'bugAdd', 'sessionkey', 'project', 'summary', 'details')
soapClient.addMethod( 'bugUpdate', 'sessionkey', 'project', 'bugid', 'comment')

func = ARGV.shift

if (func == "add") 
 sessionKey = soapClient.login("tom", "tomtom") 
 puts soapClient.bugAdd(sessionKey, "othello", "a summary #{Time.now}", "a comment #{Time.now}")
 soapClient.logout(sessionKey) 
elsif (func == "update") 
 sessionKey = soapClient.login("tom", "tomtom") 
 soapClient.bugUpdate(sessionKey, "othello", "16", "Bizbuz")
 soapClient.logout(sessionKey) 
elsif (func == "log")
 soapClient.login("tom", "tomtom") 
 soapClient.logout(sessionKey) 
elsif (func == "list")
 sessionKey = soapClient.login("tom", "tomtom") 
 bugs= soapClient.bugList(sessionKey, "othello")
 bugs.each {|bugid|
  bug = soapClient.bugFetch(sessionKey, "othello", bugid)
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
