# Generate Session Transcript Command

## Quick Command
At the end of any session, simply say:

```
Generate a complete transcript of this session to @transcripts/
```

## Detailed Command Template
For more specific transcript generation, use:

```
Create a comprehensive session transcript that includes:
1. The initial request/task description
2. All conversation exchanges between user and assistant
3. Every code change with before/after snippets
4. All file reads and their purposes
5. Decision points and corrections
6. Technical implementation details
7. Files created or modified
8. Final outcomes and summary

Save to: @transcripts/transcript-[descriptive-title]-[YYYY-MM-DD]-[HHMM].md

Format should include:
- Session metadata (date, time, duration)
- Full conversation flow
- Code blocks with syntax highlighting
- Clear section headers
- User interventions highlighted
- Assistant's reasoning documented
```

## Alternative Short Commands

### Option 1: Simple
```
Transcript this session
```

### Option 2: With Title
```
Transcript this session as "fix-update-form"
```

### Option 3: Detailed
```
Generate full transcript with all code changes
```

## What Gets Captured

The transcript should automatically include:
- **Initial Request**: The full problem statement
- **Analysis Phase**: How the problem was understood
- **Planning**: Task breakdown and approach
- **Implementation**: All code changes with context
- **Interactions**: User feedback and course corrections
- **Decisions**: Why certain approaches were chosen
- **Results**: What was accomplished

## Example Usage

```
User: Generate a complete transcript of this session to @transcripts/