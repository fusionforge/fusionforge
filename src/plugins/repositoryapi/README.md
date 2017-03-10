# Repository API plugin

## Intro

> Plugin providing an API for retrieving repository-wide activities

This plugin makes it possible to list the activities in all public projects hosted on the forge, extending to the full repository the functionality that was previously provided only at the project level.

A typical use of this API is for tracking changes of all public projects: this enables external archival services like Software Heritage to update their archive by pulling only the changes that happened after the last visit, thus reducing the workload both on the forge, and on the archival service.

The development of this plugin has been sponsored by [Adullact](http://www.adullact.org), that has deployed it on [its own instance of FusionForge](https://adullact.net/) on 2017-03-10.

## Technical description

This plugin provides a forge-wide view into the activities of all
projects (modulo permissions).  It can be seen as an aggregate of all
the project-wide "activity" pages.

The relevant data is made available through a SOAP API.

## Documentation

All doc is in the [Documentation directory](Documentation/README.md).