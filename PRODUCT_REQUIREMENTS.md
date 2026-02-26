# osTicket — Product & Technical Requirements Document

> **Generated from**: Repository analysis of [osTicket](https://github.com/osTicket/osTicket)
>
> **Purpose**: Structured documentation for AI coding agents and development teams.
> This document does NOT contain code. It contains business, functional, non-functional,
> domain, API, database, and security requirements derived from the existing codebase.

---

## Table of Contents

1. [Product Vision](#1-product-vision)
2. [Business Requirements](#2-business-requirements)
3. [Functional Requirements](#3-functional-requirements)
4. [Non-Functional Requirements](#4-non-functional-requirements)
5. [Domain Model Requirements](#5-domain-model-requirements)
6. [API Contract Expectations](#6-api-contract-expectations)
7. [Database Design Implications](#7-database-design-implications)
8. [Security Model](#8-security-model)
9. [Assumptions](#9-assumptions)

---

## 1. Product Vision

### Core Problem Being Solved

Organizations need a centralized system to receive, track, prioritize, and resolve
customer support inquiries arriving through multiple channels (email, web forms, phone
notes, and API integrations). Without such a system, inquiries are lost, response times
are inconsistent, and there is no accountability or audit trail for customer interactions.

### Target Personas

| Persona | Description |
|---------|-------------|
| **Support Agent (Staff)** | Front-line staff member who responds to tickets, adds internal notes, and collaborates with other agents. Uses the Staff Control Panel (`/scp/`). |
| **Help Desk Manager** | Configures departments, SLAs, help topics, roles, and workflows. Manages agent assignments, monitors queue health, and reviews performance. |
| **System Administrator** | Installs, configures, and maintains the osTicket instance. Manages plugins, email piping, API keys, and system settings. |
| **End User (Client)** | External customer who submits tickets via the client portal, email, or phone. May log in to check status and communicate with agents. |
| **Organization Manager** | Manages a group of end users under a shared organization with shared ticket visibility rules. |

### Value Proposition

- **Open-source and self-hosted**: Full data ownership with no recurring SaaS fees.
- **Multi-channel intake**: Tickets created from email, web portal, phone notes, and REST API.
- **Configurable workflow**: Custom statuses, SLAs, help topics, departments, and routing rules.
- **Role-based access control**: Granular permissions per department via roles.
- **Extensible via plugins**: Plugin system supports custom authentication backends, API endpoints, and features.
- **Internationalization**: Multi-language support with Crowdin-managed translations.

### Competitive Positioning

osTicket targets small-to-medium organizations that need a full-featured help desk without
the cost of commercial SaaS solutions (Zendesk, Freshdesk, ServiceNow). It competes on
simplicity of deployment (PHP + MySQL), low infrastructure requirements, and customizability.

### Strategic Importance

As an open-source project with a large installed base, osTicket serves as a foundation for
organizations requiring on-premise customer support management with complete control over
data residency and customization.

---

## 2. Business Requirements

### BR-001: Multi-Channel Ticket Intake

- **Description**: The system must allow tickets to be created from web forms, email, phone notes, and REST API.
- **Rationale**: Customers use different channels; unified intake prevents lost inquiries.
- **Success Metric**: 100% of incoming inquiries (regardless of channel) result in a tracked ticket.
- **Priority**: High

### BR-002: Ticket Lifecycle Management

- **Description**: The system must support configurable ticket states (open, closed, archived, custom) with transitions tracked in an audit log.
- **Rationale**: Organizations need visibility into ticket progress and the ability to enforce workflows.
- **Success Metric**: All state transitions are recorded with timestamps and actor information.
- **Priority**: High

### BR-003: Agent Assignment and Collaboration

- **Description**: The system must support assigning tickets to individual agents, teams, or departments, and allow collaborators to participate in ticket threads.
- **Rationale**: Efficient routing reduces resolution time and ensures accountability.
- **Success Metric**: 20% reduction in average first-response time after implementing routing rules.
- **Priority**: High

### BR-004: Service Level Agreement (SLA) Enforcement

- **Description**: The system must enforce configurable SLAs with grace periods, business-hours schedules, and overdue alerts.
- **Rationale**: SLA compliance is a contractual obligation for many organizations.
- **Success Metric**: 95% of tickets resolved within SLA grace period.
- **Priority**: High

### BR-005: Role-Based Access Control

- **Description**: The system must enforce granular permissions (ticket create, edit, assign, close, delete, etc.) through roles that can vary by department.
- **Rationale**: Prevents unauthorized access and ensures staff only perform authorized actions.
- **Success Metric**: Zero unauthorized ticket modifications in audit log.
- **Priority**: High

### BR-006: Client Self-Service Portal

- **Description**: End users must be able to create tickets, check status, and communicate with agents through a web portal.
- **Rationale**: Self-service reduces support staff workload and improves customer satisfaction.
- **Success Metric**: 30% of ticket updates made by clients through the portal.
- **Priority**: Medium

### BR-007: Knowledge Base

- **Description**: The system must provide a knowledge base where articles can be published and made available to clients.
- **Rationale**: Deflects common inquiries, reducing ticket volume.
- **Success Metric**: 10% reduction in ticket volume for topics covered by KB articles.
- **Priority**: Medium

### BR-008: Email Integration

- **Description**: The system must support inbound email parsing (creating tickets from emails) and outbound email notifications (alerts, auto-responses).
- **Rationale**: Email is the primary communication channel for most support operations.
- **Success Metric**: 100% of valid inbound emails converted to tickets or thread entries.
- **Priority**: High

### BR-009: Reporting and Audit Trail

- **Description**: The system must maintain a complete audit trail of all ticket actions and provide reporting capabilities.
- **Rationale**: Compliance, performance monitoring, and dispute resolution require complete records.
- **Success Metric**: Every ticket action (create, edit, assign, close, reopen) recorded with timestamp and actor.
- **Priority**: High

### BR-010: Plugin Extensibility

- **Description**: The system must support a plugin architecture allowing third-party extensions for authentication, API endpoints, and custom features.
- **Rationale**: Organizations need to integrate with existing infrastructure (LDAP, OAuth providers, CRM systems).
- **Success Metric**: Plugins can be installed, enabled, configured, and disabled without modifying core code.
- **Priority**: Medium

### BR-011: Internationalization

- **Description**: The system must support multiple languages for both staff and client interfaces.
- **Rationale**: Organizations serve global customer bases with diverse language requirements.
- **Success Metric**: UI strings available in target languages with no hardcoded English text.
- **Priority**: Medium

### BR-012: Organization and User Management

- **Description**: The system must support grouping end users into organizations with shared ticket visibility and automatic collaborator addition.
- **Rationale**: Enterprise customers expect shared visibility of their organization's tickets.
- **Success Metric**: Organization members can view tickets from other members when configured.
- **Priority**: Medium

---

## 3. Functional Requirements

### FR-001: Create Ticket via Web Form

- **ID**: FR-001
- **Actor**: End User (Client)
- **Trigger**: Client submits a ticket form on the client portal.
- **System Behavior**: Validate form fields, create ticket record, create initial thread entry, apply help topic routing rules, assign default SLA, send confirmation email.
- **Preconditions**: Client portal is accessible; help topics are configured.
- **Postconditions**: Ticket exists with status "Open", thread contains the initial message, auto-response sent.
- **Error Handling**: Return validation errors for missing/invalid required fields. If email delivery fails, ticket is still created (email failure is non-blocking).
- **Edge Cases**: Duplicate submission (same content within short window); client not registered (guest ticket); attachments exceed size limit; CAPTCHA validation failure.

### FR-002: Create Ticket via Email

- **ID**: FR-002
- **Actor**: End User (via email)
- **Trigger**: Inbound email received by configured mailbox, fetched by mail poller or piped via `/api/pipe.php`.
- **System Behavior**: Parse email headers, body, and attachments. Match sender to existing user or create new user. Create ticket with parsed content. Apply email-specific routing rules.
- **Preconditions**: Email account configured; mail fetching enabled or pipe endpoint accessible.
- **Postconditions**: Ticket created with email source; sender linked as ticket owner; attachments stored.
- **Error Handling**: Malformed email returns bounce message. Blocked sender (ban list) silently discards. Auto-reply loop detection prevents infinite responses.
- **Edge Cases**: HTML-only email with no text part; email with inline images; email exceeding size limit; email from banned address; reply to closed ticket (reopen behavior); email with no subject line.

### FR-003: Create Ticket via API

- **ID**: FR-003
- **Actor**: External System
- **Trigger**: POST request to `/api/tickets.json` or `/api/tickets.xml`.
- **System Behavior**: Validate API key and IP address. Parse request body (JSON or XML). Validate required fields. Create ticket. Return ticket number or error.
- **Preconditions**: API key exists, is active, has `can_create_tickets` permission, and is bound to the request IP.
- **Postconditions**: Ticket created; HTTP 201 response returned.
- **Error Handling**: 401 for invalid/missing API key; 403 for IP mismatch or insufficient permissions; 400 for invalid request body; 415 for unsupported content type; 500 for internal errors.
- **Edge Cases**: API key with expired or revoked status; request body exceeding size limit; concurrent requests creating duplicate tickets; XML with encoding issues.

### FR-004: Agent Responds to Ticket

- **ID**: FR-004
- **Actor**: Support Agent (Staff)
- **Trigger**: Agent submits a reply from the Staff Control Panel.
- **System Behavior**: Validate agent has `ticket.reply` permission for the ticket's department. Create thread entry of type "Response". Update ticket `lastupdate` and `isanswered` flag. Send email notification to ticket owner and collaborators. Update SLA timers.
- **Preconditions**: Agent is authenticated; ticket is in an open state; agent has reply permission.
- **Postconditions**: Thread entry created; client notified; `isanswered` set to true.
- **Error Handling**: Permission denied if agent lacks `ticket.reply` for the department. Lock conflict if another agent has the ticket locked.
- **Edge Cases**: Agent replies to a closed ticket (auto-reopen behavior); reply with only attachments (no text body); reply to ticket owned by banned user.

### FR-005: Assign Ticket to Agent or Team

- **ID**: FR-005
- **Actor**: Support Agent or Manager
- **Trigger**: Agent uses assign action from ticket view.
- **System Behavior**: Validate `ticket.assign` permission. Update `ticket.staff_id` and/or `ticket.team_id`. Create thread event recording assignment. Send assignment alert to assignee.
- **Preconditions**: Agent is authenticated with `ticket.assign` permission for the ticket's department.
- **Postconditions**: Ticket reflects new assignment; assignee notified; event logged.
- **Error Handling**: Permission denied for unauthorized agent. Assignee validation fails if target staff is inactive or on vacation.
- **Edge Cases**: Re-assigning from one agent to another; assigning to a team when already assigned to an individual; auto-assignment rules from department configuration.

### FR-006: Transfer Ticket Between Departments

- **ID**: FR-006
- **Actor**: Support Agent or Manager
- **Trigger**: Agent uses transfer action.
- **System Behavior**: Validate `ticket.transfer` permission. Update `ticket.dept_id`. Apply new department's default SLA if ticket has no manual SLA. Create thread event. Send alerts to new department.
- **Preconditions**: Agent has `ticket.transfer` permission; target department exists and is active.
- **Postconditions**: Ticket moved to new department; SLA may change; previous assignment may be cleared.
- **Error Handling**: Cannot transfer to archived department. Cannot transfer if department has no active agents.
- **Edge Cases**: Transfer causes SLA change that makes ticket overdue; transfer removes current agent's access; circular transfer detection.

### FR-007: Close Ticket

- **ID**: FR-007
- **Actor**: Support Agent
- **Trigger**: Agent uses close action or sets status to a closed state.
- **System Behavior**: Validate `ticket.close` permission. Update `ticket.status_id` to a status with `state=closed`. Set `ticket.closed` timestamp. Create thread event. Send closure notification to client.
- **Preconditions**: Agent has `ticket.close` permission; ticket is in an open state.
- **Postconditions**: Ticket status is closed; `closed` timestamp set; client notified.
- **Error Handling**: Permission denied if agent lacks permission. Cannot close ticket that is already closed.
- **Edge Cases**: Client replies to closed ticket (reopen behavior); bulk close operation; closing with a specific closure reason.

### FR-008: Merge Tickets

- **ID**: FR-008
- **Actor**: Support Agent
- **Trigger**: Agent selects merge action on two or more tickets.
- **System Behavior**: Validate `ticket.merge` permission. Combine thread entries from secondary tickets into primary ticket. Update `ticket.flags` with merge indicators. Close or delete secondary tickets.
- **Preconditions**: Agent has `ticket.merge` permission; at least two tickets selected; tickets are compatible for merge.
- **Postconditions**: Single ticket with combined thread; secondary tickets marked as merged.
- **Error Handling**: Cannot merge tickets from different users without confirmation. Cannot merge already-merged tickets.
- **Edge Cases**: Merging tickets with different SLAs (use the stricter one); merging tickets across departments; merging ticket with itself.

### FR-009: SLA Grace Period and Overdue Detection

- **ID**: FR-009
- **Actor**: System (automated)
- **Trigger**: Cron job executes or ticket is created/updated.
- **System Behavior**: Calculate due date by adding SLA grace period (in business hours) to ticket creation date. Compare current time against due date. If overdue, set `ticket.isoverdue` flag, trigger overdue alert, and optionally escalate priority.
- **Preconditions**: SLA is assigned to ticket; business hours schedule is defined.
- **Postconditions**: `isoverdue` flag accurately reflects SLA compliance; alerts sent if configured.
- **Error Handling**: If schedule has no defined hours (e.g., holidays), use calendar time. If SLA is deleted, ticket retains last calculated due date.
- **Edge Cases**: Ticket created outside business hours; SLA changed mid-lifecycle; DST transitions during grace period calculation; ticket reopened after SLA expiry.

### FR-010: User Authentication (Staff)

- **ID**: FR-010
- **Actor**: Support Agent
- **Trigger**: Agent navigates to Staff Control Panel login page.
- **System Behavior**: Present login form. Validate credentials against configured backends (database, LDAP, OAuth2). If 2FA enabled, prompt for second factor. Create authenticated session. Redirect to SCP dashboard.
- **Preconditions**: Staff account exists and is active; authentication backend is configured.
- **Postconditions**: Authenticated session created; session token stored in HttpOnly cookie.
- **Error Handling**: Invalid credentials return generic error (no username enumeration). Account locked after excessive failures. 2FA failure returns to 2FA prompt.
- **Edge Cases**: Concurrent login from multiple devices; session expiration during active use; backend (LDAP) unavailable; password expired.

### FR-011: User Authentication (Client)

- **ID**: FR-011
- **Actor**: End User (Client)
- **Trigger**: Client navigates to portal login page.
- **System Behavior**: Present login form. Validate credentials against configured backends. Support email-based authentication and OAuth2 federation. Auto-register new users from federated sources. Create authenticated session.
- **Preconditions**: Client portal login is enabled; authentication backend is configured.
- **Postconditions**: Authenticated session created for client.
- **Error Handling**: Invalid credentials show generic error. Unregistered email prompted to register or use guest access.
- **Edge Cases**: Client with multiple email addresses; OAuth2 provider returns different email than registered; auto-registration disabled.

### FR-012: Dynamic Form Fields

- **ID**: FR-012
- **Actor**: System Administrator
- **Trigger**: Administrator configures custom form fields for tickets, users, or organizations.
- **System Behavior**: Store form definitions with field types (text, dropdown, checkbox, date, etc.), validation rules, and visibility settings. Render fields on ticket creation, user registration, and organization forms. Store submitted values in form entry tables.
- **Preconditions**: Administrator has access to form configuration.
- **Postconditions**: Custom fields appear on relevant forms; submitted values are stored and searchable.
- **Error Handling**: Required field validation; field type mismatch; dropdown with no options configured.
- **Edge Cases**: Changing field type after data exists; deleting a field with existing data; field visibility per help topic.

### FR-013: Email Notifications and Auto-Responses

- **ID**: FR-013
- **Actor**: System (automated)
- **Trigger**: Ticket events (creation, response, assignment, closure, overdue).
- **System Behavior**: Select email template based on event type and department. Render template with ticket data. Send email via configured SMTP or mail function. Record delivery in thread.
- **Preconditions**: Email templates configured; SMTP settings valid; auto-response not disabled.
- **Postconditions**: Email sent to relevant recipients; delivery recorded.
- **Error Handling**: SMTP failure logged but does not block ticket operations. Bounce-back handling for invalid recipient addresses.
- **Edge Cases**: Auto-response disabled per department or help topic; recipient email address is invalid; email template has missing variables; loop detection for auto-replies.

### FR-014: Ticket Locking

- **ID**: FR-014
- **Actor**: Support Agent
- **Trigger**: Agent opens a ticket for editing.
- **System Behavior**: Acquire lock on ticket to prevent concurrent editing. Lock has a configurable timeout. Display lock status to other agents attempting to access the same ticket.
- **Preconditions**: Ticket exists and is not locked by another agent, or existing lock has expired.
- **Postconditions**: Lock acquired; other agents see ticket as locked.
- **Error Handling**: Lock conflict shows owning agent's name and expiration time. Lock auto-released on timeout.
- **Edge Cases**: Agent closes browser without releasing lock (timeout handles this); agent session expires while holding lock; administrator override to release lock.

### FR-015: Internal Notes

- **ID**: FR-015
- **Actor**: Support Agent
- **Trigger**: Agent adds an internal note to a ticket.
- **System Behavior**: Create thread entry of type "Note" (type='N'). Notes are visible only to staff, not to clients. Send alert to assigned agent and department members if configured.
- **Preconditions**: Agent is authenticated and has access to the ticket.
- **Postconditions**: Internal note visible in ticket thread to staff only.
- **Error Handling**: Client portal never displays entries of type 'N'.
- **Edge Cases**: Note added to closed ticket; note with attachments only; note mentioning another agent (no @mention system exists currently).

### FR-016: Plugin Installation and Management

- **ID**: FR-016
- **Actor**: System Administrator
- **Trigger**: Administrator installs or configures a plugin via SCP.
- **System Behavior**: Scan plugin directory or accept PHAR upload. Validate plugin manifest (`plugin.php`). For PHAR files, verify signature. Register plugin in database. Execute plugin bootstrap on enable.
- **Preconditions**: Plugin files present in `/include/plugins/` directory or uploaded as PHAR.
- **Postconditions**: Plugin registered, enabled, and executing bootstrap code.
- **Error Handling**: Invalid manifest returns descriptive error. PHAR signature verification failure blocks installation. Plugin with conflicting ID rejected.
- **Edge Cases**: Plugin depends on a specific osTicket version; plugin modifies database schema; disabling plugin with active sessions; plugin causing PHP fatal error.

---

## 4. Non-Functional Requirements

### 4.1 Performance

| Metric | Requirement | Notes |
|--------|-------------|-------|
| Page load time (SCP) | < 3 seconds for ticket list, < 2 seconds for ticket view | Database indexing on common query patterns |
| Ticket creation (API) | < 1 second response time | Excludes email delivery time |
| Email parsing | < 5 seconds per email | Including attachment processing |
| Search query | < 3 seconds for full-text search | Depends on MySQL full-text index configuration |
| Concurrent staff sessions | Support 50+ concurrent agents | Session storage in database, not filesystem |
| Database queries per page | < 20 queries per page load | ORM query optimization |

### 4.2 Security

- **Authentication model**: Session-based with HttpOnly cookies (staff and client portals); API key with IP binding (REST API).
- **Role/claim mapping**: JSON-based permission storage in roles; roles assigned per staff member and optionally overridden per department.
- **Encryption**: Passwords hashed via bcrypt (or backend-specific mechanism); sensitive data encrypted with `Crypto` class using SECRET_SALT.
- **Session security**: Token-based session validation with browser fingerprint and optional IP binding; session ID regeneration on configurable interval.
- **CSRF protection**: Token-based, rotated on expiration, validated on all POST requests.
- **2FA**: Optional TOTP/HOTP second factor for staff accounts.

### 4.3 Availability

| Metric | Target |
|--------|--------|
| Uptime SLA | 99.9% (depends on hosting infrastructure) |
| Planned maintenance window | < 15 minutes for upgrades |
| Database backup frequency | Hourly recommended; migration support from v1.6+ |
| Recovery time objective (RTO) | < 1 hour with database restore |
| Recovery point objective (RPO) | < 1 hour with hourly backups |

### 4.4 Observability

- **Logging**: PHP error log for application errors; `ost_syslog` table for system events; email delivery logs in thread entries.
- **Audit trail**: Thread events (`thread_event` table) record all ticket actions with actor, timestamp, and detail.
- **Admin dashboard**: System information page shows PHP version, database status, and configuration health.
- **Cron monitoring**: Cron job execution tracked; alerts if cron has not run within expected interval.

### 4.5 Maintainability

- **PHP compatibility**: PHP 8.2–8.4 (8.4 recommended).
- **Database compatibility**: MySQL 5.5+.
- **Upgrade path**: Supported from v1.6-rc1 forward; database migration scripts in `/setup/inc/streams/`.
- **Configuration**: Stored in database (`config` table), not in filesystem files (except initial `ost-config.php`).

### 4.6 Extensibility

- **Plugin system**: Directory-based or PHAR-based plugins with manifest files.
- **Signal system**: Event-driven hooks allow plugins to intercept and modify core behavior.
- **Custom authentication backends**: Extend `AuthenticationBackend` for LDAP, SAML, or custom auth.
- **Custom API endpoints**: Register via `api` signal on the Dispatcher.
- **Custom form fields**: Dynamic form system allows arbitrary fields per entity type.

### 4.7 Data Isolation

- **Single-tenant architecture**: Each osTicket installation is a single tenant. Multi-tenancy is achieved through separate installations.
- **Table prefix**: Configurable `TABLE_PREFIX` allows multiple osTicket instances on a single database.
- **No cross-tenant data access**: By design, each installation has its own database or prefixed tables.

### 4.8 API Versioning

- **Current state**: No explicit API versioning. Single version endpoint structure (`/api/tickets.json`).
- **Recommendation**: Future changes should consider versioned endpoints (`/api/v1/tickets.json`).
- **Backward compatibility**: API consumers rely on current endpoint structure; breaking changes require deprecation period.

---

## 5. Domain Model Requirements

### 5.1 Core Entities

#### Ticket (Aggregate Root)

- **Fields**: ticket_id (PK), number (display), user_id (FK), status_id (FK), dept_id (FK), sla_id (FK), topic_id (FK), staff_id (FK, nullable), team_id (FK, nullable), source (enum: Web/Email/Phone/API/Other), flags (bitmask), isoverdue, isanswered, duedate, est_duedate, reopened, closed, lastupdate, created, updated
- **Invariants**: Must have a user (owner); must have a department; must have a status; number must be unique.
- **Lifecycle States**: Open → Closed → Archived (configurable custom states)
- **Domain Events**: TicketCreated, TicketAssigned, TicketTransferred, TicketResponded, TicketClosed, TicketReopened, TicketOverdue, TicketMerged, TicketLinked

#### Thread (Value Object / Child of Ticket)

- **Fields**: id (PK), object_id (FK: ticket_id or task_id), object_type (char: T=Ticket, K=Task), lastresponse, lastmessage
- **Invariants**: Exactly one thread per ticket/task.
- **Contains**: ThreadEntry[], ThreadEvent[], ThreadCollaborator[]

#### ThreadEntry

- **Fields**: id (PK), thread_id (FK), pid (parent entry FK), staff_id (FK, nullable), user_id (FK, nullable), type (char: M=message, N=note, R=response), flags, poster, source, title, body, format, ip_address, created, updated
- **Invariants**: Must belong to a thread; type determines visibility (N=staff-only).

#### User (End User / Client)

- **Fields**: id (PK), org_id (FK, nullable), default_email_id (FK), name, status, created, updated
- **Invariants**: Must have at least one email; default_email_id must reference one of the user's emails.
- **Relationships**: Has many UserEmail, optionally has UserAccount (portal login), belongs to Organization.

#### Staff (Support Agent)

- **Fields**: staff_id (PK), dept_id (FK), role_id (FK), username (unique), firstname, lastname, email, isactive, isadmin, permissions (JSON), lastlogin
- **Invariants**: Username must be unique; must have a primary department and role.
- **Relationships**: Has many StaffDeptAccess (extended departments), belongs to many Teams.

#### Department

- **Fields**: id (PK), pid (FK, nullable for hierarchy), sla_id (FK, nullable), manager_id (FK, nullable), name, flags, ispublic, path
- **Invariants**: Name must be unique within parent; hierarchical structure.
- **Relationships**: Has many Staff (primary + extended), has default SLA, has manager.

#### Role

- **Fields**: id (PK), name (unique), permissions (JSON), flags
- **Invariants**: Name must be unique; permissions is a JSON object of permission keys.
- **Permissions Map**: `{"ticket.create": 1, "ticket.edit": 1, "ticket.assign": 1, ...}`

#### SLA (Service Level Agreement)

- **Fields**: id (PK), name (unique), schedule_id (FK), grace_period (hours), flags
- **Invariants**: Grace period must be positive; name must be unique.
- **Flags**: FLAG_ACTIVE, FLAG_ESCALATE, FLAG_NOALERTS, FLAG_TRANSIENT

#### Organization

- **Fields**: id (PK), name, manager (string encoding: s{id} for staff, t{id} for team), status, domain, extra (JSON)
- **Invariants**: Name should be unique.
- **Flags**: COLLAB_ALL_MEMBERS, COLLAB_PRIMARY_CONTACT, ASSIGN_AGENT_MANAGER, SHARE_PRIMARY_CONTACT, SHARE_EVERYBODY

#### HelpTopic

- **Fields**: topic_id (PK), topic_pid (FK, nullable), dept_id (FK), staff_id (FK, nullable), team_id (FK, nullable), sla_id (FK, nullable), topic (name), flags, ispublic
- **Invariants**: Topic name unique within parent; hierarchical structure.
- **Purpose**: Categorization and automatic routing rules.

#### Task

- **Fields**: id (PK), object_id (FK, nullable), object_type, dept_id (FK), staff_id (FK, nullable), team_id (FK, nullable), flags, title, duedate, created, updated
- **Invariants**: Must have a department.
- **Relationships**: Associated with Ticket (optional), has own Thread.

### 5.2 Entity Relationships

```
Ticket ──→ User (owner, M:1)
Ticket ──→ Staff (assignee, M:1, nullable)
Ticket ──→ Team (assignee, M:1, nullable)
Ticket ──→ Department (routing, M:1)
Ticket ──→ TicketStatus (state, M:1)
Ticket ──→ SLA (service level, M:1, nullable)
Ticket ──→ HelpTopic (category, M:1, nullable)
Ticket ──→ Thread (conversation, 1:1)
Thread ──→ ThreadEntry (messages, 1:M)
Thread ──→ ThreadCollaborator (participants, 1:M)
User ──→ UserEmail (addresses, 1:M)
User ──→ UserAccount (portal login, 1:0..1)
User ──→ Organization (membership, M:1, nullable)
Staff ──→ Department (primary, M:1)
Staff ──→ Role (permissions, M:1)
Staff ──→ StaffDeptAccess (extended depts, 1:M)
Staff ──→ TeamMember (teams, M:M through team_member)
Department ──→ Department (hierarchy, self-referential)
HelpTopic ──→ HelpTopic (hierarchy, self-referential)
Organization ──→ User (members, 1:M)
```

### 5.3 Domain Events

| Event | Trigger | Data |
|-------|---------|------|
| TicketCreated | New ticket via any channel | ticket_id, source, user_id, dept_id |
| TicketAssigned | Agent assignment change | ticket_id, staff_id, team_id, assigner_id |
| TicketTransferred | Department change | ticket_id, old_dept_id, new_dept_id, actor_id |
| TicketResponded | Staff reply posted | ticket_id, entry_id, staff_id |
| TicketClosed | Status changed to closed | ticket_id, actor_id, reason |
| TicketReopened | Closed ticket receives new message or manual reopen | ticket_id, actor_id |
| TicketOverdue | SLA grace period exceeded | ticket_id, sla_id, duedate |
| TicketMerged | Tickets combined | primary_ticket_id, merged_ticket_ids |
| MessageReceived | Client posts a message | ticket_id, entry_id, user_id |
| NoteAdded | Internal note posted | ticket_id, entry_id, staff_id |

---

## 6. API Contract Expectations

### 6.1 Create Ticket (JSON)

- **Endpoint**: `POST /api/tickets.json`
- **Purpose**: Create a new support ticket from an external system.
- **Authentication**: `X-API-Key` header; IP must match registered API key.
- **Request Structure**:
  ```
  {
    "name": "John Doe",              // Required: client name
    "email": "john@example.com",     // Required: client email
    "subject": "Help needed",        // Required: ticket subject
    "message": "Detailed issue...",  // Required: ticket body (HTML or text)
    "topicId": 1,                    // Optional: help topic ID
    "phone": "555-0100",             // Optional: contact phone
    "attachments": [                 // Optional: file attachments
      {
        "filename": "screenshot.png",
        "data": "<base64-encoded>",
        "type": "image/png"
      }
    ],
    "ip": "192.168.1.1",            // Optional: client IP address
    "priority": 2                    // Optional: priority ID
  }
  ```
- **Response Structure (Success)**: HTTP 201 with ticket number in body.
- **Error Responses**:
  | Status | Meaning |
  |--------|---------|
  | 400 | Bad request (missing required fields, validation error) |
  | 401 | Invalid or missing API key |
  | 403 | IP not authorized or insufficient API key permissions |
  | 415 | Unsupported content type |
  | 500 | Internal server error |
- **Pagination**: Not applicable (single resource creation).
- **Idempotency**: Not enforced. Duplicate requests create duplicate tickets. Consumers should implement client-side deduplication.

### 6.2 Create Ticket (XML)

- **Endpoint**: `POST /api/tickets.xml`
- **Purpose**: Same as JSON endpoint, accepts XML payload.
- **Authentication**: Same as JSON endpoint.
- **Request Structure**: XML equivalent of JSON structure above.
- **Response / Errors**: Same as JSON endpoint.

### 6.3 Create Ticket (Email Format)

- **Endpoint**: `POST /api/tickets.email`
- **Purpose**: Create ticket from raw email content (used by email piping).
- **Authentication**: Same `X-API-Key` header.
- **Request Structure**: Raw RFC 2822 email message in request body.
- **Response / Errors**: Same status codes; additional parsing errors for malformed email.

### 6.4 Execute Cron

- **Endpoint**: `POST /api/tasks/cron`
- **Purpose**: Trigger scheduled maintenance tasks (mail fetching, overdue detection, etc.).
- **Authentication**: `X-API-Key` header with `can_exec_cron` permission; IP binding.
- **Request Structure**: Empty body.
- **Response**: HTTP 200 on success.
- **Error Responses**: 401/403 for auth failures.
- **Idempotency**: Safe to call repeatedly; idempotent by nature.

### 6.5 Email Pipe

- **Endpoint**: `POST /api/pipe.php`
- **Purpose**: Receive piped email from mail server (e.g., Postfix pipe).
- **Authentication**: `X-API-Key` header.
- **Request Structure**: Raw email on stdin/request body.
- **Response**: Exit code 0 on success (for MTA integration).

### 6.6 Plugin-Registered Endpoints

- **Mechanism**: Plugins register custom endpoints via the `api` signal on the Dispatcher.
- **Contract**: Plugin-defined; no standardized request/response structure.
- **Discovery**: Not discoverable via API; documentation is plugin-specific.

---

## 7. Database Design Implications

### 7.1 Core Tables

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `ticket` | Primary ticket records | ticket_id (PK), number, user_id, status_id, dept_id, sla_id, staff_id, team_id |
| `ticket_status` | Custom status definitions | id (PK), name, state, mode, flags |
| `thread` | Conversation container | id (PK), object_id, object_type |
| `thread_entry` | Individual messages/notes | id (PK), thread_id, type, staff_id, user_id, body |
| `thread_event` | Audit log of actions | id (PK), thread_id, event_id, staff_id, data, timestamp |
| `thread_collaborator` | External participants | id (PK), thread_id, user_id, role |
| `user` | End users / clients | id (PK), org_id, default_email_id, name |
| `user_email` | User email addresses | id (PK), user_id, address (unique) |
| `user_account` | Portal login credentials | id (PK), user_id, username (unique), passwd |
| `staff` | Support agents | staff_id (PK), dept_id, role_id, username (unique) |
| `staff_dept_access` | Cross-department access | staff_id + dept_id (composite PK), role_id |
| `department` | Support departments | id (PK), pid, sla_id, manager_id, name |
| `team` | Agent teams | team_id (PK), lead_id, name |
| `team_member` | Staff-to-team mapping | team_id + staff_id (composite PK) |
| `role` | Permission groups | id (PK), name (unique), permissions (JSON) |
| `sla` | Service level agreements | id (PK), name (unique), schedule_id, grace_period |
| `schedule` | Business hours definition | id (PK), name, timezone |
| `schedule_entry` | Business hours entries | id (PK), schedule_id, starts_at, ends_at, repeats |
| `help_topic` | Ticket categories | topic_id (PK), topic_pid, dept_id, topic |
| `organization` | Customer organizations | id (PK), name, manager, domain |
| `form` | Dynamic form definitions | id (PK), type, title |
| `form_field` | Dynamic field definitions | id (PK), form_id, type, name, label |
| `form_entry` | Form submission instances | id (PK), form_id, object_id, object_type |
| `form_entry_values` | Submitted field values | entry_id + field_id (composite), value |
| `email` | Configured email accounts | email_id (PK), dept_id, email, name |
| `email_template` | Notification templates | id (PK), tpl_id, code_name, subject, body |
| `api_key` | REST API keys | id (PK), isactive, ipaddr, apikey (unique) |
| `plugin` | Installed plugins | id (PK), name, install_path, isactive |
| `config` | System configuration | id (PK), namespace, key, value |
| `session` | Active sessions | session_id (PK), session_data, session_expire |
| `syslog` | System log entries | log_id (PK), log_type, title, log, ip_address, created |
| `task` | Tasks associated with tickets | id (PK), object_id, dept_id, staff_id, title |

### 7.2 Indexing Strategy

| Table | Index | Purpose |
|-------|-------|---------|
| `ticket` | user_id, dept_id, staff_id, team_id | Assignment and ownership lookups |
| `ticket` | status_id, created, closed, duedate | Filtering and queue display |
| `ticket` | sla_id, topic_id | Routing and SLA queries |
| `ticket` | number | Unique ticket number lookup |
| `thread_entry` | thread_id, type | Thread traversal by entry type |
| `thread_entry` | staff_id, user_id | Author-based queries |
| `thread_event` | thread_id, timestamp | Audit trail queries |
| `user_email` | address (unique) | Email-to-user resolution |
| `staff` | username (unique) | Login lookup |
| `user_account` | username (unique) | Client login lookup |

### 7.3 Unique Constraints

- `ticket.number` — Ticket display number must be globally unique.
- `user_email.address` — Each email address belongs to exactly one user.
- `staff.username` — Staff login names must be unique.
- `user_account.username` — Client portal login names must be unique.
- `role.name` — Role names must be unique.
- `ticket_status.name` — Status names must be unique.
- `sla.name` — SLA names must be unique.
- `api_key.apikey` — API keys must be unique.

### 7.4 Soft Delete Strategy

- **No universal soft delete**: The codebase does not implement a global soft-delete pattern.
- **Status-based archival**: Tickets use status states (closed, archived) rather than deletion.
- **Department archival**: Departments support an `archived` flag in their flags bitmask.
- **Active/inactive flags**: Staff and API keys use `isactive` boolean fields.
- **Hard delete**: Users, tickets, and entries can be permanently deleted when agents have `*.delete` permission.

### 7.5 Audit Fields

| Field | Tables | Purpose |
|-------|--------|---------|
| `created` | ticket, user, staff, thread_entry, syslog | Creation timestamp |
| `updated` | ticket, user, thread_entry | Last modification timestamp |
| `lastupdate` | ticket | Last activity (any type) |
| `closed` | ticket | Closure timestamp |
| `reopened` | ticket | Reopen timestamp |
| `lastlogin` | staff | Last successful login |
| `editor` / `editor_type` | thread_entry | Last editor identity |
| `ip_address` | thread_entry, syslog | Actor IP address |

### 7.6 Tenant Isolation Strategy

- **Single-tenant by design**: Each osTicket installation is a separate tenant.
- **Table prefix**: `TABLE_PREFIX` constant allows multiple installations on one database.
- **No row-level tenant isolation**: There is no `tenant_id` column on tables.
- **Multi-tenancy recommendation**: Deploy separate osTicket instances per tenant, optionally sharing the same database server with different prefixes.

### 7.7 Migration Considerations

- **Upgrade support**: From v1.6-rc1 forward.
- **Migration scripts**: Located in `/setup/inc/streams/` organized by version.
- **Schema changes**: Applied sequentially during upgrade wizard (`/setup/`).
- **Backup requirement**: Database and codebase backup mandatory before upgrade.
- **Data preservation**: Migrations are non-destructive (additive schema changes).

---

## 8. Security Model

### 8.1 Authentication Mechanism

| Context | Mechanism | Details |
|---------|-----------|---------|
| **Staff Portal** | Session-based (cookie) | Custom `OSTSESSID` cookie; HttpOnly; Secure flag on HTTPS; session stored in database |
| **Client Portal** | Session-based (cookie) | Same session mechanism as staff with separate session namespace |
| **REST API** | API key (header) | `X-API-Key` header; key validated against database; IP address binding |
| **OAuth2** | Token-based | Access tokens with expiration and scope; refresh token support; used for federated authentication |

### 8.2 Role Model

- **Role entity**: Named permission group stored in `role` table.
- **Permission storage**: JSON object in `role.permissions` column.
- **Assignment**: Staff have a default role (`staff.role_id`); additional roles per department via `staff_dept_access.role_id`.
- **Admin flag**: `staff.isadmin` grants full system access independent of role.
- **Evaluation**: Permission check for a ticket considers the ticket's department and the staff's role in that department.

### 8.3 Permission Model

| Category | Permissions |
|----------|-------------|
| **Ticket** | ticket.create, ticket.edit, ticket.assign, ticket.release, ticket.transfer, ticket.refer, ticket.merge, ticket.link, ticket.reply, ticket.markanswered, ticket.close, ticket.delete |
| **Task** | task.create, task.edit, task.assign, task.close, task.delete |
| **User** | user.create, user.edit, user.delete, user.manage, user.dir |
| **Organization** | org.create, org.edit, org.delete |
| **Knowledge Base** | kb.create, kb.edit, kb.delete, kb.publish |
| **Miscellaneous** | visibility.agents, visibility.departments, attachment.manage, email.banlist |

### 8.4 Data Access Rules

- **Staff access to tickets**: Governed by department membership (primary + extended via `staff_dept_access`) and `assigned_only` flag.
- **Client access to tickets**: Clients see only their own tickets; organization members may see other members' tickets based on organization sharing flags.
- **API access**: Scoped by API key permissions (`can_create_tickets`, `can_exec_cron`); no ticket read/update via API in core.
- **Admin override**: Staff with `isadmin=1` bypass department-based access restrictions.

### 8.5 Cross-Tenant Protection

- **Isolation by installation**: Each osTicket instance has its own database/prefix, session namespace, and `SECRET_SALT`.
- **No shared authentication**: Sessions and API keys are not shared across installations.
- **Table prefix**: Prevents accidental cross-tenant data access when sharing a database server.

### 8.6 Rate Limiting

- **Not built into core**: osTicket does not implement application-level rate limiting.
- **Recommendation**: Rate limiting should be configured at the web server level (e.g., Apache `mod_ratelimit`, Nginx `limit_req_zone`).
- **Account lockout**: Excessive failed login attempts may trigger account lockout (backend-dependent).

### 8.7 CSRF Protection

- **Mechanism**: Token-based CSRF protection via `class.csrf.php`.
- **Token generation**: `sha1(session_id() . random_bytes . SECRET_SALT)`.
- **Token delivery**: Hidden form field (`__CSRFToken__`) on all POST forms.
- **Validation**: Every POST request validated against session-stored token.
- **Rotation**: Token rotated on expiration (configurable timeout).

### 8.8 XSS Protection

- **HTML filtering**: HTMLawed library used to sanitize user-submitted HTML content.
- **Output encoding**: Template rendering escapes output.
- **Content-Type**: Responses set explicit `Content-Type` with charset.
- **Gaps**: No explicit `Content-Security-Policy` header in application code; recommended to configure at web server level.

### 8.9 Additional Security Measures

- **HTTPS enforcement**: Configurable redirect from HTTP to HTTPS.
- **Session hardening**: Session ID regeneration at configurable intervals; HttpOnly cookies; optional IP binding.
- **Disabled PHP features**: `register_globals`, `allow_url_fopen`, `allow_url_include` disabled at runtime.
- **PHAR signature verification**: Plugin integrity verified via PHAR signatures.
- **Password security**: bcrypt hashing via `PasswordHash` class.
- **Two-factor authentication**: Optional TOTP/HOTP for staff accounts.

---

## 9. Assumptions

The following assumptions were made based on repository analysis where the codebase
lacked explicit documentation:

1. **Single-tenant architecture**: The system is designed for single-tenant deployment. Multi-tenancy is achieved through separate installations, not row-level isolation.
2. **No built-in rate limiting**: Rate limiting is expected to be handled by the web server or reverse proxy, not the application.
3. **No API versioning**: The current API does not use versioned endpoints. This is noted as a future improvement area.
4. **No REST API for reading tickets**: The current API supports only ticket creation and cron execution. Reading, updating, and deleting tickets via API is not implemented in core (may be added via plugins).
5. **Custom test framework**: The project uses a custom test runner, not PHPUnit or similar frameworks.
6. **No CI/CD pipeline**: The repository does not include automated CI/CD configuration.
7. **Session-based auth only**: The staff and client portals use session-based authentication exclusively. JWT or stateless auth is not implemented for web interfaces.
8. **MySQL-only database support**: While the code uses a database abstraction layer, only MySQL/MariaDB is supported.
9. **Apache/IIS deployment**: The `.htaccess` file indicates Apache as the primary web server; IIS support is mentioned in documentation.
10. **No WebSocket or real-time features**: All interactions are request/response based. There is no real-time notification system (e.g., push notifications, WebSocket).
