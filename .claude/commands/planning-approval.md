# Planning Approval Gate

Manage approval gates between planning agent phases.

**Usage:** 
- `/planning-approval review [AGENT_OUTPUT_FILE]` - Review agent output and approve/reject
- `/planning-approval approve [SESSION_ID] [AGENT_NUMBER]` - Approve agent output
- `/planning-approval reject [SESSION_ID] [AGENT_NUMBER] [REASON]` - Reject with feedback
- `/planning-approval status [SESSION_ID]` - Check approval status

## Review Agent Output

```bash
AGENT_FILE="$ARGUMENTS"
if [ -f "$AGENT_FILE" ]; then
  echo "=========================================="
  echo "REVIEWING: $(basename "$AGENT_FILE")"
  echo "=========================================="
  echo ""
  
  # Display the agent output
  cat "$AGENT_FILE"
  
  echo ""
  echo "=========================================="
  echo "REVIEW CHECKLIST"
  echo "=========================================="
  
  # Determine which agent this is and show relevant checklist
  case "$AGENT_FILE" in
    *requirements-analysis.md)
      echo "Agent 1 - Requirements Analysis Checklist:"
      echo "□ Feature scope clearly defined"
      echo "□ All affected systems identified"
      echo "□ Dependencies properly analyzed"
      echo "□ Risks adequately assessed"
      echo "□ Recommended approach is sound"
      echo "□ Prerequisites are realistic"
      echo "□ Performance implications considered"
      echo "□ Alignment with project goals confirmed"
      ;;
    *architecture-plan.md)
      echo "Agent 2 - Architecture Planning Checklist:"
      echo "□ Component breakdown is logical"
      echo "□ File structure plan is organized"
      echo "□ Interface definitions are clear"
      echo "□ Implementation sequence is realistic"
      echo "□ Testing strategy is comprehensive"
      echo "□ Design patterns are appropriate"
      echo "□ Modularity and separation of concerns maintained"
      echo "□ Future extensibility considered"
      ;;
    *integration-strategy.md)
      echo "Agent 3 - Integration Strategy Checklist:"
      echo "□ Integration approach minimizes disruption"
      echo "□ Backward compatibility strategy is sound"
      echo "□ Error handling is comprehensive"
      echo "□ Fallback mechanisms are in place"
      echo "□ Migration procedures are detailed"
      echo "□ Testing strategy covers all scenarios"
      echo "□ Monitoring and validation plans are adequate"
      echo "□ Troubleshooting guide is helpful"
      ;;
    *quality-review.md)
      echo "Agent 4 - Quality Review Checklist:"
      echo "□ DRY violations identified and addressed"
      echo "□ Refactoring recommendations are practical"
      echo "□ Best practices compliance is thorough"
      echo "□ Code quality metrics are realistic"
      echo "□ Reusable patterns are well-designed"
      echo "□ Maintenance considerations are complete"
      echo "□ Performance optimizations are appropriate"
      echo "□ Technical debt is properly assessed"
      ;;
  esac
  
  echo ""
  echo "=========================================="
  echo "APPROVAL OPTIONS"
  echo "=========================================="
  echo "After reviewing the output above:"
  echo ""
  echo "To APPROVE this agent's work:"
  echo "/planning-approval approve [SESSION_ID] [AGENT_NUMBER]"
  echo ""
  echo "To REJECT and request revisions:"
  echo "/planning-approval reject [SESSION_ID] [AGENT_NUMBER] \"reason for rejection\""
  echo ""
  echo "To check overall session status:"
  echo "/planning-approval status [SESSION_ID]"
  
else
  echo "File not found: $AGENT_FILE"
  echo ""
  echo "Usage: /planning-approval review [AGENT_OUTPUT_FILE]"
  echo "Example: /planning-approval review .taskmaster/docs/planning/sessions/20240710_143022_user-auth/requirements-analysis.md"
fi
```

## Approve Agent Output

```bash
SESSION_ID=$(echo "$ARGUMENTS" | cut -d' ' -f1)
AGENT_NUMBER=$(echo "$ARGUMENTS" | cut -d' ' -f2)
SESSION_DIR=".taskmaster/docs/planning/sessions/$SESSION_ID"

if [ ! -d "$SESSION_DIR" ]; then
  echo "Session not found: $SESSION_ID"
  exit 1
fi

# Create approvals directory if it doesn't exist
mkdir -p "$SESSION_DIR/.approvals"

# Create approval record
TIMESTAMP=$(date "+%Y-%m-%d %H:%M:%S")
echo "$TIMESTAMP - Agent $AGENT_NUMBER approved" >> "$SESSION_DIR/.approvals/approval-log.txt"

# Create agent-specific approval file
touch "$SESSION_DIR/.approvals/agent-$AGENT_NUMBER-approved"

echo "✅ Agent $AGENT_NUMBER output approved for session $SESSION_ID"
echo "Approval logged at: $TIMESTAMP"

# Determine next step
case $AGENT_NUMBER in
  1)
    echo ""
    echo "Next Step: Agent 2 (Architecture Planning)"
    echo "Review the requirements analysis and proceed with architecture planning."
    ;;
  2)
    echo ""
    echo "Next Step: Agent 3 (Integration Strategy)"
    echo "Review the architecture plan and proceed with integration planning."
    ;;
  3)
    echo ""
    echo "Next Step: Agent 4 (Quality Review)"
    echo "Review all previous outputs and proceed with quality review."
    ;;
  4)
    echo ""
    echo "Next Step: Generate Final PRD"
    echo "All agents complete. Ready to generate final PRD and integrate with Task Master AI."
    ;;
esac
```

## Reject Agent Output

```bash
SESSION_ID=$(echo "$ARGUMENTS" | cut -d' ' -f1)
AGENT_NUMBER=$(echo "$ARGUMENTS" | cut -d' ' -f2)
REASON=$(echo "$ARGUMENTS" | cut -d' ' -f3-)
SESSION_DIR=".taskmaster/docs/planning/sessions/$SESSION_ID"

if [ ! -d "$SESSION_DIR" ]; then
  echo "Session not found: $SESSION_ID"
  exit 1
fi

# Create approvals directory if it doesn't exist
mkdir -p "$SESSION_DIR/.approvals"

# Create rejection record
TIMESTAMP=$(date "+%Y-%m-%d %H:%M:%S")
echo "$TIMESTAMP - Agent $AGENT_NUMBER REJECTED: $REASON" >> "$SESSION_DIR/.approvals/approval-log.txt"

# Create agent-specific rejection file with reason
echo "Rejected: $TIMESTAMP" > "$SESSION_DIR/.approvals/agent-$AGENT_NUMBER-rejected"
echo "Reason: $REASON" >> "$SESSION_DIR/.approvals/agent-$AGENT_NUMBER-rejected"

echo "❌ Agent $AGENT_NUMBER output rejected for session $SESSION_ID"
echo "Rejection logged at: $TIMESTAMP"
echo "Reason: $REASON"
echo ""
echo "Required Action:"
echo "1. Review the rejection reason above"
echo "2. Revise the agent's output to address the concerns"
echo "3. Re-submit for approval when ready"

# Determine which file needs revision
case $AGENT_NUMBER in
  1)
    echo "4. File to revise: $SESSION_DIR/requirements-analysis.md"
    ;;
  2)
    echo "4. File to revise: $SESSION_DIR/architecture-plan.md"
    ;;
  3)
    echo "4. File to revise: $SESSION_DIR/integration-strategy.md"
    ;;
  4)
    echo "4. File to revise: $SESSION_DIR/quality-review.md"
    ;;
esac
```

## Check Approval Status

```bash
SESSION_ID="$ARGUMENTS"
SESSION_DIR=".taskmaster/docs/planning/sessions/$SESSION_ID"

if [ ! -d "$SESSION_DIR" ]; then
  echo "Session not found: $SESSION_ID"
  exit 1
fi

echo "Approval Status for Session: $SESSION_ID"
echo "==========================================="

# Check each agent's approval status
for i in 1 2 3 4; do
  if [ -f "$SESSION_DIR/.approvals/agent-$i-approved" ]; then
    APPROVAL_TIME=$(grep "Agent $i approved" "$SESSION_DIR/.approvals/approval-log.txt" | tail -1 | cut -d' ' -f1-2)
    echo "✅ Agent $i: APPROVED ($APPROVAL_TIME)"
  elif [ -f "$SESSION_DIR/.approvals/agent-$i-rejected" ]; then
    REJECTION_REASON=$(grep "Reason:" "$SESSION_DIR/.approvals/agent-$i-rejected" | cut -d' ' -f2-)
    echo "❌ Agent $i: REJECTED - $REJECTION_REASON"
  else
    echo "⏳ Agent $i: PENDING"
  fi
done

echo ""
echo "Session Files:"
ls -la "$SESSION_DIR/" | grep -E '\.(md)$'

echo ""
if [ -f "$SESSION_DIR/.approvals/approval-log.txt" ]; then
  echo "Approval History:"
  cat "$SESSION_DIR/.approvals/approval-log.txt"
else
  echo "No approval history yet."
fi
```

## Quality Gates

The approval system enforces these quality gates:

### Agent 1 → Agent 2
- Requirements analysis must be approved before architecture planning
- Ensures requirements are solid before design begins

### Agent 2 → Agent 3  
- Architecture plan must be approved before integration planning
- Ensures design is sound before implementation planning

### Agent 3 → Agent 4
- Integration strategy must be approved before quality review
- Ensures implementation approach is validated

### Agent 4 → Final PRD
- Quality review must be approved before PRD generation
- Ensures all quality concerns are addressed

## Approval Workflow

1. **Agent completes their work** → Creates output file
2. **Review agent output** → Use `/planning-approval review [FILE]`
3. **Make decision** → Approve or reject with reasons
4. **If rejected** → Agent revises output and re-submits
5. **If approved** → Next agent can begin work
6. **All approved** → Generate final PRD and integrate with Task Master AI

## Integration with Planning Agent

The main `/planning-agent` command automatically prompts for approval after each agent completes their work. This approval system provides:

- **Quality control** at each phase
- **Clear feedback mechanism** for improvements
- **Audit trail** of decisions and reasoning
- **Flexible workflow** allowing revisions as needed

The approval gates ensure that each phase builds on a solid foundation from the previous phase, maintaining high quality throughout the planning process.