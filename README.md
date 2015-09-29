![cogs](http://radium-codex.info/og-framework/cogs.png)
## A personal PHP application framework
[![Build Status](https://travis-ci.org/OddGreg/og-framework.svg?branch=master)](https://travis-ci.org/OddGreg/og-framework)
[![Coverage Status](https://coveralls.io/repos/OddGreg/og-framework/badge.svg?branch=master&service=github)](https://coveralls.io/github/OddGreg/og-framework?branch=master)

__Caveat Emptor__

     This is a complete rewrite of my Radium-Codex project. **WORK-IN-PROGRESS**.
     I stridently recommend that you avoid using this repository for the time being. 
     There isn't much to see yet.
    
__WORK IS ONGOING AND THE CODE CHANGES FREQUENTLY.__

* COGS is a personal project.
* COGS is an event-driven architecture.

### Design Goal: Decoupled, Event-Driven Architecture

The four major components of the framework - __Control__, __Request__, __Command__, and __Response__ - are providers and/or consumers of 
event messages, effectively arbitrating concerns over where or what generated or consumed an event.

- To build a framework that separates major concerns into event-driven entities.
- Entities are loosely coupled via a message queue and a unified data object structure.
- `Control` manages the Event Queue and other meta-aspects of the framework.
- `Request Dispatch` begins with at index.php and then ceases after sending the request via the message queue.
- `Action and Control` processes the incoming `Request Message` without concern for its origin. 
- When processing is complete, `Action and Control` queues a `Processing Complete` event message.
- The `Rendering and Reporting Dispatcher` consumes `completion`, `rendering` and `reporting` event messages.

### Installation and Testing

```
> git clone https://github.com/OddGreg/cogs.git project
> cd project
> composer install
> cd og/Support
> composer install
> cd ../..
```

Edit the `.env` file to set your environment variables. Optionally, run the tests:

```
> phpunit 
```

### Contribute
Contact me if you would like to contribute to this project.

### Change Log
see [Changelog](https://github.com/OddGreg/og-framework/blob/0.1.0/CHANGELOG.md).

