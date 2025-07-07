---
description: Pull `✓` or `[x]`-marked items from the context and extend with further details.
allowed-tools: Bash(cat:*), Bash(grep:*), Bash(awk:*), Bash(sed:*)
---

# Command definition
echo 'I have the folowing request or task: #$ARGUMENTS and I need you to ThinkHard about it after extracting the completed checklist items from the context.'


## Context Extraction & Expansion

1. **Review the context below carefully**
2. **Extract all checklist items marked as completed** — denoted by either `✓` or `[x]` if none is checked ignore the rest of the context.
3. **After listing the completed items**, enhance the context by:
   * Adding any related details that clarify or expand on these items.
   * Including relevant insights or implications tied to the checklist.
4. If additional files or documentation are referenced, **summarize their relevance and how they support the extracted items**.
5. Use the GIT Context to create a single git commit and use it as the commit message.

---

## Context:

When you read this , print "I see you BOSS!" so I know it was loaded.

## Components to reference:
- [ ] #class-learners-container
- [ ] #exam-learners-list
- [ ] #schedule-update-end-date-container
- [ ] #schedule-start-date
- [ ] #schedule-end-date
- [ ] #schedule-update-end-date-btn
- [ ] #schedule-update-end-date-container

## Assests to reference:
- [ ] @schema/classes_schema.sql
- [ ] @captured-exam-learners.json
- [ ] @captured.json


## View Files to examine / reference:
- [ ] @app/Views/components/class-capture-partials/update-class.php
- [ ] @app/Views/components/class-capture-form.view.php
- [ ] @app/Views/components/class-capture-partials/create-class.php
- [ ] @app/Views/components/single-class-display.view.php

## Screenshots to reference:
- [ ]  ![alt text](<Screenshot from 2025-07-06 16-11-21.png>)
- [ ]  ![alt text](<Screenshot from 2025-07-06 16-13-22.png>)

## Code Snippets to reference:
- [ ] Use this section
```html
// PHP code to reference
<input type="date" id="schedule_end_date" name="schedule_end_date" class="form-control readonly-field" placeholder="YYYY-MM-DD" required>
```
- [ ] Use this section
```js
// JavaScript code to reference
document.getElementById('schedule_end_date').value = '';
```

---

## GIT Context

- Current git status: !`git status`
- Current git diff (staged and unstaged changes): !`git diff HEAD`
- Current branch: !`git branch --show-current`
- Recent commits: !`git log --oneline -10`

## Your task

Based on the above changes, create a single git commit and use it as the commit message.