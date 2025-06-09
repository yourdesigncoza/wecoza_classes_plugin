You are an AI assistant that produces a Daily Development Report. Follow these steps exactly:

1. **Check for Uncommitted Changes**  
   - Run `git status --porcelain`.  
   - If any files are uncommitted:
     1. Run:
        ```
        git add -A
        git commit -m "chore: auto-commit before end-of-day report"
        git push origin main
        ```
     2. Then proceed directly to Step 2 (do not stop).
   - If the working directory is already clean, skip the above and proceed to Step 2.

2. **Ensure Remote Is Up to Date**  
   - Run `git fetch origin` (assume your main branch is `origin/main`).  
   - You do **not** need to merge or rebase; simply ensure you have the latest remote state.

3. **Collect Today’s Git Pushes**  
   - Define “today” as 00:00 to 23:59 in your local timezone (Africa/Johannesburg).  
   - Run:
     ```
     git log origin/main --since="2025-06-05 00:00" --until="2025-06-05 23:59" --pretty=format:"- %h  **%s**  (%an)"
     ```
     This will return a bullet list of all commits pushed to `origin/main` today, in the format:
     ```
     - abc1234  **Commit message here**  (Author Name)
     - def5678  **Another commit message**  (Author Name)
     ```
   - If no commits are found, note “No commits found for today.”

4. **Assemble the Markdown Report**  
   - Use the following structure (replace placeholders with actual data):

     ```
     # Daily Development Report
     **Date:** 2025-06-05  
     **Developer:** [Your Name or Git username]  
     **Project:** [e.g. WeCoza 3 Child Theme – Calendar Integration]
     **Title:** WEC-DAILY-WORK-REPORT-2025-06-05

     ## Executive Summary
     [A 1-2 sentence description of the day’s overall focus]

     ## 1. Git Commits (2025-06-05)
     [Insert the bullet list from Step 3, or “No commits found for today.”]

     ## 2. Detailed Changes
     For each commit listed above, expand with bullet points (if applicable). Example:

     - **abc1234  Fix calendar event styling**  
       • Adjusted `.text-primary` class for events.  
       • Simplified tooltip text to show only times.

     - **def5678  Update public holidays AJAX**  
       • Added year-based filtering.  
       • Improved error handling when no holidays found.

     *(If there were no commits, you can omit this section.)*

     ## 3. Quality Assurance / Testing
     - ✅ [e.g.] Verified calendar renders in Firefox and Chrome.  
     - ✅ [e.g.] Confirmed holiday events show correctly on mobile.

     *(If no QA was done today, you can skip or say “No testing performed today.”)*

     ## 4. Next Steps
     - [ ] [e.g.] Implement click-to-edit on calendar events (ticket #123).  
     - [ ] [e.g.] Write documentation for Exception Date templates.  

     ## 5. Blockers / Notes
     - [If any issues are blocking you, describe here; otherwise say “None.”]
     ```

5. **Output**  
   - Output nothing but the fully-formed Markdown report (no extra commentary).  
   - If at any point Git commands fail, stop and output only the relevant Git error.

6. **Save Report**  
   - Save the generated report to `@wecoza-dev-flow/WEC-DAILY-WORK-REPORT-2025-06-05.md` (replace date with today’s date).
