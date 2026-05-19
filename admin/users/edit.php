<?php
session_start();
include('../../includes/config.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . base_url('login.php'));
    exit();
}
include('../../includes/db.php');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    $_SESSION['error_message'] = 'ID utilisateur invalide.';
    header('Location: ' . base_url('admin/users/list.php'));
    exit();
}

$errors = array(
    'nom' => '',
    'email' => '',
    'role' => '',
    'mot_de_passe' => ''
);
$nom = '';
$email = '';
$role = 'apprenant';
$general_error = '';

$select_sql = "SELECT id, nom, email, role FROM utilisateurs WHERE id = $id LIMIT 1";
$select_result = mysqli_query($conn, $select_sql);
if (!$select_result || mysqli_num_rows($select_result) !== 1) {
    $_SESSION['error_message'] = 'Utilisateur introuvable.';
    header('Location: ' . base_url('admin/users/list.php'));
    exit();
}
$user = mysqli_fetch_assoc($select_result);
$nom = $user['nom'];
$email = $user['email'];
$role = $user['role'];

if (isset($_POST['update_user'])) {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $mot_de_passe = trim($_POST['mot_de_passe']);

    if ($nom === '') {
        $errors['nom'] = 'Le nom est obligatoire.';
    }

    if ($email === '') {
        $errors['email'] = "L'email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email invalide.';
    }

    if ($role !== 'apprenant' && $role !== 'formateur' && $role !== 'admin') {
        $errors['role'] = 'Role invalide.';
    }

    if ($mot_de_passe !== '' && strlen($mot_de_passe) < 6) {
        $errors['mot_de_passe'] = 'Le mot de passe doit contenir au moins 6 caracteres.';
    }

    if ($errors['nom'] === '' && $errors['email'] === '' && $errors['role'] === '' && $errors['mot_de_passe'] === '') {
        $email_safe = mysqli_real_escape_string($conn, $email);
        $check_sql = "SELECT id FROM utilisateurs WHERE email = '$email_safe' AND id <> $id LIMIT 1";
        $check_result = mysqli_query($conn, $check_sql);

        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $errors['email'] = 'Cet email est deja utilise.';
        } elseif (!$check_result) {
            $general_error = 'Erreur SQL : ' . mysqli_error($conn);
        } else {
            $nom_safe = mysqli_real_escape_string($conn, $nom);
            $role_safe = mysqli_real_escape_string($conn, $role);

            if ($mot_de_passe !== '') {
                $password_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
                $password_safe = mysqli_real_escape_string($conn, $password_hash);
                $update_sql = "UPDATE utilisateurs SET nom = '$nom_safe', email = '$email_safe', role = '$role_safe', mot_de_passe = '$password_safe' WHERE id = $id";
            } else {
                $update_sql = "UPDATE utilisateurs SET nom = '$nom_safe', email = '$email_safe', role = '$role_safe' WHERE id = $id";
            }

            $update_result = mysqli_query($conn, $update_sql);
            if ($update_result) {
                $_SESSION['success_message'] = 'Utilisateur modifie avec succes.';
                header('Location: ' . base_url('admin/users/list.php'));
                exit();
            } else {
                $general_error = 'Erreur lors de la modification : ' . mysqli_error($conn);
            }
        }
    }
}

$page_title = 'Modifier utilisateur';
include('../../includes/header.php');
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="card shadow-sm auth-card">
            <div class="card-body">
                <h2 class="h4 mb-3">Modifier utilisateur</h2>
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
                        <label for="role" class="form-label">Role</label>
                        <select name="role" id="role" class="form-select <?php echo $errors['role'] !== '' ? 'is-invalid' : ''; ?>">
                            <option value="apprenant" <?php echo $role === 'apprenant' ? 'selected' : ''; ?>>apprenant</option>
                            <option value="formateur" <?php echo $role === 'formateur' ? 'selected' : ''; ?>>formateur</option>
                            <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>admin</option>
                        </select>
                        <div class="invalid-feedback"><?php echo $errors['role']; ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="mot_de_passe" class="form-label">Nouveau mot de passe (optionnel)</label>
                        <input type="password" name="mot_de_passe" id="mot_de_passe" class="form-control <?php echo $errors['mot_de_passe'] !== '' ? 'is-invalid' : ''; ?>">
                        <div class="invalid-feedback"><?php echo $errors['mot_de_passe']; ?></div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" name="update_user" class="btn btn-primary">Mettre a jour</button>
                        <a href="<?php echo base_url('admin/users/list.php'); ?>" class="btn btn-secondary">Retour</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>
