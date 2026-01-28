# 🧠 Advanced AI Quiz System

A high-performance, real-time quiz platform powered by Laravel and Google Gemini AI. This system allows for automated question generation, smart code evaluation, and instant user feedback.

---

## 🚀 Advanced Features & Optimizations

### 🤖 AI Core (Google Gemini)

- **Background Generation**: Questions are generated using background workers (`Laravel Queues`), ensuring the website stays fast while the AI works.
- **Smart Code Evaluation**: Beyond simple string matching, the AI understands logical correctness in coding answers.
- **Configurable Models**: Easily switch between `gemini-2.0-flash-lite`, `gemini-1.5-pro`, or lateest models via `.env`.
- **API Protection**: Built-in rate limiting (`Throttle`) to protect your Gemini API quota from abuse.

### ⚡ Performance & Scalability

- **Real-Time WebSockets**: Integrated with **Laravel Reverb**. Users see AI generation progress and completion alerts instantly without refreshing the page.
- **Optimized Database**:
    - Full indexing on heavy columns (`slug`, `user_id`, `category_id`).
    - Smart pagination on all listing pages.
    - N+1 query detection enabled for elite-level development.
- **Redis Infrastructure**: Designed to use Redis for blazing-fast Sessions, Caching, and Queues.

### 🛠️ Infrastructure (Cloud Ready)

- **Railway Optimized**: Pre-configured for deployment with separate services for the Web UI, Background Workers, and WebSocket Server.
- **Robust Configuration**: Crash-proof environment variables designed for modern CI/CD pipelines.

---

## 📖 Features

### For Users

- **Instant Quizzes**: Attempt quizzes across various categories.
- **Real-time Feedback**: Watch the AI build your personalized quiz in real-time.
- **Progress Tracking**: Detailed history of attempts and score breakdowns.

### For Admins / Instructors

- **AI Question Bank**: Instant generation of MCQ, Fill-in-the-blank, and Coding questions.
- **Instructor Approval**: Multi-tier user system with admin oversight.
- **Automated Grading**: Hands-free evaluation for complex coding tasks.

---

## 🛠️ Technical Stack & Architecture

### **Core Backend**

- **Framework**: [Laravel 12.x](https://laravel.com) (Released 2025)
- **Runtime**: PHP 8.4+ (Optimized for typed properties and JIT compilation)
- **Database**:
    - **SQLite**: Local development (disk-based, no-config)
    - **Postgres/MySQL**: Production-ready configuration.
- **Queue/Asynchronous**:
    - **Connection**: Database (Local) / Redis (Production)
    - **Worker**: Dedicated background process for heavy AI lifting.
- **Cache & Sessions**: Optimized for high-speed RAM-based storage.

### **AI Engineering**

- **LLM**: Google Gemini `flash-lite-2.5` & `flash-lite-2.0` (Configurable)
- **Integration**: `google-gemini-php/laravel` for secure API handling.
- **Robustness**:
    - **Circuit Breaker Pattern**: Basic retry logic for AI API 429 errors.
    - **JSON Output Validation**: Automated regex-based cleansing of AI response Markdown code blocks.

### **Frontend & UX**

- **Real-time**: [Laravel Reverb](https://reverb.laravel.com) (High-performance WebSocket server)
- **Engine**: [Vite 6.x](https://vitejs.dev/) for lightning-fast asset bundling.
- **Style**: [Tailwind CSS 3.4+](https://tailwindcss.com/) for a sleek, premium UI.
- **State Management**: Alpine.js for lightweight, reactive components.

### **Quality & Optimization**

- **Database Indexing**: Optimized for `O(n)` to `O(log n)` read performance on `slug` searches.
- **N+1 Prevention**: Integrated `BeyondCode Query Detector` to maintain high performance.
- **Rate Limiting**: Custom `ai_generations` throttle to protect instructor balances.

---

## 🛠️ Required Services & Setup

1. **Google Gemini API**:
    - Get an API key from [Google AI Studio](https://aistudio.google.com/).
    - Add to `.env`: `GEMINI_API_KEY=your_key` and `GEMINI_MODEL=gemini-2.0-flash-lite`.

2. **Redis (Recommended)**:
    - Used for high-speed queues and session management.
    - Set `QUEUE_CONNECTION=redis` in production.

3. **Broadcasting (WebSockets)**:
    - Uses **Laravel Reverb** (included).
    - Requires `ext-pcntl` and `ext-posix` on your server.

---

## 💻 Local Installation

### Requirements

- PHP 8.4+
- Composer
- Node.js & NPM
- SQLite (or MySQL/PostgreSQL)
- Redis (Optional but recommended)

### Steps

1. **Clone & Install**:
    ```bash
    composer install
    npm install && npm run build
    ```
2. **Environment**:
    ```bash
    copy .env.example .env
    php artisan key:generate
    ```
3. **Database**:
    ```bash
    php artisan migrate --seed
    ```
4. **Run the Application** (Require 3 terminal windows):
    - **Terminal 1**: `php artisan serve` (Web Server)
    - **Terminal 2**: `php artisan queue:work` (AI Background Worker)
    - **Terminal 3**: `php artisan reverb:start` (Real-time Socket Server)

---

## 🚢 Deployment (Railway Examples)

For a professional setup, deploy as three separate services using the same repository:

1. **Quiz-Web**: Default Laravel server.
2. **Quiz-Worker**: Custom Start Command: `php artisan queue:work`.
3. **Quiz-Websockets**: Custom Start Command: `php artisan reverb:start --host=0.0.0.0 --port=$PORT`.

---

## 🛡️ License

Open-source under the MIT License. Developed with ❤️ for the Laravel community.
