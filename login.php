<?php
session_start();
include('includes/config.php');
include('includes/db.php');

$errors = array(
    'email' => '',
    'mot_de_passe' => ''
);
$email = '';
$general_error = '';

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $mot_de_passe = trim($_POST['mot_de_passe']);

    if ($email === '') {
        $errors['email'] = "L'email est obligatoire.";
    }

    if ($mot_de_passe === '') {
        $errors['mot_de_passe'] = 'Le mot de passe est obligatoire.';
    }

    if ($errors['email'] === '' && $errors['mot_de_passe'] === '') {
        $email_safe = mysqli_real_escape_string($conn, $email);
        $sql = "SELECT id, nom, email, mot_de_passe, role FROM utilisateurs WHERE email = '$email_safe' LIMIT 1";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);

            if (password_verify($mot_de_passe, $user['mot_de_passe'])) {
                $_SESSION['id'] = $user['id'];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['success_message'] = 'Connexion reussie.';

                if ($user['role'] === 'apprenant') {
                    header('Location: ' . base_url('apprenant/dashboard.php'));
                    exit();
                } elseif ($user['role'] === 'formateur') {
                    header('Location: ' . base_url('formateur/sessions.php'));
                    exit();
                } elseif ($user['role'] === 'admin') {
                    header('Location: ' . base_url('admin/users/list.php'));
                    exit();
                }
            } else {
                $general_error = 'Email ou mot de passe incorrect.';
            }
        } elseif (!$result) {
            $general_error = 'Erreur SQL (connexion) : ' . mysqli_error($conn);
        } else {
            $general_error = 'Email ou mot de passe incorrect.';
        }
    }
}

$page_title = 'Connexion';
include('includes/header.php');
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm auth-card">
            <div class="card-body">
                <h2 class="h4 mb-3">Connexion</h2>
                <?php if ($general_error !== '') { ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($general_error); ?></div>
                <?php } ?>
                <form name="formLogin" action="" method="post" onsubmit="return valider(this)" data-loading="true">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="text" name="email" id="email" placeholder="exemple@email.com" class="form-control <?php echo $errors['email'] !== '' ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>">
                        <div id="email_error" class="invalid-feedback"><?php echo $errors['email']; ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="mot_de_passe" class="form-label">Mot de passe</label>
                        <div class="input-group">
                            <input type="password" name="mot_de_passe" id="mot_de_passe" placeholder="Entrez votre mot de passe" class="form-control <?php echo $errors['mot_de_passe'] !== '' ? 'is-invalid' : ''; ?>">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('mot_de_passe', this)">Afficher</button>
                        </div>
                        <div id="mot_de_passe_error" class="invalid-feedback"><?php echo $errors['mot_de_passe']; ?></div>
                    </div>

                    <button type="submit" name="login" class="btn btn-primary w-100">Se connecter</button>
                </form>
                <div class="text-center mt-3">
                    <small class="text-muted">Pas encore de compte ? <a href="<?php echo base_url('register.php'); ?>">Inscrivez-vous</a></small>
                </div>
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
    const email = document.getElementById('email').value.trim();
    const motDePasse = document.getElementById('mot_de_passe').value.trim();

    resetErreur('email', 'email_error');
    resetErreur('mot_de_passe', 'mot_de_passe_error');

    if (email === '') {
        afficherErreur('email', 'email_error', "L'email est obligatoire.");
        valide = false;
    }

    if (motDePasse === '') {
        afficherErreur('mot_de_passe', 'mot_de_passe_error', 'Le mot de passe est obligatoire.');
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
