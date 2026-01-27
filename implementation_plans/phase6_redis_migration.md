# Phase 6: Infrastructure - High-Performance Redis Migration

## Overview

Currently, your app uses the database for **Sessions**, **Caching**, and **Queues**. While this works, it adds unnecessary load to your database. Moving these to **Redis** will:

1. Speed up user sessions and logins.
2. Make the background generator pick up jobs instantly (zero latency).
3. Free up database connections for actual quiz data.

---

## Phase 6.1: Setup Redis on Railway

### Step 6.1.1: Add Redis Service

1. Go to your Railway Project.
2. Click **+ New** -> **Database** -> **Redis**.
3. Railway will add a Redis instance to your canvas.

### Step 6.1.2: Connect the Services

1. Click on the **Redis** service box.
2. Go to **Variables**.
3. Look for the "Connect" button or just reference the Redis host/port/password in your main app.
4. **Best Way**: Railway usually automatically injects `REDIS_URL` or `REDISHOST` if you link the services.

---

## Phase 6.2: Configure Laravel for Redis

Laravel needs a small package to "speak" to Redis efficiently.

### Step 6.2.1: Install Redis Client

Run this command in your local terminal:

```bash
composer require predis/predis
```

### Step 6.2.2: Update `.env` (Local & Railway)

Change your drivers from `database` to `redis`:

```env
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Connection settings (Railway will provide these)
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

## Phase 6.3: Verification Checklist

- [ ] Redis service added in Railway
- [ ] `predis/predis` package installed
- [ ] `CACHE_STORE=redis`
- [ ] `SESSION_DRIVER=redis`
- [ ] `QUEUE_CONNECTION=redis`
- [ ] Background jobs still process correctly via Redis
- [ ] Site speed improvement verified

---

## Next Steps

After moving to Redis, we can enable **FrankenPHP Worker Mode** (Octane) which uses Redis's speed to keep the entire Laravel app in memory for near-instant response times.
