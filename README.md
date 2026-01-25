# Quiz System

## Overview

This is a simple quiz system where users can create, take, and manage quizzes. It's designed to help users test their knowledge on various topics and track their progress.

## Features

### For Users

- **Create Quizzes**: Users can create their own quizzes on different topics.
- **Take Quizzes**: Users can attempt quizzes created by others.
- **View Results**: After completing a quiz, users can see their scores and review their answers.
- **Track Progress**: Users can view their past quiz attempts and see how they've improved over time.

### For Instructors / Admins

- **AI Question Generator**: Generate high-quality questions for any topic using Google Gemini AI.
- **Manage Users**: Admins can approve or reject instructor registrations.
- **Manage Quizzes**: Use varied question types (MCQ, Fill-in-the-blank, and Code-based).
- **Smart Grading**: AI-powered evaluation for code-based questions that understands logic beyond simple text matching.

## How to Use

### Getting Started

1. **Register**: Create an account to start using the quiz system.
2. **Login**: Use your credentials to log in.
3. **Explore**: Browse through available quizzes or create your own.

### Creating a Quiz

1. Go to the "Quizzes" section.
2. Click on "Create Quiz".
3. Fill in the quiz details, such as the title and category.
4. Add questions to your quiz.
5. Save your quiz.

### Taking a Quiz

1. Go to the "Quizzes" section.
2. Choose a quiz you want to take.
3. Click on "Attempt Quiz".
4. Answer the questions and submit your answers.
5. View your results and see how you did.

### Viewing Your Progress

1. Go to your profile.
2. Click on "User Quiz Attempts".
3. See a list of all the quizzes you've taken and your scores.

## Required Services & Setup

To run this project fully, you will need the following services configured:

1. **Google Gemini API**:
    - Required for AI Question Generation and Smart Code Grading.
    - Get an API key from [Google AI Studio](https://aistudio.google.com/).
    - Add it to your `.env` as `GEMINI_API_KEY=your_key_here`.

2. **Email Server (SMTP)**:
    - Required for Admin access notifications.
    - For **Development**: Use [Mailpit](https://github.com/axllent/mailpit) (included in Laravel Herd).
    - For **Production**: Configure an SMTP service like Mailgun, Postmark, or Amazon SES.

3. **Database**:
    - The project is configured for **SQLite** by default (easy to set up), but also supports **MySQL** or **PostgreSQL**.

## Installation

### Requirements

- A web server (e.g., Apache, Nginx)
- PHP (version 8.0 or higher)
- A database (e.g., MySQL, SQLite)

### Steps

1. **Download the Code**: Get the latest version of the quiz system.
2. **Set Up the Database**: Create a database and update the configuration file with your database details.
3. **Run Migrations**: Set up the database tables by running the migration commands.
4. **Start the Server**: Launch the application on your web server.
5. **Access the System**: Open your browser and go to the application URL.

## Support

If you have any questions or need help, feel free to reach out. We're here to assist you!

## License

This project is open-source and available for anyone to use and modify.

---
