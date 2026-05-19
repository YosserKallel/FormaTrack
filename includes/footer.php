</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function masquerAlertes() {
    const alertes = document.querySelectorAll('.auto-hide-alert');
    if (alertes.length > 0) {
        setTimeout(() => {
            alertes.forEach((alerte) => {
                alerte.classList.add('fade');
                setTimeout(() => {
                    alerte.style.display = 'none';
                }, 300);
            });
        }, 5000);
    }
}

function activerSpinnerFormulaire() {
    const formulaires = document.querySelectorAll('form[data-loading="true"]');
    formulaires.forEach((formulaire) => {
        formulaire.addEventListener('submit', function () {
            if (formulaire.dataset.validationOk !== '1') {
                return;
            }
            const bouton = formulaire.querySelector('button[type="submit"]');
            if (bouton) {
                bouton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Traitement...';
            }
            formulaire.dataset.validationOk = '';
        });
    });
}

masquerAlertes();
activerSpinnerFormulaire();
</script>
</body>
</html>
