<!DOCTYPE html>
<html>
<head>
    <style>
        .button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h2>New Admin Request</h2>
    <p>A new user has registered and requested **Instructor/Admin** access:</p>
    
    <ul>
        <li><strong>Name:</strong> {{ $user->name }}</li>
        <li><strong>Email:</strong> {{ $user->email }}</li>
    </ul>

    <p>Please log in to the dashboard to approve or reject this request.</p>
    
    <a href="{{ route('admin.requests') }}" class="button">View Requests</a>
</body>
</html>