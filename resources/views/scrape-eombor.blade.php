<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-ombor Scraper</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        select, input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>e-ombor Data Scraper</h2>
        <form action="{{ route('scrape.eombor.process') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="command_type">Select Command Type:</label>
                <select id="command_type" name="command_type" required>
                    <option value="" disabled selected>Select a command type</option>
                    <option value="scrape_eombor">Scrape Eombor by Tranzid id</option>
                    <option value="scrape_mintrans">Scrape Mintrans by Transport number</option>
                </select>
                <div class="error" id="command_type_error">Please select a command type</div>
            </div>
            <div class="form-group">
                <label for="start_id">Start ID:</label>
                <input type="text" id="start_id" name="start_id" required placeholder="Enter a start ID in format ATXXXXXXXXXXX">
                <div class="error" id="start_id_error">Please enter a valid Start ID</div>
            </div>
            <div class="form-group">
                <label for="count">Count:</label>
                <input type="number" id="count" name="count" required min="1" placeholder="Enter a count of ids">
                <div class="error" id="count_error">Please enter a number greater than 0</div>
            </div>
            <button type="submit">Yuborish</button>
        </form>
    </div>

    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const commandType = document.getElementById('command_type').value;
            const startId = document.getElementById('start_id').value;
            const count = document.getElementById('count').value;
            const commandTypeError = document.getElementById('command_type_error');
            const startIdError = document.getElementById('start_id_error');
            const countError = document.getElementById('count_error');

            commandTypeError.style.display = 'none';
            startIdError.style.display = 'none';
            countError.style.display = 'none';

            if (!commandType) {
                e.preventDefault();
                commandTypeError.style.display = 'block';
            } else if (!/^[A-Z]{2}\d{11}$/.test(startId)) {
                e.preventDefault();
                startIdError.style.display = 'block';
            } else if (count <= 0 || !Number.isInteger(Number(count))) {
                e.preventDefault();
                countError.style.display = 'block';
            }
        });
    </script>
</body>
</html>