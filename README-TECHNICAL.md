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

## Database Schema

### Tables

#### Users

- **id**: Unique identifier for the user.
- **name**: User's name.
- **email**: User's email address.
- **password**: User's password (hashed for security).
- **role**: User's role (user or admin).

#### Categories

- **id**: Unique identifier for the category.
- **name**: Category name.
- **slug**: SEO-friendly URL slug for the category.
- **description**: Category description.
- **creator**: User who created the category.

#### Quizzes

- **id**: Unique identifier for the quiz.
- **title**: Quiz title.
- **slug**: SEO-friendly URL slug for the quiz.
- **user_id**: User who created the quiz.
- **category_id**: Category of the quiz.

#### Questions

- **id**: Unique identifier for the question.
- **quiz_id**: Quiz the question belongs to.
- **question_text**: Question text.
- **question_type**: Type of question (`mcq`, `fill_blank`, `code`).
- **a, b, c, d**: Answer options (for MCQ).
- **correct_answer**: Correct answer or valid code solution.
- **hint**: Evaluation tip or code keyword requirements for AI.

#### Results

- **id**: Unique identifier for the result.
- **user_id**: User who attempted the quiz.
- **quiz_id**: Quiz that was attempted.
- **total_questions**: Total questions.
- **correct_answers**: Correct answers.
- **score_percentage**: User's score as a percentage.
- **user_answers**: JSON object containing user responses and AI evaluations.
- **status**: Evaluation status (`pending`, `completed`).

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

## AI Integration & Smart Grading

### Google Gemini Integration

The system integrates with Google Gemini (via `gemini-2.5-flash`) for two primary purposes:

1. **Question Generation**: Located in `QuestionBankService`, it parses a topic and generates structured JSON questions including MCQs, Fill-in-blanks, and Code challenges.
2. **Smart Code Evaluation**: Located in `GeminiService.evaluateCode`, it performs a logical analysis of user-submitted code.

### Hybrid Evaluation Logic

To optimize performance and API usage, code questions follow a 3-step check:

1. **Empty Check**: Instant failure if no code is submitted.
2. **Fast Check**: A "Normalized" comparison that strips PHP tags, comments, and whitespace to see if the logic matches the reference exactly.
3. **Smart AI Check**: If the fast check fails, the AI evaluates the logic (e.g., verifying that different variable names like `$i` vs `$index` perform the same task).

### Asynchronous Evaluation Flow

To avoid blocking the user during heavy AI processing:

1. User clicks **Submit**.
2. System saves the results as `pending` and redirects to the results page.
3. The result page shows a loading skeleton and triggers an **AJAX POST** to `/quiz/result/{result}/evaluate`.
4. The backend processes AI evaluations and updates the result record.
5. The UI dynamically re-renders the final score and feedback once complete.

## Instructor Approval Workflow

To ensure quality, users cannot become Instructors (Admins) immediately.

1. **Registration**: When a user selects "Instructor" during registration, their account is created with `role: 'pending_admin'`.
2. **Notifications**: The system automatically identifies all existing users with the `admin` role and sends them an **AdminRequestMail** notification via SMTP (configured with Mailpit for development).
3. **Review**: Admins can click the link in the email or navigate to the **"Admin Access Requests"** dashboard.
4. **Action**:
    - **Approve**: Updates the user's role to `admin`, granting them access to quiz creation and AI generation tools.
    - **Reject**: Reverts the user's role to `user`, allowing them only to attempt quizzes.

## API Endpoints

### Authentication

- **POST /register**: Register a new user (Role can be `user` or `admin`).
- **POST /login**: Log in an existing user.
- **POST /logout**: Log out the current user.

### Quizzes & Evaluation

- **GET /quizzes**: List all quizzes.
- **POST /quiz/{quiz}/submit**: Submit a quiz attempt (sets status to `pending`).
- **GET /quiz/result/{result}**: Show result page (shows skeleton if pending).
- **POST /quiz/result/{result}/evaluate**: Perform AI evaluation (returns JSON).

### Admin / Instructor Tools

- **GET /admin/question-bank**: Access AI generation form.
- **POST /admin/question-bank/generate**: Generate questions via Gemini.
- **GET /admin/requests**: List instructor registration requests.
- **POST /admin/approve/{user}**: Approve instructor request.
- **POST /admin/reject/{user}**: Reject instructor request.

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

## Future Enhancements

Potential future enhancements to the Quiz System include:

- **Quiz Timer**: Add a timer to quizzes to limit the time users have to complete them.
- **Quiz Categories**: Allow users to categorize their quizzes for better organization.
- **Quiz Sharing**: Allow users to share their quizzes with others.
- **Quiz Analytics**: Provide detailed analytics on quiz performance and user engagement.
- **Quiz Export**: Allow users to export their quizzes and results in various formats.

## Deployment to Railway.app (using Nixpacks):

1. **Connect GitHub**: Connect your repository to a new Railway project.
2. **Add PostgreSQL**: Add a PostgreSQL database service. Railway automatically provides the `DATABASE_URL`.
3. **Environment Variables**: In your web service "Variables" tab, add:
    - `APP_KEY`: (Get from your local `.env` or run `php artisan key:generate --show`)
    - `APP_ENV`: `production`
    - `DB_CONNECTION`: `pgsql`
    - `GEMINI_API_KEY`: Your key from Google AI Studio.
    - `NIXPACKS_PHP_POST_INSTALL_COMMAND`: `php artisan migrate --force`
4. **Auto-Deployment**: Railway will detect Laravel, install dependencies, compile assets (`npm run build`), and run migrations automatically.

## Conclusion

This technical documentation provides a comprehensive overview of the Quiz System, including its architecture, data flow, and user roles. It is intended for developers and technical users who want to understand the inner workings of the system and how to extend or modify it.

For more information, please refer to the source code and comments within the application.
