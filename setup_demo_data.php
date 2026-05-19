<?php
session_start();
include('includes/db.php');

$messages = array();
$errors = array();

if (isset($_POST['generate_demo'])) {
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");
    mysqli_query($conn, "TRUNCATE TABLE presences");
    mysqli_query($conn, "TRUNCATE TABLE inscriptions");
    mysqli_query($conn, "TRUNCATE TABLE sessions");
    mysqli_query($conn, "TRUNCATE TABLE formations");
    mysqli_query($conn, "TRUNCATE TABLE utilisateurs");
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");

    $users = array(
        array('Yosser Admin', 'yosser@gmail.com', 'admin', 'Yosser21042004'),
        array('Amine Formateur', 'amine@gmail.com', 'formateur', 'Yosser21042004'),
        array('IChrak Apprenant', 'ichrak@gmail.com', 'apprenant', 'Yosser21042004'),
        array('Omar Trabelsi', 'omar.apprenant@formatrack.com', 'apprenant', 'Apprenant@123'),
        array('Nour Mansouri', 'nour.apprenant@formatrack.com', 'apprenant', 'Apprenant@123'),
        array('Karim Kefi', 'karim.apprenant@formatrack.com', 'apprenant', 'Apprenant@123'),
        array('Rim Dhaouadi', 'rim.apprenant@formatrack.com', 'apprenant', 'Apprenant@123')
    );

    $user_ids = array();
    foreach ($users as $u) {
        $nom = mysqli_real_escape_string($conn, $u[0]);
        $email = mysqli_real_escape_string($conn, $u[1]);
        $role = mysqli_real_escape_string($conn, $u[2]);
        $hash = mysqli_real_escape_string($conn, password_hash($u[3], PASSWORD_DEFAULT));
        $sql = "INSERT INTO utilisateurs (nom, email, mot_de_passe, role, created_at)
                VALUES ('$nom', '$email', '$hash', '$role', NOW())";
        if (mysqli_query($conn, $sql)) {
            $user_ids[$u[1]] = mysqli_insert_id($conn);
        } else {
            $errors[] = "Erreur insertion utilisateur: " . mysqli_error($conn);
        }
    }

    if (count($errors) === 0) {
        $formations = array(
            array('Formation PHP MySQL', 'Developpement web back-end avec PHP et MySQL.', '2026-05-01', '2026-07-01', $user_ids['amine@gmail.com']),
            array('Formation JavaScript Front', 'Validation de formulaires et manipulation DOM.', '2026-05-10', '2026-06-30', $user_ids['amine@gmail.com']),
            array('Bootstrap UI Pro', 'Interfaces responsives avec Bootstrap 5.', '2026-05-15', '2026-06-20', $user_ids['amine@gmail.com'])
        );

        $formation_ids = array();
        foreach ($formations as $f) {
            $intitule = mysqli_real_escape_string($conn, $f[0]);
            $description = mysqli_real_escape_string($conn, $f[1]);
            $date_debut = mysqli_real_escape_string($conn, $f[2]);
            $date_fin = mysqli_real_escape_string($conn, $f[3]);
            $formateur_id = (int) $f[4];
            $sql = "INSERT INTO formations (intitule, description, date_debut, date_fin, formateur_id)
                    VALUES ('$intitule', '$description', '$date_debut', '$date_fin', $formateur_id)";
            if (mysqli_query($conn, $sql)) {
                $formation_ids[] = mysqli_insert_id($conn);
            } else {
                $errors[] = "Erreur insertion formation: " . mysqli_error($conn);
            }
        }

        $sessions_data = array(
            array($formation_ids[0], $user_ids['amine@gmail.com'], '2026-05-11', '09:00:00', '12:00:00', 'Salle A1'),
            array($formation_ids[0], $user_ids['amine@gmail.com'], '2026-05-18', '09:00:00', '12:00:00', 'Salle A1'),
            array($formation_ids[1], $user_ids['amine@gmail.com'], '2026-05-12', '14:00:00', '17:00:00', 'Salle B2'),
            array($formation_ids[1], $user_ids['amine@gmail.com'], '2026-05-19', '14:00:00', '17:00:00', 'Salle B2'),
            array($formation_ids[2], $user_ids['amine@gmail.com'], '2026-05-13', '10:00:00', '12:00:00', 'Lab UI'),
            array($formation_ids[2], $user_ids['amine@gmail.com'], '2026-05-20', '10:00:00', '12:00:00', 'Lab UI')
        );

        $session_ids = array();
        foreach ($sessions_data as $s) {
            $formation_id = (int) $s[0];
            $formateur_id = (int) $s[1];
            $date = mysqli_real_escape_string($conn, $s[2]);
            $hd = mysqli_real_escape_string($conn, $s[3]);
            $hf = mysqli_real_escape_string($conn, $s[4]);
            $salle = mysqli_real_escape_string($conn, $s[5]);
            $sql = "INSERT INTO sessions (formation_id, formateur_id, date, heure_debut, heure_fin, salle)
                    VALUES ($formation_id, $formateur_id, '$date', '$hd', '$hf', '$salle')";
            if (mysqli_query($conn, $sql)) {
                $session_ids[] = mysqli_insert_id($conn);
            } else {
                $errors[] = "Erreur insertion session: " . mysqli_error($conn);
            }
        }

        $apprenants = array(
            $user_ids['ichrak@gmail.com'],
            $user_ids['omar.apprenant@formatrack.com'],
            $user_ids['nour.apprenant@formatrack.com'],
            $user_ids['karim.apprenant@formatrack.com'],
            $user_ids['rim.apprenant@formatrack.com']
        );

        foreach ($apprenants as $idx => $apprenant_id) {
            $fid1 = (int) $formation_ids[0];
            $fid2 = (int) $formation_ids[1];
            $fid3 = (int) $formation_ids[2];
            mysqli_query($conn, "INSERT INTO inscriptions (utilisateur_id, formation_id, date_inscription) VALUES ($apprenant_id, $fid1, NOW())");
            mysqli_query($conn, "INSERT INTO inscriptions (utilisateur_id, formation_id, date_inscription) VALUES ($apprenant_id, $fid2, NOW())");
            if ($idx % 2 === 0) {
                mysqli_query($conn, "INSERT INTO inscriptions (utilisateur_id, formation_id, date_inscription) VALUES ($apprenant_id, $fid3, NOW())");
            }
        }

        $statuts = array('present', 'retard', 'absent');
        foreach ($session_ids as $i => $sid) {
            foreach ($apprenants as $j => $uid) {
                $statut = $statuts[($i + $j) % 3];
                $commentaire = $statut === 'absent' ? 'Absence justifiee' : ($statut === 'retard' ? 'Retard de 10 min' : 'Ponctuel');
                $commentaire_safe = mysqli_real_escape_string($conn, $commentaire);
                mysqli_query($conn, "INSERT INTO presences (session_id, utilisateur_id, statut, commentaire, date_saisie)
                                     VALUES ($sid, $uid, '$statut', '$commentaire_safe', NOW())");
            }
        }
    }

    if (count($errors) === 0) {
        $messages[] = 'Donnees demo generees avec succes.';
        $messages[] = 'Admin: yosser@gmail.com / Yosser21042004';
        $messages[] = 'Formateur: amine@gmail.com / Yosser21042004';
        $messages[] = 'Apprenant: ichrak@gmail.com / Yosser21042004';
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Demo Data - FormaTrack</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 mb-3">Generateur de donnees demo - FormaTrack</h1>
                    <p class="text-muted">Ce bouton reinitialise et remplit toute la base avec des donnees realistes de test.</p>
                    <form action="" method="post">
                        <button type="submit" name="generate_demo" class="btn btn-primary">Generer les donnees demo</button>
                        <a href="index.php" class="btn btn-secondary">Retour au site</a>
                    </form>

                    <?php if (count($messages) > 0) { ?>
                        <div class="alert alert-success mt-3">
                            <?php foreach ($messages as $m) { ?>
                                <div><?php echo htmlspecialchars($m); ?></div>
                            <?php } ?>
                        </div>
                    <?php } ?>

                    <?php if (count($errors) > 0) { ?>
                        <div class="alert alert-danger mt-3">
                            <?php foreach ($errors as $e) { ?>
                                <div><?php echo htmlspecialchars($e); ?></div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
