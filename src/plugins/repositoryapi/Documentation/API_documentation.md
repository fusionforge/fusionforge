# API Documentation

The SOAP API of the plugin provides the following commands:

* `repositoryapi_repositoryList`
* `repositoryapi_repositoryInfo`
* `repositoryapi_repositoryActivity`

## Data structure: repository entry

A repository entry is compound of the following elements:

* `group_id`: the id of the project
* `repository_id`: the id of the repos
* `repository_urls[]`: array containing the URL(s) of the repository(ies). Typically, SVN repos have at both https:// and svn:// URLs.
* `repository_type`: type of the repository, may be "git" for "svn"

## Data structure: activity entry

* `group_id`: the id of the project
* `repository_id`: the id of the repos
* `timestamp`: timestamp of the activity, expressed as number of seconds since 1/1/1970 00:00:00.
* `type`: As of 2017-03-10, always = "change" (i.e. means: a modification has been made on the repos)

From a human point of view, an *activity* is "something done" on the repos.
Typically, this is a commit for an SVN repos, a push for a git repos.

## repositoryapi_repositoryList

### Purpose

Retuns a list of repositories available on the forge.

### Parameters

```python
repositoryapi_repositoryList(session, limit, offset)
```

* `session`: **MANDATORY** Id of session if identified, empty if anonymous.
* `limit`: Maximum number of entries returned. Default value = 1000. Higher possible value: 1000.
* `offset`: Number of entries to bypass.

### Results returned

A set of repository entries.

### Example: List first 1000 repositories

```python
repositoryapi_repositoryList(session)
```

Only one parameter is passed, as we rely on default values for `limit` and `offset`.

### Example: List repositories from 1001 to 2000

```python
repositoryapi_repositoryList(session, 999, 1001)
```

## repositoryapi_repositoryInfo

### Purpose

Returns details on a given repository

### Parameters

```python
repositoryapi_repositoryInfo(session, repo_id)
```

* `session`: **MANDATORY** Id of session if identified, empty if anonymous.
* `repo_id`: **MANDATORY** Id of the repository queried

### Results returned

One repository entries.

### Example: query repository "s2low/svn/s2low"

```python
repositoryapi_repositoryInfo(session, "s2low/svn/s2low")
```

## repositoryapi_repositoryActivity

### Purpose

Returns the activity of the forge. This is the main feature of the plugin.

### Parameters

```php
repositoryapi_repositoryActivity(session, fromDate, toDate, limit, offset)
```

* `session`: **MANDATORY** Id of session if identified, empty if anonymous.
* `fromDate`: **MANDATORY** Min date to look for activity
* `toDate`: **MANDATORY** Max date to look for activity
* `limit`: Maximum number of entries returned. Default value = 1000. Higher possible value: 1000.
* `offset`: Number of entries to bypass.

**Important notes**:

* Dates are expressed as number of seconds since 1/1/1970 00:00:00.
* Interval between `fromDate` and `toDate` must be less or equal 31 days

### Results returned

* `effective_t0`: the actual `fromDate` taken for grabbing activities
* `effective_t1`: the actual `toDate` taken for grabbing activities
* a set of activity entries

**Important Notes**:

The number of returned activity entries is **limited to 1000**.

Thus the actual `fromDate` and `toDate` may change. That's why the `effective_t0`
and `effective_t1` are part of the result.

For instance, let say you request the activities on a 31-days interval, and there are (say) 2000 activities
made. You will only get the first 1000 activities. And the interval between `effective_t0` and `effective_t1` will be
smaller than the one between `fromDate` and `toDate`.