<!DOCTYPE html>
<html>
<head>
    <title>Test Page</title>
</head>
<body>
    <h1>Route Test</h1>

    <form action="{{ route('test.call') }}" method="POST">
        @csrf
        <button type="submit">Click Me</button>
    </form>
</body>
</html>
