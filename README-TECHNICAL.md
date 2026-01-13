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
- **user_id**: User who created the quiz (foreign key to Users table).
- **category_id**: Category of the quiz (foreign key to Categories table).

#### Questions
- **id**: Unique identifier for the question.
- **quiz_id**: Quiz the question belongs to (foreign key to Quizzes table).
- **question_text**: Question text.
- **slug**: SEO-friendly URL slug for the question.
- **a, b, c, d**: Answer options.
- **correct_answer**: Correct answer option.

#### Results
- **id**: Unique identifier for the result.
- **user_id**: User who attempted the quiz (foreign key to Users table).
- **quiz_id**: Quiz that was attempted (foreign key to Quizzes table).
- **total_questions**: Total number of questions in the quiz.
- **correct_answers**: Number of correct answers.
- **score_percentage**: User's score as a percentage.

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

## API Endpoints

### Authentication
- **POST /register**: Register a new user.
- **POST /login**: Log in an existing user.
- **POST /logout**: Log out the current user.

### Quizzes
- **GET /quizzes**: List all quizzes.
- **GET /quizzes/{quiz}**: Show a specific quiz.
- **GET /quizzes/create**: Show the form to create a new quiz.
- **POST /quizzes**: Store a new quiz.
- **GET /quizzes/{quiz}/edit**: Show the form to edit a quiz.
- **PUT/PATCH /quizzes/{quiz}**: Update a quiz.
- **DELETE /quizzes/{quiz}**: Delete a quiz.
- **GET /quiz/{quiz}/attempt**: Attempt a quiz.
- **POST /quiz/{quiz}/submit**: Submit a quiz attempt.

### Categories
- **GET /categories**: List all categories.
- **GET /categories/{category}**: Show a specific category.
- **GET /categories/create**: Show the form to create a new category.
- **POST /categories**: Store a new category.
- **GET /categories/{category}/edit**: Show the form to edit a category.
- **PUT/PATCH /categories/{category}**: Update a category.
- **DELETE /categories/{category}**: Delete a category.

### Questions
- **GET /questions**: List all questions.
- **GET /questions/{question}**: Show a specific question.
- **GET /questions/create**: Show the form to create a new question.
- **POST /questions**: Store a new question.
- **GET /questions/{question}/edit**: Show the form to edit a question.
- **PUT/PATCH /questions/{question}**: Update a question.
- **DELETE /questions/{question}**: Delete a question.

### Results
- **GET /{user}/attemptedQuiz**: List all quiz attempts for a user.

### Admin
- **GET /admin/requests**: List all user registration requests.
- **POST /admin/approve/{user}**: Approve a user registration request.

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

## Conclusion
This technical documentation provides a comprehensive overview of the Quiz System, including its architecture, data flow, and user roles. It is intended for developers and technical users who want to understand the inner workings of the system and how to extend or modify it.

For more information, please refer to the source code and comments within the application.