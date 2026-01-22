# Roadmap: Class Code Simplification

## Overview

This roadmap transforms class code generation from complex timestamp-based format (30+ characters) to simple sequential codes (7 characters) using PostgreSQL sequences and server-side generation. The implementation moves code generation from client-side JavaScript to server-side PHP, integrates with existing MVC architecture, and maintains backward compatibility with existing beta site data. Four sequential phases deliver database foundation, core generation logic, controller integration, and frontend updates with comprehensive testing.

## Phases

**Phase Numbering:**
- Integer phases (1, 2, 3): Planned milestone work
- Decimal phases (2.1, 2.2): Urgent insertions (marked with INSERTED)

Decimal phases appear between their surrounding integers in numeric order.

- [ ] **Phase 1: Database Foundation** - PostgreSQL sequence, constraints, indexes
- [ ] **Phase 2: Server-Side Code Generation** - Core generation logic with prefix extraction
- [ ] **Phase 3: Controller Integration** - AJAX endpoint integration with create/update separation
- [ ] **Phase 4: Frontend Integration & Testing** - JavaScript cleanup and comprehensive testing

## Phase Details

### Phase 1: Database Foundation
**Goal**: Database provides race-condition-free sequential numbering with uniqueness guarantees
**Depends on**: Nothing (first phase)
**Requirements**: DB-01, DB-02, DB-03, DB-04
**Success Criteria** (what must be TRUE):
  1. PostgreSQL sequence class_code_seq exists and returns incremental values
  2. Database prevents duplicate class_code values via unique constraint
  3. Class_code queries complete in under 100ms even with 1000+ existing classes
  4. Concurrent code generation requests never produce duplicate sequential numbers
**Plans**: 1 plan

Plans:
- [ ] 01-01-PLAN.md - Create sequence, unique index, and constraint via SQL migrations

### Phase 2: Server-Side Code Generation
**Goal**: Core generation logic produces simple, memorable codes from client names and sequential numbers
**Depends on**: Phase 1
**Requirements**: GEN-01, GEN-02, GEN-03, GEN-04, GEN-05, GEN-06, GEN-07
**Success Criteria** (what must be TRUE):
  1. Method extracts 3 uppercase letters from client name (e.g., "Real Logistics" becomes "REA")
  2. Client names shorter than 3 letters pad to 3 characters (e.g., "AB Ltd" becomes "ABX")
  3. Unicode client names process correctly without character corruption
  4. Generated codes follow format ABC1234 (3 letters + 4 zero-padded digits)
  5. Code generation queries sequence and formats correctly in single operation
**Plans**: TBD

Plans:
- [ ] 02-01: TBD

### Phase 3: Controller Integration
**Goal**: AJAX endpoint generates codes on class creation while protecting existing class codes from regeneration
**Depends on**: Phase 2
**Requirements**: GEN-07, BC-01, BC-02, BC-03
**Success Criteria** (what must be TRUE):
  1. New class creation generates and saves simple code format automatically
  2. Existing classes retain their old codes during update operations
  3. Update form submissions never trigger code regeneration
  4. AJAX response returns generated code to frontend for display
  5. Unique constraint violations are caught and handled gracefully
**Plans**: TBD

Plans:
- [ ] 03-01: TBD

### Phase 4: Frontend Integration & Testing
**Goal**: Users see simple codes immediately after class creation with comprehensive validation
**Depends on**: Phase 3
**Requirements**: JS-01, JS-02, JS-03, JS-04
**Success Criteria** (what must be TRUE):
  1. Class code input field displays as read-only with visual indicator
  2. User sees generated code in success message after creating class
  3. Client-side code generation function no longer executes
  4. Two users creating classes simultaneously receive unique sequential codes
  5. Client names with emoji, accents, or special characters produce valid codes
**Plans**: TBD

Plans:
- [ ] 04-01: TBD

## Progress

**Execution Order:**
Phases execute in numeric order: 1 -> 2 -> 3 -> 4

| Phase | Plans Complete | Status | Completed |
|-------|----------------|--------|-----------|
| 1. Database Foundation | 0/1 | Planned | - |
| 2. Server-Side Code Generation | 0/TBD | Not started | - |
| 3. Controller Integration | 0/TBD | Not started | - |
| 4. Frontend Integration & Testing | 0/TBD | Not started | - |
