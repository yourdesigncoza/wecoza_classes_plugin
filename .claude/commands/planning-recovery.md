# Planning Recovery - Error Handling & Session Recovery

Handle errors and recover from interrupted planning sessions.

**Usage:** 
- `/planning-recovery diagnose [SESSION_ID]` - Diagnose session issues
- `/planning-recovery repair [SESSION_ID]` - Attempt to repair corrupted session
- `/planning-recovery backup [SESSION_ID]` - Create backup of session
- `/planning-recovery restore [SESSION_ID] [BACKUP_ID]` - Restore from backup

## Diagnose Session Issues

```bash
SESSION_ID="$ARGUMENTS"
SESSION_DIR=".taskmaster/docs/planning/sessions/$SESSION_ID"

echo "Diagnosing Planning Session: $SESSION_ID"
echo "=========================================="

if [ ! -d "$SESSION_DIR" ]; then
  echo "❌ CRITICAL: Session directory not found"
  echo "   Expected: $SESSION_DIR"
  echo ""
  echo "Available sessions:"
  find .taskmaster/docs/planning/sessions -name "*" -type d -exec basename {} \; 2>/dev/null | head -10
  exit 1
fi

echo "✅ Session directory exists: $SESSION_DIR"
echo ""

# Check directory structure
echo "Directory Structure Analysis:"
echo "----------------------------"
ls -la "$SESSION_DIR/" 2>/dev/null || echo "❌ Cannot read session directory"
echo ""

# Check required files
echo "Agent Output Files:"
echo "------------------"
AGENT_FILES=("requirements-analysis.md" "architecture-plan.md" "integration-strategy.md" "quality-review.md")
MISSING_COUNT=0
CORRUPTED_COUNT=0

for file in "${AGENT_FILES[@]}"; do
  FILEPATH="$SESSION_DIR/$file"
  if [ -f "$FILEPATH" ]; then
    SIZE=$(stat -c%s "$FILEPATH" 2>/dev/null || echo "0")
    if [ "$SIZE" -gt 100 ]; then
      echo "✅ $file (${SIZE} bytes)"
    else
      echo "⚠️  $file (${SIZE} bytes - possibly incomplete)"
      CORRUPTED_COUNT=$((CORRUPTED_COUNT + 1))
    fi
  else
    echo "❌ $file (missing)"
    MISSING_COUNT=$((MISSING_COUNT + 1))
  fi
done

echo ""
echo "Approval Status:"
echo "---------------"
if [ -d "$SESSION_DIR/.approvals" ]; then
  if [ -f "$SESSION_DIR/.approvals/approval-log.txt" ]; then
    echo "✅ Approval system initialized"
    echo "Approval history:"
    cat "$SESSION_DIR/.approvals/approval-log.txt" | sed 's/^/   /'
  else
    echo "⚠️  Approval directory exists but no log found"
  fi
  
  # Check individual approvals
  for i in 1 2 3 4; do
    if [ -f "$SESSION_DIR/.approvals/agent-$i-approved" ]; then
      echo "✅ Agent $i approved"
    elif [ -f "$SESSION_DIR/.approvals/agent-$i-rejected" ]; then
      echo "❌ Agent $i rejected"
    else
      echo "⏳ Agent $i pending"
    fi
  done
else
  echo "⚠️  No approval system found"
fi

echo ""
echo "Session Integrity Summary:"
echo "========================="
echo "Missing agent files: $MISSING_COUNT/4"
echo "Potentially corrupted files: $CORRUPTED_COUNT"

# Determine session state
if [ "$MISSING_COUNT" -eq 4 ]; then
  echo "🔍 Status: NEW SESSION (no agent work completed)"
  echo "💡 Recommendation: Start with Agent 1 (Requirements Analysis)"
elif [ "$MISSING_COUNT" -eq 3 ]; then
  echo "🔍 Status: EARLY STAGE (only Agent 1 completed)"
  echo "💡 Recommendation: Proceed to Agent 2 (Architecture Planning)"
elif [ "$MISSING_COUNT" -eq 2 ]; then
  echo "🔍 Status: MID-STAGE (Agents 1-2 completed)"
  echo "💡 Recommendation: Proceed to Agent 3 (Integration Strategy)"
elif [ "$MISSING_COUNT" -eq 1 ]; then
  echo "🔍 Status: LATE STAGE (Agents 1-3 completed)"
  echo "💡 Recommendation: Complete Agent 4 (Quality Review)"
elif [ "$MISSING_COUNT" -eq 0 ] && [ ! -f "$SESSION_DIR/final-prd.md" ]; then
  echo "🔍 Status: READY FOR FINALIZATION"
  echo "💡 Recommendation: Run /planning-finalize $SESSION_ID"
elif [ -f "$SESSION_DIR/final-prd.md" ]; then
  echo "🔍 Status: COMPLETED"
  echo "💡 Recommendation: Session appears complete"
else
  echo "🔍 Status: UNKNOWN"
  echo "💡 Recommendation: Manual investigation required"
fi

if [ "$CORRUPTED_COUNT" -gt 0 ]; then
  echo ""
  echo "⚠️  WARNING: Some files appear incomplete or corrupted"
  echo "💡 Recommendation: Review and potentially regenerate affected agent outputs"
fi
```

## Repair Corrupted Session

```bash
SESSION_ID="$ARGUMENTS"
SESSION_DIR=".taskmaster/docs/planning/sessions/$SESSION_ID"

echo "Attempting to Repair Session: $SESSION_ID"
echo "=========================================="

if [ ! -d "$SESSION_DIR" ]; then
  echo "❌ Session directory not found: $SESSION_DIR"
  exit 1
fi

# Create backup before repair
BACKUP_DIR="$SESSION_DIR/.backups/$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"
cp -r "$SESSION_DIR"/* "$BACKUP_DIR/" 2>/dev/null
echo "✅ Backup created: $BACKUP_DIR"

# Repair approval system
if [ ! -d "$SESSION_DIR/.approvals" ]; then
  echo "🔧 Repairing: Creating approval system"
  mkdir -p "$SESSION_DIR/.approvals"
  echo "$(date "+%Y-%m-%d %H:%M:%S") - Approval system repaired" >> "$SESSION_DIR/.approvals/approval-log.txt"
fi

# Check and repair permissions
echo "🔧 Repairing: Checking file permissions"
chmod 755 "$SESSION_DIR" 2>/dev/null
chmod 644 "$SESSION_DIR"/*.md 2>/dev/null
chmod -R 755 "$SESSION_DIR/.approvals" 2>/dev/null

# Validate agent files
echo "🔧 Repairing: Validating agent files"
AGENT_FILES=("requirements-analysis.md" "architecture-plan.md" "integration-strategy.md" "quality-review.md")

for file in "${AGENT_FILES[@]}"; do
  FILEPATH="$SESSION_DIR/$file"
  if [ -f "$FILEPATH" ]; then
    SIZE=$(stat -c%s "$FILEPATH")
    if [ "$SIZE" -lt 100 ]; then
      echo "⚠️  $file appears corrupted (only $SIZE bytes)"
      echo "   Moving to: $FILEPATH.corrupted"
      mv "$FILEPATH" "$FILEPATH.corrupted"
      echo "   File backed up, agent will need to regenerate"
    else
      echo "✅ $file appears valid"
    fi
  fi
done

# Repair session metadata if missing
if [ ! -f "$SESSION_DIR/session-metadata.json" ]; then
  echo "🔧 Repairing: Creating session metadata"
  FEATURE_NAME=$(basename "$SESSION_DIR" | sed 's/^[0-9]*_[0-9]*_//')
  cat > "$SESSION_DIR/session-metadata.json" << EOF
{
  "session_id": "$SESSION_ID",
  "feature_name": "$FEATURE_NAME",
  "created": "$(date -r "$SESSION_DIR" "+%Y-%m-%d %H:%M:%S")",
  "repaired": "$(date "+%Y-%m-%d %H:%M:%S")",
  "status": "in_progress"
}
EOF
fi

echo ""
echo "✅ Repair completed for session: $SESSION_ID"
echo "📁 Backup available at: $BACKUP_DIR"
echo ""
echo "Next steps:"
echo "1. Use /planning-session-manager status $SESSION_ID to check current state"
echo "2. Resume work with /planning-session-manager resume $SESSION_ID"
echo "3. If files were corrupted, regenerate the affected agent outputs"
```

## Backup Session

```bash
SESSION_ID="$ARGUMENTS"
SESSION_DIR=".taskmaster/docs/planning/sessions/$SESSION_ID"

if [ ! -d "$SESSION_DIR" ]; then
  echo "❌ Session not found: $SESSION_ID"
  exit 1
fi

# Create timestamped backup
BACKUP_ID="$(date +%Y%m%d_%H%M%S)"
BACKUP_DIR="$SESSION_DIR/.backups/$BACKUP_ID"
mkdir -p "$BACKUP_DIR"

# Copy all session files
cp -r "$SESSION_DIR"/* "$BACKUP_DIR/" 2>/dev/null

# Exclude backup directories from backup (prevent recursion)
rm -rf "$BACKUP_DIR/.backups" 2>/dev/null

# Create backup manifest
cat > "$BACKUP_DIR/backup-manifest.txt" << EOF
Backup Information
==================
Session ID: $SESSION_ID
Backup ID: $BACKUP_ID
Created: $(date "+%Y-%m-%d %H:%M:%S")
Original Location: $SESSION_DIR

Files Backed Up:
$(find "$BACKUP_DIR" -type f | sed "s|$BACKUP_DIR/||g" | sort)
EOF

echo "✅ Backup created successfully"
echo "📁 Backup ID: $BACKUP_ID"
echo "📁 Location: $BACKUP_DIR"
echo "📄 Manifest: $BACKUP_DIR/backup-manifest.txt"
echo ""
echo "To restore: /planning-recovery restore $SESSION_ID $BACKUP_ID"
```

## Restore from Backup

```bash
ARGS_ARRAY=($ARGUMENTS)
SESSION_ID="${ARGS_ARRAY[0]}"
BACKUP_ID="${ARGS_ARRAY[1]}"

if [ -z "$SESSION_ID" ] || [ -z "$BACKUP_ID" ]; then
  echo "Usage: /planning-recovery restore [SESSION_ID] [BACKUP_ID]"
  echo ""
  echo "Available sessions:"
  find .taskmaster/docs/planning/sessions -name "*" -type d -exec basename {} \; | head -5
  exit 1
fi

SESSION_DIR=".taskmaster/docs/planning/sessions/$SESSION_ID"
BACKUP_DIR="$SESSION_DIR/.backups/$BACKUP_ID"

if [ ! -d "$BACKUP_DIR" ]; then
  echo "❌ Backup not found: $BACKUP_ID"
  echo ""
  echo "Available backups for session $SESSION_ID:"
  if [ -d "$SESSION_DIR/.backups" ]; then
    ls -1 "$SESSION_DIR/.backups" | head -10
  else
    echo "No backups available"
  fi
  exit 1
fi

echo "Restoring Session from Backup"
echo "============================="
echo "Session: $SESSION_ID"
echo "Backup: $BACKUP_ID"
echo ""

# Show backup manifest
if [ -f "$BACKUP_DIR/backup-manifest.txt" ]; then
  echo "Backup Details:"
  cat "$BACKUP_DIR/backup-manifest.txt"
  echo ""
fi

# Create current backup before restore
CURRENT_BACKUP_ID="pre-restore-$(date +%Y%m%d_%H%M%S)"
CURRENT_BACKUP_DIR="$SESSION_DIR/.backups/$CURRENT_BACKUP_ID"
mkdir -p "$CURRENT_BACKUP_DIR"
cp -r "$SESSION_DIR"/* "$CURRENT_BACKUP_DIR/" 2>/dev/null
rm -rf "$CURRENT_BACKUP_DIR/.backups" 2>/dev/null
echo "✅ Current state backed up as: $CURRENT_BACKUP_ID"

# Restore from backup
echo "🔄 Restoring files..."
# Remove current files (except .backups)
find "$SESSION_DIR" -maxdepth 1 -type f -delete 2>/dev/null
find "$SESSION_DIR" -maxdepth 1 -type d ! -name ".backups" ! -path "$SESSION_DIR" -exec rm -rf {} \; 2>/dev/null

# Copy backup files
cp -r "$BACKUP_DIR"/* "$SESSION_DIR/" 2>/dev/null
rm -rf "$SESSION_DIR/.backups" 2>/dev/null  # Remove nested backups

# Restore the backups directory structure
mkdir -p "$SESSION_DIR/.backups"
mv "$CURRENT_BACKUP_DIR" "$SESSION_DIR/.backups/"

echo "✅ Restore completed successfully"
echo ""
echo "Restored session: $SESSION_ID"
echo "From backup: $BACKUP_ID"
echo "Pre-restore backup: $CURRENT_BACKUP_ID"
echo ""
echo "Next steps:"
echo "1. Verify restored files: /planning-session-manager status $SESSION_ID"
echo "2. Resume work: /planning-session-manager resume $SESSION_ID"
```

## Error Prevention

### Common Issues & Solutions

1. **Corrupted Agent Files**
   - Cause: Interrupted writing, disk issues
   - Prevention: Regular backups, atomic writes
   - Recovery: Regenerate affected agent outputs

2. **Missing Approval System**
   - Cause: Manual file manipulation
   - Prevention: Use planning commands only
   - Recovery: Automatic repair in diagnose/repair

3. **Permission Issues**
   - Cause: Manual file operations with wrong permissions
   - Prevention: Consistent tool usage
   - Recovery: Automatic permission repair

4. **Incomplete Sessions**
   - Cause: Interrupted planning process
   - Prevention: Complete agents in sequence
   - Recovery: Resume from last completed agent

### Recovery Best Practices

1. **Always diagnose first** - Use `/planning-recovery diagnose` before attempting repairs
2. **Backup before repair** - Automatic backups are created during repair operations
3. **Validate after recovery** - Use session manager to verify successful recovery
4. **Document issues** - Approval logs track all recovery operations

This recovery system ensures that planning sessions can be restored and continued even after unexpected interruptions or file corruption, maintaining the integrity of the multi-agent planning process.