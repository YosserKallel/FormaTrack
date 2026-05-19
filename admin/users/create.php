<?php
session_start();
include('../../includes/config.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . base_url('login.php'));
    exit();
}
include('../../includes/db.php');

$errors = array(
    'nom' => '',
    'email' => '',
    'mot_de_passe' => '',
    'role' => ''
);
$nom = '';
$email = '';
$role = 'apprenant';
$general_error = '';

if (isset($_POST['save_user'])) {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $mot_de_passe = trim($_POST['mot_de_passe']);
    $role = trim($_POST['role']);

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

    if ($role !== 'apprenant' && $role !== 'formateur' && $role !== 'admin') {
        $errors['role'] = 'Role invalide.';
    }

    if ($errors['nom'] === '' && $errors['email'] === '' && $errors['mot_de_passe'] === '' && $errors['role'] === '') {
        $email_safe = mysqli_real_escape_string($conn, $email);
        $check_sql = "SELECT id FROM utilisateurs WHERE email = '$email_safe' LIMIT 1";
        $check_result = mysqli_query($conn, $check_sql);

        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $errors['email'] = 'Cet email existe deja.';
        } elseif (!$check_result) {
            $general_error = 'Erreur SQL : ' . mysqli_error($conn);
        } else {
            $nom_safe = mysqli_real_escape_string($conn, $nom);
            $password_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            $password_safe = mysqli_real_escape_string($conn, $password_hash);
            $role_safe = mysqli_real_escape_string($conn, $role);

            $insert_sql = "INSERT INTO utilisateurs (nom, email, mot_de_passe, role, created_at) VALUES ('$nom_safe', '$email_safe', '$password_safe', '$role_safe', NOW())";
            $insert_result = mysqli_query($conn, $insert_sql);

            if ($insert_result) {
                $_SESSION['success_message'] = 'Utilisateur ajoute avec succes.';
                header('Location: ' . base_url('admin/users/list.php'));
                exit();
            } else {
                $general_error = "Erreur lors de l'ajout : " . mysqli_error($conn);
            }
        }
    }
}

$page_title = 'Ajouter utilisateur';
include('../../includes/header.php');
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="card shadow-sm auth-card">
            <div class="card-body">
                <h2 class="h4 mb-3">Ajouter un utilisateur</h2>
                <?php if ($general_error !== '') { ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($general_error); ?></div>
                <?php } ?>
                <form action="" method="post">
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom</label>
                        <input type="text" name="nom" id="nom" class="form-control <?php echo $errors['nom'] !== '' ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($nom); ?>">
                        <div class="invalid-feedback"><?php echo $errors['nom']; ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="text" name="email" id="email" class="form-control <?php echo $errors['email'] !== '' ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>">
                        <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="mot_de_passe" class="form-label">Mot de passe</label>
                        <input type="password" name="mot_de_passe" id="mot_de_passe" class="form-control <?php echo $errors['mot_de_passe'] !== '' ? 'is-invalid' : ''; ?>">
                        <div class="invalid-feedback"><?php echo $errors['mot_de_passe']; ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select name="role" id="role" class="form-select <?php echo $errors['role'] !== '' ? 'is-invalid' : ''; ?>">
                            <option value="apprenant" <?php echo $role === 'apprenant' ? 'selected' : ''; ?>>apprenant</option>
                            <option value="formateur" <?php echo $role === 'formateur' ? 'selected' : ''; ?>>formateur</option>
                            <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>admin</option>
                        </select>
                        <div class="invalid-feedback"><?php echo $errors['role']; ?></div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" name="save_user" class="btn btn-success">Enregistrer</button>
                        <a href="<?php echo base_url('admin/users/list.php'); ?>" class="btn btn-secondary">Retour</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>
