# Quiz System - Technical Documentation

## Overview
This document provides a detailed technical overview of the Quiz System, including its architecture, data flow, and user roles.

## Architecture
The Quiz System is built using the Laravel framework, which follows the Model-View-Controller (MVC) architectural pattern. This pattern separates the application logic into three interconnected components:

- **Model**: Handles data and business logic.
- **View**: Handles the user interface and presentation.
- **Controller**: Handles user input and interacts with the Model to render the View.

## Data Flow

### User Registration and Authentication
1. **Registration**: Users register by providing their details. The data is validated and stored in the database.
2. **Login**: Users log in using their credentials. The system authenticates the user and starts a session.
3. **Authorization**: The system checks the user's role (user or admin) and grants access to appropriate features.

### Quiz Creation
1. **Input**: Users provide quiz details (title, category) and questions (question text, options, correct answer).
2. **Validation**: The system validates the input data to ensure it meets the required format and constraints.
3. **Storage**: Validated data is stored in the database. The system generates a unique slug for the quiz to create SEO-friendly URLs.
4. **Confirmation**: Users receive confirmation that their quiz has been created successfully.

### Quiz Attempt
1. **Selection**: Users select a quiz to attempt from the list of available quizzes.
2. **Presentation**: The system retrieves the quiz and its questions from the database and presents them to the user.
3. **Submission**: Users submit their answers. The system validates the submission and calculates the score.
4. **Storage**: The system stores the quiz attempt and the user's score in the database.
5. **Feedback**: Users receive feedback on their performance, including their score and correct answers.

### Quiz Management
1. **Retrieval**: The system retrieves the user's quiz attempts from the database.
2. **Presentation**: The system presents the quiz attempts in a user-friendly format, including the quiz title, score, and date.
3. **Analysis**: Users can analyze their performance and track their progress over time.

## 🗄️ Database Schema

### Core Tables

#### Users
- **id**: Primary key
- **name**: User's full name
- **email**: Unique email address
- **password**: Bcrypt hashed password
- **role**: User role (user/admin)
- **email_verified_at**: Email verification timestamp

#### Categories
- **id**: Primary key
- **name**: Category name
- **slug**: SEO-friendly URL slug
- **description**: Category description
- **creator**: User who created the category

#### Quizzes
- **id**: Primary key
- **title**: Quiz title
- **slug**: SEO-friendly URL slug
- **user_id**: Foreign key to Users
- **category_id**: Foreign key to Categories
- **description**: Quiz description
- **time_limit**: Optional time limit in minutes

### Advanced Features Tables

#### Question Banks
- **id**: Primary key
- **name**: Question bank name
- **slug**: SEO-friendly URL slug
- **description**: Bank description
- **user_id**: Owner (foreign key to Users)
- **category_id**: Category (foreign key to Categories)
- **questions_per_quiz**: Default questions per quiz

#### Question Types (Polymorphic)
- **mcq_bank_questions**: Multiple choice questions
  - question_text, option_a, option_b, option_c, option_d, correct_answer, points, difficulty
- **fill_blank_bank_questions**: Fill-in-the-blank questions
  - question_text, expected_answer, evaluation_hints, case_sensitive, points, difficulty
- **code_bank_questions**: Programming questions
  - question_text, expected_code, evaluation_criteria, language, points, difficulty

#### Quiz Templates
- **id**: Primary key
- **name**: Template name
- **description**: Template description
- **user_id**: Creator (foreign key to Users)
- **question_bank_id**: Source bank (foreign key to Question Banks)
- **config**: JSON configuration (distribution, limits, settings)
- **is_public**: Public visibility flag

#### Quiz Attempts & Analytics
- **bank_quiz_attempts**: Quiz attempt records
  - user_id, question_bank_id, total_questions, total_points, points_earned, score_percentage, completed_at
- **quiz_attempt_responses**: Detailed responses
  - attempt_id, question_type, question_id, user_answer, is_correct, points_earned, ai_feedback, ai_confidence
- **quiz_from_templates**: Template-generated quizzes
  - quiz_template_id, bank_quiz_attempt_id, selected_questions (JSON)

## User Roles

### User
- **Create Quizzes**: Users can create their own quizzes.
- **Attempt Quizzes**: Users can attempt quizzes created by others.
- **View Results**: Users can view their quiz results and track their progress.
- **Manage Profile**: Users can update their profile information.

### Admin
- **Manage Users**: Admins can approve or reject user registrations.
- **View Requests**: Admins can see a list of users waiting for approval.
- **Manage Quizzes**: Admins can manage all quizzes, including those created by other users.
- **Manage Categories**: Admins can manage all categories, including those created by other users.

## 🔗 API Endpoints

### Authentication
- **POST /register**: Register a new user
- **POST /login**: Authenticate user
- **POST /logout**: End user session
- **GET /profile**: Get user profile
- **PATCH /profile**: Update user profile

### Question Bank Management
- **GET /question-banks**: List user's question banks
- **POST /question-banks**: Create new question bank
- **GET /question-banks/{bank}**: Show question bank details
- **PUT /question-banks/{bank}**: Update question bank
- **DELETE /question-banks/{bank}**: Delete question bank

### Question Management (Nested)
- **GET /question-banks/{bank}/questions/create/{type}**: Create question form
- **POST /question-banks/{bank}/questions**: Store new question
- **GET /question-banks/{bank}/questions/{question}/edit/{type}**: Edit question form
- **PUT /question-banks/{bank}/questions/{question}/{type}**: Update question
- **DELETE /question-banks/{bank}/questions/{question}/{type}**: Delete question

### Quiz Templates
- **GET /quiz-templates**: List available templates
- **POST /quiz-templates**: Create new template
- **GET /quiz-templates/{template}**: Show template details
- **PUT /quiz-templates/{template}**: Update template
- **DELETE /quiz-templates/{template}**: Delete template
- **GET /quiz-templates/{template}/take**: Generate quiz from template

### AI-Powered Quiz Attempts
- **GET /question-banks/{bank}/start**: Start bank-based quiz
- **POST /bank-quiz/{attempt}/submit**: Submit quiz answers
- **GET /bank-quiz/{attempt}/result**: View quiz results
- **GET /my-bank-attempts**: List user's quiz attempts

### Analytics Dashboard
- **GET /question-banks/{bank}/analytics**: Comprehensive analytics
- **GET /{user}/attemptedQuiz**: User quiz history

### Traditional Quizzes (Legacy)
- **GET /quizzes**: List all quizzes
- **GET /quizzes/{quiz}**: Show specific quiz
- **GET /quizzes/create**: Create quiz form
- **POST /quizzes**: Store new quiz
- **GET /quizzes/{quiz}/edit**: Edit quiz form
- **PUT/PATCH /quizzes/{quiz}**: Update quiz
- **DELETE /quizzes/{quiz}**: Delete quiz
- **GET /quiz/{quiz}/attempt**: Attempt quiz
- **POST /quiz/{quiz}/submit**: Submit quiz answers

### Categories
- **GET /categories**: List all categories
- **GET /categories/{category}**: Show category details
- **GET /categories/create**: Create category form
- **POST /categories**: Store new category
- **GET /categories/{category}/edit**: Edit category form
- **PUT/PATCH /categories/{category}**: Update category
- **DELETE /categories/{category}**: Delete category

### Administration
- **GET /admin/requests**: List registration requests
- **POST /admin/approve/{user}**: Approve user registration

## SEO-Friendly URLs
The system uses SEO-friendly URLs to improve search engine rankings and user experience. URLs are generated using slugs instead of IDs, making them more readable and descriptive.

### Examples
- **Category URL**: `/category/laravel/quizzes` instead of `/category/1/quizzes`
- **Quiz URL**: `/quiz/laravel-basics/attempt` instead of `/quiz/1/attempt`

## Error Handling
The system includes comprehensive error handling to provide a smooth user experience. Errors are logged and presented to users in a user-friendly manner.

## Security
The system implements several security measures to protect user data and ensure the integrity of the application:

- **Authentication**: Users must log in to access certain features.
- **Authorization**: Users can only access features and data they are authorized to use.
- **Data Validation**: Input data is validated to prevent malicious input.
- **Password Hashing**: User passwords are hashed to protect them in case of a data breach.

## Testing
The system includes automated tests to ensure the application functions as expected. Tests cover various aspects of the application, including:

- **Unit Tests**: Test individual components of the application.
- **Feature Tests**: Test the application's features and user flows.
- **Integration Tests**: Test the interaction between different components of the application.

## Deployment
The system can be deployed to various environments, including:

- **Local Development**: For testing and development purposes.
- **Staging**: For testing the application in a production-like environment.
- **Production**: For deploying the application to end-users.

## 🚀 Implemented Features (Phase 4)

### ✅ AI-Powered Evaluation
- Google Gemini API integration for intelligent question assessment
- Contextual feedback for fill-in-the-blank and coding questions
- Confidence scoring and detailed explanations

### ✅ Advanced Analytics
- Real-time performance metrics and trend analysis
- Question difficulty assessment and success rate tracking
- User engagement analytics and completion statistics
- Interactive dashboards with data visualization

### ✅ Question Bank System
- Polymorphic question types (MCQ, Fill-blank, Code)
- Difficulty classification and filtering
- Bulk question management and organization
- Advanced search and categorization

### ✅ Quiz Templates
- Configurable quiz generation parameters
- Question distribution controls (type and difficulty ratios)
- Public template sharing and community features
- Usage tracking and popularity metrics

### ✅ Performance Optimization
- Intelligent caching system for question retrieval
- Database indexing for high-performance queries
- Scalable architecture for large user bases
- Optimized quiz generation algorithms

## 🔮 Future Enhancements

### Phase 5+ Potential Features
- **Mobile App**: React Native mobile application
- **Real-time Collaboration**: Live quiz creation with multiple authors
- **Advanced AI Features**: Predictive difficulty adjustment, personalized learning paths
- **Integration APIs**: LMS integration (Moodle, Canvas), webhook support
- **Gamification**: Badges, leaderboards, achievement systems
- **Multi-language Support**: Internationalization and localization
- **Video Questions**: Multimedia question types with video content
- **Advanced Reporting**: Custom report generation and export (PDF, Excel)
- **API Rate Limiting**: Advanced throttling and usage analytics

## Conclusion
This technical documentation provides a comprehensive overview of the Quiz System, including its architecture, data flow, and user roles. It is intended for developers and technical users who want to understand the inner workings of the system and how to extend or modify it.

For more information, please refer to the source code and comments within the application.