# Planning Session Manager

Manage and resume planning agent sessions.

**Usage:** 
- `/planning-session-manager list` - List all planning sessions
- `/planning-session-manager resume [SESSION_ID]` - Resume a specific session
- `/planning-session-manager status [SESSION_ID]` - Check session status
- `/planning-session-manager cleanup` - Clean up old/incomplete sessions

## Commands

### List Sessions
```bash
find .taskmaster/docs/planning/sessions -name "*" -type d | sort -r | head -10
```

Shows the 10 most recent planning sessions.

### Resume Session
```bash
SESSION_DIR=".taskmaster/docs/planning/sessions/$ARGUMENTS"
if [ -d "$SESSION_DIR" ]; then
  echo "Resuming planning session: $ARGUMENTS"
  cd "$SESSION_DIR"
  
  # Check which agents have completed
  echo "Session Status:"
  [ -f "requirements-analysis.md" ] && echo "✅ Agent 1: Requirements Analysis" || echo "❌ Agent 1: Requirements Analysis"
  [ -f "architecture-plan.md" ] && echo "✅ Agent 2: Architecture Planning" || echo "❌ Agent 2: Architecture Planning"
  [ -f "integration-strategy.md" ] && echo "✅ Agent 3: Integration Strategy" || echo "❌ Agent 3: Integration Strategy"
  [ -f "quality-review.md" ] && echo "✅ Agent 4: Quality Review" || echo "❌ Agent 4: Quality Review"
  [ -f "final-prd.md" ] && echo "✅ Final PRD Generated" || echo "❌ Final PRD Generated"
  
  # Determine next step
  if [ ! -f "requirements-analysis.md" ]; then
    echo ""
    echo "Next Step: Run Agent 1 (Requirements Analysis)"
    echo "Use the Requirements Analyzer template from agent-templates.md"
  elif [ ! -f "architecture-plan.md" ]; then
    echo ""
    echo "Next Step: Run Agent 2 (Architecture Planning)"
    echo "Review requirements-analysis.md then use Architecture Planner template"
  elif [ ! -f "integration-strategy.md" ]; then
    echo ""
    echo "Next Step: Run Agent 3 (Integration Strategy)"
    echo "Review previous outputs then use Integration Specialist template"
  elif [ ! -f "quality-review.md" ]; then
    echo ""
    echo "Next Step: Run Agent 4 (Quality Review)"
    echo "Review all previous outputs then use DRY Enforcer template"
  elif [ ! -f "final-prd.md" ]; then
    echo ""
    echo "Next Step: Generate Final PRD and integrate with Task Master AI"
  else
    echo ""
    echo "✅ Session Complete! All agents have finished and PRD generated."
  fi
else
  echo "Session not found: $ARGUMENTS"
  echo "Available sessions:"
  find .taskmaster/docs/planning/sessions -name "*" -type d -exec basename {} \;
fi
```

### Check Status
```bash
SESSION_DIR=".taskmaster/docs/planning/sessions/$ARGUMENTS"
if [ -d "$SESSION_DIR" ]; then
  echo "Planning Session: $ARGUMENTS"
  echo "Location: $SESSION_DIR"
  echo ""
  
  # Show completion status
  echo "Agent Status:"
  if [ -f "$SESSION_DIR/requirements-analysis.md" ]; then
    echo "✅ Agent 1: Requirements Analysis - $(stat -c %y "$SESSION_DIR/requirements-analysis.md" | cut -d' ' -f1)"
  else
    echo "❌ Agent 1: Requirements Analysis - Not completed"
  fi
  
  if [ -f "$SESSION_DIR/architecture-plan.md" ]; then
    echo "✅ Agent 2: Architecture Planning - $(stat -c %y "$SESSION_DIR/architecture-plan.md" | cut -d' ' -f1)"
  else
    echo "❌ Agent 2: Architecture Planning - Not completed"
  fi
  
  if [ -f "$SESSION_DIR/integration-strategy.md" ]; then
    echo "✅ Agent 3: Integration Strategy - $(stat -c %y "$SESSION_DIR/integration-strategy.md" | cut -d' ' -f1)"
  else
    echo "❌ Agent 3: Integration Strategy - Not completed"
  fi
  
  if [ -f "$SESSION_DIR/quality-review.md" ]; then
    echo "✅ Agent 4: Quality Review - $(stat -c %y "$SESSION_DIR/quality-review.md" | cut -d' ' -f1)"
  else
    echo "❌ Agent 4: Quality Review - Not completed"
  fi
  
  if [ -f "$SESSION_DIR/final-prd.md" ]; then
    echo "✅ Final PRD Generated - $(stat -c %y "$SESSION_DIR/final-prd.md" | cut -d' ' -f1)"
  else
    echo "❌ Final PRD - Not generated"
  fi
  
  echo ""
  echo "Files in session:"
  ls -la "$SESSION_DIR/"
else
  echo "Session not found: $ARGUMENTS"
fi
```

### Cleanup Sessions
```bash
echo "Finding incomplete planning sessions older than 7 days..."

find .taskmaster/docs/planning/sessions -type d -mtime +7 | while read session_dir; do
  if [ ! -f "$session_dir/final-prd.md" ]; then
    echo "Incomplete session: $(basename "$session_dir")"
    echo "  Location: $session_dir"
    echo "  Last modified: $(stat -c %y "$session_dir")"
    echo "  Files: $(ls "$session_dir" 2>/dev/null | wc -l) files"
    echo ""
  fi
done

echo "To remove old incomplete sessions, run:"
echo "find .taskmaster/docs/planning/sessions -type d -mtime +7 -exec rm -rf {} \;"
echo ""
echo "⚠️  This will permanently delete old incomplete planning sessions."
echo "Review the list above before running the cleanup command."
```

## Session Structure

Each planning session follows this structure:
```
.taskmaster/docs/planning/sessions/YYYYMMDD_HHMMSS_feature-name/
├── requirements-analysis.md      # Agent 1 output
├── architecture-plan.md          # Agent 2 output  
├── integration-strategy.md       # Agent 3 output
├── quality-review.md            # Agent 4 output
├── final-prd.md                 # Combined PRD for Task Master AI
└── session-metadata.json       # Session tracking info
```

## Integration with Planning Agent

The main `/planning-agent` command automatically:
1. Creates a new session directory with timestamp
2. Guides through each agent phase
3. Requires user approval between phases
4. Generates final PRD and integrates with Task Master AI

This session manager allows you to:
- Resume interrupted sessions
- Check progress on ongoing sessions
- Clean up old incomplete sessions
- Track session history

## Best Practices

1. **Complete sessions in order** - Don't skip agent phases
2. **Review outputs** - Carefully review each agent's output before approving
3. **Resume promptly** - Don't leave sessions incomplete for too long
4. **Clean up regularly** - Remove old incomplete sessions to avoid clutter
5. **Use descriptive names** - Use clear, descriptive feature names for better organization