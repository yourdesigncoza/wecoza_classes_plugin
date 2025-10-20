# Class Creation Notification System Analysis

## Current State Analysis

### Existing Notification Infrastructure
✅ **Complete notification system exists** in the `wecoza_events` schema:
- **`notification_queue` table** - Queues outgoing notifications (email, dashboard, etc.)
- **Supervisors table** - Stores supervisors who receive notifications
- **Email template system** - Supports notification templates
- **Multiple channels** - Email, dashboard notifications supported
- **Scheduling system** - Notifications can be scheduled for delivery

### Database Triggers & Events
✅ **Class change logging system** exists:
- **`log_class_change()` trigger** - Fires on INSERT/UPDATE/DELETE to classes table
- **`pg_notify()` calls** - Sends real-time notifications via PostgreSQL channels
- **`class_change_logs` table** - Tracks all class changes with diffs

### Current Gaps
❌ **No class creation notifications** currently implemented:
- ClassController doesn't send notifications when creating classes
- No notification events triggered for new class creation
- No email alerts sent to supervisors/admins when classes are created

## Key Findings

### 1. Infrastructure Ready
The system has a complete notification infrastructure:
- Database tables for queueing notifications (`notification_queue`)
- Supervisor management system
- Email template support
- Real-time notification via PostgreSQL channels

### 2. Missing Integration
While the infrastructure exists, there's no integration between class creation and the notification system:
- `saveClassAjax()` method saves classes but doesn't trigger notifications
- No event system for class creation
- No email templates for class creation notifications

### 3. Draft/Active Status Impact
With the recent draft/active status implementation:
- Draft classes should trigger "Class Draft Created" notifications
- When classes become active (order number assigned), "Class Activated" notifications should be sent
- This creates additional notification opportunities

## Recommended Implementation Approach

### Phase 1: Basic Class Creation Notifications
1. Create `NotificationService` class
2. Integrate with `saveClassAjax()` method
3. Send notifications to project supervisors
4. Create basic email templates

### Phase 2: Enhanced Notifications
1. Add agent notifications
2. Client contact notifications (optional)
3. System administrator notifications
4. Rich email templates with class details

### Phase 3: Status-Based Notifications
1. Draft→Active transition notifications
2. Class update notifications
3. Class deletion notifications
4. Customizable notification preferences

## Technical Requirements

### Notification Service
- Queue notifications in `notification_queue` table
- Support multiple recipients and channels
- Handle email template rendering
- Schedule notifications for immediate or batch delivery

### Email Templates
- Professional template design
- Dynamic content with class details
- HTML and plain text versions
- Customizable subject lines

### Recipient Management
- Project supervisors (based on assignment)
- System administrators
- Class agents
- Client contacts (optional)

## Benefits
1. **Improved Communication**: Immediate notification of class assignments
2. **Better Coordination**: Supervisors and agents stay informed
3. **Workflow Automation**: Reduces manual communication overhead
4. **Audit Trail**: Complements existing change logging system
5. **Client Service**: Optional client notifications improve service quality

This analysis will be saved as `class-notification-system-analysis.md` in the docs folder.