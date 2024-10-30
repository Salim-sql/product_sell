<?php
// Include the database connection file
include_once '../db.inc/connection.php';

// Handle form submission to add a new client
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_client = $_POST['nom_client'];
    $num_client = $_POST['num_client'];
    $localisation = $_POST['localisation'];

    // Validate form fields (simple validation)
    if (!empty($nom_client) && !empty($num_client) && !empty($localisation)) {
        // Insert new client into the database
        $insert_query = 'INSERT INTO client (nom_client, num_client, localisation) 
                         VALUES (:nom_client, :num_client, :localisation)';
        $stmt = $pdo->prepare($insert_query);
        $stmt->bindParam(':nom_client', $nom_client);
        $stmt->bindParam(':num_client', $num_client);
        $stmt->bindParam(':localisation', $localisation);

        // Execute the query
        if ($stmt->execute()) {
            echo "Client added successfully!";
            header("Location: ../affichage/client.php"); // Redirect to client list or other page
            exit();
        } else {
            echo "Failed to add client!";
        }
    } else {
        echo "All fields are required!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/style.css">
    <title>Ajouter Client</title>
    <style>
        form {
            max-width: 400px;
            margin: 15vh auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        input[type="text"],
        input[type="tel"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 10px;
        }

        button[type="button"] {
            background-color: #007bff;
        }

        button[type="button"] a {
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <form method="POST">
        <h2>Ajouter Client</h2>

        <label for="nom_client">Nom du Client:</label>
        <input type="text" name="nom_client" id="nom_client" required>

        <label for="num_client">Numéro de Téléphone:</label>
        <input type="tel" name="num_client" id="num_client" required>

        <label for="localisation">Localisation:</label>
        <input type="text" name="localisation" id="localisation" required>

        <button type="submit">Ajouter le client</button>
        <button type="button"><a href="../affichage/client.php">Annuler</a></button>
    </form>
</body>
</html>
