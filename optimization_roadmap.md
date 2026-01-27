# Optimization Roadmap & Future Tasks

## 1. Database Performance ✅ COMPLETED

- [x] **Database Indexing**: Add indexes to `slug`, `user_id`, `category_id`, and `quiz_id` columns.
- [x] **Query Caching**: Implement Redis/Memcached for static data like Category lists.
- [x] **Pagination**: Replace `.get()` with `.paginate()` on heavy data listing pages.
- [x] **N+1 Monitoring**: Install `laravel-query-detector` to identify inefficient queries.

## 2. AI Stability & Performance (Gemini) ✅ COMPLETED

- [x] **Background Queues**: Move Gemini question generation and code evaluation to Laravel background jobs.
- [x] **Generation Status**: Real-time feedback for users during background AI generation.
- [ ] **Evaluation Caching**: Cache AI verdicts for identical code submissions using input hashing.
- [ ] **Circuit Breaker**: Implement a fallback mechanism for Gemini rate limits (429 errors).

## 3. Infrastructure & Scalability

- [ ] **FrankenPHP Worker Mode**: Enable worker mode in Railway/FrankenPHP for 3x faster response times.
- [ ] **Redis Backend**: Switch Session and Cache drivers from `database` to `redis`.
- [ ] **Static Asset Preloading**: Configure Vite/FrankenPHP to push assets to the browser early.

## 4. Security & Robustness

- [ ] **Rate Limiting**: Add `Throttle` middleware to AI-heavy routes to prevent credit exhaustion.
- [ ] **API Resources**: Use `JsonResource` for consistent and secure data exposure.
- [ ] **Automated Testing**: Write Pest/PHPUnit tests for core scoring and AI parsing logic.

## 5. User Experience (UX)

- [ ] **Frontend Lazy Loading**: Optimize image and heavy asset loading for mobile users.
- [ ] **Real-time Feedback**: Use Pusher or Laravel Reverb for real-time AI generation progress updates.
