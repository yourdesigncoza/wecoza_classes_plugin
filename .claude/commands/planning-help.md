# Planning Agent Help & Documentation

Get help and documentation for the Multi-Agent Planning System.

**Usage:** `/planning-help [topic]`

## Available Topics

- `overview` - System overview and workflow
- `agents` - Information about the 4 planning agents
- `commands` - List of all planning commands
- `workflow` - Step-by-step workflow guide
- `troubleshooting` - Common issues and solutions
- `examples` - Usage examples and test cases

## Default Help (No Topic)

```bash
echo "Multi-Agent Feature Planning System"
echo "=================================="
echo ""
echo "A structured 4-agent workflow for comprehensive feature planning:"
echo ""
echo "🔍 Agent 1: Requirements Analyzer - Analyzes feature against existing architecture"
echo "🏗️  Agent 2: Architecture Planner - Designs implementation strategy"
echo "🔗 Agent 3: Integration Specialist - Plans seamless integration"
echo "✨ Agent 4: DRY Enforcer - Ensures code quality and eliminates duplication"
echo ""
echo "Quick Start:"
echo "1. /planning-agent \"your feature description\""
echo "2. Follow guided workflow through each agent"
echo "3. Review and approve each agent's output"
echo "4. Generate final PRD and integrate with Task Master AI"
echo ""
echo "For detailed help: /planning-help [topic]"
echo ""
echo "Available topics: overview, agents, commands, workflow, troubleshooting, examples"
```

## Topic-Specific Help

```bash
TOPIC="$ARGUMENTS"

case "$TOPIC" in
  "overview")
    echo "System Overview"
    echo "=============="
    echo ""
    echo "The Multi-Agent Planning System provides a structured approach to feature"
    echo "development through 4 specialized AI agents working in sequence:"
    echo ""
    echo "1. Requirements Analyzer & Project Assessor"
    echo "   - Analyzes feature requests against existing architecture"
    echo "   - Identifies conflicts, dependencies, and integration points"
    echo "   - Assesses feasibility and complexity"
    echo ""
    echo "2. Code Architecture Planner"
    echo "   - Designs detailed implementation plans"
    echo "   - Creates modular, scalable code architecture"
    echo "   - Plans file structures and component interactions"
    echo ""
    echo "3. Integration Specialist"
    echo "   - Plans seamless feature implementation"
    echo "   - Handles compatibility and migration concerns"
    echo "   - Ensures proper error handling and testing"
    echo ""
    echo "4. DRY Principle Enforcer & Code Quality Guardian"
    echo "   - Eliminates code duplication opportunities"
    echo "   - Ensures adherence to best practices"
    echo "   - Optimizes for maintainability"
    echo ""
    echo "Key Features:"
    echo "- Structured workflow with approval gates"
    echo "- Integration with Task Master AI"
    echo "- Session management and recovery"
    echo "- Comprehensive documentation generation"
    ;;
    
  "agents")
    echo "Planning Agents Detailed Information"
    echo "==================================="
    echo ""
    echo "Agent 1: Requirements Analyzer"
    echo "-----------------------------"
    echo "Role: Analyzes feature requests against existing project architecture"
    echo "Output: requirements-analysis.md"
    echo "Focus: Feasibility, dependencies, risk assessment"
    echo ""
    echo "Agent 2: Architecture Planner"
    echo "----------------------------"
    echo "Role: Designs implementation strategies and code architecture"
    echo "Output: architecture-plan.md"
    echo "Focus: Modularity, scalability, component design"
    echo ""
    echo "Agent 3: Integration Specialist"
    echo "------------------------------"
    echo "Role: Plans seamless integration with existing systems"
    echo "Output: integration-strategy.md"
    echo "Focus: Compatibility, testing, error handling"
    echo ""
    echo "Agent 4: DRY Enforcer"
    echo "--------------------"
    echo "Role: Ensures code quality and eliminates duplication"
    echo "Output: quality-review.md"
    echo "Focus: Best practices, maintainability, optimization"
    echo ""
    echo "Each agent builds on the previous agent's work, creating a"
    echo "comprehensive planning document that translates directly"
    echo "into actionable Task Master AI tasks."
    ;;
    
  "commands")
    echo "Planning System Commands"
    echo "======================="
    echo ""
    echo "Core Commands:"
    echo "/planning-agent \"feature description\"     - Start new planning session"
    echo "/planning-finalize [SESSION_ID]           - Generate PRD and integrate with Task Master AI"
    echo ""
    echo "Session Management:"
    echo "/planning-session-manager list            - List all planning sessions"
    echo "/planning-session-manager resume [ID]     - Resume specific session"
    echo "/planning-session-manager status [ID]     - Check session status"
    echo "/planning-session-manager cleanup         - Clean up old sessions"
    echo ""
    echo "Approval Gates:"
    echo "/planning-approval review [FILE]          - Review agent output"
    echo "/planning-approval approve [ID] [AGENT]   - Approve agent work"
    echo "/planning-approval reject [ID] [AGENT]    - Reject with feedback"
    echo "/planning-approval status [ID]            - Check approval status"
    echo ""
    echo "Recovery & Maintenance:"
    echo "/planning-recovery diagnose [ID]          - Diagnose session issues"
    echo "/planning-recovery repair [ID]            - Repair corrupted session"
    echo "/planning-recovery backup [ID]            - Create session backup"
    echo "/planning-recovery restore [ID] [BACKUP] - Restore from backup"
    echo ""
    echo "Help & Documentation:"
    echo "/planning-help [topic]                    - This help system"
    ;;
    
  "workflow")
    echo "Planning Workflow Guide"
    echo "======================"
    echo ""
    echo "Step 1: Initialize Planning Session"
    echo "-----------------------------------"
    echo "Command: /planning-agent \"your feature description\""
    echo "- Creates timestamped session directory"
    echo "- Sets up approval system"
    echo "- Prepares for Agent 1"
    echo ""
    echo "Step 2: Agent 1 - Requirements Analysis"
    echo "--------------------------------------"
    echo "- Analyze feature against existing architecture"
    echo "- Create requirements-analysis.md"
    echo "- Review output: /planning-approval review [FILE]"
    echo "- Approve: /planning-approval approve [SESSION_ID] 1"
    echo ""
    echo "Step 3: Agent 2 - Architecture Planning"
    echo "--------------------------------------"
    echo "- Design implementation strategy"
    echo "- Create architecture-plan.md"
    echo "- Review and approve before proceeding"
    echo ""
    echo "Step 4: Agent 3 - Integration Strategy"
    echo "-------------------------------------"
    echo "- Plan integration approach"
    echo "- Create integration-strategy.md"
    echo "- Review and approve before proceeding"
    echo ""
    echo "Step 5: Agent 4 - Quality Review"
    echo "--------------------------------"
    echo "- Review all previous work for quality"
    echo "- Create quality-review.md"
    echo "- Review and approve"
    echo ""
    echo "Step 6: Finalization"
    echo "-------------------"
    echo "Command: /planning-finalize [SESSION_ID]"
    echo "- Combines all agent outputs into comprehensive PRD"
    echo "- Integrates with Task Master AI using parse-prd"
    echo "- Creates structured implementation tasks"
    echo ""
    echo "Quality Gates:"
    echo "- Each agent requires approval before next agent begins"
    echo "- Rejection provides feedback for revision"
    echo "- Audit trail maintained throughout process"
    ;;
    
  "troubleshooting")
    echo "Troubleshooting Guide"
    echo "===================="
    echo ""
    echo "Common Issues and Solutions:"
    echo ""
    echo "Issue: Session not found"
    echo "Solution: Check session ID with /planning-session-manager list"
    echo ""
    echo "Issue: Agent output appears incomplete"
    echo "Solution: Use /planning-recovery diagnose [SESSION_ID]"
    echo ""
    echo "Issue: Approval system not working"
    echo "Solution: Use /planning-recovery repair [SESSION_ID]"
    echo ""
    echo "Issue: Task Master AI integration fails"
    echo "Solution: Check task-master configuration and API keys"
    echo ""
    echo "Issue: Corrupted session files"
    echo "Solution:"
    echo "1. /planning-recovery backup [SESSION_ID]"
    echo "2. /planning-recovery repair [SESSION_ID]"
    echo "3. Regenerate corrupted agent outputs"
    echo ""
    echo "Issue: Cannot resume interrupted session"
    echo "Solution:"
    echo "1. /planning-session-manager status [SESSION_ID]"
    echo "2. /planning-session-manager resume [SESSION_ID]"
    echo "3. Continue from last completed agent"
    echo ""
    echo "Prevention Tips:"
    echo "- Complete agents in sequence"
    echo "- Don't manually edit session files"
    echo "- Use planning commands exclusively"
    echo "- Regular backups for important sessions"
    echo ""
    echo "For complex issues:"
    echo "/planning-recovery diagnose [SESSION_ID] - Comprehensive analysis"
    ;;
    
  "examples")
    echo "Usage Examples"
    echo "============="
    echo ""
    echo "Example 1: User Authentication System"
    echo "------------------------------------"
    echo "/planning-agent \"Add comprehensive user authentication with JWT tokens\""
    echo ""
    echo "Example 2: Real-time Notifications"
    echo "---------------------------------"
    echo "/planning-agent \"Implement real-time notifications for class updates\""
    echo ""
    echo "Example 3: Data Export Feature"
    echo "-----------------------------"
    echo "/planning-agent \"Create automated backup and export system for class data\""
    echo ""
    echo "Complete Workflow Example:"
    echo "-------------------------"
    echo "# Start planning"
    echo "/planning-agent \"Add user dashboard with analytics\""
    echo ""
    echo "# Check session status"
    echo "/planning-session-manager status 20240710_143022_user-dashboard"
    echo ""
    echo "# Review Agent 1 output"
    echo "/planning-approval review .taskmaster/docs/planning/sessions/20240710_143022_user-dashboard/requirements-analysis.md"
    echo ""
    echo "# Approve Agent 1"
    echo "/planning-approval approve 20240710_143022_user-dashboard 1"
    echo ""
    echo "# Continue through all agents..."
    echo ""
    echo "# Finalize when all agents complete"
    echo "/planning-finalize 20240710_143022_user-dashboard"
    echo ""
    echo "# Verify Task Master AI integration"
    echo "task-master list"
    echo ""
    echo "Test Case:"
    echo "---------"
    echo "See: .taskmaster/docs/planning/example-test-session.md"
    echo "Contains comprehensive test scenario with validation checklist"
    ;;
    
  *)
    echo "Unknown help topic: $TOPIC"
    echo ""
    echo "Available topics:"
    echo "- overview     : System overview and workflow"
    echo "- agents       : Information about the 4 planning agents"
    echo "- commands     : List of all planning commands"
    echo "- workflow     : Step-by-step workflow guide"
    echo "- troubleshooting : Common issues and solutions"
    echo "- examples     : Usage examples and test cases"
    echo ""
    echo "Usage: /planning-help [topic]"
    ;;
esac
```

## Quick Reference

### Essential Commands
- `/planning-agent "feature"` - Start planning
- `/planning-finalize [ID]` - Complete planning
- `/planning-help workflow` - Detailed workflow guide

### Session Management
- `/planning-session-manager list` - See all sessions
- `/planning-session-manager resume [ID]` - Continue session

### Troubleshooting
- `/planning-recovery diagnose [ID]` - Find problems
- `/planning-help troubleshooting` - Solutions guide

This help system provides comprehensive documentation for all aspects of the multi-agent planning system, making it easy for users to understand and effectively use the workflow.