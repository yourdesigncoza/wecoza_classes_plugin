# Calendar Caching Optimization Strategy

## Current State Analysis

### Calendar Data Flow Architecture

**Frontend (wecoza-calendar.js):**
- Uses FullCalendar library with dual event sources
- Makes 2 separate AJAX calls per calendar initialization:
  - `get_calendar_events` - fetches class schedule data
  - `get_public_holidays` - fetches holiday data for current year
- Automatically refreshes holiday data when navigating to different years

**Backend (Controllers):**
- `ClassController::getCalendarEventsAjax()` - processes class schedule data
- `PublicHolidaysController::handlePublicHolidaysAjax()` - provides static holiday data
- Complex event generation from schedule data stored in PostgreSQL JSONB format

### Current Performance Bottlenecks

**Database Performance Issues:**
- No caching implemented despite cache configuration in `config/app.php`
- Each calendar load triggers full database query to PostgreSQL
- Complex schedule data processing happens on every request
- No query optimization for calendar event generation

**Frontend Performance Issues:**
- FullCalendar makes separate AJAX calls for class events and holidays
- Holiday data is refetched every year navigation (not cached)
- No client-side caching of previously loaded events
- Calendar reloads all data on every view change

**Data Processing Bottlenecks:**
- Complex `generateEventsFromV2Pattern()` method processes schedule data in real-time
- Schedule data JSON parsing happens on every request
- No pre-computed event storage for frequently accessed calendars

### Missing Caching Mechanisms

**Backend Caching:**
- WordPress transients not implemented despite configuration
- No database query result caching
- No processed event data caching
- Holiday data regenerated on every request

**Frontend Caching:**
- No browser caching of event data
- No localStorage or sessionStorage utilization
- FullCalendar doesn't cache event sources between page loads

## Recommended Optimization Plan

### Phase 1: Implement Backend Caching (High Priority)

#### 1.1 Add WordPress Transient Caching
- [ ] Cache processed calendar events per class (30 minutes TTL)
- [ ] Cache public holidays per year (24 hours TTL)
- [ ] Cache database query results (15 minutes TTL)
- [ ] Implement cache invalidation on class updates

#### 1.2 Optimize Database Queries
- [ ] Add database indexes for calendar-related queries
- [ ] Implement query result caching in DatabaseService
- [ ] Pre-compute and cache complex schedule calculations
- [ ] Add query optimization for date range filtering

#### 1.3 Controller-Level Caching
- [ ] Add caching layer to `ClassController::getCalendarEventsAjax()`
- [ ] Implement cache key generation based on date ranges
- [ ] Add cache warming for frequently accessed calendars
- [ ] Create cache management utilities

### Phase 2: Frontend Optimization (Medium Priority)

#### 2.1 Implement Client-Side Caching
- [ ] Add browser localStorage for holiday data
- [ ] Cache processed event data between sessions
- [ ] Implement smart refresh logic to avoid unnecessary API calls
- [ ] Add cache expiration management

#### 2.2 Optimize FullCalendar Configuration
- [ ] Configure event source caching
- [ ] Implement selective refresh instead of full reload
- [ ] Add loading states and error handling improvements
- [ ] Optimize AJAX request batching

#### 2.3 JavaScript Performance Improvements
- [ ] Implement debounced calendar navigation
- [ ] Add lazy loading for large date ranges
- [ ] Optimize event rendering performance
- [ ] Add client-side event filtering

### Phase 3: Data Structure Optimization (Low Priority)

#### 3.1 Pre-compute Calendar Events
- [ ] Add background job to generate calendar events
- [ ] Store pre-computed events in cache/database
- [ ] Implement incremental updates for schedule changes
- [ ] Create event data normalization

#### 3.2 Optimize Schedule Data Processing
- [ ] Simplify complex schedule generation logic
- [ ] Add event data normalization
- [ ] Implement efficient date range queries
- [ ] Create schedule data indexing

#### 3.3 Advanced Caching Strategies
- [ ] Implement multi-level caching (memory + database)
- [ ] Add cache sharding for large datasets
- [ ] Create cache analytics and monitoring
- [ ] Implement cache warming strategies

## Implementation Strategy

### Priority Order
1. **Backend Transient Caching** - Immediate performance gains
2. **Database Query Optimization** - Reduce database load
3. **Frontend Client-Side Caching** - Better user experience
4. **Pre-computed Event Storage** - Long-term scalability

### Performance Metrics
- [ ] Measure current calendar load times
- [ ] Track database query performance
- [ ] Monitor AJAX request frequency
- [ ] Measure memory usage improvements

### Testing Strategy
- [ ] Create performance benchmarks
- [ ] Test with large datasets
- [ ] Validate cache invalidation logic
- [ ] Monitor cache hit/miss ratios

### Rollback Plan
- [ ] Maintain current system as fallback
- [ ] Implement feature flags for caching
- [ ] Create cache disable mechanism
- [ ] Monitor error rates during rollout

## Expected Performance Improvements

### Phase 1 (Backend Caching)
- **Calendar Load Time**: 60-80% reduction
- **Database Queries**: 70-90% reduction
- **Server Response Time**: 50-70% improvement

### Phase 2 (Frontend Optimization)
- **User Experience**: Instant navigation between dates
- **Network Requests**: 50-70% reduction
- **Page Load Speed**: 40-60% improvement

### Phase 3 (Data Optimization)
- **Scalability**: Support for 10x more concurrent users
- **Memory Usage**: 30-50% reduction
- **Long-term Performance**: Maintains speed as data grows

## Implementation Notes

### Technical Considerations
- WordPress transient API for server-side caching
- Browser localStorage for client-side caching
- Cache key generation based on user context and date ranges
- Proper cache invalidation on data updates

### Dependencies
- Existing WordPress transient system
- Browser localStorage support
- FullCalendar configuration options
- PostgreSQL query optimization

### Monitoring
- Cache hit/miss ratios
- Database query performance
- User experience metrics
- Error rate monitoring

---

**Last Updated**: 2025-07-04
**Status**: Planning Phase
**Next Steps**: Begin Phase 1 implementation with backend caching