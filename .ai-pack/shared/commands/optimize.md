# Optimize Command

Analyzes and optimizes code for performance, bundle size, and efficiency.

## Usage

```bash
/optimize [target] [options]
```

## Arguments

- `target`: What to optimize (component, api, database, bundle, all)

## Options

- `--analyze`: Only analyze, don't apply changes
- `--focus <area>`: Focus area (speed, memory, bundle-size, database)
- `--aggressive`: Apply aggressive optimizations
- `--report`: Generate detailed optimization report

## Examples

```bash
# Optimize specific component
/optimize src/components/DataTable.tsx

# Analyze all performance issues
/optimize all --analyze --report

# Optimize bundle size
/optimize bundle --focus bundle-size

# Aggressive database optimization
/optimize database --aggressive

# API performance optimization
/optimize api --focus speed
```

## Optimization Areas

### Frontend Performance
- React component optimization
  - Memoization (React.memo, useMemo, useCallback)
  - Code splitting
  - Lazy loading
  - Virtual scrolling
- Bundle size reduction
  - Tree shaking
  - Dynamic imports
  - Dependency optimization
- Rendering performance
  - Reduce re-renders
  - Optimize reconciliation
  - Debounce/throttle

### Backend Performance
- Database optimization
  - Query optimization
  - Index suggestions
  - N+1 query detection
  - Connection pooling
- API optimization
  - Response caching
  - Compression
  - Rate limiting
  - Pagination
- Algorithm optimization
  - Time complexity reduction
  - Space complexity reduction
  - Async optimization

### Bundle Optimization
- Dependency analysis
- Unused code elimination
- Code splitting strategy
- Asset optimization

## Optimization Report

```markdown
# Optimization Report

## Current Performance Metrics
- Component render time: 145ms (Target: <100ms)
- Bundle size: 2.5MB (Target: <1MB)
- API response time: 350ms (Target: <200ms)
- Database query time: 180ms (Target: <100ms)

## Identified Issues

### High Priority
1. **Expensive re-renders in DataTable component**
   - Issue: Re-renders on every parent state change
   - Impact: 45ms render time
   - Solution: Add React.memo and optimize props

2. **Large moment.js bundle**
   - Issue: Importing entire moment.js library
   - Impact: 250KB to bundle
   - Solution: Switch to date-fns or day.js

3. **N+1 query in user endpoint**
   - Issue: Separate query for each user's posts
   - Impact: 150ms additional latency
   - Solution: Use JOIN or eager loading

### Medium Priority
4. **Unused CSS in bundle**
   - Impact: 80KB
   - Solution: Enable PurgeCSS

5. **Missing database index**
   - Impact: Slow user lookups
   - Solution: Add index on email column

## Recommendations

### Immediate Actions
- Implement React.memo for DataTable
- Replace moment.js with day.js
- Fix N+1 query with JOIN

### Short-term Improvements
- Enable code splitting
- Add database indexes
- Implement response caching

### Long-term Optimizations
- Consider virtual scrolling for lists
- Implement service worker for caching
- Migrate to edge functions

## Expected Impact
- Bundle size: 2.5MB → 1.2MB (52% reduction)
- Render time: 145ms → 65ms (55% improvement)
- API latency: 350ms → 180ms (49% improvement)
```

## Automated Optimizations

The command can automatically apply:
- Add React.memo where beneficial
- Convert to useCallback/useMemo
- Split large components
- Add lazy loading
- Optimize imports
- Add database indexes
- Implement caching

## AI Agents

Uses:
- `frontend-specialist`: Frontend optimizations
- `backend-specialist`: Backend optimizations
- `architect`: Overall optimization strategy
