<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quotes List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: auto;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        .export-button {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .export-button:hover {
            background-color: #218838;
        }

        .quote-card {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .quote-text {
            font-size: 1.2rem;
            color: #555;
        }

        .quote-author {
            margin-top: 10px;
            font-style: italic;
            color: #888;
        }

        .quote-tags {
            margin-top: 10px;
            font-size: 0.9rem;
            color: #666;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Quotes</h1>

    <a href="{{ route('quotes.export') }}" class="export-button">📥 Export to Excel</a>

    @foreach ($quotes as $quote)
        <div class="quote-card">
            <div class="quote-text">“{{ $quote->text }}”</div>
            <div class="quote-author">— {{ $quote->author }}</div>
            <div class="quote-tags">
                <strong>Tags:</strong> {{ implode(', ', $quote->tags) }}
            </div>
        </div>
    @endforeach
</div>
</body>
</html>
