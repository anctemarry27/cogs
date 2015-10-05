<?php

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

# Application
const OG_APPLICATION_SHUTDOWN = 'application.shutdown';
const OG_APPLICATION_STARTUP = 'application.startup';
const OG_APPLICATION_RESPONSE = 'application.response';
# Dispatch
const OG_BEFORE_ROUTE_DISPATCH = 'before.route.dispatch';
const OG_AFTER_ROUTE_DISPATCH = 'after.route.dispatch';

# Errors
const NOTIFY_CORE_ERROR = 'core.error';

# View
const NOTIFY_RENDERING = 'view.rendering';

# Composers
const NOTIFY_COMPOSING = 'composing: ';
const NOTIFY_COMPOSE = 'compose';
const NOTIFY_CREATE = 'create';
const NOTIFY_COMPOSERS = 'composing: compose'; # expects $name, $context
const NOTIFY_CREATORS = 'composing: create';

# Routing
const NOTIFY_ROUTING_XHR = 'routing.xhr';
const NOTIFY_ROUTING_REGISTERED = 'routing.registered';
const NOTIFY_ROUTING_FOUND = 'routing.found';
const NOTIFY_ROUTING_MATCH = 'routing.match';
const NOTIFY_ROUTING_FAIL = 'routing.fail';

# HTTP
const NOTIFY_REQUEST_LOADED = 'request.loaded';

# Auth
const NOTIFY_IDENTITY_UPDATE = 'identity.update';
const NOTIFY_AUTHENTICATE_FAILURE = 'identity.authenticate.failure';
const NOTIFY_AUTHENTICATE_SUCCESS = 'identity.authenticate.success';

# Response
const NOTIFY_RESPONSE_ERROR_HTTP = 'respond.error_http';
const NOTIFY_RESPONSE_ERROR_API = 'respond.error_api';
const NOTIFY_RESPONSE_ERROR_GENERAL = 'respond.error_general';
const NOTIFY_RESPONSE_SEND_HTTP = 'respond.http';
const NOTIFY_RESPONSE_SENDING = 'respond.send';

# Database
const NOTIFY_PDO_BOOTED = 'database.pdo_booted';
const NOTIFY_ORM_BOOTED = 'database.orm_booted';
const NOTIFY_DATABASE_BOOTED = 'database.booted';
const NOTIFY_ILLUMINATE_QUERY = 'illuminate.query';
