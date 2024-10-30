<?php
// Include the database connection file
include_once '../db.inc/connection.php';

// Check if the 'id' parameter is set in the URL
if (isset($_GET['id'])) {
    // Get the id of the fournisseur to modify
    $nom_fournisseur = $_GET['id'];

    // Fetch the existing data of the fournisseur
    $query = 'SELECT * FROM fournisseur WHERE nom_fournisseur = :nom_fournisseur';
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nom_fournisseur', $nom_fournisseur);
    $stmt->execute();
    $fournisseurs = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$fournisseurs) {
        echo "fournisseur not found!";
        exit;
    }

    // Check if the form is submitted to update the fournisseur
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Get the updated form data
        $new_nom_fournisseur = $_POST['nom_fournisseur'];
        $num_fournisseur = $_POST['num_fournisseur'];
        $localisation = $_POST['localisation'];

        // Update the fournisseur in the database
        $update_query = 'UPDATE fournisseur SET 
                            nom_fournisseur = :new_nom_fournisseur,
                            num_fournisseur = :num_fournisseur,
                            localisation = :localisation
                        WHERE nom_fournisseur = :nom_fournisseur';
        $update_stmt = $pdo->prepare($update_query);

        // Bind the parameters
        $update_stmt->bindParam(':new_nom_fournisseur', $new_nom_fournisseur);
        $update_stmt->bindParam(':num_fournisseur', $num_fournisseur);
        $update_stmt->bindParam(':localisation', $localisation);
        $update_stmt->bindParam(':nom_fournisseur', $nom_fournisseur);

        // Execute the update
        if ($update_stmt->execute()) {
            echo "fournisseur updated successfully!";
            header("location: ../affichage/fournisseur.php");
            exit;
        } else {
            echo "Failed to update fournisseur!";
        }
    }
} else {
    echo "Invalid request.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/style.css">
    <title>Modify fournisseur</title>
    <style>
        form {
            max-width: 400px;
            margin: 200px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        input[type="text"],
        input[type="number"],
        select {
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
    <h2>Modify fournisseur</h2>

        <label for="nom_fournisseur">Nom fournisseur:</label>
        <input type="text" name="nom_fournisseur" value="<?php echo htmlspecialchars($fournisseurs['nom_fournisseur']); ?>" required>
        <br>

        <label for="num_fournisseur">Num fournisseur:</label>
        <input type="text" name="num_fournisseur" value="<?php echo htmlspecialchars($fournisseurs['num_fournisseur']); ?>" required>
        <br>

        <label for="localisation">Localisation:</label>
        <input type="text" name="localisation" value="<?php echo htmlspecialchars($fournisseurs['localisation']); ?>" required>
        <br>

        <button type="submit">Save Changes</button>
        <button type="button"><a href="../affichage/fournisseur.php">Retour</a></button>
    </form>
</body>

</html>