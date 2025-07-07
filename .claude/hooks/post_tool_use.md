---
description: Append latest used references to the top of each section in @reference/reference-library.md, maintaining a max of 10 per section.
---

## Update Reference Library

After making code changes, update `@reference/reference-library.md` as follows:

1. **For each section** (e.g., Components, Assets, View Files, Screenshots, Code Snippets):
    - Add the latest references you used to the **top** of the relevant section’s list.
    - Do **not** remove any existing references, just prepend the new ones.
    - If a section exceeds 10 references, **remove the oldest** (bottom) items to keep only the 10 most recent.
2. **Never delete** references, only add.
3. **Input:** Specify the section and the references you used in the following format:
    ```
    Section: <Section Name>
    References:
    - <reference1>
    - <reference2>
    ...
    ```
4. **Repeat** for each section you updated.

**Example:**
