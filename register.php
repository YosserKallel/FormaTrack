<?php
session_start();
include('includes/config.php');
include('includes/db.php');

$errors = array(
    'nom' => '',
    'email' => '',
    'mot_de_passe' => '',
    'confirmation' => ''
);
$general_error = '';
$nom = '';
$email = '';

if (isset($_POST['register'])) {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $mot_de_passe = trim($_POST['mot_de_passe']);
    $confirmation = trim($_POST['confirmation']);

    if ($nom === '') {
        $errors['nom'] = 'Le nom est obligatoire.';
    }

    if ($email === '') {
        $errors['email'] = "L'email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email invalide.';
    }

    if (strlen($mot_de_passe) < 6) {
        $errors['mot_de_passe'] = 'Le mot de passe doit contenir au moins 6 caracteres.';
    }

    if ($confirmation !== $mot_de_passe) {
        $errors['confirmation'] = 'La confirmation ne correspond pas.';
    }

    if ($errors['nom'] === '' && $errors['email'] === '' && $errors['mot_de_passe'] === '' && $errors['confirmation'] === '') {
        $email_safe = mysqli_real_escape_string($conn, $email);
        $check_sql = "SELECT id FROM utilisateurs WHERE email = '$email_safe' LIMIT 1";
        $check_result = mysqli_query($conn, $check_sql);

        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $errors['email'] = 'Cet email existe deja.';
        } elseif (!$check_result) {
            $general_error = "Erreur SQL (verification email) : " . mysqli_error($conn);
        } else {
            $nom_safe = mysqli_real_escape_string($conn, $nom);
            $password_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            $password_safe = mysqli_real_escape_string($conn, $password_hash);
            $insert_sql = "INSERT INTO utilisateurs (nom, email, mot_de_passe, role, created_at) VALUES ('$nom_safe', '$email_safe', '$password_safe', 'apprenant', NOW())";
            $insert_result = mysqli_query($conn, $insert_sql);

            if ($insert_result) {
                $_SESSION['success_message'] = 'Inscription reussie. Vous pouvez maintenant vous connecter.';
                header('Location: ' . base_url('login.php'));
                exit();
            } else {
                $general_error = "Erreur lors de l'inscription : " . mysqli_error($conn);
            }
        }
    }
}

$page_title = 'Inscription';
include('includes/header.php');
?>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="card shadow-sm auth-card">
            <div class="card-body">
                <h2 class="h4 mb-3">Creer un compte apprenant</h2>
                <?php if ($general_error !== '') { ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($general_error); ?></div>
                <?php } ?>
                <form name="formRegister" action="" method="post" onsubmit="return valider(this)" data-loading="true">
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom</label>
                        <input type="text" name="nom" id="nom" placeholder="Votre nom complet" class="form-control <?php echo $errors['nom'] !== '' ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($nom); ?>">
                        <div id="nom_error" class="invalid-feedback"><?php echo $errors['nom']; ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="text" name="email" id="email" placeholder="exemple@email.com" class="form-control <?php echo $errors['email'] !== '' ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>">
                        <div id="email_error" class="invalid-feedback"><?php echo $errors['email']; ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="mot_de_passe" class="form-label">Mot de passe</label>
                        <div class="input-group">
                            <input type="password" name="mot_de_passe" id="mot_de_passe" placeholder="Minimum 6 caracteres" class="form-control <?php echo $errors['mot_de_passe'] !== '' ? 'is-invalid' : ''; ?>">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('mot_de_passe', this)">Afficher</button>
                        </div>
                        <div id="mot_de_passe_error" class="invalid-feedback"><?php echo $errors['mot_de_passe']; ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="confirmation" class="form-label">Confirmation mot de passe</label>
                        <div class="input-group">
                            <input type="password" name="confirmation" id="confirmation" placeholder="Retapez le mot de passe" class="form-control <?php echo $errors['confirmation'] !== '' ? 'is-invalid' : ''; ?>">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirmation', this)">Afficher</button>
                        </div>
                        <div id="confirmation_error" class="invalid-feedback"><?php echo $errors['confirmation']; ?></div>
                    </div>

                    <button type="submit" name="register" class="btn btn-success w-100">S'inscrire</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function resetErreur(idInput, idError) {
    document.getElementById(idInput).classList.remove('is-invalid');
    document.getElementById(idError).innerHTML = '';
}

function afficherErreur(idInput, idError, message) {
    document.getElementById(idInput).classList.add('is-invalid');
    document.getElementById(idError).innerHTML = message;
}

function valider(formulaire) {
    let valide = true;
    const nom = document.getElementById('nom').value.trim();
    const email = document.getElementById('email').value.trim();
    const motDePasse = document.getElementById('mot_de_passe').value.trim();
    const confirmation = document.getElementById('confirmation').value.trim();
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    resetErreur('nom', 'nom_error');
    resetErreur('email', 'email_error');
    resetErreur('mot_de_passe', 'mot_de_passe_error');
    resetErreur('confirmation', 'confirmation_error');

    if (nom === '') {
        afficherErreur('nom', 'nom_error', 'Le nom est obligatoire.');
        valide = false;
    }

    if (email === '') {
        afficherErreur('email', 'email_error', "L'email est obligatoire.");
        valide = false;
    } else if (!emailPattern.test(email)) {
        afficherErreur('email', 'email_error', 'Format email invalide.');
        valide = false;
    }

    if (motDePasse.length < 6) {
        afficherErreur('mot_de_passe', 'mot_de_passe_error', 'Le mot de passe doit contenir au moins 6 caracteres.');
        valide = false;
    }

    if (confirmation !== motDePasse) {
        afficherErreur('confirmation', 'confirmation_error', 'La confirmation ne correspond pas.');
        valide = false;
    }

    formulaire.dataset.validationOk = valide ? '1' : '0';
    return valide;
}

function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = 'Masquer';
    } else {
        input.type = 'password';
        btn.innerHTML = 'Afficher';
    }
}
</script>

<?php include('includes/footer.php'); ?>
