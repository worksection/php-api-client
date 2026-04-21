# Worksection PHP API Client

Official PHP client library for the [Worksection](https://worksection.com) API.

## Requirements

- PHP 7.4+
- Guzzle 7.4+
- extension: mbstring
- extension: json

## Installation

```bash
composer require worksection/api
```

## Authentication

The client supports two authentication methods: **API Key** (admin access) and **OAuth2** (user access).

### API Key

Get your API key from your Worksection account settings.

```php
use Worksection\Api\Client;

$client = new Client('https://your-account.worksection.com');
$client->setApiKey('your-api-key');
```

### OAuth2

```php
use Worksection\Api\Client;

$client = new Client('https://your-account.worksection.com');
$client->setClient([
    'client_id'     => 'your-client-id',
    'client_secret' => 'your-client-secret',
    'redirect_uri'  => 'https://your-app.com/callback',
    'scope'         => ['projects_read', 'tasks_read', 'tasks_write'],
    // defaults:
    'auth_url'      => 'https://worksection.com/oauth2/authorize',
    'token_url'     => 'https://worksection.com/oauth2/token',
    'user_url'      => 'https://worksection.com/oauth2/refresh'
]);

// Step 1: redirect the user to the authorization URL
$state = bin2hex(random_bytes(16));
$authUrl = $client->oauth->getAuthorizationUrl($state);
header('Location: ' . $authUrl);

// Step 2: exchange the authorization code for an access token (in your callback handler)
if ($_GET['state'] !== $state) throw new Exception('Invalid state');
$token = $client->oauth->fetchAccessTokenByAuthCode($_GET['code']);
$client->setAccessToken($token);

// The client will automatically refresh the access token using the refresh token when needed.
```

#### Available OAuth2 scopes

`projects_read`, `projects_write`, `tasks_read`, `tasks_write`, `costs_read`, `costs_write`,
`tags_read`, `tags_write`, `comments_read`, `comments_write`, `files_read`, `files_write`,
`users_read`, `users_write`, `contacts_read`, `contacts_write`, `administrative`

## Usage

### Projects

```php
// List all projects
$projects = $client->projects->list();

// Filter projects: active | pending | archived
$active = $client->projects->list(['filter' => 'active']);

// Get a single project
$project = $client->projects->get($projectId);

// Create a project
$project = $client->projects->create('Project title', [
    'email_manager' => 'manager@example.com',
    'dateend'       => '2025-12-31',
]);

// Update a project
$project = $client->projects->update($projectId, ['title' => 'New title']);

// Archive / activate
$client->projects->close($projectId);
$client->projects->activate($projectId);

// Manage members
$client->projects->addMembers($projectId, ['user@example.com']);
$client->projects->removeMembers($projectId, ['user@example.com']);

// Project groups (folders)
$groups = $client->projects->groups();
$group  = $client->projects->createGroup('Group name');

// Tags
$tags = $client->projects->tags();
$tag  = $client->projects->createTag($groupId, 'Tag name');
$client->projects->updateTags($projectId, addIds: [1, 2], removeIds: [3]);

// Tag groups
$tagGroups = $client->projects->tagGroups();
$tagGroup  = $client->projects->createTagGroup('Group name', access: 'public');

// Custom fields
$fields = $client->projects->customFields();
```

### Tasks

```php
// List tasks in a project
$tasks = $client->tasks->list($projectId);

// List all tasks across all projects
$tasks = $client->tasks->list();

// Get a single task (with extra data)
$task = $client->tasks->get($taskId, ['extra' => 'text,files,comments']);

// Create a task
$task = $client->tasks->create($projectId, 'Task title', [
    'email_user_to' => 'assignee@example.com',
    'dateend'       => '2025-06-30',
    'priority'      => 'high',
]);

// Create a subtask
$subtask = $client->tasks->create($projectId, 'Subtask title', [
    'id_parent' => $parentTaskId,
]);

// Update a task
$task = $client->tasks->update($taskId, ['title' => 'New title']);

// Complete / reopen
$client->tasks->complete($taskId);
$client->tasks->reopen($taskId);

// Search tasks
$tasks = $client->tasks->search(['email_user_to' => 'user@example.com', 'status' => 'active']);

// Subscribers
$client->tasks->subscribe($taskId, 'user@example.com');
$client->tasks->unsubscribe($taskId, 'user@example.com');

// Tags
$tags = $client->tasks->tags();
$tag  = $client->tasks->createTag($groupId, 'Tag name');
$client->tasks->updateTags($taskId, addIds: [1, 2], removeIds: [3]);

// Tag groups
$tagGroups = $client->tasks->tagGroups();
$tagGroup  = $client->tasks->createTagGroup('Group name', type: 'status', access: 'public');

// Custom fields
$fields = $client->tasks->customFields();
```

### Comments

```php
// List comments on a task
$comments = $client->comments->list($taskId);

// List comments with attached files
$comments = $client->comments->list($taskId, ['extra' => 'files']);

// Create a comment
$comment = $client->comments->create($taskId, 'Comment text', [
    'email_user_from' => 'author@example.com',
    'hidden'          => 1,   // internal comment
]);
```

### Members

```php
// List all account members
$members = $client->members->list();

// Invite a new member
$member = $client->members->create('newuser@example.com', [
    'first_name' => 'John',
    'last_name'  => 'Doe',
    'title'      => 'Developer',
    'role'       => 'user',  // user | manager | admin
]);

// Member groups (teams)
$groups = $client->members->groups();
$group  = $client->members->createGroup('Backend Team');

// Work schedules
$schedules = $client->members->schedule([
    'users'     => ['user@example.com'],
    'datestart' => '2025-01-01',
    'dateend'   => '2025-01-31',
]);

$client->members->updateSchedule([
    'user@example.com' => ['mon' => 8, 'tue' => 8, 'wed' => 8, 'thu' => 8, 'fri' => 8],
]);
```

### User (OAuth2 only)

```php
// Get the current authenticated user's profile
$profile = $client->user->profile();

// Timer management for the current user
$timer = $client->user->timer();           // get active timer (or null)
$client->user->start_timer($taskId);       // start timer on a task
$client->user->stop_timer('Done for today'); // stop and log with a comment
$client->user->discard_timer();            // discard without logging
```

### Costs

```php
// List expense records
$costs = $client->costs->list();

// Filter by project, task, or date range
$costs = $client->costs->list([
    'id_project' => $projectId,
    'datestart'  => '2025-01-01',
    'dateend'    => '2025-01-31',
]);

// Get totals/summary
$total = $client->costs->total(['id_project' => $projectId]);

// Get totals broken down by project and task
$total = $client->costs->total(['extra' => 'projects,tasks']);

// Create an expense record (time in minutes, money in account currency)
$costId = $client->costs->create($taskId, [
    'time'            => 90,
    'money'           => 50,
    'email_user_from' => 'user@example.com',
    'comment'         => 'Design work',
    'date'            => '15.06.2025',
]);

// Update an expense record
$client->costs->update($costId, ['time' => 120, 'comment' => 'Updated']);

// Delete an expense record
$client->costs->delete($costId);
```

### Timers (admin)

```php
// List all active timers across the account
$timers = $client->timers->all();

// Stop a specific timer
$client->timers->stop($timerId);
```

### Contacts

```php
// List all contacts
$contacts = $client->contacts->list();

// Create a contact
$contact = $client->contacts->create('client@example.com', 'Jane Smith', [
    'title'    => 'CEO',
    'group'    => $groupId,
    'phone'    => '+1 555 000 0000',
    'address'  => '123 Main St',
]);

// Contact groups
$groups = $client->contacts->groups();
$group  = $client->contacts->createGroup('VIP Clients');
```

### Events

```php
// Get recent events for the whole account
$events = $client->events->list('7d');

// Get events for a specific project
$events = $client->events->list('30d', $projectId);
```

### Files

```php
// List files on a task
$files = $client->files->list(['id_task' => $taskId]);

// List files in a project
$files = $client->files->list(['id_project' => $projectId]);
```

### Webhooks

```php
// List all webhooks
$webhooks = $client->webhook->list();

// Create a webhook
// Available events: post_task, post_comment, post_project,
//                   update_task, update_comment, update_project,
//                   delete_task, delete_comment, close_task
$id = $client->webhook->create(
    'https://your-app.com/webhook',
    ['post_task', 'update_task', 'close_task'],
    ['projects' => [$projectId]],
);

// Delete a webhook
$client->webhook->delete($id);
```

## Error handling

```php
use Worksection\Api\Exceptions\ResponseException;
use Worksection\Api\Exceptions\UnauthorizedException;

try {
    $task = $client->tasks->get($taskId);
} catch (UnauthorizedException $e) {
    // Invalid or expired token
} catch (ResponseException $e) {
    // API returned an error response
}
```

## License

MIT
