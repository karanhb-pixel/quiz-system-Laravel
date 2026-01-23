# Quiz System - Deployment Guide

## 🚀 AI Configuration & Deployment

### Prerequisites
- PHP 8.1+
- Laravel 10+
- Database (MySQL/PostgreSQL/SQLite)
- Google Gemini API Key

### Environment Setup

#### 1. Environment Variables
Copy `.env.example` to `.env` and configure the following AI-related variables:

```env
# AI Configuration
GEMINI_API_KEY=your_actual_gemini_api_key_here
AI_GENERATION_ENABLED=true
AI_QUALITY_THRESHOLD=0.8
AI_RATE_LIMIT_PER_HOUR=100
AI_MAX_QUESTIONS_PER_REQUEST=10
AI_CACHE_TTL=3600

# Rate Limiting
AI_RATE_LIMITING_ENABLED=true
AI_MAX_ATTEMPTS_PER_MINUTE=10
AI_MAX_ATTEMPTS_PER_HOUR=100
AI_BLOCK_DURATION_MINUTES=15

# Caching
AI_CACHE_ENABLED=true
AI_CACHE_TTL=3600

# Monitoring
AI_LOG_REQUESTS=true
AI_LOG_ERRORS=true
AI_TRACK_METRICS=true
```

#### 2. Google Gemini API Setup
1. Visit [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Create a new API key
3. Add the key to your `.env` file as `GEMINI_API_KEY`

### Database Migration

Run the AI metadata migration:
```bash
php artisan migrate
```

This adds the following columns to question tables:
- `generated_by_ai` (boolean)
- `needs_review` (boolean)
- `quality_score` (integer, nullable)
- `generation_metadata` (JSON, nullable)

### Health Check

After deployment, verify AI functionality:
```bash
curl http://your-domain.com/ai-health-check
```

Expected response:
```json
{
  "status": "ok",
  "ai_generation_enabled": true,
  "api_key_configured": true,
  "rate_limiting_enabled": true,
  "cache_enabled": true,
  "timestamp": "2024-01-22T13:00:00.000000Z"
}
```

### Production Considerations

#### Rate Limiting
- Default: 10 requests/minute, 100 requests/hour per user
- Blocked users are prevented from AI generation for 15 minutes
- Monitor logs for abuse patterns

#### Caching
- AI-generated questions are cached for 1 hour by default
- Reduces API costs and improves response times
- Cache keys include topic, difficulty, and context

#### Monitoring
- All AI requests are logged to Laravel logs
- Track success rates, response times, and error patterns
- Use `/ai-questions/metrics` dashboard for insights

#### Security
- API keys are encrypted in environment variables
- Rate limiting prevents abuse
- Input validation on all AI generation requests

### Troubleshooting

#### Common Issues

1. **"AI generation is currently disabled"**
   - Check `AI_GENERATION_ENABLED=true` in `.env`

2. **"Gemini API key is not configured"**
   - Verify `GEMINI_API_KEY` is set in `.env`
   - Ensure the key is valid and has proper permissions

3. **Rate limiting errors**
   - User has exceeded limits
   - Check rate limiting configuration
   - Clear cache if needed: `php artisan cache:clear`

4. **Quality threshold issues**
   - Adjust `AI_QUALITY_THRESHOLD` (0.0-1.0)
   - Lower values allow more questions, higher values enforce stricter quality

#### Performance Tuning

```env
# Increase for high-traffic sites
AI_RATE_LIMIT_PER_HOUR=500
AI_MAX_ATTEMPTS_PER_MINUTE=50

# Decrease for cost control
AI_CACHE_TTL=7200  # 2 hours
AI_QUALITY_THRESHOLD=0.9  # Stricter quality
```

### Backup & Recovery

#### Database Backups
Include AI metadata in regular backups:
```bash
# Backup with AI data
mysqldump -u username -p database > backup_with_ai.sql
```

#### Cache Management
```bash
# Clear AI caches
php artisan cache:clear

# Clear specific AI caches
php artisan tinker
>>> Cache::forget('ai_rate_limit:*');
>>> Cache::forget('ai_generation:*');
```

### Monitoring & Analytics

#### Key Metrics to Monitor
- AI API success rate (>95% target)
- Average response time (<3 seconds target)
- Questions generated per day
- Cache hit rate (>70% target)
- Rate limiting blocks (should be minimal)

#### Log Analysis
```bash
# Check AI request logs
tail -f storage/logs/laravel.log | grep "AI Request"
```

### Scaling Considerations

#### Horizontal Scaling
- Multiple application servers can share the same cache/redis instance
- Rate limiting works across server instances with shared cache
- Database handles concurrent AI metadata updates

#### API Limits
- Monitor Google Gemini API quotas
- Implement exponential backoff for rate limits
- Consider API key rotation for high-volume deployments

### Support

For deployment issues:
1. Check health endpoint: `/ai-health-check`
2. Review Laravel logs: `storage/logs/laravel.log`
3. Verify environment variables
4. Test API connectivity manually

---

**Deployment Checklist:**
- [ ] Environment variables configured
- [ ] Database migrated
- [ ] API key valid
- [ ] Health check passes
- [ ] AI generation tested
- [ ] Rate limiting configured
- [ ] Monitoring enabled
- [ ] Backup procedures in place