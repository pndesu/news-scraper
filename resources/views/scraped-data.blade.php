<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scraped Data</title>
</head>
<body>
    <h1>News Updates</h1>
    <table border="1">
        <tr>
            <th>Category</th>
            <th>Title</th>
            <th>Link</th>
            <th>Summary</th>
        </tr>
        @foreach ($data as $item)
            <tr>
                <td>{{ $item['category']}}</td>
                <td>{{ $item['title'] }}</td>
                <td><a href="{{ $item['link'] }}">View</a></td>
                <td>{{ $item['summary']}}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>