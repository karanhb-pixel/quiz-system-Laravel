# Quiz System

## Overview
A comprehensive quiz management platform built with Laravel, featuring AI-powered evaluation, advanced analytics, question banks, and customizable quiz templates. Perfect for educational institutions, corporate training, and knowledge assessment.

## 🚀 Key Features

### 🤖 AI-Powered Quiz System
- **Intelligent Question Evaluation**: AI-powered assessment for fill-in-the-blank and coding questions
- **Smart Feedback**: Contextual feedback and scoring suggestions
- **Adaptive Difficulty**: Questions categorized by difficulty levels (Easy, Medium, Hard)

### 📊 Advanced Analytics Dashboard
- **Real-time Statistics**: Track quiz performance, completion rates, and user engagement
- **Question Performance Analysis**: Identify difficult questions and success patterns
- **Trend Analysis**: Monitor progress over time with interactive charts
- **Comprehensive Reports**: Detailed insights into quiz effectiveness

### 🏦 Question Bank Management
- **Mixed Question Types**: Support for Multiple Choice, Fill-in-the-Blank, and Code questions
- **Organized Storage**: Categorize and manage questions efficiently
- **Bulk Operations**: Import, export, and manage large question collections
- **Difficulty Classification**: Automatic difficulty assessment and filtering

### 📋 Quiz Templates
- **Customizable Templates**: Pre-configured quiz structures with specific distributions
- **Template Sharing**: Public templates for community use
- **Advanced Generation**: Smart question selection based on difficulty and type
- **Usage Tracking**: Monitor template popularity and effectiveness

### ⚡ Performance & Caching
- **Intelligent Caching**: Optimized question retrieval and quiz generation
- **Database Indexing**: High-performance queries for large datasets
- **Scalable Architecture**: Built for high-traffic educational platforms

## 🎯 User Features

### For Quiz Creators
- **Question Bank Creation**: Build comprehensive question libraries
- **Template Design**: Create reusable quiz templates
- **Analytics Access**: Monitor quiz performance and learner progress
- **Advanced Settings**: Configure time limits, question distribution, and scoring

### For Quiz Takers
- **Diverse Question Types**: Experience engaging multiple-choice, fill-blank, and coding challenges
- **AI-Powered Feedback**: Receive intelligent explanations and improvement suggestions
- **Progress Tracking**: View detailed performance history and improvement trends
- **Flexible Attempts**: Multiple attempt options with time management

### For Administrators
- **User Management**: Approve registrations and manage user access
- **System Analytics**: Global insights into platform usage and effectiveness
- **Content Moderation**: Oversee quiz templates and question banks
- **Performance Monitoring**: Track system health and user engagement

## 📖 How to Use

### Getting Started
1. **Register**: Create an account to access the platform
2. **Login**: Use your credentials to access the dashboard
3. **Choose Your Path**: Start as a quiz creator or taker

### 🏦 Question Bank Management
1. **Create Question Bank**: Navigate to "Question Banks" and create a new bank
2. **Add Questions**: Choose from three types:
   - **Multiple Choice**: Standard A/B/C/D format
   - **Fill-in-the-Blank**: AI-evaluated text responses
   - **Code Questions**: Programming challenges with syntax validation
3. **Organize**: Categorize questions by difficulty and topic

### 📋 Creating Quiz Templates
1. **Design Template**: Specify question distribution (MCQ: 40%, Fill-blank: 30%, Code: 30%)
2. **Set Parameters**: Configure time limits, difficulty ratios, and attempt limits
3. **Generate Quiz**: Use template to create randomized quizzes from your question bank
4. **Share Publicly**: Make templates available to other users

### 📊 Analytics Dashboard
1. **View Statistics**: Access comprehensive analytics for your question banks
2. **Monitor Performance**: Track completion rates, average scores, and trends
3. **Question Analysis**: Identify which questions need improvement
4. **User Insights**: See how learners perform across different topics

### 🎯 Taking AI-Powered Quizzes
1. **Select Quiz**: Choose from available quizzes or use templates
2. **Experience AI**: Get intelligent feedback on complex question types
3. **Review Results**: See detailed explanations and improvement suggestions
4. **Track Progress**: Monitor your learning journey over time

## 🛠️ Technical Architecture

### Core Technologies
- **Laravel 11**: Modern PHP framework with MVC architecture
- **Tailwind CSS**: Utility-first CSS framework for responsive design
- **Alpine.js**: Lightweight JavaScript framework for interactive components
- **MySQL/SQLite**: Robust database support with advanced querying
- **Pest**: Modern PHP testing framework for comprehensive test coverage

### Advanced Features
- **AI Integration**: Google Gemini API for intelligent question evaluation
- **Caching System**: Redis/file-based caching for optimal performance
- **Analytics Engine**: Real-time data processing and visualization
- **Template System**: Dynamic quiz generation with customizable parameters
- **Authorization**: Role-based access control with Laravel Policies

## 🚀 Installation & Setup

### System Requirements
- **PHP**: 8.2 or higher
- **Database**: MySQL 8.0+ or SQLite 3.0+
- **Web Server**: Apache/Nginx with mod_rewrite
- **Node.js**: 18+ (for asset compilation)
- **Composer**: Latest version for dependency management

### Quick Start
```bash
# Clone the repository
git clone <repository-url>
cd quiz-system

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Set up environment
cp .env.example .env
php artisan key:generate

# Configure database in .env file
# Run migrations
php artisan migrate

# Seed the database (optional)
php artisan db:seed

# Build assets
npm run build

# Start the development server
php artisan serve
```

### Environment Configuration
```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=quiz_system
DB_USERNAME=your_username
DB_PASSWORD=your_password

# AI Integration (Optional)
GEMINI_API_KEY=your_gemini_api_key

# Cache Configuration
CACHE_STORE=redis
# or
CACHE_STORE=file
```

### AI Features Setup
To enable AI-powered question evaluation:
1. Obtain a Google Gemini API key
2. Add it to your `.env` file
3. The system will automatically use AI evaluation for supported question types

## Support
If you have any questions or need help, feel free to reach out. We're here to assist you!

## License
This project is open-source and available for anyone to use and modify.

---
