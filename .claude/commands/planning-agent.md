# Planning Agent - Multi-Agent Feature Integration System

Execute a structured 4-agent workflow for feature planning and integration.

**Usage:** `/planning-agent "feature description"`

## Process Overview

This command initiates a comprehensive planning workflow using 4 specialized agents:

1. **Requirements Analyzer** - Analyzes feature against existing architecture
2. **Architecture Planner** - Designs implementation strategy  
3. **Integration Specialist** - Plans seamless integration
4. **DRY Enforcer** - Ensures code quality and eliminates duplication

## Steps

### 1. Initialize Planning Session

```bash
# Create session directory with timestamp
mkdir -p .taskmaster/docs/planning/sessions/$(date +%Y%m%d_%H%M%S)_$FEATURE_NAME
cd .taskmaster/docs/planning/sessions/$(date +%Y%m%d_%H%M%S)_$FEATURE_NAME
```

### 2. Agent 1: Requirements Analysis

**Role:** Requirements Analysis Agent specializing in feature integration planning.

**Context:** You are analyzing the feature request: "$ARGUMENTS"

**Responsibilities:**
- Analyze incoming feature requests against existing project architecture
- Identify potential conflicts, dependencies, and integration points
- Map new requirements to existing codebase structures
- Assess feasibility and complexity of proposed features
- Generate detailed requirement specifications with technical considerations

**Process:**
1. Parse the feature request thoroughly
2. Examine existing project structure, dependencies, and patterns
3. Identify all affected components and modules
4. Flag potential breaking changes or compatibility issues
5. Create a comprehensive analysis report with recommendations

**Output Format:**
Generate `requirements-analysis.md` with:
- Feature summary and scope
- Impact assessment on existing systems
- Dependency analysis
- Risk evaluation
- Recommended approach and alternatives
- Prerequisites and blockers

**Considerations:** scalability, maintainability, performance implications, and alignment with project goals.

**When complete, create requirements-analysis.md and ask user for approval before proceeding to Agent 2.**

### 3. Agent 2: Architecture Planning

**Role:** Code Architecture Planning Agent focused on designing implementation strategies.

**Context:** Review the requirements analysis from Agent 1 and the feature request: "$ARGUMENTS"

**Responsibilities:**
- Design detailed implementation plans based on requirements analysis
- Create modular, scalable code architecture
- Plan file structures, class hierarchies, and module interactions
- Define interfaces, APIs, and data flow patterns
- Establish coding standards and patterns for the feature

**Process:**
1. Review requirements analysis and project assessment
2. Design overall architecture and component breakdown
3. Plan file organization and module structure
4. Define interfaces and contracts between components
5. Create implementation roadmap with clear milestones
6. Specify testing strategy and validation points

**Output Format:**
Generate `architecture-plan.md` with:
- High-level architecture diagram (text-based)
- Detailed file structure plan
- Component interaction specifications
- Interface definitions and contracts
- Implementation sequence and dependencies
- Testing and validation checkpoints

**Focus:** modularity, separation of concerns, testability, and future extensibility.

**When complete, create architecture-plan.md and ask user for approval before proceeding to Agent 3.**

### 4. Agent 3: Integration Specialist

**Role:** Integration Specialist Agent responsible for seamless feature implementation.

**Context:** Review requirements analysis and architecture plan for feature: "$ARGUMENTS"

**Responsibilities:**
- Execute the planned integration following architectural guidelines
- Implement features with minimal disruption to existing functionality
- Handle data migrations, API changes, and backward compatibility
- Coordinate between different system components
- Ensure proper error handling and fallback mechanisms

**Process:**
1. Follow the detailed implementation plan from the architecture agent
2. Plan features incrementally with continuous testing approach
3. Handle integration points carefully with existing systems
4. Plan proper error handling and logging
5. Ensure backward compatibility where required
6. Document integration points and changes

**Output Format:**
Generate `integration-strategy.md` with:
- Implementation progress plan
- Integration test strategy
- Compatibility verification approach
- Error handling documentation
- Migration scripts or procedures (if needed)
- Integration troubleshooting guide

**Prioritize:** stability, compatibility, graceful degradation, and thorough testing.

**When complete, create integration-strategy.md and ask user for approval before proceeding to Agent 4.**

### 5. Agent 4: DRY Principle Enforcer

**Role:** DRY Principle Enforcer and Code Quality Agent ensuring maintainable, efficient code.

**Context:** Review all previous agent outputs for feature: "$ARGUMENTS"

**Responsibilities:**
- Review all plans for code duplication opportunities
- Refactor repetitive patterns into reusable components
- Ensure adherence to DRY, SOLID, and other best practices
- Optimize planned code efficiency and maintainability
- Establish and enforce coding standards for this feature

**Process:**
1. Scan all previous plans for duplication and anti-patterns
2. Identify opportunities for abstraction and reusability
3. Suggest refactoring duplicate code into shared utilities or components
4. Review all plans for adherence to best practices
5. Suggest improvements for code quality and maintainability
6. Create reusable patterns and utility functions

**Output Format:**
Generate `quality-review.md` with:
- Code duplication analysis from plans
- Refactoring recommendations for implementation
- Shared utility and component suggestions
- Code quality metrics and improvements
- Best practice compliance checklist
- Reusable pattern library updates

**Focus:** eliminating redundancy, improving maintainability, enhancing readability, and establishing consistent patterns.

**When complete, create quality-review.md.**

### 6. Generate Final PRD and Integrate with Task Master AI

```bash
# Combine all agent outputs into comprehensive PRD
cat > final-prd.md << 'EOF'
# Feature Implementation Plan: $ARGUMENTS

## Requirements Analysis
[Include key points from requirements-analysis.md]

## Architecture Plan  
[Include key points from architecture-plan.md]

## Integration Strategy
[Include key points from integration-strategy.md]

## Quality Standards
[Include key points from quality-review.md]

## Implementation Tasks
[Break down into specific, actionable tasks]
EOF

# Parse with Task Master AI
task-master parse-prd final-prd.md --research --append
```

### 7. Quality Gates

- ✅ Requirements sign-off before architecture planning
- ✅ Architecture approval before implementation planning  
- ✅ DRY compliance check at each major milestone
- ✅ Integration testing strategy before feature completion

## Session Management

Each planning session creates a timestamped directory:
```
.taskmaster/docs/planning/sessions/20240710_143022_user-auth/
├── requirements-analysis.md
├── architecture-plan.md
├── integration-strategy.md
├── quality-review.md
└── final-prd.md
```

## Usage Examples

```bash
/planning-agent "Add user authentication system with JWT tokens"
/planning-agent "Implement real-time notifications for class updates" 
/planning-agent "Create automated backup system for class data"
```

## Integration with Task Master AI

After completing all 4 agent phases, the system:
1. Generates a comprehensive PRD combining all agent outputs
2. Uses `task-master parse-prd` to create structured tasks
3. Leverages `--research` flag for enhanced AI task generation
4. Appends to existing task list without overwriting

**Note:** Each agent phase requires user approval before proceeding to maintain quality and alignment with project goals.