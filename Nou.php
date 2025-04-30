<?php
/**
 * Nou.php
 * ───────────────────────────────────────────────
 * Alta de productes a la base de dades la_meva_botiga
 * - GET  ➜  muestra el formulari de creació
 * - POST ➜  valida i insereix el nou producte
 */

require_once 'Connexio.php';
require_once 'Header.php';
require_once 'Footer.php';

class Nou {

    /** @var mysqli */
    private $conn;

    public function __construct() {
        $this->conn = (new Connexio())->obtenirConnexio();
    }

    /** Pinta el formulari */
    public function mostrarFormulari(string $missatge = ''): void {
        $header = new Header();
        $footer = new Footer();
        $categories = $this->obtenirCategories();

        $header->mostrarHeader();        // <head> + navbar

        echo '<div class="container mt-4">';
        echo '  <h2>Afegir nou producte</h2>';

        if ($missatge !== '') {
            echo "<div class=\"alert alert-info\">$missatge</div>";
        }

        echo '  <form method="post" class="row g-3">';
        echo '    <div class="col-md-6">
                    <label class="form-label">Nom</label>
                    <input type="text" name="nom" class="form-control" required>
                  </div>';

        echo '    <div class="col-md-6">
                    <label class="form-label">Preu (€)</label>
                    <input type="number" step="0.01" name="preu" class="form-control" required>
                  </div>';

        echo '    <div class="col-12">
                    <label class="form-label">Descripció</label>
                    <textarea name="descripcio" class="form-control" rows="3" required></textarea>
                  </div>';

        echo '    <div class="col-md-6">
                    <label class="form-label">Categoria</label>
                    <select name="categoria" class="form-select" required>';
        foreach ($categories as $cat) {
            echo "<option value=\"{$cat['id']}\">{$cat['nom']}</option>";
        }
        echo '        </select>
                  </div>';

        echo '    <div class="col-12">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <a href="Principal.php" class="btn btn-secondary">Tornar</a>
                  </div>';
        echo '  </form>';
        echo '</div>';

        $footer->mostrarFooter();
    }

    /** Procesa el POST i insereix el nou producte */
    public function insertar(): void {
        $nom        = $_POST['nom']        ?? '';
        $descripcio = $_POST['descripcio'] ?? '';
        $preu       = $_POST['preu']       ?? '';
        $categoria  = $_POST['categoria']  ?? '';

        // Validació bàsica
        if ($nom === '' || $descripcio === '' || $preu === '' || $categoria === '') {
            $this->mostrarFormulari('Tots els camps són obligatoris.');
            return;
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO productes (nom, descripció, preu, categoria_id) VALUES (?,?,?,?)"
        );
        $stmt->bind_param('ssdi', $nom, $descripcio, $preu, $categoria);

        if ($stmt->execute()) {
            $this->mostrarFormulari('Producte afegit correctament!');
        } else {
            $this->mostrarFormulari('Error en afegir el producte: ' . $stmt->error);
        }
    }

    /** Llegeix les categories per desplegable */
    private function obtenirCategories(): array {
        $res = $this->conn->query("SELECT id, nom FROM categories ORDER BY nom");
        return $res->fetch_all(MYSQLI_ASSOC);
    }
}

/* ────── control directe ────── */
$nou = new Nou();
($_SERVER['REQUEST_METHOD'] === 'POST')
    ? $nou->insertar()
    : $nou->mostrarFormulari();
