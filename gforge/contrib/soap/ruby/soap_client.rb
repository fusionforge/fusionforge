#!/usr/bin/env ruby
require 'soap/driver'

class GForge
	def create_session(host, userid, passwd)
		soapClient = SOAP::Driver.new( Devel::Logger.new(STDERR) , nil, 'GForgeAPI', "http://#{host}/soap/SoapAPI.php" )
		soapClient = SOAP::Driver.new( nil , nil, 'GForgeAPI', "http://#{host}/soap/SoapAPI.php" )
		#soapClient.setWireDumpDev(STDERR)
		soapClient.addMethod( 'login', 'userid', 'passwd')
		soapClient.addMethod( 'logout', 'sessionkey')
		soapClient.addMethod( 'bugList', 'sessionkey', 'project')
		soapClient.addMethod( 'bugFetch', 'sessionkey', 'project', 'bugid')
		soapClient.addMethod( 'bugAdd', 'sessionkey', 'project', 'summary', 'details')
		soapClient.addMethod( 'bugUpdate', 'sessionkey', 'project', 'bugid', 'comment')
		soapClient.addMethod( 'rfeAdd', 'sessionkey', 'project', 'summary', 'details')
		soapClient.addMethod( 'rfeAddMessage', 'sessionkey', 'project', 'rfeid', 'body')
		session=Session.new(soapClient, userid, passwd)
		return session
	end
end

class Session
	@key=@soapClient=nil
	def initialize(soapClient, userid, passwd)
		@key = soapClient.login(userid, passwd)
		@soapClient = soapClient
	end
	def create_rfe(project, summary, details)
 		return @soapClient.rfeAdd(@key, project, summary, details)
	end
	def add_message_to_rfe(project, rfeID, body)
 		return @soapClient.rfeAddMessage(@key, project, rfeID,body)
	end
	def logout
		@soapClient.logout(@key.to_s)
	end
end

session = GForge.new.create_session("localhost", "tom", "tomtom")
rfeID = session.create_rfe("othello", "A summary #{Time.now}", "A comment #{Time.now}")
session.add_message_to_rfe("othello", rfeID, "A message");
session.logout

exit


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
elsif (func == "hello")
	soapClient.addMethod( 'hello', 'helloRequest')
	puts soapClient.hello("word up")
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
 puts "Usage: soapClient.rb add|update|log|list|hello"
end
