# Planning Finalize - Task Master AI Integration

Generate final PRD and integrate with Task Master AI system.

**Usage:** `/planning-finalize [SESSION_ID]`

## Process

```bash
SESSION_ID="$ARGUMENTS"
SESSION_DIR=".taskmaster/docs/planning/sessions/$SESSION_ID"

if [ ! -d "$SESSION_DIR" ]; then
  echo "Session not found: $SESSION_ID"
  echo "Available sessions:"
  find .taskmaster/docs/planning/sessions -name "*" -type d -exec basename {} \;
  exit 1
fi

echo "Finalizing planning session: $SESSION_ID"
echo "=========================================="

# Verify all agents have completed
MISSING_FILES=""
[ ! -f "$SESSION_DIR/requirements-analysis.md" ] && MISSING_FILES="$MISSING_FILES requirements-analysis.md"
[ ! -f "$SESSION_DIR/architecture-plan.md" ] && MISSING_FILES="$MISSING_FILES architecture-plan.md" 
[ ! -f "$SESSION_DIR/integration-strategy.md" ] && MISSING_FILES="$MISSING_FILES integration-strategy.md"
[ ! -f "$SESSION_DIR/quality-review.md" ] && MISSING_FILES="$MISSING_FILES quality-review.md"

if [ ! -z "$MISSING_FILES" ]; then
  echo "❌ Cannot finalize - missing agent outputs:"
  for file in $MISSING_FILES; do
    echo "   - $file"
  done
  echo ""
  echo "Complete all agent phases before finalizing."
  exit 1
fi

# Verify all agents are approved
MISSING_APPROVALS=""
[ ! -f "$SESSION_DIR/.approvals/agent-1-approved" ] && MISSING_APPROVALS="$MISSING_APPROVALS Agent-1"
[ ! -f "$SESSION_DIR/.approvals/agent-2-approved" ] && MISSING_APPROVALS="$MISSING_APPROVALS Agent-2"
[ ! -f "$SESSION_DIR/.approvals/agent-3-approved" ] && MISSING_APPROVALS="$MISSING_APPROVALS Agent-3"
[ ! -f "$SESSION_DIR/.approvals/agent-4-approved" ] && MISSING_APPROVALS="$MISSING_APPROVALS Agent-4"

if [ ! -z "$MISSING_APPROVALS" ]; then
  echo "⚠️  Warning - not all agents are approved:"
  for agent in $MISSING_APPROVALS; do
    echo "   - $agent"
  done
  echo ""
  echo "Proceeding anyway - you can approve them later if needed."
fi

echo "✅ All required agent outputs found"
echo ""

# Extract feature name from session directory
FEATURE_NAME=$(basename "$SESSION_DIR" | sed 's/^[0-9]*_[0-9]*_//')

echo "Generating final PRD for: $FEATURE_NAME"
echo "=========================================="

# Generate comprehensive PRD by combining all agent outputs
cat > "$SESSION_DIR/final-prd.md" << EOF
# Feature Implementation Plan: $FEATURE_NAME

Generated from multi-agent planning session: $SESSION_ID
Generated on: $(date "+%Y-%m-%d %H:%M:%S")

## Executive Summary

This document outlines the comprehensive implementation plan for the "$FEATURE_NAME" feature, developed through a structured 4-agent analysis process covering requirements analysis, architecture planning, integration strategy, and quality review.

## Requirements Analysis

EOF

# Extract key sections from requirements analysis
if [ -f "$SESSION_DIR/requirements-analysis.md" ]; then
  echo "### Feature Summary" >> "$SESSION_DIR/final-prd.md"
  sed -n '/## Feature Summary/,/## Impact Assessment/p' "$SESSION_DIR/requirements-analysis.md" | head -n -1 >> "$SESSION_DIR/final-prd.md"
  echo "" >> "$SESSION_DIR/final-prd.md"
  
  echo "### Impact Assessment" >> "$SESSION_DIR/final-prd.md"
  sed -n '/## Impact Assessment/,/## Risk Evaluation/p' "$SESSION_DIR/requirements-analysis.md" | head -n -1 >> "$SESSION_DIR/final-prd.md"
  echo "" >> "$SESSION_DIR/final-prd.md"
  
  echo "### Dependencies & Prerequisites" >> "$SESSION_DIR/final-prd.md"
  sed -n '/## Prerequisites/,/## /p' "$SESSION_DIR/requirements-analysis.md" | head -n -1 >> "$SESSION_DIR/final-prd.md"
  echo "" >> "$SESSION_DIR/final-prd.md"
fi

echo "## Architecture & Design" >> "$SESSION_DIR/final-prd.md"
echo "" >> "$SESSION_DIR/final-prd.md"

# Extract key sections from architecture plan
if [ -f "$SESSION_DIR/architecture-plan.md" ]; then
  echo "### Component Architecture" >> "$SESSION_DIR/final-prd.md"
  sed -n '/## High-Level Architecture/,/## File Structure Plan/p' "$SESSION_DIR/architecture-plan.md" | head -n -1 >> "$SESSION_DIR/final-prd.md"
  echo "" >> "$SESSION_DIR/final-prd.md"
  
  echo "### File Structure" >> "$SESSION_DIR/final-prd.md"
  sed -n '/## File Structure Plan/,/## Component Interactions/p' "$SESSION_DIR/architecture-plan.md" | head -n -1 >> "$SESSION_DIR/final-prd.md"
  echo "" >> "$SESSION_DIR/final-prd.md"
  
  echo "### Implementation Sequence" >> "$SESSION_DIR/final-prd.md"
  sed -n '/## Implementation Sequence/,/## Dependencies/p' "$SESSION_DIR/architecture-plan.md" | head -n -1 >> "$SESSION_DIR/final-prd.md"
  echo "" >> "$SESSION_DIR/final-prd.md"
fi

echo "## Integration Strategy" >> "$SESSION_DIR/final-prd.md"
echo "" >> "$SESSION_DIR/final-prd.md"

# Extract key sections from integration strategy
if [ -f "$SESSION_DIR/integration-strategy.md" ]; then
  echo "### Implementation Approach" >> "$SESSION_DIR/final-prd.md"
  sed -n '/## Implementation Approach/,/## Compatibility Strategy/p' "$SESSION_DIR/integration-strategy.md" | head -n -1 >> "$SESSION_DIR/final-prd.md"
  echo "" >> "$SESSION_DIR/final-prd.md"
  
  echo "### Testing & Validation" >> "$SESSION_DIR/final-prd.md"
  sed -n '/## Testing Strategy/,/## Migration Procedures/p' "$SESSION_DIR/integration-strategy.md" | head -n -1 >> "$SESSION_DIR/final-prd.md"
  echo "" >> "$SESSION_DIR/final-prd.md"
fi

echo "## Quality Standards & Best Practices" >> "$SESSION_DIR/final-prd.md"
echo "" >> "$SESSION_DIR/final-prd.md"

# Extract key sections from quality review
if [ -f "$SESSION_DIR/quality-review.md" ]; then
  echo "### Code Quality Requirements" >> "$SESSION_DIR/final-prd.md"
  sed -n '/## Best Practices Compliance/,/## Code Quality Metrics/p' "$SESSION_DIR/quality-review.md" | head -n -1 >> "$SESSION_DIR/final-prd.md"
  echo "" >> "$SESSION_DIR/final-prd.md"
  
  echo "### Refactoring & Optimization" >> "$SESSION_DIR/final-prd.md"
  sed -n '/## Refactoring Recommendations/,/## /p' "$SESSION_DIR/quality-review.md" | head -n -1 >> "$SESSION_DIR/final-prd.md"
  echo "" >> "$SESSION_DIR/final-prd.md"
fi

# Add implementation tasks section
echo "## Implementation Tasks" >> "$SESSION_DIR/final-prd.md"
echo "" >> "$SESSION_DIR/final-prd.md"
echo "The following tasks should be created in Task Master AI for systematic implementation:" >> "$SESSION_DIR/final-prd.md"
echo "" >> "$SESSION_DIR/final-prd.md"

# Generate specific implementation tasks based on the planning
echo "### Phase 1: Foundation & Setup" >> "$SESSION_DIR/final-prd.md"
echo "- Set up project structure and base files" >> "$SESSION_DIR/final-prd.md"
echo "- Implement core interfaces and contracts" >> "$SESSION_DIR/final-prd.md"
echo "- Create shared utilities and helper functions" >> "$SESSION_DIR/final-prd.md"
echo "" >> "$SESSION_DIR/final-prd.md"

echo "### Phase 2: Core Implementation" >> "$SESSION_DIR/final-prd.md"
echo "- Implement main feature components" >> "$SESSION_DIR/final-prd.md"
echo "- Add data layer and persistence logic" >> "$SESSION_DIR/final-prd.md"
echo "- Create business logic and service classes" >> "$SESSION_DIR/final-prd.md"
echo "" >> "$SESSION_DIR/final-prd.md"

echo "### Phase 3: Integration & Testing" >> "$SESSION_DIR/final-prd.md"
echo "- Integrate with existing systems" >> "$SESSION_DIR/final-prd.md"
echo "- Implement error handling and logging" >> "$SESSION_DIR/final-prd.md"
echo "- Add comprehensive test coverage" >> "$SESSION_DIR/final-prd.md"
echo "- Perform integration testing" >> "$SESSION_DIR/final-prd.md"
echo "" >> "$SESSION_DIR/final-prd.md"

echo "### Phase 4: Quality & Optimization" >> "$SESSION_DIR/final-prd.md"
echo "- Code review and DRY principle enforcement" >> "$SESSION_DIR/final-prd.md"
echo "- Performance optimization" >> "$SESSION_DIR/final-prd.md"
echo "- Documentation and user guides" >> "$SESSION_DIR/final-prd.md"
echo "- Final testing and validation" >> "$SESSION_DIR/final-prd.md"
echo "" >> "$SESSION_DIR/final-prd.md"

echo "## Success Criteria" >> "$SESSION_DIR/final-prd.md"
echo "" >> "$SESSION_DIR/final-prd.md"
echo "- [ ] All planned components implemented and tested" >> "$SESSION_DIR/final-prd.md"
echo "- [ ] Integration with existing systems verified" >> "$SESSION_DIR/final-prd.md"
echo "- [ ] Performance requirements met" >> "$SESSION_DIR/final-prd.md"
echo "- [ ] Code quality standards maintained" >> "$SESSION_DIR/final-prd.md"
echo "- [ ] Documentation complete" >> "$SESSION_DIR/final-prd.md"
echo "- [ ] User acceptance testing passed" >> "$SESSION_DIR/final-prd.md"
echo "" >> "$SESSION_DIR/final-prd.md"

echo "---" >> "$SESSION_DIR/final-prd.md"
echo "Generated by Claude Code Multi-Agent Planning System" >> "$SESSION_DIR/final-prd.md"
echo "Session: $SESSION_ID" >> "$SESSION_DIR/final-prd.md"

echo "✅ Final PRD generated: $SESSION_DIR/final-prd.md"
echo ""

# Now integrate with Task Master AI
echo "Integrating with Task Master AI..."
echo "=================================="

# Change to project root for Task Master AI commands
cd /opt/lampp/htdocs/wecoza/wp-content/plugins/wecoza-classes-plugin

# Use Task Master AI to parse the PRD and create tasks
echo "Running: task-master parse-prd \"$SESSION_DIR/final-prd.md\" --research --append"
task-master parse-prd "$SESSION_DIR/final-prd.md" --research --append

if [ $? -eq 0 ]; then
  echo ""
  echo "✅ Successfully integrated with Task Master AI"
  echo "📋 Tasks created from planning session"
  echo ""
  echo "Next steps:"
  echo "1. Review generated tasks: task-master list"
  echo "2. Start implementation: task-master next"
  echo "3. Track progress with Task Master AI workflow"
  echo ""
  echo "Planning session complete: $SESSION_ID"
  
  # Create completion marker
  touch "$SESSION_DIR/.planning-complete"
  echo "$(date "+%Y-%m-%d %H:%M:%S") - Planning session completed and integrated with Task Master AI" >> "$SESSION_DIR/.approvals/approval-log.txt"
else
  echo "❌ Error integrating with Task Master AI"
  echo "You can manually run: task-master parse-prd \"$SESSION_DIR/final-prd.md\" --research --append"
fi
```

## Summary

This command:

1. **Validates completion** - Ensures all 4 agents have produced outputs
2. **Checks approvals** - Verifies quality gates (warnings if not approved)
3. **Generates comprehensive PRD** - Combines all agent outputs into structured document
4. **Creates implementation tasks** - Breaks down work into phases
5. **Integrates with Task Master AI** - Uses `parse-prd` to create structured tasks
6. **Tracks completion** - Marks session as complete

## Integration Features

### Automatic PRD Generation
- Extracts key sections from each agent's output
- Creates structured, comprehensive requirements document  
- Includes implementation tasks and success criteria
- Maintains traceability to original planning session

### Task Master AI Integration
- Uses `parse-prd` with `--research` flag for enhanced task generation
- Appends to existing task list without overwriting
- Creates structured tasks based on comprehensive planning
- Maintains integration with existing Task Master AI workflow

### Quality Assurance
- Validates all agents completed their work
- Checks approval status (warns if not approved)
- Creates audit trail of completion
- Maintains session integrity

This finalization process ensures that the comprehensive multi-agent planning translates directly into actionable tasks within your existing Task Master AI workflow, maintaining the DRY principles and quality standards established throughout the planning process.