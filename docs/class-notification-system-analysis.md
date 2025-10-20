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

## Database Schema Reference

### Notification Queue Structure
```sql
CREATE TABLE wecoza_events.notification_queue (
    id integer NOT NULL,
    event_name character varying(100) NOT NULL,
    recipient_email character varying(255) NOT NULL,
    channel character varying(50) DEFAULT 'email'::character varying,
    template_name character varying(100),
    payload jsonb,
    scheduled_at timestamp with time zone DEFAULT now(),
    status character varying(20) DEFAULT 'pending'::character varying,
    attempts integer DEFAULT 0,
    max_attempts integer DEFAULT 3,
    sent_at timestamp with time zone,
    created_at timestamp with time zone DEFAULT now(),
    updated_at timestamp with time zone DEFAULT now(),
    idempotency_key character varying(255)
);
```

### Supervisors Structure
```sql
CREATE TABLE wecoza_events.supervisors (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    department character varying(100),
    is_active boolean DEFAULT true,
    notification_preferences jsonb DEFAULT '{}'::jsonb,
    created_at timestamp with time zone DEFAULT now(),
    updated_at timestamp with time zone DEFAULT now()
);
```

## Current Class Creation Flow

### Existing Flow
1. User fills out class creation form
2. `saveClassAjax()` method processes form data
3. ClassModel saves class to database
4. `log_class_change()` trigger fires (logs to database, sends pg_notify)
5. User receives success message
6. No email notifications sent

### Gaps Identified
- Step 4: Database change logging exists but no human-readable notifications
- Step 5: Success message only visible to creating user
- No notifications sent to supervisors, agents, or other stakeholders

## Recommended Implementation Approach

### Phase 1: Basic Class Creation Notifications
1. **Create NotificationService class**
   - Location: `app/Services/NotificationService.php`
   - Methods: `queueClassCreationNotification()`, `getNotificationRecipients()`
   
2. **Integrate with ClassController**
   - Modify `saveClassAjax()` to trigger notifications after successful save
   - Add notification for new classes only (not updates initially)
   
3. **Implement basic email templates**
   - Location: `resources/email-templates/class-created.html`
   - Include essential class details: client, subject, start date, agent
   
4. **Send to project supervisors**
   - Query supervisors based on `project_supervisor_id` assignment
   - Send immediate notifications

### Phase 2: Enhanced Notifications
1. **Agent notifications**
   - Notify assigned class agents about new assignments
   - Include class schedule and contact information
   
2. **System administrator notifications**
   - Send overview notifications to all administrators
   - Include daily/weekly summary options
   
3. **Rich email templates**
   - Professional HTML templates with branding
   - Dynamic content blocks
   - Responsive design for mobile devices

### Phase 3: Status-Based Notifications
1. **Draft→Active transition notifications**
   - Trigger when `order_nr` field is populated
   - Send to class supervisor and agent
   
2. **Class update notifications**
   - Notify relevant parties of class changes
   - Configure what changes trigger notifications
   
3. **Customizable notification preferences**
   - Allow users to opt-in/out of specific notifications
   - Per-class notification settings

## Technical Implementation Details

### NotificationService Class Structure
```php
<?php
namespace WeCozaClasses\Services;

class NotificationService {
    /**
     * Queue notification for class creation
     */
    public static function queueClassCreationNotification($classId, $recipients = []) {
        // Implementation details
    }
    
    /**
     * Queue notification for class status change
     */
    public static function queueClassStatusChangeNotification($classId, $oldStatus, $newStatus) {
        // Implementation details
    }
    
    /**
     * Get notification recipients based on class and event type
     */
    public static function getNotificationRecipients($class, $eventType) {
        // Implementation details
    }
    
    /**
     * Process notification queue
     */
    public static function sendNotifications() {
        // Implementation details
    }
}
```

### Integration Points

#### ClassController Integration
```php
// In saveClassAjax() method after successful save:
if (!$isUpdate) {
    NotificationService::queueClassCreationNotification($class->getId());
}
```

#### Draft/Active Status Integration
```php
// In ClassModel setter for order_nr:
public function setOrderNr($order_nr) {
    $wasDraft = $this->isDraft();
    $this->order_nr = is_string($order_nr) ? $order_nr : null;
    
    // Trigger notification if transitioning from Draft to Active
    if ($wasDraft && $this->isActive()) {
        NotificationService::queueClassStatusChangeNotification(
            $this->getId(), 
            'Draft', 
            'Active'
        );
    }
    
    return $this;
}
```

### Email Template Structure
```html
<!-- resources/email-templates/class-created.html -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Class Created - {{client_name}}</title>
</head>
<body>
    <h2>New Class Created</h2>
    <p>A new class has been created with the following details:</p>
    
    <table>
        <tr><td><strong>Client:</strong></td><td>{{client_name}}</td></tr>
        <tr><td><strong>Class Subject:</strong></td><td>{{class_subject}}</td></tr>
        <tr><td><strong>Start Date:</strong></td><td>{{start_date}}</td></tr>
        <tr><td><strong>Assigned Agent:</strong></td><td>{{agent_name}}</td></tr>
        <tr><td><strong>Project Supervisor:</strong></td><td>{{supervisor_name}}</td></tr>
        <tr><td><strong>Status:</strong></td><td>{{status}}</td></tr>
    </table>
    
    <p><a href="{{class_url}}">View Class Details</a></p>
</body>
</html>
```

## Configuration Options

### Plugin Settings
- **Enable notifications**: Toggle on/off for entire system
- **Default recipients**: Choose which roles receive notifications
- **Email customization**: Subject line customization, template selection
- **Batch processing**: Process notifications in batches vs immediate delivery

### Notification Events
- `class_draft_created`: When new draft class is created
- `class_activated`: When draft class becomes active
- `class_updated`: When class details are changed
- `class_deleted`: When class is deleted

## Benefits of Implementation

### 1. Immediate Awareness
- Supervisors instantly know about new class assignments
- Agents receive immediate notification of new responsibilities
- Reduces communication delays and manual follow-ups

### 2. Better Coordination
- Clear communication channels for class management
- All stakeholders stay informed about class changes
- Supports distributed team coordination

### 3. Workflow Automation
- Reduces manual communication overhead
- Automatic notifications reduce human error
- Supports scalable class management

### 4. Enhanced Audit Trail
- Complements existing database change logging
- Human-readable notifications supplement technical logs
- Provides accountability and transparency

### 5. Improved Client Service
- Optional client notifications improve service quality
- Proactive communication about class scheduling
- Supports client relationship management

## Testing Strategy

### Unit Tests
- Test NotificationService methods
- Verify notification queueing logic
- Test recipient determination algorithms

### Integration Tests
- Verify notification queuing and delivery
- Test email template rendering
- Validate database integration

### End-to-End Tests
- Test complete notification flow from class creation to email delivery
- Verify notifications reach correct recipients
- Test notification templates with actual class data

## Rollout Plan

### Phase 1: Foundation (Weeks 1-2)
- Implement basic NotificationService
- Create simple email templates
- Integrate with class creation
- Test with supervisor notifications only

### Phase 2: Enhancement (Weeks 3-4)
- Add agent notifications
- Implement rich email templates
- Add administrator notifications
- Test recipient selection logic

### Phase 3: Advanced Features (Weeks 5-6)
- Implement status change notifications
- Add customizable notification preferences
- Create notification management interface
- Performance optimization and batch processing

### Phase 4: Polish & Launch (Weeks 7-8)
- Comprehensive testing and bug fixes
- Documentation and user guides
- Monitoring and analytics
- Production rollout

## Success Metrics

### Engagement Metrics
- Notification open rates
- Click-through rates on class detail links
- User feedback on notification usefulness

### Operational Metrics
- Time from class creation to supervisor awareness
- Reduction in manual communication overhead
- Notification delivery success rates

### User Satisfaction
- Supervisor satisfaction scores
- Agent feedback on notification usefulness
- Client satisfaction with communication quality

---

*This analysis provides a comprehensive foundation for implementing class creation notifications in the WeCoza Classes Plugin, leveraging existing infrastructure while addressing current gaps in stakeholder communication.*
