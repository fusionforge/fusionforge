#!/usr/local/bin/perl

#conver the postgres schema to oracle 8

$postgres_file = "SourceForge.sql";
$oracle_file = "SourceForge_oci8.sql";
$trigger_auto = "Trigger_auto.sql";
$trigger_er = "Trigger_er.sql";
$drop_file = "Drop.sql";

if (!(open (POSTGRES, "<$postgres_file")))
{
  die "Can not open $postgres_file\n";
}
if (!(open (ORACLE, ">$oracle_file")))
{
  die "Can not open $oracle_file\n";
}
if (!(open (TRIGGER_AUTO, ">$trigger_auto")))
{
  die "Can not open $trigger_auto\n";
}
if (!(open (TRIGGER_ER, ">$trigger_er")))
{
  die "Can not open $trigger_er\n";
}
if (!(open (DROP , ">$drop_file")))
{
  die "Can not open $drop_file\n";
}

$table = '';

while (<POSTGRES>)
{
  # filter "
  $_ =~ s/\"//g;

  if ($_ =~ /CREATE\s+TABLE\s+(\w+)/i)
  {
    $table = $1;
    if ($table =~ /session/i)
    {
      $table = 'session1';
    }
    $_ =~ s/session/session1/g;

    print DROP "drop table $table;\n";

    if (length($table) > 30)
    {
      print STDOUT "TAB NAME: $table\n";
    }
  }

  # change the sequence creation statement
  if ($_ =~ /CREATE\s+SEQUENCE (\w+)/i)
  {
    $_ = "CREATE SEQUENCE $1 START WITH 1;\n";
    print DROP "drop sequence $1;\n";
  }

  # change the index creation statement
  $_ =~ s/ using btree//g;
  $_ =~ s/ int4_ops//g;
  $_ =~ s/ text_ops//g;
  $_ =~ s/ varchar_ops//g;
  $_ =~ s/ bpchar_ops//g;

  if ($_ =~ /CREATE\s+INDEX\s+(\w+)/i)
  {
    if (length($1) > 30)
    {
      print STDOUT "IND NAME: $1\n";
    }
    if ($1 =~ /session/i)
    {
      $_ =~ s/session/session1/g;
    }
  }

  # replace the nextval with a trigger
  if ($_ =~ /\s+(\w+)\s+\w+\s+DEFAULT\s+nextval\(\'(\w+)\'/i)
  {

    if (length($2) > 30)
    {
      print STDOUT "SEQ NAME: $2\n";
    }

    if (length($2) > 28)
    {
      print STDOUT "N_T NAME: A_$2\n";
    }

    $trigger = <<ENDTRIGGER;

CREATE OR REPLACE TRIGGER A_$2
        BEFORE INSERT OR UPDATE of $1
        ON $table FOR EACH ROW
BEGIN
        IF (:new.$1 is null) then
          IF INSERTING THEN
            SELECT $2.nextval INTO :new.$1 FROM DUAL;
          ELSIF UPDATING THEN
            :new.$1 := :old.$1;
          END IF;
        END IF;
END;

ENDTRIGGER

    $_ =~ s/DEFAULT\s+nextval\(\'\w+\'::text\) //g;
    $_ =~ s/DEFAULT\s+nextval\(\'\w+\'::text\)//g;
  }
  # replace integer with number(*)
  $_ =~ s/ integer,/ number(\*),/g;
  $_ =~ s/ integer / number(\*) /g;

  # replace text with long varchar2
  if ($_ =~ / text\s*\((\d+)\)/i)
  {
    $_ =~ s/ text/ varchar2/g;
  }
  else
  {
    $_ =~ s/ text,/ varchar2(4000),/g;
    $_ =~ s/ text / varchar2(4000) /g;
  }
  
  # replace field name date with date1
  $_ =~ s/date number/date1 number/g;
  $_ =~ s/ date / date1 /g;
  $_ =~ s/_date1 /_date /g;

  # replace trigger statements
  $_ =~ s/ CONSTRAINT / OR REPLACE /g;

  # set the sequence current value: oracle has no setval
  # so we drop the sequence and then re-create it with new
  # start value

  if ($_ =~ /SELECT\s+setval\s+\('(\w+)',\s+(\d+)/i)
  {
    $new_seq = <<ENDSEQ;
DROP SEQUENCE $1;
CREATE SEQUENCE $1 START WITH $2;
ENDSEQ
    print ORACLE $new_seq;
  }
  elsif ($_ =~ /(\w+)\s+AFTER\s+INSERT\s+OR\s+UPDATE\s+ON\s+(\w+)/i)
  {
    $t_name = $1;
    $tab_name = $2;

    if (length($t_name) > 30)
    {
      print STDOUT "R_T NAME: $t_name\n";
    }

    if ($_ =~ /\('(\w+)',\s+'(\w+)',\s+'(\w+)',\s+'(\w+)',\s+'(\w+)',\s+'(\w+)'\)/i)
    {
      $trigger_new  = <<ENDTRIGGER;
CREATE OR REPLACE TRIGGER $t_name 
        AFTER INSERT OR UPDATE 
        ON $tab_name FOR EACH ROW
declare numrows INTEGER;
begin
        select count(*) into numrows from $3
        where :new.$5 = $3.$6;
        if (:new.$5 is not null and numrows = 0) then
          raise_application_error(-20001, 
            'Cannot INSERT/UPDATE $tab_name using non-existing $6 ($5).');
        end if;
end;
ENDTRIGGER
      print TRIGGER_ER $trigger_new;
      print TRIGGER_ER "\n\/\n";
    }
  }
  elsif ($_ =~ /(\w+)\s+AFTER\s+UPDATE\s+ON\s+(\w+)/i)
  {
    $t_name = $1;
    $tab_name = $2;

    if ($_ =~ /\('(\w+)',\s+'(\w+)',\s+'(\w+)',\s+'(\w+)',\s+'(\w+)',\s+'(\w+)'\)/i)
    {
      $trigger_new  = '';
      #print TRIGGER_ER $trigger_new;
      #print TRIGGER_ER "\n\/\n";
    }
  }
  elsif ($_ =~ /(\w+)\s+AFTER\s+DELETE\s+ON\s+(\w+)/i)
  {
    $t_name = $1;
    $tab_name = $2;

    if ($_ =~ /\('(\w+)',\s+'(\w+)',\s+'(\w+)',\s+'(\w+)',\s+'(\w+)',\s+'(\w+)'\)/i)
    {
      $trigger_new  = '';
      #print TRIGGER_ER $trigger_new;
      #print TRIGGER_ER "\n\/\n";
    }
  }
  else
  {
    print ORACLE $_;
  }

  if ($_ =~ /;/ && $table)
  {
    print TRIGGER_AUTO $trigger;
    print TRIGGER_AUTO "\n\/\n";
    $table = '';
    $trigger = '';
  }
}

close (POSTGRES);
close (ORACLE);
close (TRIGGER_AUTO);
close (TRIGGER_ER);
close (DROP);
