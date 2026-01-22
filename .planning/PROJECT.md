# WeCoza Classes Plugin - Class Code Simplification

## What This Is

A WordPress plugin enhancement to simplify class code generation in the WeCoza Classes training management system. Changes the auto-generated class codes from complex datetime-based format (`11-REALLL-RLN-2025-06-25-02-14`) to clean, memorable codes (`AGR1234`) using client name abbreviation plus global sequential numbering.

## Core Value

Class codes must be simple, memorable, and easy to communicate between users - reducing complexity from 30+ characters to 7 characters while maintaining uniqueness.

## Requirements

### Validated

These capabilities already exist in the WeCoza Classes Plugin:

- ✓ Class creation via `[wecoza_capture_class]` shortcode - existing
- ✓ Auto-generated class codes on class creation - existing
- ✓ Client dropdown selection with name/ID pairs - existing
- ✓ PostgreSQL database integration via DatabaseService - existing
- ✓ MVC architecture with AJAX endpoints - existing
- ✓ ClassModel entity with class_code field - existing
- ✓ JavaScript-based form validation and submission - existing

### Active

New functionality being built:

- [ ] Generate class codes in format: [CLIENT][NNNN] (e.g., AGR1234)
- [ ] Extract first 3 uppercase letters from client name for abbreviation
- [ ] Implement global sequential numbering across all classes (0001, 0002, 0003...)
- [ ] Move code generation from client-side JavaScript to server-side PHP
- [ ] Query database for highest existing sequential number on each class creation
- [ ] Maintain backward compatibility - existing classes keep their old codes

### Out of Scope

- Migrating existing class codes to new format - site is in beta, old codes remain unchanged
- Handling client name abbreviations shorter than 3 characters - edge case deferred
- Per-client sequential numbering - explicitly using global counter instead
- Customizable code format - fixed format for consistency
- Manual code entry or override - auto-generation only

## Context

**Codebase State:**
- Existing WordPress plugin with MVC architecture
- External PostgreSQL database (DigitalOcean cluster) - not WordPress MySQL
- Current code generation: JavaScript function in `assets/js/class-types.js` line 237-250
- Format: `${clientId}-${classType}-${subjectId}-${year}-${month}-${day}-${hour}-${minute}`
- Client data: Dropdown populated from database with numeric ID and text name

**User Feedback:**
- Current codes too complex and long (30+ characters)
- Difficult to communicate codes verbally or in documentation
- Need simpler format for training program management

**Technical Environment:**
- PHP 7.4+, WordPress 5.0+
- PostgreSQL with PDO
- Bootstrap 5 UI framework
- No build system - direct file editing

**Database Schema:**
- Table: `classes`
- Field: `class_code` (TEXT) - stores generated code
- No existing sequential counter field - will query MAX() on each generation

## Constraints

- **Backward Compatibility**: Existing classes must retain their old codes - this is a beta site with active data
- **Tech Stack**: PHP/WordPress/PostgreSQL only - no new dependencies
- **Database**: PostgreSQL via existing DatabaseService singleton - no direct queries
- **Performance**: Code generation happens once per class - acceptable to query MAX(class_code) on each create
- **Client Names**: Must handle client names with spaces, special characters, varying lengths
- **Format**: Fixed to 3 uppercase letters + 4 digits - no customization needed

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| Global sequential vs per-client | User explicitly requested global numbering | — Pending |
| Server-side generation | Sequential numbering requires database query for current max | — Pending |
| Uppercase abbreviation | User explicitly requested uppercase format | — Pending |
| Keep old codes | Beta site - avoid data migration complexity and risk | — Pending |

---
*Last updated: 2026-01-22 after initialization*
