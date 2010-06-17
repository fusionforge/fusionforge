# -*-python-*-
#
# Copyright (C) 1999-2006 The ViewCVS Group. All Rights Reserved.
#
# By using this file, you agree to the terms and conditions set forth in
# the LICENSE.html file which can be found at the top level of the ViewVC
# distribution or at http://viewvc.org/license-1.html.
#
# For more information, visit http://viewvc.org/
#
# -----------------------------------------------------------------------

import os
import sys
import string
import time
import fnmatch
import re

import dbi


## error
error = "cvsdb error"

## cached (active) database connections
gCheckinDatabase = None
gCheckinDatabaseReadOnly = None


## CheckinDatabase provides all interfaces needed to the SQL database
## back-end; it needs to be subclassed, and have its "Connect" method
## defined to actually be complete; it should run well off of any DBI 2.0
## complient database interface

class CheckinDatabase:
    def __init__(self, host, port, user, passwd, database, row_limit):
        self._host = host
        self._port = port
        self._user = user
        self._passwd = passwd
        self._database = database
        self._row_limit = row_limit

        ## database lookup caches
        self._get_cache = {}
        self._get_id_cache = {}
        self._desc_id_cache = {}

    def Connect(self):
        self.db = dbi.connect(
            self._host, self._port, self._user, self._passwd, self._database)

    def sql_get_id(self, table, column, value, auto_set):
        sql = "SELECT id FROM %s WHERE %s=%%s" % (table, column)
        sql_args = (value, )
        
        cursor = self.db.cursor()
        cursor.execute(sql, sql_args)
        try:
            (id, ) = cursor.fetchone()
        except TypeError:
            if not auto_set:
                return None
        else:
            return str(int(id))
   
        ## insert the new identifier
        sql = "INSERT INTO %s(%s) VALUES(%%s)" % (table, column)
        sql_args = (value, )
        cursor.execute(sql, sql_args)

        return self.sql_get_id(table, column, value, 0)

    def get_id(self, table, column, value, auto_set):
        ## attempt to retrieve from cache
        try:
            return self._get_id_cache[table][column][value]
        except KeyError:
            pass

        id = self.sql_get_id(table, column, value, auto_set)
        if id == None:
            return None

        ## add to cache
        try:
            temp = self._get_id_cache[table]
        except KeyError:
            temp = self._get_id_cache[table] = {}

        try:
            temp2 = temp[column]
        except KeyError:
            temp2 = temp[column] = {}

        temp2[value] = id
        return id

    def sql_get(self, table, column, id):
        sql = "SELECT %s FROM %s WHERE id=%%s" % (column, table)
        sql_args = (id, )

        cursor = self.db.cursor()
        cursor.execute(sql, sql_args)
        try:
            (value, ) = cursor.fetchone()
        except TypeError:
            return None

        return value

    def get(self, table, column, id):
        ## attempt to retrieve from cache
        try:
            return self._get_cache[table][column][id]
        except KeyError:
            pass

        value = self.sql_get(table, column, id)
        if value == None:
            return None

        ## add to cache
        try:
            temp = self._get_cache[table]
        except KeyError:
            temp = self._get_cache[table] = {}

        try:
            temp2 = temp[column]
        except KeyError:
            temp2 = temp[column] = {}

        temp2[id] = value
        return value
        
    def get_list(self, table, field_index):
        sql = "SELECT * FROM %s" % (table)
        cursor = self.db.cursor()
        cursor.execute(sql)

        list = []
        while 1:
            row = cursor.fetchone()
            if row == None:
                break
            list.append(row[field_index])

        return list

    def GetBranchID(self, branch, auto_set = 1):
        return self.get_id("branches", "branch", branch, auto_set)

    def GetBranch(self, id):
        return self.get("branches", "branch", id)

    def GetDirectoryID(self, dir, auto_set = 1):
        return self.get_id("dirs", "dir", dir, auto_set)

    def GetDirectory(self, id):
        return self.get("dirs", "dir", id)

    def GetFileID(self, file, auto_set = 1):
        return self.get_id("files", "file", file, auto_set)

    def GetFile(self, id):
        return self.get("files", "file", id)
    
    def GetAuthorID(self, author, auto_set = 1):
        return self.get_id("people", "who", author, auto_set)

    def GetAuthor(self, id):
        return self.get("people", "who", id)
    
    def GetRepositoryID(self, repository, auto_set = 1):
        return self.get_id("repositories", "repository", repository, auto_set)

    def GetRepository(self, id):
        return self.get("repositories", "repository", id)

    def SQLGetDescriptionID(self, description, auto_set = 1):
        ## lame string hash, blame Netscape -JMP
        hash = len(description)

        sql = "SELECT id FROM descs WHERE hash=%s AND description=%s"
        sql_args = (hash, description)
        
        cursor = self.db.cursor()
        cursor.execute(sql, sql_args)
        try:
            (id, ) = cursor.fetchone()
        except TypeError:
            if not auto_set:
                return None
        else:
            return str(int(id))

        sql = "INSERT INTO descs (hash,description) values (%s,%s)"
        sql_args = (hash, description)
        cursor.execute(sql, sql_args)
        
        return self.GetDescriptionID(description, 0)

    def GetDescriptionID(self, description, auto_set = 1):
        ## attempt to retrieve from cache
        hash = len(description)
        try:
            return self._desc_id_cache[hash][description]
        except KeyError:
            pass

        id = self.SQLGetDescriptionID(description, auto_set)
        if id == None:
            return None

        ## add to cache
        try:
            temp = self._desc_id_cache[hash]
        except KeyError:
            temp = self._desc_id_cache[hash] = {}

        temp[description] = id
        return id

    def GetDescription(self, id):
        return self.get("descs", "description", id)

    def GetRepositoryList(self):
        return self.get_list("repositories", 1)

    def GetBranchList(self):
        return self.get_list("branches", 1)

    def GetAuthorList(self):
        return self.get_list("people", 1)

    def AddCommitList(self, commit_list):
        for commit in commit_list:
            self.AddCommit(commit)

    def AddCommit(self, commit):
        ci_when = dbi.DateTimeFromTicks(commit.GetTime())
        ci_type = commit.GetTypeString()
        who_id = self.GetAuthorID(commit.GetAuthor())
        repository_id = self.GetRepositoryID(commit.GetRepository())
        directory_id = self.GetDirectoryID(commit.GetDirectory())
        file_id = self.GetFileID(commit.GetFile())
        revision = commit.GetRevision()
        sticky_tag = "NULL"
        branch_id = self.GetBranchID(commit.GetBranch())
        plus_count = commit.GetPlusCount()
        minus_count = commit.GetMinusCount()
        description_id = self.GetDescriptionID(commit.GetDescription())

        sql = "REPLACE INTO checkins"\
              "  (type,ci_when,whoid,repositoryid,dirid,fileid,revision,"\
              "   stickytag,branchid,addedlines,removedlines,descid)"\
              "VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)"
        sql_args = (ci_type, ci_when, who_id, repository_id,
                    directory_id, file_id, revision, sticky_tag, branch_id,
                    plus_count, minus_count, description_id)

        cursor = self.db.cursor()
        cursor.execute(sql, sql_args)

    def SQLQueryListString(self, field, query_entry_list):
        sqlList = []

        for query_entry in query_entry_list:
            data = query_entry.data
            ## figure out the correct match type
            if query_entry.match == "exact":
                match = "="
            elif query_entry.match == "like":
                match = " LIKE "
            elif query_entry.match == "glob":
                match = " REGEXP "
                # use fnmatch to translate the glob into a regexp
                data = fnmatch.translate(data)
                if data[0] != '^': data = '^' + data
            elif query_entry.match == "regex":
                match = " REGEXP "
            elif query_entry.match == "notregex":
                match = " NOT REGEXP "

            sqlList.append("%s%s%s" % (field, match, self.db.literal(data)))

        return "(%s)" % (string.join(sqlList, " OR "))

    def CreateSQLQueryString(self, query):
        tableList = [("checkins", None)]
        condList = []

        if len(query.repository_list):
            tableList.append(("repositories",
                              "(checkins.repositoryid=repositories.id)"))
            temp = self.SQLQueryListString("repositories.repository",
                                           query.repository_list)
            condList.append(temp)

        if len(query.branch_list):
            tableList.append(("branches", "(checkins.branchid=branches.id)"))
            temp = self.SQLQueryListString("branches.branch",
                                           query.branch_list)
            condList.append(temp)

        if len(query.directory_list):
            tableList.append(("dirs", "(checkins.dirid=dirs.id)"))
            temp = self.SQLQueryListString("dirs.dir", query.directory_list)
            condList.append(temp)
            
        if len(query.file_list):
            tableList.append(("files", "(checkins.fileid=files.id)"))
            temp = self.SQLQueryListString("files.file", query.file_list)
            condList.append(temp)
            
        if len(query.author_list):
            tableList.append(("people", "(checkins.whoid=people.id)"))
            temp = self.SQLQueryListString("people.who", query.author_list)
            condList.append(temp)
            
        if query.from_date:
            temp = "(checkins.ci_when>=\"%s\")" % (str(query.from_date))
            condList.append(temp)

        if query.to_date:
            temp = "(checkins.ci_when<=\"%s\")" % (str(query.to_date))
            condList.append(temp)

        if query.sort == "date":
            order_by = "ORDER BY checkins.ci_when DESC,descid"
        elif query.sort == "author":
            tableList.append(("people", "(checkins.whoid=people.id)"))
            order_by = "ORDER BY people.who,descid"
        elif query.sort == "file":
            tableList.append(("files", "(checkins.fileid=files.id)"))
            order_by = "ORDER BY files.file,descid"

        ## exclude duplicates from the table list, and split out join
        ## conditions from table names.  In future, the join conditions
        ## might be handled by INNER JOIN statements instead of WHERE
        ## clauses, but MySQL 3.22 apparently doesn't support them well.
        tables = []
        joinConds = []
        for (table, cond) in tableList:
            if table not in tables:
                tables.append(table)
                if cond is not None: joinConds.append(cond)

        tables = string.join(tables, ",")
        conditions = string.join(joinConds + condList, " AND ")
        conditions = conditions and "WHERE %s" % conditions

        ## limit the number of rows requested or we could really slam
        ## a server with a large database
        limit = ""
        if query.limit:
            limit = "LIMIT %s" % (str(query.limit))
        elif self._row_limit:
            limit = "LIMIT %s" % (str(self._row_limit))

        sql = "SELECT checkins.* FROM %s %s %s %s" % (
            tables, conditions, order_by, limit)

        return sql
    
    def RunQuery(self, query):
        sql = self.CreateSQLQueryString(query)
        cursor = self.db.cursor()
        cursor.execute(sql)
        
        while 1:
            row = cursor.fetchone()
            if not row:
                break
            
            (dbType, dbCI_When, dbAuthorID, dbRepositoryID, dbDirID,
             dbFileID, dbRevision, dbStickyTag, dbBranchID, dbAddedLines,
             dbRemovedLines, dbDescID) = row

            commit = LazyCommit(self)
            if dbType == 'Add':
              commit.SetTypeAdd()
            elif dbType == 'Remove':
              commit.SetTypeRemove()
            else:
              commit.SetTypeChange()
            commit.SetTime(dbi.TicksFromDateTime(dbCI_When))
            commit.SetFileID(dbFileID)
            commit.SetDirectoryID(dbDirID)
            commit.SetRevision(dbRevision)
            commit.SetRepositoryID(dbRepositoryID)
            commit.SetAuthorID(dbAuthorID)
            commit.SetBranchID(dbBranchID)
            commit.SetPlusCount(dbAddedLines)
            commit.SetMinusCount(dbRemovedLines)
            commit.SetDescriptionID(dbDescID)

            query.AddCommit(commit)

    def CheckCommit(self, commit):
        repository_id = self.GetRepositoryID(commit.GetRepository(), 0)
        if repository_id == None:
            return None

        dir_id = self.GetDirectoryID(commit.GetDirectory(), 0)
        if dir_id == None:
            return None

        file_id = self.GetFileID(commit.GetFile(), 0)
        if file_id == None:
            return None

        sql = "SELECT * FROM checkins WHERE "\
              "  repositoryid=%s AND dirid=%s AND fileid=%s AND revision=%s"
        sql_args = (repository_id, dir_id, file_id, commit.GetRevision())

        cursor = self.db.cursor()
        cursor.execute(sql, sql_args)
        try:
            (ci_type, ci_when, who_id, repository_id,
             dir_id, file_id, revision, sticky_tag, branch_id,
             plus_count, minus_count, description_id) = cursor.fetchone()
        except TypeError:
            return None

        return commit

## the Commit class holds data on one commit, the representation is as
## close as possible to how it should be committed and retrieved to the
## database engine
class Commit:
    ## static constants for type of commit
    CHANGE = 0
    ADD = 1
    REMOVE = 2
    
    def __init__(self):
        self.__directory = ''
        self.__file = ''
        self.__repository = ''
        self.__revision = ''
        self.__author = ''
        self.__branch = ''
        self.__pluscount = ''
        self.__minuscount = ''
        self.__description = ''
        self.__gmt_time = 0.0
        self.__type = Commit.CHANGE

    def SetRepository(self, repository):
        self.__repository = repository

    def GetRepository(self):
        return self.__repository
        
    def SetDirectory(self, dir):
        self.__directory = dir

    def GetDirectory(self):
        return self.__directory

    def SetFile(self, file):
        self.__file = file

    def GetFile(self):
        return self.__file
        
    def SetRevision(self, revision):
        self.__revision = revision

    def GetRevision(self):
        return self.__revision

    def SetTime(self, gmt_time):
        self.__gmt_time = float(gmt_time)

    def GetTime(self):
        return self.__gmt_time

    def SetAuthor(self, author):
        self.__author = author

    def GetAuthor(self):
        return self.__author

    def SetBranch(self, branch):
        self.__branch = branch or ''

    def GetBranch(self):
        return self.__branch

    def SetPlusCount(self, pluscount):
        self.__pluscount = pluscount

    def GetPlusCount(self):
        return self.__pluscount

    def SetMinusCount(self, minuscount):
        self.__minuscount = minuscount

    def GetMinusCount(self):
        return self.__minuscount

    def SetDescription(self, description):
        self.__description = description

    def GetDescription(self):
        return self.__description

    def SetTypeChange(self):
        self.__type = Commit.CHANGE

    def SetTypeAdd(self):
        self.__type = Commit.ADD

    def SetTypeRemove(self):
        self.__type = Commit.REMOVE

    def GetType(self):
        return self.__type

    def GetTypeString(self):
        if self.__type == Commit.CHANGE:
            return 'Change'
        elif self.__type == Commit.ADD:
            return 'Add'
        elif self.__type == Commit.REMOVE:
            return 'Remove'

## LazyCommit overrides a few methods of Commit to only retrieve
## it's properties as they are needed
class LazyCommit(Commit):
  def __init__(self, db):
    Commit.__init__(self)
    self.__db = db

  def SetFileID(self, dbFileID):
    self.__dbFileID = dbFileID

  def GetFileID(self):
    return self.__dbFileID

  def GetFile(self):
    return self.__db.GetFile(self.__dbFileID)

  def SetDirectoryID(self, dbDirID):
    self.__dbDirID = dbDirID

  def GetDirectoryID(self):
    return self.__dbDirID

  def GetDirectory(self):
    return self.__db.GetDirectory(self.__dbDirID)

  def SetRepositoryID(self, dbRepositoryID):
    self.__dbRepositoryID = dbRepositoryID

  def GetRepositoryID(self):
    return self.__dbRepositoryID

  def GetRepository(self):
    return self.__db.GetRepository(self.__dbRepositoryID)

  def SetAuthorID(self, dbAuthorID):
    self.__dbAuthorID = dbAuthorID

  def GetAuthorID(self):
    return self.__dbAuthorID

  def GetAuthor(self):
    return self.__db.GetAuthor(self.__dbAuthorID)

  def SetBranchID(self, dbBranchID):
    self.__dbBranchID = dbBranchID

  def GetBranchID(self):
    return self.__dbBranchID

  def GetBranch(self):
    return self.__db.GetBranch(self.__dbBranchID)

  def SetDescriptionID(self, dbDescID):
    self.__dbDescID = dbDescID

  def GetDescriptionID(self):
    return self.__dbDescID

  def GetDescription(self):
    return self.__db.GetDescription(self.__dbDescID)

## QueryEntry holds data on one match-type in the SQL database
## match is: "exact", "like", or "regex"
class QueryEntry:
    def __init__(self, data, match):
        self.data = data
        self.match = match

## CheckinDatabaseQueryData is a object which contains the search parameters
## for a query to the CheckinDatabase
class CheckinDatabaseQuery:
    def __init__(self):
        ## sorting
        self.sort = "date"
        
        ## repository to query
        self.repository_list = []
        self.branch_list = []
        self.directory_list = []
        self.file_list = []
        self.author_list = []

        ## date range in DBI 2.0 timedate objects
        self.from_date = None
        self.to_date = None

        ## limit on number of rows to return
        self.limit = None

        ## list of commits -- filled in by CVS query
        self.commit_list = []

        ## commit_cb provides a callback for commits as they
        ## are added
        self.commit_cb = None

    def SetRepository(self, repository, match = "exact"):
        self.repository_list.append(QueryEntry(repository, match))

    def SetBranch(self, branch, match = "exact"):
        self.branch_list.append(QueryEntry(branch, match))

    def SetDirectory(self, directory, match = "exact"):
        self.directory_list.append(QueryEntry(directory, match))

    def SetFile(self, file, match = "exact"):
        self.file_list.append(QueryEntry(file, match))

    def SetAuthor(self, author, match = "exact"):
        self.author_list.append(QueryEntry(author, match))

    def SetSortMethod(self, sort):
        self.sort = sort

    def SetFromDateObject(self, ticks):
        self.from_date = dbi.DateTimeFromTicks(ticks)

    def SetToDateObject(self, ticks):
        self.to_date = dbi.DateTimeFromTicks(ticks)

    def SetFromDateHoursAgo(self, hours_ago):
        ticks = time.time() - (3600 * hours_ago)
        self.from_date = dbi.DateTimeFromTicks(ticks)
        
    def SetFromDateDaysAgo(self, days_ago):
        ticks = time.time() - (86400 * days_ago)
        self.from_date = dbi.DateTimeFromTicks(ticks)

    def SetToDateDaysAgo(self, days_ago):
        ticks = time.time() - (86400 * days_ago)
        self.to_date = dbi.DateTimeFromTicks(ticks)

    def SetLimit(self, limit):
        self.limit = limit;

    def AddCommit(self, commit):
        self.commit_list.append(commit)


##
## entrypoints
##
def CreateCommit():
    return Commit()
    
def CreateCheckinQuery():
    return CheckinDatabaseQuery()

def ConnectDatabaseReadOnly(cfg):
    global gCheckinDatabaseReadOnly
    
    if gCheckinDatabaseReadOnly:
        return gCheckinDatabaseReadOnly
    
    gCheckinDatabaseReadOnly = CheckinDatabase(
        cfg.cvsdb.host,
        cfg.cvsdb.port,
        cfg.cvsdb.readonly_user,
        cfg.cvsdb.readonly_passwd,
        cfg.cvsdb.database_name,
        cfg.cvsdb.row_limit)
    
    gCheckinDatabaseReadOnly.Connect()
    return gCheckinDatabaseReadOnly

def ConnectDatabase(cfg):
    global gCheckinDatabase

    if gCheckinDatabase:
        return gCheckinDatabase

    gCheckinDatabase = CheckinDatabase(
        cfg.cvsdb.host,
        cfg.cvsdb.port,
        cfg.cvsdb.user,
        cfg.cvsdb.passwd,
        cfg.cvsdb.database_name,
        cfg.cvsdb.row_limit)
    
    gCheckinDatabase.Connect()
    return gCheckinDatabase

def GetCommitListFromRCSFile(repository, path_parts, revision=None):
    commit_list = []

    directory = string.join(path_parts[:-1], "/")
    file = path_parts[-1]

    revs = repository.itemlog(path_parts, revision, {"cvs_pass_rev": 1})
    for rev in revs:
        commit = CreateCommit()
        commit.SetRepository(repository.rootpath)
        commit.SetDirectory(directory)
        commit.SetFile(file)
        commit.SetRevision(rev.string)
        commit.SetAuthor(rev.author)
        commit.SetDescription(rev.log)
        commit.SetTime(rev.date)

        if rev.changed:
            # extract the plus/minus and drop the sign
            plus, minus = string.split(rev.changed)
            commit.SetPlusCount(plus[1:])
            commit.SetMinusCount(minus[1:])

            if rev.dead:
                commit.SetTypeRemove()
            else:
                commit.SetTypeChange()
        else:
            commit.SetTypeAdd()

        commit_list.append(commit)

        # if revision is on a branch which has at least one tag
        if len(rev.number) > 2 and rev.branches:
            commit.SetBranch(rev.branches[0].name)

    return commit_list

def GetUnrecordedCommitList(repository, path_parts, db):
    commit_list = GetCommitListFromRCSFile(repository, path_parts)

    unrecorded_commit_list = []
    for commit in commit_list:
        result = db.CheckCommit(commit)
        if not result:
            unrecorded_commit_list.append(commit)

    return unrecorded_commit_list

_re_likechars = re.compile(r"([_%\\])")

def EscapeLike(literal):
  """Escape literal string for use in a MySQL LIKE pattern"""
  return re.sub(_re_likechars, r"\\\1", literal)

def FindRepository(db, path):
  """Find repository path in database given path to subdirectory
  Returns normalized repository path and relative directory path"""
  path = os.path.normpath(path)
  dirs = []
  while path:
    rep = os.path.normcase(path)
    if db.GetRepositoryID(rep, 0) is None:
      path, pdir = os.path.split(path)
      if not pdir:
        return None, None
      dirs.append(pdir)
    else:
      break
  dirs.reverse()
  return rep, dirs

def CleanRepository(path):
  """Return normalized top-level repository path"""
  return os.path.normcase(os.path.normpath(path))

