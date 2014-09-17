-- Switch OpenSSH integration to AuthorizedKeysCommand
ALTER TABLE sshkeys DROP COLUMN deploy;
ALTER TABLE sshkeys DROP COLUMN deleted;
CREATE VIEW ssh_authorized_keys AS
  SELECT users.user_name, sshkey FROM users JOIN sshkeys ON users.user_id = sshkeys.userid
  WHERE users.status = 'A' AND users.unix_status = 'A';
