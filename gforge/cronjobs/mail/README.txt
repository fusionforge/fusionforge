The primary script that was updated was mailing_lists_create.php. It first enumerates all 
the lists that exist on the listserver using mailman’s list_lists command and stores them in 
an array. It then retrieves the mailing lists and their owners from the database and creates 
each list, where needed. The in_array function is used to simplify this process. 
The mailman command newlist is invoked over ssh to create the list and the output of the command is
captured since it contains the aliases that mailman doesn’t actually add to the mail system (thanks guys)
It appends each alias using a special script described below which is also invoked over ssh.
Once all lists have been processed, it runs newaliases over ssh to update the hash/dbm maps.

The approach taken was that the webserver and the listserver are different hosts.
In our environment they are the same, but it still simplifies the process. 
The alternate would be to write "C" wrappers around scripts as Mailman has done.
In order to minimize risks, an ssh trust was used between the two accounts. 
Our specific implementation uses kerberos for security and neither of these accounts
have principals created for them so they’re only accessible to root. 
Ssh_keygen2 was used to create rsa and dsa keys for each user which were stored in ~/.ssh. 
The permissions for this directory are 600 and the home directories must also have all access to “other” removed. 
I have not verified it, but I believe that group may only have read and execute permissions as well. 
Since mailman must trust apache, ~apache/.ssh/id_dsa.pub and ~apache/.ssh/id_rsa.pub were appended
to ~mailman/.ssh/authorized_keys2. This then allows ssh from apache to the other without any
password prompt - ideally suited as a mechanism for executing scripts in the other's context.
The other change that was necessary was to create a script to add entries to a special aliases file
owned by mailman that is referenced in sendmail.cf. To simplify tracking, I named this /etc/mail/aliases.mailman.
According the the sendmail FAQ, root must run newaliases the first time and then the owner may 
subsequently run it as needed. Note that this configuration is mailsystem dependent. I hope others can extend
this to support other mailers such as Postfix, Exim, and Qmail. I wrote the script in Python and located it in 
mailman’s executable directory on the listserver since it’s a shortcoming in mailman that necessitates it. 
I've also written and attached an equivalent in PHP. 