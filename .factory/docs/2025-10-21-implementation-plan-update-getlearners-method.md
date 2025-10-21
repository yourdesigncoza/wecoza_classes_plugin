# Implementation Plan: Update `getLearners()` Method

## Overview
Replace the static demo data in `getLearners()` method with real PostgreSQL database queries following the existing controller patterns.

## Database Schema
- **Table**: `public.learners`
- **Key fields**: `id`, `first_name`, `second_name`, `initials`, `surname`, `sa_id_no`, `passport_number`, `city_town_id`, `province_region_id`, `postal_code`

## Implementation Steps

### 1. Database Query Implementation
- Use `DatabaseService::getInstance()` pattern (consistent with `getClients()`, `getSites()`, etc.)
- Query learners table with proper error handling
- Format full names combining name fields for display

### 2. Data Format
```php
[
    'id' => (int)$row['id'],
    'name' => sanitize_text_field($formattedName),
    'id_number' => sanitize_text_field($idNumber)
]
```

### 3. Name Construction Logic
- Primary: `first_name + ' ' + surname`
- If available: include `second_name` and `initials`
- Fallback ID: use `sa_id_no` or `passport_number`

### 4. Error Handling
- Wrap in try-catch block
- Return empty array on database failure
- Log errors for debugging

### 5. Frontend Compatibility
- Maintain current structure (`id`, `name`, `id_number`)
- Data populates `#add_learner` select dropdown
- Consistent with existing JavaScript expectations

## Code Pattern
Follow the exact pattern used in other methods like `getClients()` and `getSites()` for consistency in error handling, data sanitization, and array formatting.