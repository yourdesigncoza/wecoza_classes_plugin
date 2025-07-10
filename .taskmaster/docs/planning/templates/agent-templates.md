# Multi-Agent Planning System Templates

## Agent 1: Requirements Analyzer Template

### Role
Requirements Analysis Agent specializing in feature integration planning.

### Responsibilities
- Analyze incoming feature requests against existing project architecture
- Identify potential conflicts, dependencies, and integration points
- Map new requirements to existing codebase structures
- Assess feasibility and complexity of proposed features
- Generate detailed requirement specifications with technical considerations

### Process
1. Parse the feature request thoroughly
2. Examine existing project structure, dependencies, and patterns
3. Identify all affected components and modules
4. Flag potential breaking changes or compatibility issues
5. Create a comprehensive analysis report with recommendations

### Output Template: requirements-analysis.md

```markdown
# Requirements Analysis: [FEATURE_NAME]

## Feature Summary
- **Requested Feature:** [Brief description]
- **Scope:** [Detailed scope definition]
- **Priority:** [High/Medium/Low]
- **Estimated Complexity:** [1-10 scale]

## Impact Assessment

### Affected Systems
- [List of existing systems that will be impacted]

### Dependencies
- **Required:** [List of required dependencies]
- **Optional:** [List of optional dependencies]
- **Conflicts:** [Any conflicting dependencies]

### Integration Points
- [List of systems this feature needs to integrate with]

## Risk Evaluation

### High Risk Items
- [List high-risk aspects]

### Medium Risk Items
- [List medium-risk aspects]

### Mitigation Strategies
- [Risk mitigation approaches]

## Recommended Approach
[Detailed recommendation for implementation approach]

### Alternatives Considered
- **Option 1:** [Alternative approach 1]
- **Option 2:** [Alternative approach 2]

## Prerequisites
- [List of prerequisites that must be completed first]

## Blockers
- [List of current blockers or dependencies]

## Performance Implications
- [Expected performance impact]

## Scalability Considerations
- [How this feature will scale]

## Maintainability Impact
- [Impact on code maintainability]

## Alignment with Project Goals
- [How this aligns with overall project objectives]

---
**Status:** Requirements analysis complete
**Next Phase:** Architecture Planning (@ArchitecturePlanner)
```

---

## Agent 2: Architecture Planner Template

### Role
Code Architecture Planning Agent focused on designing implementation strategies.

### Responsibilities
- Design detailed implementation plans based on requirements analysis
- Create modular, scalable code architecture
- Plan file structures, class hierarchies, and module interactions
- Define interfaces, APIs, and data flow patterns
- Establish coding standards and patterns for the feature

### Process
1. Review requirements analysis and project assessment
2. Design overall architecture and component breakdown
3. Plan file organization and module structure
4. Define interfaces and contracts between components
5. Create implementation roadmap with clear milestones
6. Specify testing strategy and validation points

### Output Template: architecture-plan.md

```markdown
# Architecture Plan: [FEATURE_NAME]

## High-Level Architecture

### Component Overview
```
[TEXT-BASED ARCHITECTURE DIAGRAM]
┌─────────────────┐    ┌─────────────────┐
│   Component A   │───▶│   Component B   │
└─────────────────┘    └─────────────────┘
         │                       │
         ▼                       ▼
┌─────────────────┐    ┌─────────────────┐
│   Component C   │    │   Component D   │
└─────────────────┘    └─────────────────┘
```

### Core Components
- **[Component Name]:** [Purpose and responsibilities]

## File Structure Plan

### New Files
```
[DIRECTORY_STRUCTURE]
project/
├── path/to/new/
│   ├── component1.php
│   ├── component2.php
│   └── interfaces/
│       └── component-interface.php
└── existing/modified/
    └── existing-file.php (modifications)
```

### Modified Files
- **[File Path]:** [Description of modifications needed]

## Component Interactions

### Data Flow
1. [Step 1 of data flow]
2. [Step 2 of data flow]
3. [Step 3 of data flow]

### Interface Definitions

#### [Interface Name]
```php
interface ComponentInterface {
    public function method1($param): ReturnType;
    public function method2($param): ReturnType;
}
```

### API Specifications
- **Endpoint:** [API endpoint if applicable]
- **Method:** [HTTP method]
- **Parameters:** [Required parameters]
- **Response:** [Expected response format]

## Implementation Sequence

### Phase 1: Foundation
- [ ] [Task 1]
- [ ] [Task 2]

### Phase 2: Core Implementation
- [ ] [Task 1]
- [ ] [Task 2]

### Phase 3: Integration
- [ ] [Task 1]
- [ ] [Task 2]

### Phase 4: Testing & Validation
- [ ] [Task 1]
- [ ] [Task 2]

## Dependencies

### Implementation Dependencies
- [Dependency 1] must be completed before [Dependent Task]

### External Dependencies
- [External library or service requirements]

## Testing Strategy

### Unit Testing
- [Components requiring unit tests]

### Integration Testing
- [Integration points requiring testing]

### User Acceptance Testing
- [UAT scenarios]

## Validation Checkpoints
- [ ] [Checkpoint 1: Description]
- [ ] [Checkpoint 2: Description]

## Coding Standards
- [Specific coding standards for this feature]

## Design Patterns
- **Pattern:** [Design pattern to be used]
- **Rationale:** [Why this pattern was chosen]

---
**Status:** Architecture planning complete
**Next Phase:** Integration Planning (@IntegrationSpecialist)
```

---

## Agent 3: Integration Specialist Template

### Role
Integration Specialist Agent responsible for seamless feature implementation.

### Responsibilities
- Execute the planned integration following architectural guidelines
- Implement features with minimal disruption to existing functionality
- Handle data migrations, API changes, and backward compatibility
- Coordinate between different system components
- Ensure proper error handling and fallback mechanisms

### Process
1. Follow the detailed implementation plan from the architecture agent
2. Plan features incrementally with continuous testing approach
3. Handle integration points carefully with existing systems
4. Plan proper error handling and logging
5. Ensure backward compatibility where required
6. Document integration points and changes

### Output Template: integration-strategy.md

```markdown
# Integration Strategy: [FEATURE_NAME]

## Implementation Approach

### Incremental Development Plan
1. **Phase 1:** [Minimal viable implementation]
2. **Phase 2:** [Core functionality]
3. **Phase 3:** [Full feature set]
4. **Phase 4:** [Optimization and polish]

### Integration Points

#### Existing System Integrations
- **[System Name]:** [Integration approach and considerations]

#### Database Integration
- **Schema Changes:** [Required database modifications]
- **Migration Strategy:** [How to handle data migration]
- **Rollback Plan:** [Rollback strategy if needed]

## Compatibility Strategy

### Backward Compatibility
- [How to maintain backward compatibility]
- [Deprecated features and timeline]

### Forward Compatibility
- [How to prepare for future changes]

### Version Management
- [Versioning strategy for APIs/interfaces]

## Error Handling

### Error Scenarios
1. **[Error Type]:** [How to handle this error type]
2. **[Error Type]:** [How to handle this error type]

### Fallback Mechanisms
- **Primary Failure:** [Fallback approach]
- **Secondary Failure:** [Additional fallback]

### Logging Strategy
- **Error Logging:** [What errors to log and where]
- **Performance Logging:** [Performance metrics to track]
- **User Action Logging:** [User interactions to log]

## Testing Strategy

### Integration Test Plan
- [ ] [Integration test 1]
- [ ] [Integration test 2]

### Compatibility Testing
- [ ] [Backward compatibility test]
- [ ] [Forward compatibility test]

### Performance Testing
- [ ] [Performance test scenarios]

### Error Handling Testing
- [ ] [Error scenario test 1]
- [ ] [Error scenario test 2]

## Migration Procedures

### Data Migration
```sql
-- Migration script example
-- [Include actual migration scripts if needed]
```

### Configuration Migration
- [Configuration changes needed]

### Deployment Steps
1. [Step 1]
2. [Step 2]
3. [Step 3]

## Monitoring & Validation

### Success Metrics
- [Metric 1: Expected value]
- [Metric 2: Expected value]

### Health Checks
- [Health check implementations]

### Rollback Triggers
- [Conditions that would trigger a rollback]

## Integration Troubleshooting

### Common Issues
- **Issue:** [Problem description]
  - **Cause:** [Root cause]
  - **Solution:** [How to resolve]

### Debugging Tools
- [Tools and techniques for debugging integration issues]

### Support Documentation
- [Links to relevant documentation]

---
**Status:** Integration strategy complete
**Next Phase:** Quality Review (@DRYEnforcer)
```

---

## Agent 4: DRY Enforcer Template

### Role
DRY Principle Enforcer and Code Quality Agent ensuring maintainable, efficient code.

### Responsibilities
- Review all plans for code duplication opportunities
- Refactor repetitive patterns into reusable components
- Ensure adherence to DRY, SOLID, and other best practices
- Optimize planned code efficiency and maintainability
- Establish and enforce coding standards for this feature

### Process
1. Scan all previous plans for duplication and anti-patterns
2. Identify opportunities for abstraction and reusability
3. Suggest refactoring duplicate code into shared utilities or components
4. Review all plans for adherence to best practices
5. Suggest improvements for code quality and maintainability
6. Create reusable patterns and utility functions

### Output Template: quality-review.md

```markdown
# Quality Review & DRY Analysis: [FEATURE_NAME]

## Code Duplication Analysis

### Identified Duplications
- **Location 1:** [File/function with duplication]
  - **Duplication:** [What is duplicated]
  - **Recommendation:** [How to eliminate duplication]

### Potential Duplications
- **Future Risk:** [Areas that might become duplicated]
  - **Prevention:** [How to prevent duplication]

## Refactoring Recommendations

### Shared Utilities Needed
```php
// Example utility class
class FeatureUtilities {
    public static function commonFunction($param) {
        // Shared functionality
    }
}
```

### Component Abstractions
- **Abstract Class:** [Name and purpose]
- **Interface:** [Name and purpose]
- **Trait:** [Name and purpose]

## Best Practices Compliance

### DRY Principle
- ✅ [Compliance item 1]
- ⚠️ [Warning item 1]
- ❌ [Non-compliance item 1 - with fix]

### SOLID Principles
- **Single Responsibility:** [Assessment]
- **Open/Closed:** [Assessment]
- **Liskov Substitution:** [Assessment]
- **Interface Segregation:** [Assessment]
- **Dependency Inversion:** [Assessment]

### Other Best Practices
- **Code Readability:** [Assessment and recommendations]
- **Performance:** [Assessment and optimizations]
- **Security:** [Security considerations]
- **Documentation:** [Documentation requirements]

## Code Quality Metrics

### Complexity Assessment
- **Cyclomatic Complexity:** [Expected complexity levels]
- **Maintainability Index:** [Target maintainability score]

### Performance Metrics
- **Expected Performance:** [Performance benchmarks]
- **Optimization Opportunities:** [Areas for optimization]

## Reusable Pattern Library

### New Patterns Created
```php
// Pattern example
trait CommonFeatureBehavior {
    public function standardMethod() {
        // Standard implementation
    }
}
```

### Pattern Usage Guidelines
- **When to use:** [Usage scenarios]
- **When not to use:** [Avoid in these scenarios]

## Quality Assurance Checklist

### Code Standards
- [ ] Consistent naming conventions
- [ ] Proper error handling
- [ ] Adequate documentation
- [ ] Test coverage requirements met

### Architecture Standards
- [ ] Follows established patterns
- [ ] Proper separation of concerns
- [ ] Minimal coupling
- [ ] High cohesion

### Performance Standards
- [ ] Meets performance requirements
- [ ] No obvious performance bottlenecks
- [ ] Efficient algorithms used
- [ ] Resource usage optimized

## Maintenance Considerations

### Future Extensibility
- [How this feature can be extended]

### Technical Debt Assessment
- **New Debt:** [Any technical debt introduced]
- **Debt Reduction:** [Debt eliminated by this feature]

### Long-term Maintainability
- [Strategies for long-term maintenance]

## Recommendations Summary

### High Priority
1. [Recommendation 1]
2. [Recommendation 2]

### Medium Priority
1. [Recommendation 1]
2. [Recommendation 2]

### Low Priority
1. [Recommendation 1]
2. [Recommendation 2]

---
**Status:** Quality review complete
**Next Phase:** Final PRD Generation and Task Master Integration
```

---

## Session Coordination

### Handoff Protocol
Each agent should:
1. Complete their assigned template
2. Tag output with `@NextAgent` 
3. Include context summary for next agent
4. Wait for user approval before proceeding

### Quality Gates
- Requirements sign-off before architecture
- Architecture approval before integration planning
- Integration strategy approval before quality review
- Quality review before final PRD generation

### State Management
Each session maintains:
- Session ID: `YYYYMMDD_HHMMSS_feature-name`
- Agent completion status
- User approval status
- Integration with Task Master AI status