#!/bin/bash

# Post-Development Update Hook
# Triggered on Edit/MultiEdit completion
# Updates reference library with newly used references

log_file="$HOME/.claude/logs/hooks.log"

# Source project detection utility
source "$HOME/.claude/hooks/utils/project-detection.sh"

# Log function
log() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') [POST-DEV] $1" >> "$log_file"
}

# Read hook input
input=$(cat)
tool_name=$(echo "$input" | jq -r '.tool_name')
tool_input=$(echo "$input" | jq -r '.tool_input')

log "Hook triggered for tool: $tool_name"

# Only process Edit/MultiEdit/TodoWrite calls
if [[ "$tool_name" != "Edit" && "$tool_name" != "MultiEdit" && "$tool_name" != "TodoWrite" ]]; then
    log "Skipping - not an Edit/MultiEdit/TodoWrite call"
    exit 0
fi

# Handle different tool types
if [[ "$tool_name" == "TodoWrite" ]]; then
    # For TodoWrite, extract any file references from todo content
    todos_content=$(echo "$tool_input" | jq -r '.todos[]?.content // empty' 2>/dev/null)
    log "TodoWrite detected - scanning for file references in todo content"
    
    # Extract @file references from todo content
    file_refs=$(echo "$todos_content" | grep -oE '@[^[:space:]]+' | sed 's/^@//' || true)
    
    if [[ -z "$file_refs" ]]; then
        log "No @file references found in TodoWrite content"
        exit 0
    fi
    
    # Process each file reference
    while IFS= read -r file_ref; do
        if [[ -n "$file_ref" ]]; then
            log "Processing file reference from todo: $file_ref"
            # Set file_path for processing below
            file_path="$file_ref"
            # Process this file reference (rest of script will handle it)
            break
        fi
    done <<< "$file_refs"
    
else
    # For Edit/MultiEdit, extract file path normally
    file_path=$(echo "$tool_input" | jq -r '.file_path')
    if [[ -z "$file_path" || "$file_path" == "null" ]]; then
        log "No file path found in tool input"
        exit 0
    fi
fi

log "Processing file: $file_path"

# Detect project root and reference library
project_root=$(find_project_root)
if [[ $? -ne 0 || -z "$project_root" ]]; then
    log "No supported project found - skipping reference processing"
    exit 0
fi

reference_file=$(get_reference_library_path "$project_root")
log "Using project root: $project_root"
log "Using reference library: $reference_file"

# Check if file is within project directory
if [[ "$file_path" != "$project_root"* ]]; then
    log "File not in project directory - skipping"
    exit 0
fi

# Get relative path from project base
rel_path="${file_path#$project_root/}"
log "Relative path: $rel_path"

# Check if reference library exists
if [[ ! -f "$reference_file" ]]; then
    log "Reference library not found at: $reference_file"
    exit 0
fi

# Determine which section this file belongs to
section=""
reference_entry=""

case "$rel_path" in
    app/Views/*)
        section="View Files to examine / reference"
        reference_entry="- [ ] @$rel_path"
        ;;
    schema/*)
        section="Assests to reference"
        reference_entry="- [ ] @$rel_path"
        ;;
    assets/*)
        section="Assests to reference"
        reference_entry="- [ ] @$rel_path"
        ;;
    *.json)
        section="Assests to reference"
        reference_entry="- [ ] @$rel_path"
        ;;
    *.sql)
        section="Assests to reference"
        reference_entry="- [ ] @$rel_path"
        ;;
    *)
        log "File type not categorized - skipping"
        exit 0
        ;;
esac

log "Categorized as: $section"
log "Reference entry: $reference_entry"

# Call utility script to update reference library
"$HOME/.claude/hooks/utils/update-reference-library.sh" "$reference_file" "$section" "$reference_entry"

log "Reference library update completed"
exit 0