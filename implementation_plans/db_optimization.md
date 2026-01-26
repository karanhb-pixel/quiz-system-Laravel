# Implementation Plan: Database Performance Optimization

This plan breaks down Task #1 from the Roadmap into small, safe steps with verification checkpoints.

## Phase 1: Database Indexing

**Goal**: Make search and relationship lookups instant.

- [ ] **1.1 Create Migration for Indexes**
    - Columns to index:
        - `categories`: `slug`
        - `quizzes`: `slug`, `user_id`, `category_id`
        - `questions`: `quiz_id`, `category_id`, `topic`
        - `results`: `user_id`, `quiz_id`
    - _Checkpoint_: Create migration file but do not run yet. Review for any duplicate indexes.

- [ ] **1.2 Apply and Verify**
    - Run `php artisan migrate`.
    - _Checkpoint_: Use `psql` or a tool to verify the indexes exist in the live database.

---

## Phase 2: Pagination

**Goal**: Prevent "Memory Exhaustion" when the app grows.

- [ ] **2.1 Update Controllers**
    - Update `QuizController@index` to use `paginate(15)`.
    - Update `CategoryController@index` to use `paginate(12)`.
    - Update `QuizController@showByCategory` to use `paginate(10)`.
    - _Checkpoint_: Verify that the variables returned are now `LengthAwarePaginator` instances.

- [ ] **2.2 Update Blade Views**
    - Add `{{ $items->links() }}` to the bottom of the tables.
    - Style the pagination links (may need `php artisan vendor:publish --tag=laravel-pagination`).
    - _Checkpoint_: Click through Page 1, Page 2 to ensure data matches the URL.

---

## Phase 3: Query Caching

**Goal**: Stop redundant hits on the database for static data.

- [ ] **3.1 Implement Category Cache**
    - Wrap Category fetching in `Cache::remember('categories_all', 3600, ...)` inside `UserController` and `QuizController`.
    - _Checkpoint_: Refresh the dashboard; the first load will log a query, the second load should be silent in DB logs.

- [ ] **3.2 Cache Invalidation**
    - Add logic to `CategoryController@store` and `destroy` to run `Cache::forget('categories_all')`.
    - _Checkpoint_: Create a new category and verify it appears immediately in the dropdown without waiting for the cache to expire.

---

## Phase 4: N+1 Monitoring Setup

**Goal**: Automatic "Seatbelt" for future code changes.

- [ ] **4.1 Install Monitoring Tool**
    - Install `beyondcode/laravel-query-detector` as a dev dependency.
    - _Checkpoint_: Verify it is NOT installed in production (check `composer.json` require-dev).

- [ ] **4.2 Configure and Test**
    - Set it to "Log" or "Alert" in development.
    - _Checkpoint_: Intentionally write a bad loop in a view and see if the tool catches it.
